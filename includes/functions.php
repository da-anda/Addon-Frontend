<?php
// PHP Functions //
require_once('includes/SimpleImage.php');


//php function to check whether the url exists or not and validate it  
function check_url($url)  
{  
$check = @fopen($url,"r"); // we are opening url with fopen  
if($check)  
 $status = true;  
else  
 $status = false;  
   
return $status;  
}  
 

function getDisclaimer() {
	$view = new TemplateView();
	return $view->render('disclaimer');
}

/**
 * Will check for wrong directory separators and inject the ones returned by the DIRECTORY_SEPARATOR constant
 * 
 * @param string $pathName The path to sanitize
 * @return string The sanitized path
 */
function sanitizeFilePath($pathName) {
	if (!is_string($pathName)) throw new Exception('Can\'t sanitize value of type ' . gettype($pathName) . ' as pathName. String expected.');
	if (!strlen($pathName)) return '';
	return preg_replace('!(\\\\|/)+!is', DIRECTORY_SEPARATOR, $pathName);
}

function shutdown() {
	global $db;
	if (isset($db)) {
		$db->disconnect();
	}
}

/**
 * Will return the IMG url of the addons thumbnail in defined size
 *
 * @param string $addonId	The id of the addon
 * @param string $size		Name of the image size to return. Sizes can be defined in the global configuration
 * @return string
 */
function getAddonThumbnail($addonId, $size) {
	global $configuration;

	$addonWritePath = $configuration['cache']['pathWrite'] . 'Addons' . DIRECTORY_SEPARATOR . $addonId . DIRECTORY_SEPARATOR;
	$addonThumbnailPath = $addonWritePath . 'icon.png';

	// if addon folder doesn't exist, create it and download thumb.
	// this is a temporary workaround until we have a local repo copy.
	// this could also be done in sync script to update old cached icons
	if (!file_exists($addonWritePath)) {
		mkdir($addonWritePath, 0777, TRUE);
		$downloadUrl = 'http://mirrors.xbmc.org/addons/' . $configuration['repository']['version'] . '/' . $addonId . '/icon.png';

		$fp = fopen($addonThumbnailPath, 'w');
		if ($fp) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $downloadUrl);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_HTTPGET, 'GET');
			#curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			$followLocation = @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

			curl_setopt($ch, CURLOPT_FILE, $fp);
			$data = curl_exec($ch);
			#file_put_contents($filePath, curl_exec($ch));
			curl_close($ch);
			fclose($fp);
			if (!$data) {
				unlink($addonThumbnailPath);
			}
		}
	}

	if (!file_exists($addonThumbnailPath)) {
		$addonThumbnailPath = $configuration['images']['dummy'];
	}

	return getThumbnailUrl($addonThumbnailPath, $size);
}

/**
 * Will return the IMG url to the cached thumbnail of given source in desired size
 *
 * @param string $source	The path to the source image
 * @param string $size		Name of the image size to return. Sizes can be defined in the global configuration
 * @return string
 */
function getThumbnailUrl($source, $size) {
	global $configuration;

	if (!file_exists($source) || !is_file($source)) return '';

	$fileInfo = pathinfo($source);
	$cacheFileName = substr(md5($source . $size), 0, 30) . '_' . $fileInfo['filename'] . '.' . $fileInfo['extension'];
	$cacheWritePath = $configuration['cache']['pathWrite'] . 'images' . DIRECTORY_SEPARATOR;
	$fileWritePath = $cacheWritePath . $cacheFileName;
	$targetSize = FALSE;
	if (isset($configuration['images']['sizes'][$size])) {
		$targetSize = $configuration['images']['sizes'][$size];
	}
	$filePath = $source;

	if (!file_exists($cacheWritePath)) {
		mkdir($cacheWritePath, 0777, TRUE);
	}

	// auto update cache if source is newer then cached file
	if (file_exists($fileWritePath) && filemtime($fileWritePath) < filemtime($source)) {
		unlink($fileWritePath);
	}

	// create cache file
	if (!file_exists($fileWritePath) && is_array($targetSize)) {
		$image = new SimpleImage();
		$image->load($source);
		$image->resize($targetSize[0], $targetSize[1]);
		$image->save($fileWritePath);
	}
	if (file_exists($fileWritePath)) {
		$filePath = $configuration['cache']['pathRead'] . 'images/' . $cacheFileName;
	}
	return $filePath;
}
?>