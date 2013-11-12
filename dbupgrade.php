<?php
//  ##############   Include Files  ################ //
	require_once("includes/configuration.php");
	require_once("includes/db_connection.php");
	require_once("includes/functions.php");
	require_once("includes/pageRenderer.php");
//  ##############  Finish Includes  ############### //

$page = new PageRenderer();

	$worked = 0;
	$content = '<h2>Status</h2>
	<p>Upgrading to Database Version 1.1</p>
	<ul>';

	// Check WEBSITE column exists and create if it doesn't
	$result = $db->get_results("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'xbmcrepo' AND TABLE_NAME = 'addon' AND COLUMN_NAME = 'website'");
	if (!isset($result))	{
	    $db->query("ALTER TABLE addon ADD website VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER forum ;");
		$content .= 'Creating Website Field: <img src="images/icon_yes.png" height="12" width="12" /> <b>Done</b><br />';
		$worked ++;
	} else {
		$content .= 'Creating Website Field: <img src="images/icon_no.jpg" height="12" width="12" /> <b>Already Exists</b><br />';
	}

	// Check SOURCE column exists and create if it doesn't
	$result = $db->get_results("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'xbmcrepo' AND TABLE_NAME = 'addon' AND COLUMN_NAME = 'source'");
	if (!isset($result))	{
		$db->query("ALTER TABLE addon ADD source VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER website ;");
		$content .= 'Creating Source Field: <img src="images/icon_yes.png" height="12" width="12" /> <b>Done</b><br />';
		$worked ++;
	} else {
		$content .= 'Creating Source Field: <img src="images/icon_no.jpg" height="12" width="12" /> <b>Already Exists</b><br />';
	}

	// Check LICENSE column exists and create if it doesn't
	$result = $db->get_results("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'xbmcrepo' AND TABLE_NAME = 'addon' AND COLUMN_NAME = 'license'");
	if (!isset($result))	{
		$db->query("ALTER TABLE addon ADD license VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER source ;");
		$content .= 'Creating License Field: <img src="images/icon_yes.png" height="12" width="12" /> <b>Done</b><br />';
		$worked ++;
	} else {
		$content .= 'Creating License Field: <img src="images/icon_no.jpg" height="12" width="12" /> <b>Already Exists</b><br />';
	}

	// Check SOURCECODE column exists and delete if it does
	$result = $db->get_results("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'xbmcrepo' AND TABLE_NAME = 'addon' AND COLUMN_NAME = 'sourcecode'");
	if (isset($result)) {
		$db->query("ALTER TABLE addon DROP sourcecode ;");
		$content .= 'Deleting SourceCode Field: <img src="images/icon_yes.png" height="12" width="12" /> <b>Exists Deleting</b><br />';
		$worked ++;
	} else {
		$content .= 'Deleting SourceCode Field: <img src="images/icon_no.jpg" height="12" width="12" /> <b>Does Not Exist</b><br />';
	}

	// Empty table if all updates applied successfully
	if ($worked == 4) {
		$db->query("TRUNCATE TABLE addon;");
		$content .= 'Emptying Table: <img src="images/icon_yes.png" height="12" width="12" /> <b>Done</b><br />';
	} else {
		$content .= 'Emptying Table: <img src="images/icon_no.jpg" height="12" width="12" /> <b>Upgrade did not complete</b><br />';
	}

$page->setTemplate('pageNoSideColumn');
$page->setContent($content);
echo $page->render();
shutdown();
?>