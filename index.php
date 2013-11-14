<?php
//  ##############   Include Files  ################ //
	require_once("includes/configuration.php");
	require_once("includes/db_connection.php");
	require_once("includes/functions.php");
	require_once("includes/pageRenderer.php");
//  ##############  Finish Includes  ############### //

// ###############  Setup Queries    ############### //
$totalcount = $db->get_var("SELECT count(*) FROM addon");
//  ##############  Finish Queries  ############### //

$page = new PageRenderer();

$content = <<<EOF
				<h2>Categories</h2>
				<p>Browse the the Add-on categories below</p>
				
				<ul id="addonCategories"> 
					<li><a href="browse.php?t=metadata"><span class="thumbnail"><img src="images/mainicon-metadata.png" class="pic" alt="XBMC Metadata Add-Ons" /></span><strong>Metadata</strong></a></li>
					<li><a href="browse.php?t=plugin.image"><span class="thumbnail"><img src="images/mainicon-picture.png" class="pic" alt="XBMC Picture Add-Ons" /></span><strong>Pictures</strong></a></li>
					<li><a href="browse.php?t=plugin.video"><span class="thumbnail"><img src="images/mainicon-video.png" class="pic" alt="XBMC Video Add-Ons" /></span><strong>Video</strong></a></li>
					<li><a href="browse.php?t=plugin.audio"><span class="thumbnail"><img src="images/mainicon-music.png" class="pic" alt="XBMC Music Add-Ons" /></span><strong>Audio</strong></a></li>
					<li><a href="browse.php?t=plugin.games"><span class="thumbnail"><img src="images/mainicon-game.png" class="pic" alt="XBMC Game Add-Ons" /></span><strong>Games</strong></a></li>
					<li><a href="browse.php?t=plugin.program"><span class="thumbnail"><img src="images/mainicon-program.png" class="pic" alt="XBMC Program Add-Ons" /></span><strong>Programs</strong></a></li>
					<li><a href="browse.php?t=script"><span class="thumbnail"><img src="images/mainicon-script.png" class="pic" alt="XBMC Script Add-Ons" /></span><strong>Scripts</strong></a></li>
					<li><a href="browse.php?t=skin"><span class="thumbnail"><img src="images/mainicon-skin.png" class="pic" alt="XBMC Skins" /></span><strong>Skins</strong></a></li>
				</ul>
				<div class="resultCount">$totalcount Add-ons found</div>
EOF;

$content .= getDisclaimer();
$page->setContent($content);
echo $page->render();
shutdown();
?>