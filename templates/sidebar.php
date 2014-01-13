<?php
global $db, $configuration;

function renderAddonList(array $addons) {
	$output = '';
	if (is_array($addons) && count($addons))
	{
		$output .= '<ul>';
		foreach ($addons as $addon)
		{
			$output .= "<li><a href='" . createLinkUrl('addon', $addon->id) . "'>";
			$output .= "<img src='" . getAddonThumbnail($addon->id, 'addonThumbnailSmall') . "' width='60' height='60' alt='$addon->name' class='pic alignleft' />";
			$output .= "<b>$addon->name</b></a>";
			$output .= "<span class='date'>".$addon->updated."</span>";
			$output .= "</li>";
		}
		$output .= '</ul>';
	}
	return $output;
}
?>		<!-- Sidebar -->
		<div id="sidebar">
			<!-- Random add-ons widget-->
			<div class="widget-container widget_recent_projects">
				<h2>Random Add-ons</h2>
				<div class="carousel_container">
					<a class="buttons prev" href="#">left</a>
					<div class="viewport">
						<?php
						// Show some random Add-ons
						$random = $db->get_results('SELECT * FROM addon WHERE 1=1 ' . $configuration['addonExcludeClause'] . ' AND NOT broken ORDER BY RAND() DESC LIMIT 5');
						if (is_array($random) && count($random)) {
							echo '<ul class="overview">';
							foreach ($random as $addon) {
								$description = str_replace('[CR]', '', $addon->description);
								echo '<li><a class="thumb" href="' . createLinkUrl('addon', $addon->id) . '"><img src="' . getAddonThumbnail($addon->id, 'addonThumbnail') . '" height="110" alt="' . $addon->name . '" class="pic" /></a>';
								echo '<h5>' . htmlspecialchars($addon->name) . '</h5>';
								echo '<p>' . htmlspecialchars( substr($description, 0, 190) ) . (strlen($description) < 190 ? '' : '...') . '</p></li>';
							}
							echo '</ul>';
						}
						?>
					</div>
					<a class="buttons next" href="#">right</a>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Tabbed Box -->
			<div class="widget-container">
				<!-- Start Tabbed Box Container -->
				<?php
					$tabData = array(
						'newest' => array(
							'label' => 'Newest',
							'title' => 'Newest add-ons',
							'results' => $db->get_results("SELECT * FROM addon WHERE 1=1 " . $configuration['addonExcludeClause'] . " ORDER BY created DESC LIMIT 5")
						),
						'updated' => array(
							'label' => 'Update',
							'title' => 'Recently updated add-ons',
							'results' => $db->get_results("SELECT * FROM addon WHERE 1=1 " . $configuration['addonExcludeClause'] . "  ORDER BY updated DESC LIMIT 5")
						),
						'popular' => array(
							'label' => 'Popular',
							'title' => 'Popular add-ons',
							'results' => $db->get_results("SELECT * FROM addon WHERE downloads > 0 " . $configuration['addonExcludeClause'] . " AND NOT broken ORDER BY downloads DESC LIMIT 5")
						)
					);
					$tabs = array(
						'tab' => array(),
						'content' => array()
					);
					foreach ($tabData as $id => $tabConfig) {
						if ($tabConfig['results'] && count($tabConfig['results'])) {
							$tabs['tab'][] = '<li><a href="#' . $id . '" title="' . $tabConfig['title'] . '">' . $tabConfig['label'] . '</a></li>';
							$tabs['content'][] = '<div class="tabs-inner" id="' . $id . '">' . renderAddonList($tabConfig['results']) . '</div>';
						}
					}
				?>
				<div id="tabs">
					<!-- Tabs Menu -->
					<ul id="tab-items">
						<?php echo implode('', $tabs['tab']); ?>
					</ul>
					<!-- Tab Container -->
					<?php echo implode('', $tabs['content']); ?>
				</div>
				<!-- End Tabbed Box Container -->
			</div>

			<?php
			$top5 = $db->get_results("SELECT *, COUNT( provider_name ) AS counttotal FROM addon WHERE 1=1 " . $configuration['addonExcludeClause'] . " GROUP BY provider_name ORDER BY counttotal DESC LIMIT 9");
			$counter = 0;
			$iconMap = array(
				1 => 'gold.png',
				2 => 'silver.png',
				3 => 'bronze.png',
			);
			if (is_array($top5) && count($top5)):
			?>
			<!-- Any Widget -->
			<div class="widget-container">
				<h2>Top Developers</h2>
				<ul>
			<?php
				foreach ($top5 as $top5s) {
					$author = cleanupAuthorName($top5s->provider_name);
					$counter++;
					$icon = 'images/' . (isset($iconMap[$counter]) ? $iconMap[$counter] : $counter . '.png');
					echo "<li><img src='$icon' height='20' width='20' alt='Rank $counter' /><a href='" . createLinkUrl('author', $author) . "' title='Show all addons from this author'> " . substr($author, 0, 15) . " ($top5s->counttotal uploads)</a></li>";
				}
			?>
				</ul>
			</div>
			
			<div class="widget-container">
				<h2>Guides</h2>
				<ul>
			<?php
					echo "<li><img src='images/pin.png' height='22' width='22' /><a href='http://wiki.xbmc.org/index.php?title=How_to_install_an_Add-on_from_a_zip_file' > Install an Add-on from a zip file</a></li>";
					echo "<li><img src='images/pin.png' height='22' width='22' /><a href='http://wiki.xbmc.org/index.php?title=How_to_install_an_Add-on_using_the_GUI' > Install an Add-on from the GUI</a></li>";
					echo "<li><img src='images/pin.png' height='22' width='22' /><a href='http://wiki.xbmc.org/index.php?title=Submitting_Add-ons' > How to submit an Add-on</a></li>";
					echo "<li><img src='images/pin.png' height='22' width='22' /><a href='http://wiki.xbmc.org/index.php?title=Add-on_development' > Add-on Development</a></li>";
					echo "<li><img src='images/pin.png' height='22' width='22' /><a href='http://wiki.xbmc.org/index.php?title=HOW-TO:HelloWorld_addon' > Hello World Tutorial</a></li>";
					echo "<li><img src='images/pin.png' height='22' width='22' /><a href='http://wiki.xbmc.org/index.php?title=XBMC_Skinning_Tutorials'> Skinning Tutorials</a></li>";
			?>
				</ul>
			</div>
			<?php endif; ?>
		<!-- End Content Wrapper -->
		</div>
