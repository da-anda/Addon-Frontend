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
	'addonExcludeClause' => ' AND id NOT LIKE "Common%" AND id NOT LIKE "script.module%" ',
	'categories' => array(
		'audio' => array(
			'extensionPoint' => 'plugin.audio',
			'label' => 'Audio'
		),
		'video' => array(
			'extensionPoint' => 'plugin.video',
			'label' => 'Video'
		),
		'pictures' => array(
			'extensionPoint' => 'plugin.image',
			'label' => 'Pictures'
		),
		'screensaver' => array(
			'extensionPoint' => 'screensaver',
			'label' => 'Screensaver'
		),
		'skins' => array(
			'extensionPoint' => 'skin',
			'label' => 'Skins'
		),
		'weather' => array(
			'extensionPoint' => 'weather',
			'label' => 'Weather'
		),
		'programs' => array(
			'extensionPoint' => 'plugin.program',
			'label' => 'Programs'
		),
		'metadata' => array(
			'extensionPoint' => 'metadata',
			'label' => 'Metadata',
		),
		'subtitles' => array(
			'extensionPoint' => 'script.xbmc.subtitles',
			'label' => 'Subtitles'
		),
		'services' => array(
			'extensionPoint' => 'service',
			'label' => 'Services'
		),
		'scripts' => array(
			'extensionPoint' => 'script',
			'label' => 'Scripts'
		),
	),
	
	// cache settings
	'cache' => array(
		'pathWrite' => 'cache' . DIRECTORY_SEPARATOR,
		'pathRead' => 'cache/'
	),
	'baseUrl' => (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/',
	'security' => array(
		'token' => ''
	)
);


// include the context depending configuration at the bottom
// which allows to override any default configuration if needed
if (CONTEXT == 'development') {
	require_once('developmentConfiguration.php');
} else {
	require_once('/etc/xbmc/php-include/addons/private/configuration.php');
}
?>