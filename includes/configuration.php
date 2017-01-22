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
		'xbmc.gui'		=> '5.3.0'
	),
	// classes with custom marker resolvers
	'markerHandler' => array(
		'MarkerResolver' => 'includes/markerResolver.php'
	),
	// defines some settings needed to interact with the repositories
	'repositories' => array(
/*
		'gotham' => array(
			'name' => 'Kodi v13 - Main Add-On Repository',
			'dataUrl' => 'http://mirrors.kodi.tv/addons/gotham/',
			'xmlUrl' => 'http://mirrors.kodi.tv/addons/gotham/addons.xml',
			'downloadUrl' => '',
			'enableStats' => TRUE
		),
		'helix' => array(
			'name' => 'Kodi v14 - Main Add-On Repository',
			'dataUrl' => 'http://mirrors.kodi.tv/addons/helix/',
			'xmlUrl' => 'http://mirrors.kodi.tv/addons/helix/addons.xml',
			'downloadUrl' => '',
			'enableStats' => TRUE
		),
		'isengard' => array(
			'name' => 'Kodi v15 - Main Add-On Repository',
			'dataUrl' => 'http://mirrors.kodi.tv/addons/isengard/',
			'xmlUrl' => 'http://mirrors.kodi.tv/addons/isengard/addons.xml',
			'downloadUrl' => '',
			'enableStats' => TRUE
		),
*/
		'jarvis' => array(
			'name' => 'Kodi v16 - Main Add-On Repository',
			'dataUrl' => 'http://mirrors.kodi.tv/addons/jarvis/',
			'xmlUrl' => 'http://mirrors.kodi.tv/addons/jarvis/addons.xml',
			'downloadUrl' => '',
			'enableStats' => TRUE
		),
		'krypton' => array(
			'name' => 'Kodi v17 - Main Add-On Repository',
			'dataUrl' => 'http://mirrors.kodi.tv/addons/krypton/',
			'xmlUrl' => 'http://mirrors.kodi.tv/addons/krypton/addons.xml',
			'downloadUrl' => '',
			'enableStats' => TRUE
		)
	),
	// template and rendering related settings
	'templatePath' => 'templates',
	'defaultPageTitle' => 'Kodi Add-On Repository',
	'pageTitleFormat' => '###TITLE### | Kodi Add-On Repository',
	'rootlineHomeLabel' => 'Add-Ons',
	'images' => array(
		'dummy' => 'images/addon-dummy.png',
		'dummyFanart' => '',
		'sizes' => array(
			'addonThumbnail' => array(120,120),
			'addonThumbnailSmall' => array(60,60),
			'large' => array(256,256),
			'screenshotPreview' => array(160,90),
			'screenshot' => array(1280,720)
		)
	),
	'analytics' => "<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	
	ga('create', 'UA-3066672-6', 'auto');
	ga('send', 'pageview');
</script>",
	'addonExcludeClause' => ' AND NOT deleted AND id NOT LIKE "%.common.%" AND id NOT LIKE "script.module%" AND id NOT LIKE "resource.language.%" ',
	'categories' => array(
		'audio' => array(
			#'extensionPoint' => 'xbmc.python.pluginsource',
			'contentType' => 'audio',
			'label' => 'Audio',
			'pageTitle' => 'Audio Add-ons'
		),
		'video' => array(
			#'extensionPoint' => 'xbmc.python.pluginsource',
			'contentType' => 'video',
			'label' => 'Video',
			'pageTitle' => 'Video Add-ons'
		),
		'pictures' => array(
			#'extensionPoint' => 'xbmc.python.pluginsource',
			'contentType' => 'image',
			'label' => 'Pictures',
			'pageTitle' => 'Picture Add-ons'
		),
		'screensaver' => array(
			'extensionPoint' => 'xbmc.ui.screensaver',
			'label' => 'Screensaver',
			'pageTitle' => 'Screensavers'
		),
		'skins' => array(
			'extensionPoint' => 'xbmc.gui.skin',
			'label' => 'Skins',
			'pageTitle' => 'Skins'
		),
		'weather' => array(
			'extensionPoint' => 'xbmc.python.weather',
			'label' => 'Weather',
			'pageTitle' => 'Weather provider'
		),
		'games' => array(
			'contentType' => 'game',
			'label' => 'Games',
			'pageTitle' => 'Games'
		),
		'programs' => array(
			#'extensionPoint' => 'xbmc.python.pluginsource',
			'contentType' => 'executable',
			'label' => 'Programs',
			'pageTitle' => 'Programs &amp; tools'
		),
		'lyrics' => array(
			'extensionPoint' => 'xbmc.python.lyrics',
			'label' => 'Lyrics',
			'pageTitle' => 'Lyrics providers'
		),
		'webinterface' => array(
			'extensionPoint' => 'xbmc.gui.webinterface',
			'label' => 'Webinterface',
			'pageTitle' => 'Webinterfaces'
		),
		'metadata' => array(
			'extensionPoint' => 'xbmc.metadata',
			'label' => 'Metadata',
			'pageTitle' => 'Metadata provider',
			'subCategories' => array(
				'artists' => array(
					'extensionPoint' => 'xbmc.metadata.scraper.artists',
					'label' => 'Artists',
					'pageTitle' => 'Provider for artists metadata'
				),
				'albums' => array(
					'extensionPoint' => 'xbmc.metadata.scraper.albums',
					'label' => 'Albums',
					'pageTitle' => 'Provider for music album metadata'
				),
				'movies' => array(
					'extensionPoint' => 'xbmc.metadata.scraper.movies',
					'label' => 'Movies',
					'pageTitle' => 'Provider for movie metadata'
				),
				'musicvideos' => array(
					'extensionPoint' => 'xbmc.metadata.scraper.musicvideos',
					'label' => 'Musicvideos',
					'pageTitle' => 'Provider for music video metadata'
				),
				'tvshows' => array(
					'extensionPoint' => 'xbmc.metadata.scraper.tvshows',
					'label' => 'TV-Shows',
					'pageTitle' => 'Provider for TV-Shows metadata'
				),
			)
		),
		'subtitles' => array(
			'extensionPoint' => 'xbmc.subtitle.module',
			'label' => 'Subtitles',
			'pageTitle' => 'Subtitle Provider'
		),
		'services' => array(
			'extensionPoint' => 'xbmc.service',
			'label' => 'Services',
			'pageTitle' => 'Service Add-ons'
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
	// routing configuration
	'routes' => array(
		'_default' => array(
			'controller' => 'CategoryController',
			'action' => 'index'
		),
		'category' => array(
			'_default' => array(
				'controller' => 'CategoryController',
				'action' => 'show'
			),
		),
		'show' => array(
			'_default' => array(
				'controller' => 'AddonController',
				'action' => 'show'
			),
		),
		'search' => array(
			'_default' => array(
				'controller' => 'SearchController',
				'action' => 'index'
			),
		),
		'author' => array(
			'_default' => array(
				'controller' => 'AuthorController',
				'action' => 'show'
			),
		),
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
