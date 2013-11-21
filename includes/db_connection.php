<?php
require_once("ez_sql_core.php");
require_once("ez_sql_mysql.php");

if (isset($configuration['database'])) {
	$db = new ezSQL_mysql();
	// auto connect to the db in order to have a valid connection when calling $db->escape() for creating queries
	$db->quick_connect($configuration['database']['username'], $configuration['database']['password'], $configuration['database']['name'], $configuration['database']['server']);
}
?>