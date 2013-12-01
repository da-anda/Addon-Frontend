<?php
require_once('templateView.php');

class PageRenderer {

	/**
	 * the document/page title
	 *
	 * @var string
	 */
	protected $pageTitle = 'Add-Ons';

	/**
	 * The content of the page
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * Array with the rootline of the current page, used to render the breadcrumb.
	 * An item has to look like this:
	 *		$rootline = array(
	 * 			// sample item
	 *			array(
	 *				'url' => 'index.php',
	 * 				 'name' => 'Home'
	 * 			) 
	 * 		)
	 *
	 * @var array
	 */
	protected $rootline = array();

	/**
	 * The page template to use
	 *
	 * @var string
	 */
	protected $template = 'page';


	/**
	 * Setter for the pageTitle
	 *
	 * @param string $pageTitle
	 * @return void
	 */
	public function setPageTitle($pageTitle) {
		$this->pageTitle = $pageTitle;
	}

	/**
	 * Getter for the pageTitle
	 *
	 * @return string $pageTitle
	 */
	public function getPageTitle() {
		return $this->pageTitle;
	}

	/**
	 * Setter for the content
	 *
	 * @param string content
	 * @return void
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Getter for the content
	 *
	 * @return string content
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Setter for the rootline of the current page
	 *
	 * @param array $rootline
	 * @return void
	 */
	public function setRootline(array $rootline) {
		$this->rootline = $rootline;
	}

	/**
	 * Adds a page to the rootline
	 *
	 * @param array An array with key => value pairs having 'url' and 'name' as keys
	 * @return void
	 */
	public function addRootlineItem(array $item) {
		$this->rootline[] = $item;
	}

	/**
	 * Setter for the page template to use
	 *
	 * @param string $template
	 * @return void
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}



	/**
	 * Renders the breadcrumb
	 *
	 * @return string The breadcrumb as html
	 */
	protected function renderBreadCrumb() {
		if (!count($this->rootline)) return '';

		$rootline = array_merge(array(array('url' => './', 'name' => 'Add-Ons')), $this->rootline);
		$current = array_pop($rootline);
		$items = array();
		foreach($rootline as $item) {
			$items[] = '<a href="' . $item['url'] . '">' . htmlspecialchars($item['name']) . '</a>'; 
		}
		$output = implode(' » ', $items) . ' » ' . htmlspecialchars($current['name']);

		$marker = array(
			'###ROOTLINE###' => $output,
		);

		$view = new TemplateView();
		$view->setTemplate('breadcrumb');
		$view->setMarker($marker);
		return $view->render();
	}

	/**
	 * Renders the final page output
	 *
	 * @return string The rendered page;
	 */
	public function render() {
		global $configuration;
		$marker = array(
			'###PAGETITLE###' => $this->pageTitle,
			'###BREADCRUMB###' => $this->renderBreadCrumb(),
			'###CONTENT###' => $this->content,
			'###BASEURL###' => $configuration['baseUrl']
		);

		$view = new TemplateView();
		$view->setTemplate($this->template);
		$view->setMarker($marker);
		return $view->render();
	}
}
?>