<?php
require_once('markerManager.php');

class TemplateView {

	/**
	 * The page template to use
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * RenderCache
	 * 
	 * @var array
	 */
	protected $renderCache = array();

	/**
	 * Internal instance of the markerManager
	 * 
	 * @var MarkerManager
	 */
	protected $markerManager;



	public function __construct() {
		global $markerManager;
		if (isset($markerManager)) {
			$this->markerManager = &$markerManager;
		} else {
			$this->markerManager = new MarkerManager();
		} 
	}

	/**
	 * Setter for the template to use
	 *
	 * @param string $template
	 * @return void
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}

	/**
	 * Setter for the markers to replace in the template
	 *
	 * @param array $marker
	 * @return void
	 */
	public function setMarker(array $marker) {
		foreach ($marker as $name => $value) {
			$this->markerManager->setMarker($name, $value);
		}
	}

	/**
	 * Registers a additional marker handler to resolve missing markers
	 *
	 * @param mixed $className	Either Classname or Instance
	 * @return void
	 */
	public function addMarkerHandler($className) {
		$this->markerManager->registerHandler($className);
	}

	/**
	 * Renders the final page output
	 *
	 * @param string $templateName The template to render
	 * @return string The rendered page;
	 */
	public function render($templateName = NULL) {
		if ($templateName === NULL) $templateName = $this->template;

		if (isset($this->renderCache['content'][$templateName])) {
			return $this->renderCache['content'][$templateName];
		}
		// add template to processed list in order to prevent endless recursive nesting of template when rendering subtemplates
		if (isset($this->renderCache['processed']) && in_array($templateName, $this->renderCache['processed'])) {
			throw new Exception( sprintf('Recursive template rendering detected. Nesting path: %s', implode(' -> ', $this->renderCache['processed'])) );
		}
		$this->renderCache['processed'][] = $templateName;

		$templateCode = $this->getTemplate($templateName);
		$marker = $this->resolveUsedMarkers($templateCode);

		$output = $this->replaceMarker($marker, $templateCode);
		$this->renderCache['content'][$templateName] = $output;
		return $output;
	}

	/**
	 * Replaces a collection of markers in a template
	 *
	 * @param array $marker
	 * @param string $template
	 * @return string
	 */
	protected function replaceMarker(array $marker, $template) {
		return strtr($template, $marker);
	}

	/**
	 * Reads the content of a template and returns it
	 *
	 * @param string $templateName
	 * @param string $extension The file extension
	 * @return string
	 */
	protected function getTemplate($templateName, $extension = 'html') {
		if (!strlen($templateName)) throw new Exception('Empty template name given - can\'t render template!');

		$pathInfo = pathinfo($templateName);

		$templatePath = $this->getTemplatePath();
		$templateFile = $templateName;

		if (!isset($pathInfo['extension'])) {
			$templateFile .= '.' . $extension;
		} else {
			$extension = $pathInfo['extension'];
		}

		if (!is_file($templatePath . $templateFile)) throw new Exception(sprintf('Template file &quot;%s&quot; not found in the template path', $templateFile) );

		if ($extension == 'php') {
			ob_flush();
	 		ob_start();
			include($templatePath . $templateFile);
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		} else {
			return file_get_contents($templatePath . $templateFile);
		}
	}

	/**
	 * Resolves the markers used in the template
	 * 
	 * @param string $tempalteCode The raw template code (not the template name)
	 * @return array Array with ###MARKER### => 'Replacement value' pairs
	 */
	protected function resolveUsedMarkers($templateCode) {
		if (!strlen($templateCode)) return array();

		$markerArray = array();
		// find placeholders/markers for content
		if (preg_match_all('!(###|%%%)([A-Za-z0-9\._-|]*)\1!is', $templateCode, $matches)) {
			$usedMarkers = array_unique($matches[2]);

			if (count($usedMarkers)) {
				foreach ($usedMarkers as $key => $markerName) {
					$wrapper = $matches[1][$key];
					$marker = $wrapper . $markerName . $wrapper;
					// predefined? don't resolve new content for it 
					if (isset($this->marker[$marker])) continue;

					// try to find the replacement content
					$replacement = $this->getMarkerContent($markerName, $wrapper);
					if($replacement == '' && isset($markerArray[$marker])) {
						$replacement = $markerArray[$marker];
					}
					$markerArray[$marker] = $replacement;
				}		
			}
		}

		return $markerArray;
	}

	/**
	 * Resolves the placeholders/markers of nested templates
	 * 
	 * @param string $markerName The name of the marker
	 * @param string $wrapper The wrapping string of the marker, like '###' or '%%%' that determins the type of marker
	 * @return string The rendered marker if available
	 */
	protected function getMarkerContent($markerName, $wrapper) {
		$content = '';
		switch ($wrapper) {
			default:
			case '###':
				if ($this->markerManager->hasMarker($markerName, $wrapper)) {
					$content = $this->markerManager->getMarker($markerName, $wrapper);
				}
				break;
			case '%%%';
				$templatePath = $this->getTemplatePath();
				$templateInfo = pathinfo($markerName);
				if (isset($templateInfo['extension'])) {
					$content = $this->render($markerName);
				} else if (@is_file($templatePath . $markerName . '.html')) {
					$content = $this->render($markerName . '.html');
				} else if (@is_file($templatePath . $markerName . '.php')) {
					$content = $this->getTemplate($markerName . '.php');
				}
				break;
		}
		return $content;
	}

	/**
	 * Returns the absolute template path
	 *
	 * @return string
	 */
	protected function getTemplatePath() {
		global $configuration;
		$root = getcwd();
		return $root . DIRECTORY_SEPARATOR . $configuration['templatePath'] . DIRECTORY_SEPARATOR;
	}
}
?>