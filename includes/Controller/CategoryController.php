<?php
require_once('AbstractController.php');
/**
 * This controller handles category rendering
 */
class CategoryController extends AbstractController {

	public function indexAction() {
		$totalcount = $this->db->get_var('SELECT count(*) FROM addon');
		return '<h2>Categories</h2><p>Browse the the Add-on categories below</p>'
			. $this->renderCategoryList($this->configuration['categories']) 
			. '<div class="resultCount">' . $totalcount . ' Add-ons found</div>';
	}

	public function showAction() {
		$arguments = $this->arguments;
		$category = getCategoryFromArguments($arguments);
		$output = '';

		// only continue if we have a valid category
		if ($category) {
			// prepare labels and page rootline
			$categoryLabels = array();
			foreach ($category['rootline'] as $categoryData) {
				$categoryLabels[] = $categoryData['label'];
				$this->pageRenderer->addRootlineItem(array( 'url' => createLinkUrl('category', array_keys($categoryData['rootline'])), 'name' => $categoryData['label']));
			}
			$output .= '<h2>Browsing</h2><p>Category <strong>' . htmlspecialchars(implode(' / ', $categoryLabels)) . '</strong></p>';

			if (isset($category['pageTitle']) && $category['pageTitle']) {
				$this->setPageTitle($category['pageTitle']);
			}

			// render subcategories if available
			if (isset($category['subCategories'])) {
				$output .= $this->renderCategoryList($category['subCategories']);
			// render category content
			} else {
				$controller = 'AddonController';
				$action = 'list';
				// allow categories to use their own controller and action for rendering
				if (isset($category['controller']) && isset($category['action']) && $category['controller'] && $category['action']) {
					$controller = $category['controller'];
					$action = $category['action'];
				}

				require_once('includes/Controller/' . $controller . '.php');
				$controller = new $controller;
				$controller->setArguments($this->arguments);
				$methodName = $action . 'Action';
			
				$output .= $controller->$methodName($category);
			}
		} else {
			$this->pageNotFound();
			$this->forward('/');
		}
		return $output;
	}
}
?>