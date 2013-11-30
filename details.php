<?php
//  ##############   Include Files  ################ //
require_once('includes/configuration.php');
require_once('includes/db_connection.php');
require_once('includes/functions.php');
require_once('includes/pageRenderer.php');
//  ##############  Finish Includes  ############### //

//  ##############   Get Variables   ############### //
$type = $_GET['t'];
//  ##############  Finish Varibles  ############### //

// ###############  Setup Queries    ############### //
$result = $db->get_results('SELECT * FROM addon WHERE id = "' . $db->escape($type) . '"');
//  ##############  Finish Queries  ############### //

$page = new PageRenderer();
$page->addRootlineItem(array( 'url' => 'details.php?t=' . $type, 'name' => 'Details'));

$content = '';
// Loop through the add-on details array
if (isset($result) && count($result)) {
	$addon = current($result);
	$content .= '<div id="addonDetail"><h2>' . $addon->name .' '. $addon->version .'</h2>
		<span class="thumbnail"><img src="' . getAddonThumbnail($addon->id, 'large') . '" alt="' . $addon->name . '" class="pic" /></span>
		<strong>Author:</strong> <a href="' . createLinkUrl('author', $addon->provider_name) . '">' . htmlspecialchars($addon->provider_name) . '</a>';
	$content .= '<br /><br /><strong>Downloads:</strong> ' . number_format($addon->downloads);
	$content .= '<br /><br /><strong>Description:</strong> ' . str_replace('[CR]', '<br />', $addon->description);
	$content .= '<br /><br /><strong>License:</strong> ' . str_replace('[CR]', '<br />', $addon->license) . '<br /><br />';

	$content .=  '<ul class="addonLinks">';
	// Check forum link exists
	$forumLink = $addon->forum ? '<a href="' . $addon->forum .'" target="_blank"><img src="images/forum.png" alt="Forum discussion" /></a>' : '<img src="images/forumbw.png" alt="Forum discussion" />';
	$content .=  '<li><strong>Forum Discussion:</strong><br />' . $forumLink . '</li>';

	// Auto Generate Wiki Link
	$content .=  '<li><strong>Wiki Documentation:</strong><br /><a href="http://wiki.xbmc.org/index.php?title=Add-on:' . $addon->name . '" target="_blank"><img src="images/wiki.png" alt="Wiki page of this addon" /></a></li>';
	
	// Check sourcecode link exists
	$sourceLink = $addon->source ? '<a href="' . $addon->source .'" target="_blank"><img src="images/code.png" alt="Source code" /></a>' : '<img src="images/codebw.png" alt="Source code" />';
	$content .=  "<li><strong>Source Code:</strong><br />" . $sourceLink . '</li>';
	
	// Check website link exists
	$websiteLink = $addon->website ? '<a href="' . $addon->website .'" target="_blank"><img src="images/website.png" alt="Website" /></a>' : '<img src="images/websitebw.png" alt="Website" />';
	$content .=  "<li><strong>Website Link:</strong><br />" . $websiteLink . '</li>';

	$content .= '</ul></div>';
}


$content .= getDisclaimer();
$page->setContent($content);
echo $page->render();
shutdown();
?>