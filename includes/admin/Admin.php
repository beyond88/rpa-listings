<?php

namespace RPAListings\Admin;

final class Admin
{
	public function register(): void
	{
		add_action('init', [$this, 'boot'], 20);
	}

	public function boot(): void
	{
		if (!post_type_exists('project')) {
			add_action('admin_notices', [$this, 'missing_project_cpt_notice']);
			return;
		}

		add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
		(new ProjectMetabox())->register();
	}

	public function missing_project_cpt_notice(): void
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		echo esc_html__('RPA Listings: "project" post type পাওয়া যায়নি—metabox load হয়নি।', 'rpa-listings');
		echo '</p></div>';
	}

	public function enqueue_assets(): void
	{
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if (!$screen || $screen->post_type !== 'project') {
			return;
		}

		wp_enqueue_media();

		$select2_script = null;
		$select2_style = null;

		if (wp_script_is('select2', 'registered')) {
			$select2_script = 'select2';
		}

		if (wp_style_is('select2', 'registered')) {
			$select2_style = 'select2';
		}

		if (!$select2_script || !$select2_style) {
			$acf_select2_js = WP_PLUGIN_DIR . '/advanced-custom-fields/assets/inc/select2/4/select2.min.js';
			$acf_select2_css = WP_PLUGIN_DIR . '/advanced-custom-fields/assets/inc/select2/4/select2.min.css';

			if (file_exists($acf_select2_js) && file_exists($acf_select2_css)) {
				$select2_script = 'rpa-select2';
				$select2_style = 'rpa-select2';

				wp_register_script(
					$select2_script,
					plugins_url('advanced-custom-fields/assets/inc/select2/4/select2.min.js'),
					['jquery'],
					RPA_LISTINGS_VERSION,
					true
				);

				wp_register_style(
					$select2_style,
					plugins_url('advanced-custom-fields/assets/inc/select2/4/select2.min.css'),
					[],
					RPA_LISTINGS_VERSION
				);
			}
		}

		if ($select2_style) {
			wp_enqueue_style($select2_style);
		}

		wp_enqueue_style(
			'rpa-listings-admin',
			RPA_LISTINGS_URL . 'assets/css/admin.css',
			[],
			RPA_LISTINGS_VERSION
		);

		if ($select2_script) {
			wp_enqueue_script($select2_script);
		}

		$deps = array_filter(['jquery', 'jquery-ui-sortable', $select2_script]);

		wp_enqueue_script(
			'rpa-listings-admin',
			RPA_LISTINGS_URL . 'assets/js/admin.js',
			$deps,
			RPA_LISTINGS_VERSION,
			true
		);

		wp_localize_script('rpa-listings-admin', 'rpaListingsAdmin', [
			'nonce' => wp_create_nonce('rpa_listings_admin'),
		]);
	}
}
