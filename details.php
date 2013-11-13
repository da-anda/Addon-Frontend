<?php
//  ##############   Include Files  ################ //
	require_once("includes/configuration.php");
	require_once("includes/db_connection.php");
	require_once("includes/functions.php");
	require_once("includes/pageRenderer.php");
//  ##############  Finish Includes  ############### //

//  ##############   Get Variables   ############### //
	$type = $_GET["t"];
//  ##############  Finish Varibles  ############### //

// ###############  Setup Queries    ############### //
$detail = $db->get_results("SELECT * FROM addon WHERE id = '" . $db->escape($type) . "'");
$commentaddon = $db->get_results("SELECT * FROM comment WHERE addonid = '" . $db->escape($type) . "' ORDER BY date DESC LIMIT 5");
//  ##############  Finish Queries  ############### //

$page = new PageRenderer();
$page->addRootlineItem(array( 'url' => 'details.php?t=' . $type, 'name' => 'Details'));

$content = '';
		// Loop through the add-on details array
	if (isset($detail)) 
	{
		foreach ($detail as $details)
		{
			$content .= "<div id='addonDetail'><h2>$details->name ".$details->version."</h2>
				<span class='thumbnail'><img src='" . getAddonThumbnail($details->id, 'large') . "' alt='$details->name' class='pic' /></span>
				<strong>Author:</strong> <a href='browse.php?a=".$details->provider_name."'>".$details->provider_name."</a>";
			#$content .= "<br /><br /><strong>Rating:</strong> <img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' /><img src='images/star_full_off.png' width='14' height='14' />";
			$content .= "<br /><br /><strong>Downloads:</strong> ".number_format($details->downloads);
			$content .= "<br /><br /><strong>Description:</strong> ".str_replace("[CR]","<br />",$details->description);
			$content .= "<br /><br /><strong>License:</strong> ".str_replace("[CR]","<br />",$details->license)."<br /><br />";

			$content .=  '<ul class="addonLinks">';
			// forum link
			$forumLink = $details->forum ? '<a href="' . $details->forum .'"><img src="images/forum.png" alt="Forum discussion" /></a>' : '<img src="images/forumbw.png" alt="Forum discussion" />';
			$content .=  "<li><strong>Forum Discussion:</strong><br />" . $forumLink . '</li>';

			// Auto Generate Wiki Link
			$content .=  '<li><strong>Wiki Documentation:</strong><br /><a href="http://wiki.xbmc.org/index.php?title=Add-on:' . $details->name . '"><img src="images/wiki.png" alt="Wiki page of this addon" /></a></li>';

			// Donation stuff (**REMOVED FOR NOW**)
			#$donateLink = $details->donate ? '<a href="' . $details->donate .'"><img src="images/paypal.jpg" alt="Donate" /></a>' : '<img src="images/paypalbw.jpg" alt="Donate" />';
			#echo "<li><strong>Donate to Author:</strong><br />" . $donateLink . '</li>';

			
			// Check sourcecode link exists
			$sourceLink = $details->source ? '<a href="' . $details->source .'"><img src="images/code.png" alt="Source code" /></a>' : '<img src="images/codebw.png" alt="Source code" />';
			$content .=  "<li><strong>Source Code:</strong><br />" . $sourceLink . '</li>';

			$content .= '</ul></div>';
		}
	}
//	Else { $content = "none found"};
					
/* $content .= '
		<!-- Comments Javascript Livefyre Embed -->
		<div id="livefyre-comments"></div>
		<script type="text/javascript" src="http://zor.livefyre.com/wjs/v3.0/javascripts/livefyre.js"></script>
		<script type="text/javascript">
			(function () {
			var articleId = fyre.conv.load.makeArticleId(null, [\'t\']);
			fyre.conv.load({}, [{
			el: \'livefyre-comments\',
			network: "livefyre.com",
			siteId: "314161",
			articleId: articleId,
			signed: false,
			collectionMeta: {
							articleId: articleId,
							url: fyre.conv.load.makeCollectionUrl(),
							}
			}], function() {});
			}());
		</script>
		<!-- END: Livefyre Embed -->
'; */

$content .= getDisclaimer();
$page->setContent($content);
echo $page->render();
shutdown();
?>