<?php
class MarkerResolver {

	/**
	 * @var ezSQL_mysql
	 */
	protected $db;

	/**
	 * @var array
	 */
	protected $configuration;


	public function __construct() {
		global $db, $configuration;
		$this->db = &$db;
		$this->configuration = &$configuration;
	}

	/**
	 * Returns random addons
	 *
	 * @return string
	 */
	public function getRandomAddonsWidgetMarker() {
		// Show some random Add-ons
		$items = '';
		$random = $this->db->get_results('SELECT * FROM addon WHERE 1=1 ' . $this->configuration['addonExcludeClause'] . ' AND NOT broken ORDER BY RAND() DESC LIMIT 5');
		if (is_array($random) && count($random)) {
			$items = '<ul class="overview">';
			foreach ($random as $addon) {
				$description = str_replace('[CR]', '', $addon->description);
				$items .= '<li><a class="thumb" href="' . createLinkUrl('addon', $addon->id) . '"><img src="' . getAddonThumbnail($addon, 'addonThumbnail') . '" height="110" alt="' . $addon->name . '" class="pic" /></a>';
				$items .= '<h3>' . htmlspecialchars($addon->name) . '</h3>';
				$items .= '<p>' . htmlspecialchars( substr($description, 0, 190) ) . (strlen($description) < 190 ? '' : '...') . '</p></li>';
			}
			$items .= '</ul>';
		}
		$content = '
			<div class="carousel_container">
				<a class="buttons prev" href="#" data-toggle="prev">left</a>
				<div class="viewport">
					' . $items . '
				</div>
				<a class="buttons next" data-toggle="next" href="#">right</a>
			</div>
		';
		return $this->makeWidget('Random Add-ons', $content, 'widget_recent_projects');
	}

	/**
	 * Renders the addon statistics (newest, recently updated, ...)
	 * 
	 * @return string
	 */
	public function getAddonStatisticsWidgetMarker() {
		$tabData = array(
			'newest' => array(
				'label' => 'Newest',
				'title' => 'Newest add-ons',
				'results' => $this->db->get_results("SELECT * FROM addon WHERE 1=1 " . $this->configuration['addonExcludeClause'] . " ORDER BY created DESC LIMIT 5")
			),
			'updated' => array(
				'label' => 'Updated',
				'title' => 'Recently updated add-ons',
				'results' => $this->db->get_results("SELECT * FROM addon WHERE 1=1 " . $this->configuration['addonExcludeClause'] . "  ORDER BY updated DESC LIMIT 5")
			),
			'popular' => array(
				'label' => 'Popular',
				'title' => 'Popular add-ons',
				'results' => $this->db->get_results("SELECT * FROM addon WHERE downloads > 0 " . $this->configuration['addonExcludeClause'] . " AND NOT broken ORDER BY downloads DESC LIMIT 5")
			)
		);
		$tabs = array(
			'tab' => array(),
			'content' => array()
		);
		foreach ($tabData as $id => $tabConfig) {
			if ($tabConfig['results'] && count($tabConfig['results'])) {
				$tabs['tab'][] = '<li><a href="#' . $id . '" title="' . $tabConfig['title'] . '">' . $tabConfig['label'] . '</a></li>';
				$tabs['content'][] = '<div class="tabs-inner" id="' . $id . '">' . $this->renderAddonList($tabConfig['results']) . '</div>';
			}
		}

		$content = '
			<div id="tabs">
				<!-- Tabs Menu -->
				<ul id="tab-items">
					' . implode('', $tabs['tab']) . '
				</ul>
				<!-- Tab Container -->
				' . implode('', $tabs['content']) . '
			</div>
		';
		return $this->makeWidget('Add-on statistics', $content);
	}

	/**
	 * Renders the addon author ranking
	 *
	 * @return string
	 */
	public function getTopDevelopersWidgetMarker() {
		$top5 = $this->db->get_results("SELECT *, COUNT( provider_name ) AS counttotal FROM addon WHERE 1=1 " . $this->configuration['addonExcludeClause'] . " GROUP BY provider_name ORDER BY counttotal DESC LIMIT 9");
		$counter = 0;
		$iconMap = array(
			1 => 'gold.png',
			2 => 'silver.png',
			3 => 'bronze.png',
		);
		if (is_array($top5) && count($top5)) {
			$developers = '';
			foreach ($top5 as $top5s) {
				$author = cleanupAuthorName($top5s->provider_name);
				$counter++;
				$icon = 'images/' . (isset($iconMap[$counter]) ? $iconMap[$counter] : $counter . '.png');
				$developers .= "<li><img src='$icon' height='20' width='20' alt='Rank $counter' /><a href='" . createLinkUrl('author', $author) . "' title='Show all addons from this author'> " . substr($author, 0, 15) . " ($top5s->counttotal uploads)</a></li>";
			}
			return $this->makeWidget('Top developers', '<ul class="topDevelopers">' . $developers . '</ul>');
		}
		return '';
	}

	/**
	 * Wraps the content into a sidebar widget container
	 *
	 * @param string $headline
	 * @param string $content
	 * @param string $additionalClasses
	 * @return string 
	 */
	protected function makeWidget($headline, $content, $additionalClasses = FALSE) {
		return '
		<div class="widget-container' . ($additionalClasses ? ' ' . $additionalClasses : '') . '">
			<h2>' . $headline . '</h2>
			' . $content . '
		</div>
		';
	}

	/**
	 * Renders a list of addons with thumbnail and title
	 * 
	 * @param array $addons	The addons to render
	 * @return string
	 */
	protected function renderAddonList(array $addons) {
		$output = '';
		if (is_array($addons) && count($addons)) {
			$output .= '<ul class="addonteaser">';
			foreach ($addons as $addon) {
				$output .= "<li><a href='" . createLinkUrl('addon', $addon->id) . "'>";
				$output .= "<img src='" . getAddonThumbnail($addon, 'addonThumbnailSmall') . "' width='60' height='60' alt='$addon->name' class='pic alignleft' />";
				$output .= "<b>$addon->name</b></a>";
				$output .= "<span class='date'>".$addon->updated."</span>";
				$output .= "</li>";
			}
			$output .= '</ul>';
		}
		return $output;
	}
}
?>
