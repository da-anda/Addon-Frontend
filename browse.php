<?php
//  ##############   Include Files  ################ //
require_once('includes/configuration.php');
require_once('includes/db_connection.php');
require_once('includes/functions.php');
require_once('includes/pageRenderer.php');
//  ##############  Finish Includes  ############### //

//  ##############   Get Variables   ############### //
$categoryKey = NULL;
$author = NULL;
$addonscount = 0;
$itemsperpage = 40;
$pagination = '';
$browseLabel = '';
$offset = max(0, isset($_GET['page']) ? (intval($_GET['page']) -1) : 0) * $itemsperpage;
if (isset($_GET['c'])) { $categoryKey = $_GET['c']; }
if (isset($_GET['a'])) { $author = $_GET['a']; }
//  ##############  Finish Varibles  ############### //

$page = new PageRenderer();

// ###############  Setup Queries    ############### //
if ($categoryKey !== NULL) {
	$category = getCategoryFromArguments(explode('/', $categoryKey));

	if ($category) {
		$whereClause = ' extension_point = "' . $db->escape($category['extensionPoint']) . '"';
		if (isset($category['contentType']) && $category['contentType']) {
			$whereClause .= ' AND FIND_IN_SET("' . $category['contentType'] . '", content_types)';
		}

		$addons = $db->get_results('SELECT * FROM addon WHERE ' . $whereClause . $configuration['addonExcludeClause'] . ' ORDER BY name ASC LIMIT ' . $offset . ', ' . $itemsperpage);
		$count = $db->get_var('SELECT count(*) FROM addon WHERE ' . $whereClause . $configuration['addonExcludeClause']);
	} else {
		header('HTTP/1.0 404 Not Found');
	}
} else if ($author !== NULL) {
	$addons = $db->get_results('SELECT * FROM addon WHERE provider_name LIKE "%' . $db->escape($author) . '" ' . $configuration['addonExcludeClause'] . ' ORDER BY name ASC LIMIT ' . $offset . ', ' . $itemsperpage);
	$count = $db->get_var('SELECT count(*) FROM addon WHERE provider_name LIKE "%' . $db->escape($author) . '" ' . $configuration['addonExcludeClause']);
}
//  ##############  Finish Queries  ############### //

//  ##############  Render Content  ############### //
$content ='<h2>Browsing</h2>';

// render related info by category
if (isset($category)) {
	$categoryLabels = array();
	foreach ($category['rootline'] as $categoryData) {
		$categoryLabels[] = $categoryData['label'];
		$page->addRootlineItem(array( 'url' => createLinkUrl('category', implode('/', array_keys($categoryData['rootline']))), 'name' => $categoryData['label']));
	}
	$content .= '<p>Category ' . htmlspecialchars(implode(' / ', $categoryLabels)) . '</p>';
	$pagination = renderPagination(createLinkUrl('category', $categoryKey), $count, $itemsperpage);
	// render subcategories if available
	if (isset($category['subCategories'])) {
		$items = array();
		foreach ($category['subCategories'] as $categoryName => $categoryData) {
			$pathSegment = isset($categoryData['rootline']) ? implode('/', array_keys($categoryData['rootline'])) : $categoryName;
			$items[] = '<li><a href="' . createLinkUrl('category', $pathSegment) . '"><span class="thumbnail"><img src="images/categories/' . $pathSegment . '.png" class="pic" alt="' . $categoryData['label'] . '" /></span><strong>' . $categoryData['label'] . '</strong></a></li>';
		}
		$content .= '<ul id="addonCategories">' . implode("", $items) .'</ul>';
	}
// render related info by author
} else if ($author) {
	$content .= '<p>Author ' . htmlspecialchars($author) . '</p>';
	$page->addRootlineItem(array( 'url' => createLinkUrl('author', $author), 'name' => 'Browse'));
	$pagination = renderPagination(createLinkUrl('author', $author), $count, $itemsperpage);
}

// render addons
if (isset($addons) && is_array($addons) && count($addons)) {
	$content .= '<ul id="addonList">';
	foreach ($addons as $addon) {
		$content .= '<li>';
		$content .= '<a href="' . createLinkUrl('addon', $addon->id) . '"><span class="thumbnail"><img src="' . getAddonThumbnail($addon->id, 'addonThumbnail') . '" width="100%" alt="' . $addon->name . '" class="pic" /></span>';
		$content .= '<strong>' . $addon->name . '</strong></a>';
		$content .= '</li>';
		$addonscount++;
	}
	$content .= '</ul>';
	$content .= $pagination;
}

$content .= getDisclaimer();
$page->setContent($content);
echo $page->render();
shutdown();
?>