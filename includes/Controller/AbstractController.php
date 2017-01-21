<?php
abstract class AbstractController {

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * @var ezSQL_mysql
	 */
	protected $db;

	/**
	 * @var PageRenderer
	 */
	protected $pageRenderer;

	/**
	 * Injects the arguments
	 * @param array $arguments
	 */
	public function setArguments(array $arguments) {
		$this->arguments = $arguments;
	}

	public function __construct() {
		global $db, $configuration, $page;
		$this->db = &$db;
		$this->configuration = &$configuration;
		$this->pageRenderer = &$page;
	}

	/**
	 * Renders the given addons as thumb list
	 * 
	 * @param array $addons
	 * @param string $uri The URL segment to use for pagination Links
	 * @param integer $itemsTotal
	 * @param integer $itemsPerPage
	 * @return string The rendered list
	 */
	protected function renderAddonList(array $addons, $uri, $itemsTotal, $itemsPerPage = 40) {
		$output = '';
		if (is_array($addons) && count($addons)) {
			$output .= '<ul id="addonList">';
			foreach ($addons as $addon) {
				$output .= '<li>';
				$output .= '<a href="' . createLinkUrl('addon', $addon->id) . '"><span class="thumbnail"><img src="' . getAddonThumbnail($addon, 'addonThumbnail') . '" width="100%" alt="' . $addon->name . '" class="pic" /></span>';
				$output .= '<strong>' . $addon->name . '</strong></a>';
				$output .= '</li>';
			}
			$output .= '</ul>';
			// add pagination
			$output .= renderPagination($uri, $itemsTotal, $itemsPerPage);
		}
		return $output;
	}

	/**
	 * Renders a category list
	 * 
	 * @param array $categories
	 * @return string
	 */
	protected function renderCategoryList($categories) {
		$items = array();
		foreach ($categories as $categoryName => $categoryData) {
			$attributes = isset($categoryData['rootline']) ? array_keys($categoryData['rootline']) : $categoryName;
			$thumbnail = (is_array($attributes) ? implode('/', $attributes) : $attributes) . '.png';
			if (isset($categoryData['thumbnail'])) {
				$thumbnail = $categoryData['thumbnail'];
			}
			$items[] = '<li><a href="' . createLinkUrl('category', $attributes) . '"><span class="thumbnail"><img src="images/categories/' . $thumbnail . '" class="pic" alt="' . $categoryData['label'] . '" /></span><strong>' . $categoryData['label'] . '</strong></a></li>';
		}

		return '<ul id="addonCategories">
				' . implode("\n\t\t\t", $items) .'
		</ul>';
	}

	/**
	 * Forwards to the given url/action
	 * 
	 * @param string $action
	 * @return void
	 */
	protected function forward($action) {
		shutdown();
		header('Location: ' . $this->configuration['baseUrl'] . $action);
		exit;
	}

	/**
	 * Sends a 404 header
	 * 
	 * @return void
	 */
	protected function pageNotFound() {
		header('HTTP/1.0 404 Not Found');
	}

	/**
	 * Sets the page title using the configured pageTitleFormat
	 * 
	 * @param string $title
	 * @return void
	 */
	protected function setPageTitle($title) {
		$this->pageRenderer->setPageTitle(str_replace('###TITLE###', $title, $this->configuration['pageTitleFormat']));
	}
}
?>