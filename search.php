<?php
//  ##############   Include Files  ################ //
	require_once("includes/configuration.php");
	require_once("includes/db_connection.php");
	require_once("includes/functions.php");
	require_once("includes/pageRenderer.php");
//  ##############  Finish Includes  ############### //

//  ##############   Get Variables   ############### //
	$type = NULL;
	$author = NULL;
	$addonscount = 0;
	$itemsperpage = 40;
	if (isset($_GET["s"])) {$offset = intval($_GET["s"]);} else {$offset = 0;}
	$offsetfinish = $offset + $itemsperpage;
	if (isset($_GET["t"])) {$type = $_GET["t"];}
	if (isset($_GET["a"])) {$author = $_GET["a"];}
//  ##############  Finish Varibles  ############### //

// ###############  Setup Queries    ############### //

if ($type !== NULL) 
{
$category = $db->get_results("SELECT * FROM addon WHERE id LIKE '%" . $db->escape($type) . "%' AND id NOT LIKE 'Common%' AND id NOT LIKE 'script.module%' ORDER BY downloads DESC LIMIT $offset, $itemsperpage");
$count = $db->get_var("SELECT count(*) FROM addon WHERE id LIKE '%" . $db->escape($type) . "%' AND id NOT LIKE 'Common%' AND id NOT LIKE 'script.module%'");
}
else if ($author !== NULL) 
{
$category = $db->get_results("SELECT * FROM addon WHERE provider_name LIKE '" . $db->escape($author) . "%' AND id NOT LIKE 'script.module%' ORDER BY downloads DESC");
$count = $db->get_var("SELECT count(*) FROM addon WHERE provider_name LIKE '" . $db->escape($author) . "%' AND id NOT LIKE 'script.module%'");
}
//  ##############  Finish Queries  ############### //

$page = new PageRenderer();
$page->addRootlineItem(array( 'url' => 'search.php?t=' . $type . '&amp;a=' . $author, 'name' => 'Search'));

	$content ='<h2>Search</h2>';
	if ($type !== NULL || $author !== NULL)
		$content .= '<p>' . htmlspecialchars($type . $author) . '</p>';
	if (is_array($category) && count($category)) 	{
		$content .= '<ul id="addonList">';
		foreach ($category as $categories)
		{		
			$content .= "<li>";		
			$content .= '<a href="details.php?t=' . $categories->id . '"><span class="thumbnail"><img src="' . getAddonThumbnail($categories->id, 'addonThumbnail') . '" width="100%" alt="' . $categories->name . '" class="pic" /></span>';
			$content .= "<strong>" . $categories->name ."</strong></a> ";
			#echo '<span class="author">' . $categories->provider_name . '</span>';
			#echo "<br /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' />";
			$content .= "</li>";
			$addonscount++;
		}
		$content .= "</ul>";
	}
	// Show the browse page number and total add-ons found
	$addonstotal = $offset + $addonscount;
	$content .= '<div class="resultCount">Showing '.$offset.' to '.$addonstotal.' (Total:'. $count .')';
	
	// Create variables to store back and forward button offsets
	$offsetback = $offset - $itemsperpage;
	$offsetforward = $offset + $itemsperpage;
	
	// Print out the left and right browse buttons
	$content .= '</br>';
	
	// Print the left arrow if not the first results
	if ($offset != 0) {
	$content .= '<a href="search.php?t=' . $type . '&s='.$offsetback.'"/><img src="images/arrow-left.png" width="40" height="40" /><img src="images/transparent.png" width="40" height="40" /></a>';
	} 
	ELSE {
	$content .='<img src="images/transparent.png" width="40" height="40" /><img src="images/transparent.png" width="40" height="40" />';
	}
	
	// Print the right arrow if not the end results
	if ($addonscount < $itemsperpage) {
	$content .= '<img src="images/transparent.png" width="40" height="40" />';
	} 
	ELSE {
	$content .='<a href="search.php?t=' . $type . '&s='.$offsetforward.'"/><img src="images/arrow-right.png" width="40" height="40" /></a>';
	}
	$content .='</div>';

$content .= getDisclaimer();
$page->setContent($content);
echo $page->render();
shutdown();
?>