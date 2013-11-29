<?php
// protect script from unauthorized calls
require_once('../includes/configuration.php');
if (!isset($_GET['token']) || $_GET['token'] !== $configuration['security']['token']) exit;

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
	// Loop through each addon
	foreach ($xml->addon as $addons)	{
		$counter++;
		$description = '';
		$summary = '';
		$forum = '';
		$source = '';
		$website = '';
		$license = '';

		foreach ($addons->children() as $nodeName => $node) {
			if ($nodeName == 'extension' && $node['point'] == 'xbmc.addon.metadata' && $node->children()) {
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
				break;
			}
		}

		//Check here to see if the Item already exists
		$check = $db->get_row('SELECT * FROM addon WHERE id = "' . $db->escape($addons['id']) . '"');

		if (isset($check->id)) {
			//Item exists
			//Check here to see if the addon needs to be updated
			if ($check->version != $addons['version']) {
				$counterUpdated++;
				$db->query('UPDATE addon SET version = "' . $db->escape($addons['version']) . '", updated = NOW(), provider_name = "' . $db->escape($addons['provider-name']) . '", description = "' . $db->escape($description) . '", forum = "' . $db->escape($forum) . '", website = "' . $db->escape($website) . '", source = "' . $db->escape($source) . '", license = "' . $db->escape($license) . '" WHERE id = "' . $db->escape($addons['id']) . '"');
			}
		// Add a new add-on if it doesn't exist
		} else if ($description != '') {
			$counterNewlyAdded++;
			$db->query('INSERT INTO addon (id, name, provider_name, version, description, created, updated, forum, website, source, license) VALUES ("' . $db->escape($addons['id']) . '", "' . $db->escape($addons['name']) . '", "' . $db->escape($addons['provider-name']) . '", "' . $db->escape($addons['version']) . '", "' . $db->escape($description) . '", NOW(), NOW(), "' . $db->escape($forum) . '", "' . $db->escape($website) . '", "' . $db->escape($source) . '", "' . $db->escape($license) . '")');
		}
	}
	echo date('l jS \of F Y h:i:s A') . ' - Total ' . $counter . ', ' . $counterUpdated . ' Updated, ' . $counterNewlyAdded . ' New';
}

// Now update the download stats
$xmlsimple = simplexml_load_file($configuration['repository']['statsUrl']);
if ($xmlsimple && isset($xmlsimple->addon['id']))	{
	foreach ($xmlsimple->addon as $addons) {	
		$downloads = intval($addons->downloads);
		$addonId = $addons['id'];

		if ($addonId && $downloads)	{
			// To speed things up, don't check if the addon exists in the DB and then do the UPDATE query. If addon is not in DB, it won't update anything, but if it is, we saved 1 query per update
			$db->query('UPDATE addon SET downloads = "' . $downloads . '" WHERE id = "' . $db->escape($addonId) . '"');
		}
	}
}

?>