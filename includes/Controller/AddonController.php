<?php
require_once('AbstractController.php');
/**
 * This controller contains the basic browser logic as well as some of the template rendering.
 * 
 * @todo put the template rendering in templates
 * @todo put the DB stuff in to a AddonDatabase
 */
class AddonController extends AbstractController {

	public function indexAction() {
		$totalcount = $this->db->get_var('SELECT count(*) FROM addon');
		return '<h2>Categories</h2><p>Browse the the Add-on categories below</p>'
			. $this->renderCategoryList($this->configuration['categories']) 
			. '<div class="resultCount">' . $totalcount . ' Add-ons found</div>';
	}

	public function categoryAction() {
		$arguments = $this->arguments;
		$category = getCategoryFromArguments($arguments);
		$output = '<h2>Browsing</h2>';

		// only continue if we have a valid category
		if ($category) {
			// prepare labels and page rootline
			$categoryLabels = array();
			foreach ($category['rootline'] as $categoryData) {
				$categoryLabels[] = $categoryData['label'];
				$this->pageRenderer->addRootlineItem(array( 'url' => createLinkUrl('category', array_keys($categoryData['rootline'])), 'name' => $categoryData['label']));
			}
			$output .= '<p>Category <strong>' . htmlspecialchars(implode(' / ', $categoryLabels)) . '</strong></p>';

			// render subcategories if available
			if (isset($category['subCategories'])) {
				$output .= $this->renderCategoryList($category['subCategories']);
			// show addons if no subcategories
			} else {
				$whereClause = '1=1';
				if (isset($category['extensionPoint'])) {
					$whereClause .= ' AND extension_point = "' . $this->db->escape($category['extensionPoint']) . '"';
				}
				if (isset($category['contentType']) && $category['contentType']) {
					$typeClauses = array();
					$contentTypes = explode(',', $category['contentType']);
					foreach ($contentTypes as $contentType) {
						$typeClauses[] = 'FIND_IN_SET("' . $contentType . '", content_types)';
					}
					$whereClause .= ' AND (' . implode(' OR ', $typeClauses) . ')';
				}

				// execute queries
				$limit = 40;
				$offset = max(0, isset($_GET['page']) ? (intval($_GET['page']) -1) : 0) * $limit;
				$addons = $this->db->get_results('SELECT * FROM addon WHERE ' . $whereClause . $this->configuration['addonExcludeClause'] . ' ORDER BY name ASC LIMIT ' . $offset . ', ' . $limit);
				$count = $this->db->get_var('SELECT count(*) FROM addon WHERE ' . $whereClause . $this->configuration['addonExcludeClause']);

				if ($addons && is_array($addons) && count($addons)) {
					$output .= $this->renderAddonList($addons, createLinkUrl('category', array_keys($category['rootline'])), $count, $limit);
				} else {
					$output .= renderFlashMessage('No addons found', 'There are currently no addons available in this section');
				}
			}
		} else {
			$this->pageNotFound();
			$this->forward('index');
		}
		return $output;
	}

