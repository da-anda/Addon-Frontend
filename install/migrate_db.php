<?php
// protect script from unauthorized calls
$basePath = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
require_once($basePath . 'includes/configuration.php');
require_once($basePath . 'includes/functions.php');
checkAdminAccess();

//  ##############   Include Files  ################ //
require_once($basePath . 'includes/db_connection.php');

$tables = array(
	'addon' => array(
		'columns' => array(
			'add' => array(
				'extension_point' => 'tinytext DEFAULT NULL',
				'content_types' => 'tinytext DEFAULT NULL',
				'broken' => 'tinytext DEFAULT NULL',
				'deleted' => 'tinyint(3) unsigned DEFAULT \'0\' NOT NULL',
				'repository_id' => 'tinytext DEFAULT NULL',
				'platforms' => 'tinytext DEFAULT \'\'',
				'languages' => 'tinytext DEFAULT \'\'',
			)
		),
		'keys' => array(
			'add' => array(
				'keyaddontype' => '( `extension_point` ( 60 ) , `content_types` ( 100 ) )',
				'keyauthor' => '( `provider_name` ( 100 ) )',
				'keylanguages' => '( `languages` ( 60 ) )',
				'keyplatforms' => '( `platforms` ( 60 ) )'
			)
		)
	)
);

// process migration
foreach ($tables as $tableName => $configuration) {
	if (isset($configuration['columns'])) {
		$columnList = $db->get_results('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "' . $tableName . '"', ARRAY_A);
		$columnInfo = array();
		foreach ($columnList as $column) {
			$columnInfo[$column['COLUMN_NAME']] = $column;
		}

		foreach ($configuration['columns'] as $mode => $columns) {
			if ($mode == 'add') {
				foreach ($columns as $columnName => $columnType) {
					if (!isset($columnInfo[$columnName])) {
						$db->query('ALTER TABLE ' . $tableName . ' ADD ' . $columnName . ' ' . $columnType);
					}
				}
			}
			if ($mode == 'remove') {
				foreach ($columns as $columnName => $columnType) {
					if (isset($columnInfo[$columnName])) {
						$db->query('ALTER TABLE ' . $tableName . ' DROP ' . $columnName);
					}
				}
			}
		}
	}
	if (isset($configuration['keys'])) {
		$keyList = $db->get_results('SHOW KEYS FROM ' . $tableName, ARRAY_A);
		$keyInfo = array();
		foreach ($keyList as $keyData) {
			$keyInfo[$keyData['Key_name']] = $keyData;
		}
		foreach ($configuration['keys'] as $mode => $keys) {
			if ($mode == 'add') {
				foreach ($keys as $keyName => $config) {
					if (!isset($keyInfo[$keyName])) {
						$db->query('ALTER TABLE ' . $tableName . ' ADD INDEX ' . $keyName . ' ' . $config);
					}
				}
			}
			if ($mode == 'remove') {
				foreach ($keys as $keyName => $config) {
					if (isset($keyInfo[$keyName])) {
						$db->query('ALTER TABLE ' . $tableName . ' DROP INDEX ' . $keyName);
					}
				}
			}
		}
	}
}

echo 'Database was migrated.';
?>