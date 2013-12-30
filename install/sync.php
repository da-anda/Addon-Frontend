<?php
// protect script from unauthorized calls
require_once('../includes/configuration.php');
require_once('../includes/functions.php');
checkAdminAccess();

//  ##############   Include Files  ################ //
require_once('../includes/db_connection.php');
//  ##############  Finish Includes  ############### //

# Check the XML exists
$repositoryVersion = strtolower($configuration['repository']['version']);
$xml = simplexml_load_file($configuration['repository']['importUrl']);
if ($xml && isset($xml->addon['id']))	{
	$counter = 0;
	$counterUpdated = 0;
	$counterNewlyAdded = 0;

	//prepare the download stats so that we can update each addon with one single query
	$downloadCount = array();
	$xmlsimple = simplexml_load_file($configuration['repository']['statsUrl']);
	if ($xmlsimple && isset($xmlsimple->addon['id']))	{
		foreach ($xmlsimple->addon as $addon) {
			$id = (string) $addon['id'];
			$downloadStats[$id] = intval($addon->downloads);
		}
	}

	// cache existing addon-IDs
	$result = $db->get_results('SELECT id, version, deleted FROM addon');
	$addonCache = array(
		'processed' => array(),
		'existing' => array()
	);
	foreach ($result as $addon) {
		$addonCache['existing'][$addon->id] = $addon;
	}

	// Loop through each addon
	foreach ($xml->addon as $addon)	{
		$counter++;
		$description = '';
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
		$downloadCount = isset($downloadStats[$id]) ? $downloadStats[$id] : 0;

		// check for duplicates in XML and skip those
		if (isset($addonCache['processed'][$id])) {
			continue;
		}

		foreach ($addon->children() as $nodeName => $node) {
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
							$description = $subNode;
						}
						// Check for the Summary XML Subnode
						if ($subNodeName == 'summary' 
							&& ($subNode['lang'] == 'en' || !isset($subNode['lang']) ) )
						{
							$summary = $subNode;
						}
					}
					// Merge the description and summary variables
					if ($description == '' && $summary) {
						$description = $summary;
					}
				}
			}
		}

		// unify format of multiple authors
		$author = strtr($addon['provider-name'], array('|' => ',', ';' => ','));

		$contentTypes = array_unique($contentTypes);

		//Check here to see if the Item already exists
		if (isset($addonCache['existing'][$id])) {
			//Item exists
			//Check here to see if the addon needs to be updated
			$updateQuery = ' deleted = 0, provider_name = "' . $db->escape($author) . '", description = "' . $db->escape($description) . '", forum = "' . $db->escape($forum) . '", website = "' . $db->escape($website) . '", source = "' . $db->escape($source) . '", license = "' . $db->escape($license) . '", downloads = ' . $downloadCount . ', extension_point="' . $extensionPoint . '", content_types="' . implode(',', $contentTypes) . '", broken="' . $db->escape($broken) . '"';
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
			$db->query('INSERT INTO addon (id, name, provider_name, version, description, created, updated, forum, website, source, license, downloads, extension_point, content_types, broken, deleted) VALUES ("' . $db->escape($id) . '", "' . $db->escape($addon['name']) . '", "' . $db->escape($author) . '", "' . $db->escape($addon['version']) . '", "' . $db->escape($description) . '", NOW(), NOW(), "' . $db->escape($forum) . '", "' . $db->escape($website) . '", "' . $db->escape($source) . '", "' . $db->escape($license) . '", ' . $downloadCount . ', "' . $extensionPoint . '", "' . implode(',', $contentTypes) . '", "' . $db->escape($broken) . '", 0)');
		}

		$addonCache['processed'][$id] = $id;
	}

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

	echo date('l jS \of F Y h:i:s A') . ' - Total ' . $counter . ', ' . $counterUpdated . ' Updated, ' . $counterNewlyAdded . ' New, ' . count($removedAddons) . ' Removed';
}

?>