	public function authorAction() {
		$output = '<h2>Browsing</h2>';

		if (count($this->arguments)) {
			// prepare author name and page breadcrumb
			$author = revertAuthorNameCleanup($this->arguments[0]);
			$cleanAuthor = cleanupAuthorName($author);
			$this->pageRenderer->addRootlineItem(array( 'url' => createLinkUrl('author', $cleanAuthor), 'name' => $cleanAuthor));
			$output .= '<p>By author <strong>' . htmlspecialchars($cleanAuthor) . '</strong></p>';

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
			$this->forward('index');
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
			$whereClause = 'id LIKE "' . $keyword . '" OR name LIKE "%' . $keyword . '%" OR description LIKE "%' . $keyword . '" OR provider_name LIKE "%' . $keyword . '%"' . $this->configuration['addonExcludeClause'];
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

	public function showAction() {
		if (count($this->arguments)) {
			$result = $this->db->get_results('SELECT * FROM addon WHERE id = "' . $this->db->escape($this->arguments[0]) . '" LIMIT 1');
		}

		$output = '';
		if ($result) {
			// prepare variables and rootline
			$addon = current($result);
			$this->pageRenderer->addRootlineItem(array( 'url' => createLinkUrl('addon', $addon->id), 'name' => 'Details'));

			// prepare authors and create individual links if more are listed by the addon
			$authors = explode(',', $addon->provider_name);
			$authorLinks = array();
			foreach ($authors as $author) {
				if ($author) {
					$author = cleanupAuthorName($author);
					$authorLinks[] = '<a href="' . createLinkUrl('author', $author) . '">' . htmlspecialchars($author) . '</a>';
				}
			}

			// create details view
			$output .= '<div id="addonDetail">
				<span class="thumbnail"><img src="' . getAddonThumbnail($addon->id, 'large') . '" alt="' . $addon->name . '" class="pic" /></span>
				<h2>' . htmlspecialchars($addon->name) .'</h2>
				<strong>Author:</strong> ' . implode(', ', $authorLinks);

			// Show the extra details of the Add-on
			$output .= '<br /><strong>Version:</strong> ' . $addon->version;
			$output .= '<br /><strong>Released:</strong> ' . $addon->updated;

			// Show repository details
			$repoConfig = getRepositoryConfiguration($addon->repository_id);
			if ($repoConfig) {
				if (count($this->configuration['repositories']) > 1) {
					$output .= '<br /><strong>Repository:</strong> ';
					$output .= $repoConfig['downloadUrl'] ? ('<a href="' . $repoConfig['downloadUrl'] . '" rel="nofollow">' . $repoConfig['name'] . '</a>') : $repoConfig['name'];
				}

				if ($repoConfig['statsUrl'] && $addon->downloads > 0) {
					$output .= '<br /><strong>Downloads:</strong> ' . number_format($addon->downloads);
				}
			}

			if ($addon->license) {
				$output .= '<br /><strong>License:</strong> ' . str_replace('[CR]', '<br />', $addon->license);
			}
			$output .= '<div class="description"><h4>Description:</h4><p>' . str_replace('[CR]', '<br />', $addon->description) . '</p></div>';

			if ($addon->broken) {
				$output .= renderFlashMessage('Warning', 'This addon is currently reported as broken! <br /><strong>Suggestion / Reason:</strong> ' . htmlspecialchars($addon->broken) . '.', 'error');
			}

			$output .=  '<ul class="addonLinks">';
			// Check forum link exists
			$forumLink = $addon->forum ? '<a href="' . $addon->forum .'" target="_blank"><img src="images/forum.png" alt="Forum discussion" /></a>' : '<img src="images/forumbw.png" alt="Forum discussion" />';
			$output .=  '<li><strong>Forum Discussion:</strong><br />' . $forumLink . '</li>';

			// Auto Generate Wiki Link
			$output .=  '<li><strong>Wiki Docs:</strong><br /><a href="http://wiki.xbmc.org/index.php?title=Add-on:' . $addon->name . '" target="_blank"><img src="images/wiki.png" alt="Wiki page of this addon" /></a></li>';

			// Check sourcecode link exists
			$sourceLink = $addon->source ? '<a href="' . $addon->source .'" target="_blank"><img src="images/code.png" alt="Source code" /></a>' : '<img src="images/codebw.png" alt="Source code" />';
			$output .=  "<li><strong>Source Code:</strong><br />" . $sourceLink . '</li>';

			// Check website link exists
			$websiteLink = $addon->website ? '<a href="' . $addon->website .'" target="_blank"><img src="images/website.png" alt="Website" /></a>' : '<img src="images/websitebw.png" alt="Website" />';
			$output .=  "<li><strong>Website:</strong><br />" . $websiteLink . '</li>';

			// Show the Download link
			$downloadLink = getAddonDownloadLink($addon);
			if ($downloadLink) {
				$output .= '<li><strong>Direct Download:</strong><br />';
				$output .= '<a href="' . $downloadLink . '" rel="nofollow"><img src="images/download_link.png" alt="Download" /></a></li>';
			}

			$output .= '</ul></div>';
		} else {
			$this->pageNotFound();
		}
		return $output;
	}
}
?>