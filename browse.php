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
	if (isset($_GET["t"])) {$type = $_GET["t"];}
	if (isset($_GET["a"])) {$author = $_GET["a"];}
//  ##############  Finish Varibles  ############### //

// ###############  Setup Queries    ############### //

if ($type !== NULL) 
{
$category = $db->get_results("SELECT * FROM addon WHERE id LIKE '" . $db->escape($type) . "%' AND id NOT LIKE 'Common%' AND id NOT LIKE 'script.module%' ORDER BY downloads DESC");
$count = $db->get_var("SELECT count(*) FROM addon WHERE id LIKE '" . $db->escape($type) . "%' AND id NOT LIKE 'Common%' AND id NOT LIKE 'script.module%'");
}
else if ($author !== NULL) 
{
$category = $db->get_results("SELECT * FROM addon WHERE provider_name LIKE '" . $db->escape($author) . "%' AND id NOT LIKE 'script.module%' ORDER BY downloads DESC");
$count = $db->get_var("SELECT count(*) FROM addon WHERE provider_name LIKE '" . $db->escape($author) . "%' AND id NOT LIKE 'script.module%'");
}
//  ##############  Finish Queries  ############### //

$page = new PageRenderer();
$page->addRootlineItem(array( 'url' => 'browse.php?t=' . $type . '&amp;a=' . $author, 'name' => 'Browse'));

	$content ='<h2>Browsing</h2>';
	if ($type !== NULL || $author !== NULL)
		$content .= '<p>' . htmlspecialchars($type . $author) . '</p>';
	if (is_array($category) && count($category)) 	{
		$counter = 0;
		$content .= '<ul id="addonList">';
		foreach ($category as $categories)
		{
			$counter++;
			$content .= "<li>";
			$content .= '<a href="details.php?t=' . $categories->id . '"><span class="thumbnail"><img src="' . getAddonThumbnail($categories->id, 'addonThumbnail') . '" width="100%" alt="' . $categories->name . '" class="pic" /></span>';
			$content .= "<strong>" . $categories->name ."</strong></a> ";
			#echo '<span class="author">' . $categories->provider_name . '</span>';
			#echo "<br /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' />";
			$content .= "</li>";
		}
		$content .= "</ul>";
	}
	$content .= '<div class="resultCount">' . $count . ' Plugins found</div>';

$content .= getDisclaimer();
$page->setContent($content);
echo $page->render();
shutdown();
?>