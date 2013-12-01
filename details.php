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

if (isset($result) && count($result)) {
	$addon = current($result);
	$authors = explode('|', strtr($addon->provider_name, array(',' => '|', ';' => '|')));
	$authorLinks = array();

	foreach ($authors as $author) {
		if ($author) {
			$author = cleanupAuthorName($author);
			$authorLinks[] = '<a href="' . createLinkUrl('author', $author) . '">' . htmlspecialchars($author) . '</a>';
		}
	}
	$content .= '<div id="addonDetail">
		<span class="thumbnail"><img src="' . getAddonThumbnail($addon->id, 'large') . '" alt="' . $addon->name . '" class="pic" /></span>
		<h2>' . htmlspecialchars($addon->name) .'</h2>
		<strong>Author:</strong> ' . implode(', ', $authorLinks);

	// Show the extra details of the Add-on
	$content .= '<br /><strong>Version:</strong> ' . $addon->version;
	$content .= '<br /><strong>Released:</strong> ' . $addon->updated;
	$content .= '<br /><strong>Downloads:</strong> ' . number_format($addon->downloads);
	if ($addon->license) {
		$content .= '<br /><strong>License:</strong> ' . str_replace('[CR]', '<br />', $addon->license);
	}
	$content .= '<div class="description"><h4>Description:</h4><p>' . str_replace('[CR]', '<br />', $addon->description) . '</p></div>';

	$content .=  '<ul class="addonLinks">';
	// Check forum link exists
	$forumLink = $addon->forum ? '<a href="' . $addon->forum .'" target="_blank"><img src="images/forum.png" alt="Forum discussion" /></a>' : '<img src="images/forumbw.png" alt="Forum discussion" />';
	$content .=  '<li><strong>Forum Discussion:</strong><br />' . $forumLink . '</li>';

	// Auto Generate Wiki Link
	$content .=  '<li><strong>Wiki Docs:</strong><br /><a href="http://wiki.xbmc.org/index.php?title=Add-on:' . $addon->name . '" target="_blank"><img src="images/wiki.png" alt="Wiki page of this addon" /></a></li>';
	
	// Check sourcecode link exists
	$sourceLink = $addon->source ? '<a href="' . $addon->source .'" target="_blank"><img src="images/code.png" alt="Source code" /></a>' : '<img src="images/codebw.png" alt="Source code" />';
	$content .=  "<li><strong>Source Code:</strong><br />" . $sourceLink . '</li>';
	
	// Check website link exists
	$websiteLink = $addon->website ? '<a href="' . $addon->website .'" target="_blank"><img src="images/website.png" alt="Website" /></a>' : '<img src="images/websitebw.png" alt="Website" />';
	$content .=  "<li><strong>Website:</strong><br />" . $websiteLink . '</li>';

	// Show the Download link
	$content .= '<li><strong>Direct Download:</Strong><br />';
	$content .= '<a href="http://mirrors.xbmc.org/addons/' . strtolower($configuration['repository']['version']) . '/' . $addon->id . '/' . $addon->id . '-' . $addon->version . '.zip" rel="nofollow"><img src="images/download_link.png" alt="Download" /></a></li>';


	$content .= '</ul></div>';
} else {
	header('HTTP/1.0 404 Not Found');
}


$content .= getDisclaimer();
$page->setContent($content);
echo $page->render();
shutdown();
?>