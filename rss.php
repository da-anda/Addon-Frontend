<?php
//  ##############   Include Files  ################ //
require_once('includes/configuration.php');
require_once('includes/db_connection.php');
require_once('includes/functions.php');
//  ##############  Finish Includes  ############### //

// ###############  Prepare requested mode ######### //
$orderByProperty = 'created';
$feedTitle = 'Latest XBMC Add-Ons';
if (isset($_GET['mode']) && $_GET['mode'] == 'updated') {
	$orderByProperty = 'updated';
	$feedTitle = 'Recently updated XBMC Add-Ons';
}

// ###############  Setup Queries    ############### //
$queryResult = $db->get_results('SELECT * FROM addon ORDER BY ' . $orderByProperty . ' DESC LIMIT 5');
//  ##############  Finish Queries  ############### //


// Build the Add-Ons list
$itemList = '';
foreach ($queryResult as $addon) {
	$thumbnailExternalUrl = getAddonThumbnail($addon->id, 'addonThumbnail');
	$thumbnailFilePath = str_replace($configuration['cache']['pathRead'], $configuration['cache']['pathWrite'], $thumbnailExternalUrl);
	$thumbnailSize = 0;
	if (file_exists($thumbnailFilePath) && is_file($thumbnailFilePath)) {
		$thumbnailSize = filesize($thumbnailFilePath);
	}
	$itemList .= '		<item>
			<guid>' . $addon->id . '</guid>
			<title>' . $addon->name . '</title>
			<description>' . $addon->description . '</description>
			<date>' . $addon->updated . '</date>
			<link>' . $configuration['baseUrl'] . 'details.php?t=' . $addon->id . '</link>
			<enclosure url="' . $configuration['baseUrl'] . $thumbnailExternalUrl . '" length="' . $thumbnailSize . '" type="image/png" />
		</item>
';
}
shutdown();

// begin output
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<rss version="2.0">
	<channel>
		<title><?php echo $feedTitle; ?></title>
		<link><?php echo $configuration['baseUrl']; ?></link>
		<description><?php echo $feedTitle; ?></description>
		<language>en-us</language>
<?php echo $itemList; ?>
	</channel>
</rss>