<?php

namespace RPAListings\Elementor;

if (!defined('ABSPATH')) exit;

/**
 * Extended Features widget that adds post meta data source resolution.
 * Priority: Investment Highlights (post meta) > Widget Repeater (Elementor).
 */
class RPAFeatures extends Features
{
	protected function render()
	{
		$settings = $this->get_settings_for_display();

		// --- Data Source Resolution ---
		// Priority: Post Meta (Investment Highlights) > Widget Repeater
		$features_list = $settings['tg_features_list'] ?? [];
		$post_id = get_the_ID();

		if ($post_id) {
			$highlights_meta = get_post_meta($post_id, 'rpa_project_investment_highlights', true);
			if ($highlights_meta && is_string($highlights_meta)) {
				$highlights = json_decode($highlights_meta, true);
				if (is_array($highlights)) {
					$valid = array_filter($highlights, function ($item) {
						return !empty($item['headline']) || !empty($item['description']) || !empty($item['icon']);
					});
					if (!empty($valid)) {
						$features_list = [];
						foreach ($valid as $item) {
							$icon_value = !empty($item['icon']) ? $item['icon'] : 'fas fa-star';
							$library = 'solid'; 

							$features_list[] = [
								'tg_features_title'         => !empty($item['headline']) ? $item['headline'] : '',
								'tg_features_description'   => !empty($item['description']) ? $item['description'] : '',
								'tg_features_icon'          => $icon_value,
								'tg_features_selected_icon' => ['value' => $icon_value, 'library' => $library],
							];
						}
					}
				}
			}
		}
?>
		<section class="features-area">
			<div class="container">
				<div class="row justify-content-center">
					<?php foreach ($features_list as $item) : ?>
						<div class="col-lg-3 col-md-6 col-sm-10">
							<div class="features-item <?php echo !empty($settings['tg_features_align']) ? esc_attr($settings['tg_features_align']) : ''; ?>">
								<?php if (!empty($item['tg_features_icon']) || !empty($item['tg_features_selected_icon']['value'])) : ?>
									<div class="feature-icon">
										<?php
										if (function_exists('tp_render_icon')) {
											tp_render_icon($item, 'tg_features_icon', 'tg_features_selected_icon');
										} else {
											\Elementor\Icons_Manager::render_icon($item['tg_features_selected_icon'], ['aria-hidden' => 'true']);
										}
										?>
									</div>
								<?php endif; ?>

								<div class="feature-content">
									<?php if (!empty($item['tg_features_title'])) : ?>
										<h2 class="title"><?php echo function_exists('tp_kses') ? tp_kses($item['tg_features_title']) : wp_kses_post($item['tg_features_title']); ?></h2>
									<?php endif; ?>

									<?php if (!empty($item['tg_features_description'])) : ?>
										<p><?php echo function_exists('tp_kses') ? tp_kses($item['tg_features_description']) : wp_kses_post($item['tg_features_description']); ?></p>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
<?php
	}
}
