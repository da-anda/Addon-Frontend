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
define('SITE_ROOT', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

// The base configuration of the application
$configuration = array(
	// prepare dummy database settings (will be overridden by context depending configurations)
	'database' => array(
		'username'	=> '',
		'password'	=> '',
		'name'		=> '',
		'server'	=> '',
	),
	// addon dependencies to be met in order for them to be imported
	'dependencies' => array(
		'xbmc.python'	=> '2.1.0',
		'xbmc.gui'		=> '5.0.0'
	),
	// classes with custom marker resolvers
	'markerHandler' => array(
		'MarkerResolver' => 'includes/markerResolver.php'
	),
	// defines some settings needed to interact with the repositories
	'repositories' => array(
		'gotham' => array(
			'name' => 'XBMC v13 - Main Add-On Repository',
			'dataUrl' => 'http://mirrors.xbmc.org/addons/gotham/',
			'xmlUrl' => 'http://mirrors.xbmc.org/addons/gotham/addons.xml',
			'statsUrl' => '',
			'downloadUrl' => ''
		),
		'frodo' => array(
			'name' => 'XBMC v12 - Main Add-On Repository',
			'dataUrl' => 'http://mirrors.xbmc.org/addons/frodo/',
			'xmlUrl' => 'http://mirrors.xbmc.org/addons/frodo/addons.xml',
			'statsUrl' => '',
			'downloadUrl' => ''
		)
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
	'analytics' => "<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	
	ga('create', 'UA-3066672-6', 'xbmc.org');
	ga('send', 'pageview');
</script>",
	'addonExcludeClause' => ' AND NOT deleted AND id NOT LIKE "%.common.%" AND id NOT LIKE "script.module%" ',
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
		'games' => array(
			'contentType' => 'game',
			'label' => 'Games'
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
			'extensionPoint' => 'xbmc.subtitle.module',
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
		'pathWrite' => SITE_ROOT . 'cache' . DIRECTORY_SEPARATOR,
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