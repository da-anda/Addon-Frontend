<?php
require_once('AbstractController.php');
/**
 * This controller handles searches
 */
class SearchController extends AbstractController {

	public function indexAction() {
		$output = '';
		if ($_GET['keyword']) {
			return $this->searchAction();
		} else {
			// @todo add search form
		}
		return $output;
	}

	public function searchAction() {
		$output = '';
		if ($_GET['keyword']) {
			// perform search query
			$limit = 40;
			$offset = max(0, isset($_GET['page']) ? (intval($_GET['page']) -1) : 0) * $limit;
			$keyword = $this->db->escape($_GET['keyword']);
			$whereClause = '(id LIKE "' . $keyword . '" OR name LIKE "%' . $keyword . '%" OR description LIKE "%' . $keyword . '" OR provider_name LIKE "%' . $keyword . '%")' . $this->configuration['addonExcludeClause'];
			$addons = $this->db->get_results('SELECT * FROM addon WHERE ' . $whereClause . ' ORDER BY name ASC LIMIT ' . $offset . ', ' . $limit);
			$count = $this->db->get_var('SELECT count(*) FROM addon WHERE ' . $whereClause);

			// render result
			$output .= '<h2>Search</h2><p>Search results for <strong>' . htmlspecialchars($_GET['keyword']) . '</strong></p>';
			if ($addons && is_array($addons) && count($addons)) {
				$output .= $this->renderAddonList($addons, createLinkUrl('search', $_GET['keyword']), $count, $limit);
			} else {
				$output .= renderFlashMessage('No addons found', 'There where no addons found for this keyword.');
			}
		} else {
			$output .= renderFlashMessage('How to search', 'Please enter the keyword you\'re looking for in the search field on the top right corner of the website.', 'info');
		}
		return $output;
	}
}
?>