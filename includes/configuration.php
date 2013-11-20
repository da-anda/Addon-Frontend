<?php
/**
 * This file contains the main configuration as simple array
 */

// This sets the context the application is running in.
// It allows to change certain behavior depending on context.
// The currently supported contexts are:
// development		Used in the development environment
// production		Used on the live website
$context = @is_file(dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'developmentConfiguration.php') ? 'development' : 'production';
define('CONTEXT', $context);

// The base configuration of the application
$configuration = array(
	// prepare dummy database settings (will be overridden by context depending configurations)
	'database' => array(
		'username'	=> '',
		'password'	=> '',
		'name'		=> '',
		'server'	=> '',	
	),
	// defines some settings needed to interact with the repositories
	'repository' => array(
		'version' => 'frodo',
		'importUrl' => 'http://mirrors.xbmc.org/addons/frodo/addons.xml',
		'statsUrl' => 'http://mirrors.xbmc.org/addons/addons_simplestats.xml',
	),
	// template and rendering related settings
	'templatePath' => 'templates',
	'images' => array(
		'dummy' => 'images/addon-dummy.png',
		'sizes' => array(
			'addonThumbnail' => array(120,120),
			'addonThumbnailSmall' => array(60,60),
			'large' => array(256,256)
		)
	),
	// cache settings
	'cache' => array(
		'pathWrite' => 'cache' . DIRECTORY_SEPARATOR,
		'pathRead' => 'cache/'
	),
	'baseUrl' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/'
);


// include the context depending configuration at the bottom
// which allows to override any default configuration if needed
if (CONTEXT == 'development') {
	require_once('developmentConfiguration.php');
} else {
	require_once('/etc/xbmc/php-include/addons/private/configuration.php');
}
?>