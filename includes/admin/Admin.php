<?php

namespace RPAListings\Admin;

final class Admin
{
	public function register(): void
	{
		add_action('init', [$this, 'boot'], 20);
		add_action('init', [$this, 'register_cpts']);
		add_action('pre_get_posts', [$this, 'filter_projects_by_listing_status']);
		add_action('add_meta_boxes', [$this, 'add_deal_entry_meta_boxes']);
	}

	public function add_deal_entry_meta_boxes(): void
	{
		add_meta_box(
			'rpa-deal-entry-info',
			esc_html__('Deal Entry Information', 'rpa-listings'),
			[$this, 'render_deal_entry_info'],
			'deal_entry',
			'normal',
			'high'
		);
	}

	public function render_deal_entry_info(\WP_Post $post): void
	{
		$project_id = get_post_meta($post->ID, 'rpa_project_id', true);
		$first_name = get_post_meta($post->ID, 'rpa_first_name', true);
		$last_name = get_post_meta($post->ID, 'rpa_last_name', true);
		$company = get_post_meta($post->ID, 'rpa_company_name', true);
		$email = get_post_meta($post->ID, 'rpa_email', true);
		$phone = get_post_meta($post->ID, 'rpa_phone_number', true);
		$date = get_post_meta($post->ID, 'rpa_signed_date', true);
		$signature = get_post_meta($post->ID, 'rpa_signature_data', true);
		$token = get_post_meta($post->ID, 'rpa_magic_token', true);

		echo '<table class="form-table">';
		echo '<tr><th>' . esc_html__('Project', 'rpa-listings') . '</th><td><a href="' . get_edit_post_link($project_id) . '">' . get_the_title($project_id) . '</a></td></tr>';
		echo '<tr><th>' . esc_html__('Name', 'rpa-listings') . '</th><td>' . esc_html($first_name . ' ' . $last_name) . '</td></tr>';
		echo '<tr><th>' . esc_html__('Company', 'rpa-listings') . '</th><td>' . esc_html($company) . '</td></tr>';
		echo '<tr><th>' . esc_html__('Email', 'rpa-listings') . '</th><td>' . esc_html($email) . '</td></tr>';
		echo '<tr><th>' . esc_html__('Phone', 'rpa-listings') . '</th><td>' . esc_html($phone) . '</td></tr>';
		echo '<tr><th>' . esc_html__('Date Signed', 'rpa-listings') . '</th><td>' . esc_html($date) . '</td></tr>';
		echo '<tr><th>' . esc_html__('Magic Token', 'rpa-listings') . '</th><td>';
		echo '<div style="display: flex; align-items: center; gap: 10px;">';
		echo '<code>' . esc_html($token) . '</code>';
		if ($token && $email) {
			echo '<button type="button" class="rpa-modern-btn" id="rpa-resend-magic-link" data-entry-id="' . esc_attr($post->ID) . '" style="background-color: #3b82f6; color: #ffffff; border: none; border-radius: 6px; padding: 6px 14px; font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background-color 0.2s;">';
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>';
			echo esc_html__('Send Email', 'rpa-listings');
			echo '</button>';
			echo '<span id="rpa-resend-msg" style="margin-left: 10px; font-weight: 500; font-size: 13px;"></span>';
		}
		echo '</div>';
		echo '</td></tr>';
		echo '<tr><th>' . esc_html__('Signature', 'rpa-listings') . '</th><td>';
		if ($signature) {
			echo '<div style="display: flex; align-items: flex-end; gap: 10px;">';
			echo '<img src="' . esc_attr($signature) . '" style="max-width:300px; border:1px solid #ccc; background:#fff;" />';
			echo '<a href="' . esc_attr($signature) . '" download="signature-' . esc_attr(sanitize_title($first_name . '-' . $last_name)) . '.png" class="button button-secondary" title="' . esc_attr__('Download Signature', 'rpa-listings') . '" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0;">';
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>';
			echo '</a>';
			echo '</div>';
		} else {
			echo 'No signature found.';
		}
		echo '</td></tr>';
		echo '</table>';
	}

	public function register_cpts(): void
	{
		$labels = [
			'name'               => _x('Deal Entries', 'post type general name', 'rpa-listings'),
			'singular_name'      => _x('Deal Entry', 'post type singular name', 'rpa-listings'),
			'menu_name'          => _x('Deal Entries', 'admin menu', 'rpa-listings'),
			'name_admin_bar'     => _x('Deal Entry', 'add new on admin bar', 'rpa-listings'),
			'add_new'            => _x('Add New', 'deal entry', 'rpa-listings'),
			'add_new_item'       => __('Add New Deal Entry', 'rpa-listings'),
			'new_item'           => __('New Deal Entry', 'rpa-listings'),
			'edit_item'          => __('Edit Deal Entry', 'rpa-listings'),
			'view_item'          => __('View Deal Entry', 'rpa-listings'),
			'all_items'          => __('Deal Entries', 'rpa-listings'),
			'search_items'       => __('Search Deal Entries', 'rpa-listings'),
			'parent_item_colon'  => __('Parent Deal Entries:', 'rpa-listings'),
			'not_found'          => __('No deal entries found.', 'rpa-listings'),
			'not_found_in_trash' => __('No deal entries found in Trash.', 'rpa-listings')
		];

		$args = [
			'labels'             => $labels,
			'description'        => __('Description.', 'rpa-listings'),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=project',
			'query_var'          => true,
			'rewrite'            => ['slug' => 'deal-entry'],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => ['title', 'custom-fields']
		];

		register_post_type('deal_entry', $args);
	}

	public function filter_projects_by_listing_status(\WP_Query $query): void
	{
		// Since the Elementor widget creates a new WP_Query without any specific identifier,
		// we check if it's NOT the main query, NOT in admin, and requesting the 'project' post type.
		if (is_admin() || $query->is_main_query() || $query->get('post_type') !== 'project') {
			return;
		}

		// Allow bypassing this filter for specific queries (e.g. Sold Projects widget)
		if ($query->get('ignore_listing_status_filter') === true) {
			return;
		}

		$meta_query = $query->get('meta_query');
		if (!is_array($meta_query)) {
			$meta_query = [];
		}

		$meta_query[] = [
			'relation' => 'OR',
			[
				'key'     => 'rpa_project_listing_status',
				'value'   => 'sold',
				'compare' => '!=',
			],
			[
				'key'     => 'rpa_project_listing_status',
				'compare' => 'NOT EXISTS',
			],
		];

		$query->set('meta_query', $meta_query);
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
		if (!$screen || !in_array($screen->post_type, ['project', 'deal_entry'], true)) {
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
