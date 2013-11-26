<?php
//  ##############   Include Files  ################ //
	require_once("../includes/configuration.php");
	require_once("../includes/db_connection.php");
//  ##############  Finish Includes  ############### //

				# Check the XML exists
				$repositoryVersion = strtolower($configuration['repository']['version']);
				$xml = simplexml_load_file($configuration['repository']['importUrl']);
				if (isset($xml->addon['id']))	{
					$counter = 0;
					$counter2 = 0;
					$counter3 = 0;
					// Loop through each addon
					foreach ($xml->addon as $addons)	{
						$counter++;
						$description = "";
						$summary = "";
						$forum = "";
						$source = "";
						$website = "";
						$license = "";
					//	$log = $addons['id']. " ";
						foreach ($addons->children() as $nodeName => $node) {
							if ($nodeName == 'extension' && $node['point'] == 'xbmc.addon.metadata' && $node->children()) {
								foreach ($node->children() as $subNodeName => $subNode) {
									// Check for the Forum XML Subnode
									if ($subNodeName == 'forum')	{
											$forum = $subNode;
									}
									// Check for the Website XML Subnode
									if ($subNodeName == 'website')	{
											$website = $subNode;
									}
									// Check for the Source XML Subnode
									if ($subNodeName == 'source')	{
											$source = $subNode;
									}
									// Check for the License XML Subnode
									if ($subNodeName == 'license')	{
											$license = $subNode;
									}
									// Check for the Description XML Subnode
									if ($subNodeName == 'description' 
										&& ($subNode['lang'] == 'en' || !isset($subNode['lang']) ) )	{
											$description = $subNode;
									}
									// Check for the Summary XML Subnode
									if ($subNodeName == 'summary' 
										&& ($subNode['lang'] == 'en' || !isset($subNode['lang']) ) )	{
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
						// Set the individual variables for each add-on
						$id = $addons['id'];
						$name = $db->escape($addons['name']);
						$provider_name = $db->escape($addons['provider-name']);
						$version = $addons['version'];
						$description = $db->escape($description);
						
						//Check here to see if the Item already exists
						$check = $db->get_row("SELECT * FROM addon WHERE id = '$id'");
						
						if (isset($check->id))	{
							//Item exists
							//Check here to see if the addon needs to be updated
							if ($check->version == $version)	{
							// Update plugin here to new version number
							}	else	{
								$counter2++;
								$db->query("UPDATE addon SET version = '$version', updated = NOW(), provider_name = '$provider_name', description = '$description', forum = '$forum', website = '$website', source = '$source', license = '$license' WHERE id = '$id'");
								$log .= '<b>Version:</b> updated <img src="../images/icon_screens.png" height="12" width="12" />';
							}
						}
						
						// Add a new add-on if it doesn't exist
						else if ($description != "")	{
							$counter3++;
							$db->query("INSERT INTO addon (id, name, provider_name, version, description, created, updated, forum, website, source, license) VALUES ('$id', '$name', '$provider_name','$version', '$description', NOW(), NOW(),'$forum', '$website', '$source','$license')");
						}
					}
					echo date('l jS \of F Y h:i:s A') . ' - Total ' . $counter . ', ' . $counter2 . ' Updated, ' . $counter3 . ' New';
				}

				// Now update the download stats
				$xmlsimple = simplexml_load_file($configuration['repository']['statsUrl']);
				if (isset($xmlsimple->addon['id']))	{
					foreach ($xmlsimple->addon as $addons) {	
						$downloads = intval($addons->downloads);
						$addonId = $addons['id'];

						if ($addonId && $downloads)	{
							// To speed things up, don't check if the addon exists in the DB and then do the UPDATE query. If addon is not in DB, it won't update anything, but if it is, we saved 1 query per update
							// Plugin was found update with the downloads.
							if($db->query("UPDATE addon SET downloads = '$downloads' WHERE id = '" . $db->escape($addonId) . "'"));
						}
					}
				}
				
?>