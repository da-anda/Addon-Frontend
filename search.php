<?php
//  ##############   Include Files  ################ //
require_once('includes/configuration.php');
require_once('includes/db_connection.php');
require_once('includes/functions.php');
require_once('includes/pageRenderer.php');
//  ##############  Finish Includes  ############### //

//  ##############   Get Variables   ############### //
$type = NULL;
$author = NULL;
$addonscount = 0;
$itemsperpage = 40;
$offset = max(0, isset($_GET['page']) ? (intval($_GET['page']) -1) : 0) * $itemsperpage;
if (isset($_GET['t'])) { $type = $_GET['t']; }
if (isset($_GET['a'])) { $author = $_GET['a']; }
//  ##############  Finish Varibles  ############### //

// ###############  Setup Queries    ############### //
if ($type !== NULL) {
	$category = $db->get_results('SELECT * FROM addon WHERE id LIKE "%' . $db->escape($type) . '%" ' . $configuration['addonExcludeClause'] . ' ORDER BY name ASC LIMIT ' . $offset . ', ' . $itemsperpage);
	$count = $db->get_var('SELECT count(*) FROM addon WHERE id LIKE "%' . $db->escape($type) . '%"' . $configuration['addonExcludeClause']);
} else if ($author !== NULL) {
	$category = $db->get_results('SELECT * FROM addon WHERE provider_name LIKE "%' . $db->escape($author) . '" ' . $configuration['addonExcludeClause'] . ' ORDER BY name ASC LIMIT ' . $offset . ', ' . $itemsperpage);
	$count = $db->get_var('SELECT count(*) FROM addon WHERE provider_name LIKE "%' . $db->escape($author) . '" ' . $configuration['addonExcludeClause']);
}
//  ##############  Finish Queries  ############### //

$page = new PageRenderer();
$page->addRootlineItem(array('url' => 'search.php?t=' . $type . '&amp;a=' . $author, 'name' => 'Search'));

$content ='<h2>Search</h2>';

if ($type !== NULL || $author !== NULL) {
	$content .= '<p>' . htmlspecialchars($type . $author) . '</p>';
}
if (is_array($category) && count($category)) {
	$content .= '<ul id="addonList">';
	foreach ($category as $categories) {
		$content .= '<li>';
		$content .= '<a href="details.php?t=' . $categories->id . '"><span class="thumbnail"><img src="' . getAddonThumbnail($categories->id, 'addonThumbnail') . '" width="100%" alt="' . $categories->name . '" class="pic" /></span>';
		$content .= '<strong>' . $categories->name . '</strong></a>';
		$content .= '</li>';
		$addonscount++;
	}
	$content .= '</ul>';
}
// Show the browse page number and total add-ons found
$content .= renderPagination('browse.php?t=' . htmlspecialchars($type), $count, $itemsperpage);

$content .= getDisclaimer();
$page->setContent($content);
echo $page->render();
shutdown();
?>