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
		return $this->listAction();
	}

	public function listAction(array $constraints = array(), $url = NULL, array $orderings = array('name' => 'ASC')) {
		$whereClause = '1=1';
		$output = '';
		if (isset($constraints['extensionPoint'])) {
			$extensionPoints = explode(',', $constraints['extensionPoint']);
			foreach($extensionPoints as $key => $value) {
				$extensionPoints[$key] = $this->db->escape($value);
			}
			$whereClause .= ' AND extension_point IN ("' . implode('","', $extensionPoints) . '")';
		}
		if (isset($constraints['contentType']) && $constraints['contentType']) {
			$typeClauses = array();
			$contentTypes = explode(',', $constraints['contentType']);
			foreach ($contentTypes as $contentType) {
				$typeClauses[] = 'FIND_IN_SET("' . $this->db->escape($contentType) . '", content_types)';
			}
			$whereClause .= ' AND (' . implode(' OR ', $typeClauses) . ')';
		}
		if (isset($constraints['extensionIds']) && $constraints['extensionIds']) {
			$whereClause .= ' AND id IN ("' . implode('","', $constraints['extensionIds']) . '")';
		}
		if (isset($constraints['repositories']) && $constraints['repositories']) {
			$whereClause .= ' AND repository_id IN ("' . implode('","', $constraints['repositories']) . '")';
		}
		if (isset($constraints['platforms']) && $constraints['platforms']) {
			$platformClauses = array();
			foreach ($constraints['platforms'] as $platform) {
				$platformClauses[] = 'FIND_IN_SET ("' . $platform . '", platforms)';
			}
			$whereClause .= ' AND (' . implode(' OR ', $platformClauses) . ')';
		}
		if (isset($constraints['languages']) && $constraints['languages']) {
			$langClauses = array();
			foreach ($constraints['languages'] as $language) {
				$langClauses[] = 'FIND_IN_SET ("' . $language . '", languages)';
			}
			$whereClause .= ' AND (' . implode(' OR ', $langClauses) . ')';
		}

		// execute queries
		$limit = 40;
		$offset = max(0, isset($_GET['page']) ? (intval($_GET['page']) -1) : 0) * $limit;
		$orderByClause = '';
		if (count($orderings)) {
			$orderByParts = array();
			foreach ($orderings as $column => $direction) {
				$orderByParts[] = $column . ' ' . $direction;
			}
			$orderByClause = ' ORDER BY ' . implode(',', $orderByParts);
		}

		$addons = $this->db->get_results('SELECT * FROM addon WHERE ' . $whereClause . $this->configuration['addonExcludeClause'] . $orderByClause . ' LIMIT ' . $offset . ', ' . $limit);
		$count = $this->db->get_var('SELECT count(*) FROM addon WHERE ' . $whereClause . $this->configuration['addonExcludeClause']);

		if ($addons && is_array($addons) && count($addons)) {
			$output .= $this->renderAddonList($addons, $url, $count, $limit);
		} else {
			$output .= renderFlashMessage('No addons found', 'There are currently no addons available in this section');
		}
		return $output;
	}

	public function showAction() {
		if (count($this->arguments)) {
			$result = $this->db->get_results('SELECT * FROM addon WHERE id = "' . $this->db->escape($this->arguments[0]) . '"' . $this->configuration['addonExcludeClause'] . ' LIMIT 1');
		}

		$output = '';
		if ($result) {
			// prepare variables and rootline
			$addon = current($result);
			$this->pageRenderer->addRootlineItem(array( 'url' => createLinkUrl('addon', $addon->id), 'name' => 'Details'));
			$this->setPageTitle('"' . $addon->name . '" Add-On for Kodi');
			$stats = getAddonStats($addon);

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
				<span class="thumbnail"><img src="' . getAddonThumbnail($addon, 'large') . '" alt="' . htmlspecialchars($addon->name) . '" class="pic" /></span>
				<h2>' . htmlspecialchars($addon->name) .'</h2>
				<div id="addonMetaData">
				<strong>Author:</strong> ' . implode(', ', $authorLinks);

			// Show the extra details of the Add-on
			$output .= '<br /><strong>Version:</strong> ' . $addon->version;
			if (is_array($stats) && $stats['Total']) {
				$output .= '<br /><strong>Downloads:</strong> ' . number_format($stats['Total']);
			}
			$output .= '<br /><strong>Released:</strong> ' . $addon->updated;

			// Show repository details
			$repoConfig = getRepositoryConfiguration($addon->repository_id);
			if ($repoConfig) {
				if (count($this->configuration['repositories']) > 1) {
					$output .= '<br /><strong>Repository:</strong> ';
					$output .= $repoConfig['downloadUrl'] ? ('<a href="' . $repoConfig['downloadUrl'] . '" rel="nofollow">' . htmlspecialchars($repoConfig['name']) . '</a>') : htmlspecialchars($repoConfig['name']);
				}
			}

			if ($addon->license) {
				$output .= '<br /><strong>License:</strong> ' . formatKodiString($addon->license);
			}
			$output .= '</div>';
			$output .= '<span class="clearfix"></span>';

			if ($addon->news) {
				$output .= '<blockquote class="news"><strong>New in this version:</strong><br />' . formatKodiString($addon->news) . '</blockquote>';
			}

			$output .= '<div class="description"><h4>Description:</h4><p>' . formatKodiString($addon->description) . '</p></div>';

			if ($addon->broken) {
				$output .= renderFlashMessage('Warning', 'This addon is currently reported as broken! <br /><strong>Suggestion / Reason:</strong> ' . formatKodiString($addon->broken) . '.', 'error');
			}

			if ($addon->screenshots) {
				$screenshots = explode(',', $addon->screenshots);
				$gallery = '';
				foreach ($screenshots as $screenshot) {
					$filePath = getAddonScreenshot($addon, $screenshot);
					if ($filePath) {
						$gallery .= '<li><a href="' . $filePath . '" target="_screenshot" rel="lightbox[1]"><img src="' . getAddonScreenshot($addon, $screenshot, 'screenshotPreview') . '" alt="' . $addon->name . ' screenshot" /></li>';
					}
				}
				if ($gallery) {
					$output .= '<div id="screenshots"><h4>Screenshots</h4><ul>' . $gallery . '</ul></div>';
				}
			}

			$output .=  '<ul class="addonLinks">';
			// Check forum link exists
			$forumLink = $addon->forum ? '<a href="' . $addon->forum .'" target="_blank"><img src="images/forum.png" alt="Forum discussion" /></a>' : '<img src="images/forumbw.png" alt="Forum discussion" />';
			$output .=  '<li><strong>Forum Discussion:</strong><br />' . $forumLink . '</li>';

			// Auto Generate Wiki Link
			$output .=  '<li><strong>Wiki Docs:</strong><br /><a href="http://kodi.wiki/index.php?title=Add-on:' . $addon->name . '" target="_blank"><img src="images/wiki.png" alt="Wiki page of this addon" /></a></li>';

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