<?php
// protect script from unauthorized calls
$basePath = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
require_once($basePath . 'includes/configuration.php');
require_once($basePath . 'includes/functions.php');
checkAdminAccess();

//  ##############   Include Files  ################ //
require_once($basePath . 'includes/db_connection.php');
//  ##############  Finish Includes  ############### //

if (isset($configuration['repositories']) && is_array($configuration['repositories']) && count($configuration['repositories'])) {

	$consoleLog = array('Begin of import: ' . date('l jS \of F Y h:i:s A'));
	$error = FALSE;
	$checkDependencies = isset($configuration['dependencies']) && count($configuration['dependencies']) ? TRUE : FALSE;

	// cache existing addon-IDs
	$result = $db->get_results('SELECT id, version, deleted FROM addon');
	$addonCache = array(
		'processed' => array(),
		'existing' => array()
	);
	if ($result) {
		foreach ($result as $addon) {
			$addonCache['existing'][$addon->id] = $addon;
		}
	}

	// import addons from each repository
	foreach($configuration['repositories'] as $repositoryId => $repositoryConfiguration) {
		# Check the XML exists
		if (isset($repositoryConfiguration['xmlUrl']) && $repositoryConfiguration['xmlUrl']) {
			$repositoryXmlUrl = $repositoryConfiguration['xmlUrl'];
		} else {
			$repositoryXmlUrl = $repositoryConfiguration['dataUrl'] . 'addons.xml';
		}

		try {
			$xml = simplexml_load_file($repositoryXmlUrl);
		} catch(Exception $e) {}

		if (!$xml || !isset($xml->addon['id']))	{
			$error = TRUE;
			$consoleLog[] = 'Error while reading repository ' . $repositoryConfiguration['xmlUrl'];
			continue;
		}

		$counter = 0;
		$counterUpdated = 0;
		$counterNewlyAdded = 0;

		// Loop through each addon
		foreach ($xml->addon as $addon)	{
			$counter++;
			$description = '';
			$name = removeKodiFormatting($addon['name']);
			$summary = '';
			$forum = '';
			$source = '';
			$website = '';
			$license = '';
			$author = '';
			$id = (string) $addon['id'];
			$broken = '';
			$extensionPoint = '';
			$contentTypes = array();
			$platforms = array();
			$languages = array();

			$meetsRequirements = TRUE;

			foreach ($addon->children() as $nodeName => $node) {
				// only import if requirements are met
				if ($checkDependencies && $nodeName == 'requires' && $node->children()) {
					foreach($node->children() as $childName => $childNode) {
						if ($childName == 'import') {
							$dependency = (string)$childNode['addon'];
							$version = (string)$childNode['version'];
							if (isset($configuration['dependencies'][$dependency])
								&& version_compare($version, $configuration['dependencies'][$dependency], '>=') === FALSE)
							{
								$meetsRequirements = FALSE;
								break;
							}
						}
					}
					if (!$meetsRequirements) {
						break;
					}
				}
				if ($nodeName == 'extension') {
					// grab extension point and content types
					if ($node['point'] != 'xbmc.addon.metadata') {
						// don't overwrite primary/first extension point
						if ($extensionPoint == '') {
							$extensionPoint = $node['point'];
						}
						$type = $node['point'];
						if ($node->children()) {
							foreach($node->children() as $childName => $childNode) {
								if ($childName == 'provides') {
									$contentTypes = array_merge($contentTypes, explode(' ', trim((string) $childNode)));
								}
							}
						}
						// convert rarely used extensions points to common ones
						if ($extensionPoint == 'xbmc.addon.video') {
							$contentTypes[] = 'video';
						}
						if ($extensionPoint == 'xbmc.addon.audio') {
							$contentTypes[] = 'audio';
						}
						if ($extensionPoint == 'xbmc.addon.image') {
							$contentTypes[] = 'image';
						}
						// move scripts with no "provides" data to "executable"
						if ($extensionPoint == 'xbmc.python.script' && !count($contentTypes)) {
							if (strpos($id, 'script.game') !== FALSE) {
								$contentTypes[] = 'game';
							} else {
								$contentTypes[] = 'executable';
							}
						}

					// grab metadata
					} else if ($node['point'] == 'xbmc.addon.metadata' && $node->children()) {
						foreach ($node->children() as $subNodeName => $subNode) {
							// Check for the Forum XML Subnode
							if ($subNodeName == 'forum') {
								$forum = $subNode;
							}
							// Check for the Website XML Subnode
							if ($subNodeName == 'website') {
								$website = $subNode;
							}
							// Check for the Source XML Subnode
							if ($subNodeName == 'source') {
								$source = $subNode;
							}
							// Check for the License XML Subnode
							if ($subNodeName == 'license') {
								$license = $subNode;
							}
							// Check for broken status
							if ($subNodeName == 'broken') {
								$broken = $subNode;
							}
							// Check for the Description XML Subnode
							if ($subNodeName == 'description' 
								&& ($subNode['lang'] == 'en' || !isset($subNode['lang']) ) )
							{
								$description = removeKodiFormatting($subNode);
							}
							// Check for the Summary XML Subnode
							if ($subNodeName == 'summary' 
								&& ($subNode['lang'] == 'en' || !isset($subNode['lang']) ) )
							{
								$summary = removeKodiFormatting($subNode);
							}
							// Check for platform status
							if ($subNodeName == 'platform') {
								$platforms = array_unique(array_merge($platforms, explode(' ', trim((string) $subNode))));
							}
							// Check for languages
							if ($subNodeName == 'language') {
								$langs = explode(' ', trim(str_replace(',', ' ', (string) $subNode)));
								foreach ($langs as $k => $l) {
									if (strlen($l) != 2) {
										unset($langs[$k]);
									}
								}
								$languages = array_unique(array_merge($languages, $langs));
							}
						}
						// Merge the description and summary variables
						if ($description == '' && $summary) {
							$description = $summary;
						}
					}
				}
			}

			if (!$meetsRequirements) {
				continue;
			}

			// unify format of multiple authors
			$author = strtr(removeKodiFormatting($addon['provider-name']), array('|' => ',', ';' => ',', '&amp;' => ',', ', ' => ','));

			$contentTypes = array_unique($contentTypes);

			//Check here to see if the Item already exists
			if (isset($addonCache['existing'][$id])) {
				//Item exists
				//Check here to see if the addon needs to be updated
				$updateQuery = ' deleted = 0, name = "' . $db->escape($name) . '", provider_name = "' . $db->escape($author) . '", description = "' . $db->escape($description) . '", forum = "' . $db->escape($forum) . '", website = "' . $db->escape($website) . '", source = "' . $db->escape($source) . '", license = "' . $db->escape($license) . '", extension_point="' . $extensionPoint . '", content_types="' . implode(',', $contentTypes) . '", broken="' . $db->escape($broken) . '", repository_id="' . $db->escape($repositoryId) . '", platforms="' . implode(',', $platforms) . '", languages="' . implode(',', $languages) . '"';
					// only update timestamp on new version
				if ($addonCache['existing'][$id]->version != $addon['version']) {
					$counterUpdated++;
					$updateQuery .= ', version = "' . $db->escape($addon['version']) . '", updated = NOW() ';
					deleteAddonCache($id);
				}
				$db->query('UPDATE addon SET ' . $updateQuery . ' WHERE id = "' . $db->escape($id) . '"');
	
			// Add a new add-on if it doesn't exist
			} else {
				$counterNewlyAdded++;
				$query = 'INSERT INTO addon (id, name, provider_name, version, description, created, updated, forum, website, source, license, extension_point, content_types, broken, deleted, repository_id, platforms, languages) ';
				$query .= 'VALUES ("' . $db->escape($id) . '", "' . $db->escape($name) . '", "' . $db->escape($author) . '", "' . $db->escape($addon['version']) . '", "' . $db->escape($description) . '", NOW(), NOW(), "' . $db->escape($forum) . '", "' . $db->escape($website) . '", "' . $db->escape($source) . '", "' . $db->escape($license) . '", "' . $extensionPoint . '", "' . implode(',', $contentTypes) . '", "' . $db->escape($broken) . '", 0, "' . $db->escape($repositoryId) . '", "' . implode(',', $platforms) . '", "' . implode(',', $languages) . '")';
				$db->query($query);
			}

			cacheAddonData($id, $repositoryId);
			$addonCache['processed'][$id] = $id;
		}

		$consoleLog[] = 'Repository: ' . $repositoryConfiguration['name'] . ' - Total ' . $counter . ', ' . $counterUpdated . ' Updated, ' . $counterNewlyAdded . ' New';
	}

	if (!$error) {
		// mark addons no longer part of repo xml as deleted
		$orphaned = array_diff(array_keys($addonCache['existing']), array_keys($addonCache['processed']));
		$removedAddons = array();
		foreach ($orphaned as $addonId) {
			if (isset($addonCache['existing'][$addonId]) && (bool) $addonCache['existing'][$addonId]->deleted == FALSE) {
				$removedAddons[] = $db->escape($addonId);
			}
		}
		if (count($removedAddons)) {
			$db->query('UPDATE addon SET deleted = 1 WHERE id IN ("' . implode('","', $removedAddons) . '")');
		}
		$consoleLog[] = 'Import finished: ' . date('l jS \of F Y h:i:s A') . ' - ' . count($removedAddons) . ' orphaned addons removed';
	} else {
		$consoleLog[] = 'Import abortet: ' . date('l jS \of F Y h:i:s A') . ' - an error occured during import';
	}
	echo implode(" | ", $consoleLog);
}
?>