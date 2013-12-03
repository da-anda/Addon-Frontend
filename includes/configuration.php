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
			#'extensionPoint' => 'xbmc.python.pluginsource',
			'contentType' => 'audio',
			'label' => 'Audio'
		),
		'video' => array(
			#'extensionPoint' => 'xbmc.python.pluginsource',
			'contentType' => 'video',
			'label' => 'Video'
		),
		'pictures' => array(
			#'extensionPoint' => 'xbmc.python.pluginsource',
			'contentType' => 'image',
			'label' => 'Pictures'
		),
		'screensaver' => array(
			'extensionPoint' => 'xbmc.ui.screensaver',
			'label' => 'Screensaver'
		),
		'skins' => array(
			'extensionPoint' => 'xbmc.gui.skin',
			'label' => 'Skins'
		),
		'weather' => array(
			'extensionPoint' => 'xbmc.python.weather',
			'label' => 'Weather'
		),
		'programs' => array(
			#'extensionPoint' => 'xbmc.python.pluginsource',
			'contentType' => 'executable',
			'label' => 'Programs'
		),
		'lyrics' => array(
			'extensionPoint' => 'xbmc.python.lyrics',
			'label' => 'Lyrics'
		),
		'webinterface' => array(
			'extensionPoint' => 'xbmc.gui.webinterface',
			'label' => 'Webinterface'
		),
		'metadata' => array(
			'extensionPoint' => 'xbmc.metadata',
			'label' => 'Metadata',
			'subCategories' => array(
				'artists' => array(
					'extensionPoint' => 'xbmc.metadata.scraper.artists',
					'label' => 'Artists'
				),
				'albums' => array(
					'extensionPoint' => 'xbmc.metadata.scraper.albums',
					'label' => 'Albums'
				),
				'movies' => array(
					'extensionPoint' => 'xbmc.metadata.scraper.movies',
					'label' => 'Movies'
				),
				'musicvideos' => array(
					'extensionPoint' => 'xbmc.metadata.scraper.musicvideos',
					'label' => 'Musicvideos'
				),
				'tvshows' => array(
					'extensionPoint' => 'xbmc.metadata.scraper.tvshows',
					'label' => 'TV-Shows'
				),
			)
		),
		'subtitles' => array(
			'extensionPoint' => 'xbmc.python.subtitles',
			'label' => 'Subtitles'
		),
		'services' => array(
			'extensionPoint' => 'xbmc.service',
			'label' => 'Services'
		),
/*
		'scripts' => array(
			'extensionPoint' => 'xbmc.python.script',
			'contentType' => 'none',
			'label' => 'Scripts',
			'subCategories' => array(
				'audio' => array(
					'extensionPoint' => 'xbmc.python.script',
					'contentType' => 'audio',
					'label' => 'Audio'
				),
				'video' => array(
					'extensionPoint' => 'xbmc.python.script',
					'contentType' => 'video',
					'label' => 'Videos'
				),
				'pictures' => array(
					'extensionPoint' => 'xbmc.python.script',
					'contentType' => 'image',
					'label' => 'Pictures'
				),
				'programs' => array(
					'extensionPoint' => 'xbmc.python.script',
					'contentType' => 'executable',
					'label' => 'Tools'
				)
			)
		),
*/
	),
	
	// cache settings
	'cache' => array(
		'pathWrite' => 'cache' . DIRECTORY_SEPARATOR,
		'pathRead' => 'cache/'
	),
	'baseUrl' => NULL,
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