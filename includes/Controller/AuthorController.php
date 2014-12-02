<?php
require_once('AbstractController.php');
/**
 * This controller handles author rendering
 */
class AuthorController extends AbstractController {

	public function showAction() {
		$output = '';

		if (count($this->arguments)) {
			// prepare author name and page breadcrumb
			$author = revertAuthorNameCleanup($this->arguments[0]);
			$cleanAuthor = cleanupAuthorName($author);
			$this->pageRenderer->addRootlineItem(array( 'url' => createLinkUrl('author', $cleanAuthor), 'name' => $cleanAuthor));
			$output .= '<h2>Browsing</h2><p>By author <strong>' . htmlspecialchars($cleanAuthor) . '</strong></p>';

			// execute queries
			$limit = 40;
			$offset = max(0, isset($_GET['page']) ? (intval($_GET['page']) -1) : 0) * $limit;
			$addons = $this->db->get_results('SELECT * FROM addon WHERE FIND_IN_SET("' . $this->db->escape($author) . '", provider_name) ' . $this->configuration['addonExcludeClause'] . ' ORDER BY name ASC LIMIT ' . $offset . ', ' . $limit);
			$count = $this->db->get_var('SELECT count(*) FROM addon WHERE FIND_IN_SET("' . $this->db->escape($author) . '", provider_name) ' . $this->configuration['addonExcludeClause']);

			if ($addons && is_array($addons) && count($addons)) {
				$output .= $this->renderAddonList($addons, createLinkUrl('author', $cleanAuthor), $count, $limit);
			} else {
				$this->pageNotFound();
				$output .= renderFlashMessage('No addons found', 'There are currently no addons available in this section');
			}
		} else {
			$this->pageNotFound();
			$this->forward('/');
		}
		return $output;
	}
}
?>