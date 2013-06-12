<?php
//  ##############   Include Files  ################ //
	require_once("includes/configuration.php");
	require_once("includes/db_connection.php");
	require_once("includes/functions.php");
	require_once("includes/pageRenderer.php");
//  ##############  Finish Includes  ############### //

$page = new PageRenderer();

				$content = '<h2>Status</h2>
				<p>Syncing With Addons.XML, using ' . ucfirst($configuration['repository']['version']) . ' Repository.</p>
				<ul>';

				# Check the XML exists
				$repositoryVersion = strtolower($configuration['repository']['version']);
				$xml = simplexml_load_file($configuration['repository']['importUrl']);
				if (isset($xml->addon['id']))
				{
					$counter = 0;
					// Loop through each addon
					foreach ($xml->addon as $addons) 
					{
						$counter++;
						$description = "";
						$summary = "";
						$log = "<b>ID: </b>".$addons['id']. " ";
						foreach ($addons->children() as $nodeName => $node) {
							if ($nodeName == 'extension' && $node['point'] == 'xbmc.addon.metadata' && $node->children()) {
								$log .= '| <b>Metadata:</b> <img src="images/icon_yes.png" height="12" width="12" /> |';
								foreach ($node->children() as $subNodeName => $subNode) {
									if ($subNodeName == 'description' 
										&& ($subNode['lang'] == 'en' || !isset($subNode['lang']) ) )
									{
											$description = $subNode;
											break;
									}
									if ($subNodeName == 'summary' 
										&& ($subNode['lang'] == 'en' || !isset($subNode['lang']) ) )
									{
											$summary = $subNode;
											break;
									}
								}
								if ($description == '' && $summary) {
									$description = $summary;
								}
								break;
							}
						}
						// Set the individual variables for each add-on
						$id = $addons['id'];
						$name = $db->escape($addons['name']);
						$provider_name = $db->escape($addons['provider-name']);
						$version = $addons['version'];
						$description = $db->escape($description);
						
						//Check here to see if the Item already exists
						$check = $db->get_row("SELECT * FROM addon WHERE id = '$id'");
						
						if (isset($check->id))
						{
							//Item exists
							$log .= ' <b>Exists:</b> <img src="images/icon_yes.png" height="12" width="12" /> ';
							//Check here to see if the addon needs to be updated
							if ($check->version == $version)
							{
								$log .= ' | <b>Version:</b> no change';
							// Update plugin here to new version number
							}
							
							else
							{
								$db->query("UPDATE addon SET version = '$version', updated = NOW(), provider_name = '$provider_name', description = '$description' WHERE id = '$id'");
								$log .= '<b>Version:</b> updated <img src="images/icon_screens.png" height="12" width="12" />';
							}
						}
						
						// Add a new add-on if it doesn't exist
						else if ($description != "")
						{
							$db->query("INSERT INTO addon (id, name, provider_name, version, description, created, updated) VALUES ('$id', '$name', '$provider_name','$version', '$description', NOW(), NOW())");
							$log .= ' <b>Exists:</b> <img src="images/icon_no.jpg" height="12" width="12" /> (Created new!)';
						}
						else
						{
							$log .= " no description found";
						}

						// check if screenshots.zip exists
/*
						$screenshots = "http://mirrors.xbmc.org/addons/eden/".$id."/screenshots.zip";
						if(check_url("$screenshots")) {
							$log .= " - screenshot.zip found";
					 	}
*/						$content .= '<li>' . $log . '</li>';
					}
				}
				
				$content .=  '</ul>';

				// Now update the download stats
				$xmlsimple = simplexml_load_file($configuration['repository']['statsUrl']);
				if (isset($xmlsimple->addon['id']))
				{
					$content .=  '<h2>Updating download stats </h2><ul>';
					foreach ($xmlsimple->addon as $addons) 
					{	
						$downloads = intval($addons->downloads);
						$addonId = $addons['id'];

						if ($addonId && $downloads)
						{
							// To speed things up, don't check if the addon exists in the DB and then do the UPDATE query. If addon is not in DB, it won't update anything, but if it is, we saved 1 query per update
							// Plugin was found update with the downloads.
							if($db->query("UPDATE addon SET downloads = '$downloads' WHERE id = '" . $db->escape($addonId) . "'"))	$content .=  '<li><b>ID</b>: ' . $addonId . " | <b>Downloads: </b> " . $downloads . ' </li>';
						}
					}
					$content .=  '</ul>';
				}

$page->setTemplate('pageNoSideColumn');
$page->setContent($content);
echo $page->render();
shutdown();
?>