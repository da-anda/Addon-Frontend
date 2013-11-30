<?php
//  ##############   Include Files  ################ //
require_once('includes/configuration.php');
require_once('includes/db_connection.php');
require_once('includes/functions.php');
require_once('includes/pageRenderer.php');
//  ##############  Finish Includes  ############### //

// ###############  Setup Queries    ############### //
$totalcount = $db->get_var('SELECT count(*) FROM addon');
//  ##############  Finish Queries  ############### //

$categories = array();
foreach ($configuration['categories'] as $categoryName => $categoryData) {
	$categories[] = '<li><a href="' . createLinkUrl('category', $categoryName) . '"><span class="thumbnail"><img src="images/categories/' . $categoryName . '.png" class="pic" alt="' . $categoryData['label'] . '" /></span><strong>' . $categoryData['label'] . '</strong></a></li>';
}

$content = '<h2>Categories</h2><p>Browse the the Add-on categories below</p>
		<ul id="addonCategories">
			' . implode("\n\t\t\t", $categories) .'
		</ul>
		<div class="resultCount">' . $totalcount . ' Add-ons found</div>';
$content .= getDisclaimer();

$page = new PageRenderer();
$page->setContent($content);
echo $page->render();
shutdown();
?>