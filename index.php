<?php
//  ##############   Include Files  ################ //
require_once('includes/configuration.php');
require_once('includes/db_connection.php');
require_once('includes/functions.php');
require_once('includes/pageRenderer.php');
require_once('includes/Controller/AddonController.php');
//  ##############  Finish Includes  ############### //

$page = new PageRenderer();

$requestUri = urldecode($_SERVER['REQUEST_URI']);
if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])) {
	$requestUri = substr($requestUri, 0, (strlen($_SERVER['QUERY_STRING'])+1)*-1);
}
if($requestUri[0] == '/') {
	$requestUri = substr($requestUri, 1);
}
if (substr($requestUri,-1) == '/') {
	$requestUri = substr($requestUri, 0, -1);
}
$methodName = 'indexAction';
$controller = new AddonController();

if (strlen($requestUri) && $requestUri != 'dispatcher.php') {
	$pathSegments = explode('/', $requestUri);
	$controller->setArguments( array_slice($pathSegments, 1) );
	$methodName = strtolower($pathSegments[0]) . 'Action';
	#$page->addRootlineItem(array( 'url' => $pathSegments[0] . '/', 'name' => ucfirst($pathSegments[0])));
}
if (method_exists($controller, $methodName)) {
	$content = $controller->$methodName();
	$content .= getDisclaimer();
} else {
	header('HTTP/1.0 404 Not Found');
	$content = renderFlashMessage('Page not found', 'We\'re sorry, but the desired page could not be found.', 'error');
}

$page->setContent($content);
echo $page->render();
shutdown();
?>