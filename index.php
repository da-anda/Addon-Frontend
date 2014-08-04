<?php
//  ##############   Include Files  ################ //
require_once('includes/configuration.php');
require_once('includes/db_connection.php');
require_once('includes/functions.php');
require_once('includes/pageRenderer.php');
require_once('includes/Controller/AddonController.php');
//  ##############  Finish Includes  ############### //

startup();

$page = new PageRenderer();

// grab request URI and clean it up to only contain the static url/path segments
$requestUri = urldecode($_SERVER['REQUEST_URI']);
$scriptInfo = pathinfo($_SERVER['SCRIPT_NAME']);

if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])) {
	$requestUri = substr($requestUri, 0, (strlen($_SERVER['QUERY_STRING'])+1)*-1);
}
	// detect if running in a subdirectory and not webroot and fix static segments in that case
if ($scriptInfo['dirname'] != DIRECTORY_SEPARATOR) {
	$requestUri = str_replace($scriptInfo['dirname'], '', $requestUri);
}
	// remove trailing and ending slashes
if($requestUri[0] == '/') {
	$requestUri = substr($requestUri, 1);
}
if (substr($requestUri,-1) == '/') {
	$requestUri = substr($requestUri, 0, -1);
}

// define default action and controller
$methodName = 'indexAction';
$controller = new AddonController();

// check for action requested via URL
if (strlen($requestUri) && $requestUri != 'index.php') {
	$pathSegments = explode('/', $requestUri);
	$controller->setArguments( array_slice($pathSegments, 1) );
	$methodName = strtolower($pathSegments[0]) . 'Action';
	#$page->addRootlineItem(array( 'url' => $pathSegments[0] . '/', 'name' => ucfirst($pathSegments[0])));
}

// only proceed if action exists, else throw a 404
if (method_exists($controller, $methodName)) {
	$content = $controller->$methodName();
	$content .= getDisclaimer();
} else {
	header('HTTP/1.0 404 Not Found');
	$content = renderFlashMessage('Page not found', 'We\'re sorry, but the desired page could not be found.', 'error');
}

// render and exit
$page->setContent($content);
echo $page->render();
shutdown();
?>