<?php

namespace RPAListings\Admin;

final class ProjectMetabox
{
	private const NONCE_ACTION = 'rpa_project_meta_save';
	private const NONCE_NAME = 'rpa_project_meta_nonce';
	private const MAX_ADDRESSES = 5;

	private const META_NSFR = 'rpa_project_nsrf';
	private const META_PRICE = 'rpa_project_price';
	private const META_PROPERTY_TYPES = 'rpa_project_property_types';
	private const META_STATUS = 'rpa_project_status';
	private const META_LISTING_STATUS = 'rpa_project_listing_status';
	private const META_CITY = 'rpa_project_city';
	private const META_STATE = 'rpa_project_state';
	private const META_ADDRESSES = 'rpa_project_addresses';
	private const META_SITE_PLAN_ID = 'rpa_project_site_plan_id';
	private const META_AMENITIES = 'rpa_project_amenities';
	private const META_AMENITIES_TYPED = 'rpa_project_amenities_typed';
	private const META_GALLERY = 'rpa_project_gallery_ids';
	private const META_SOLD_SUMMARY = 'rpa_project_sold_summary';
	private const META_TEASER_DESC = 'rpa_project_teaser_desc';
	private const META_FULL_DESC = 'rpa_project_full_desc';
	private const META_DOCUMENTS = 'rpa_project_documents';

	public function register(): void
	{
		add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
		add_action('save_post_project', [$this, 'save'], 10, 3);
	}

	public function add_meta_boxes(): void
	{
		add_meta_box(
			'rpa-project-basic-info',
			esc_html__('Basic Info', 'rpa-listings'),
			[$this, 'render'],
			'project',
			'normal',
			'high'
		);

		add_meta_box(
			'rpa-project-descriptions',
			esc_html__('Descriptions', 'rpa-listings'),
			[$this, 'render_descriptions'],
			'project',
			'normal',
			'high'
		);

		add_meta_box(
			'rpa-project-sold-info',
			esc_html__('Sold Listing Info', 'rpa-listings'),
			[$this, 'render_sold_info'],
			'project',
			'normal',
			'high'
		);

		add_meta_box(
			'rpa-project-documents',
			esc_html__('Documents', 'rpa-listings'),
			[$this, 'render_documents'],
			'project',
			'normal',
			'high'
		);
	}

	public function render(\WP_Post $post, array $box = []): void
	{
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$nsrf = (string) get_post_meta($post->ID, self::META_NSFR, true);
		$price = (string) get_post_meta($post->ID, self::META_PRICE, true);
		$status = (string) get_post_meta($post->ID, self::META_STATUS, true);
		$listing_status = (string) get_post_meta($post->ID, self::META_LISTING_STATUS, true);
		if ($listing_status === '') {
			$listing_status = 'active';
		}
		$city = (string) get_post_meta($post->ID, self::META_CITY, true);
		$state = (string) get_post_meta($post->ID, self::META_STATE, true);

		$property_types = get_post_meta($post->ID, self::META_PROPERTY_TYPES, true);
		if (!is_array($property_types)) {
			$property_types = [];
		}

		$addresses = get_post_meta($post->ID, self::META_ADDRESSES, true);
		if (!is_array($addresses)) {
			$addresses = [];
		}
		$addresses = array_values(array_slice($addresses, 0, self::MAX_ADDRESSES));
		if (count($addresses) === 0) {
			$addresses[] = '';
		}

		$site_plan_id = (int) get_post_meta($post->ID, self::META_SITE_PLAN_ID, true);
		$site_plan_url = $site_plan_id ? wp_get_attachment_url($site_plan_id) : '';

		$amenities = get_post_meta($post->ID, self::META_AMENITIES, true);
		if (!is_array($amenities)) {
			$amenities = [];
		}
		$amenities_typed = (string) get_post_meta($post->ID, self::META_AMENITIES_TYPED, true);

		$gallery_ids = get_post_meta($post->ID, self::META_GALLERY, true);
		if (!is_array($gallery_ids)) {
			$gallery_ids = [];
		}

		$property_type_options = [
			'Self Storage' => esc_html__('Self Storage', 'rpa-listings'),
			'Land' => esc_html__('Land', 'rpa-listings'),
			'RV & Boat Storage' => esc_html__('RV & Boat Storage', 'rpa-listings'),
			'Marina' => esc_html__('Marina', 'rpa-listings'),
			'Mixed Use' => esc_html__('Mixed Use', 'rpa-listings'),
		];

		$status_options = [
			'Pre-Approved' => esc_html__('Pre-Approved', 'rpa-listings'),
			'Under Construction' => esc_html__('Under Construction', 'rpa-listings'),
			'Lease Up' => esc_html__('Lease Up', 'rpa-listings'),
			'Stabilized' => esc_html__('Stabilized', 'rpa-listings'),
			'CofO' => esc_html__('CofO', 'rpa-listings'),
		];

		$listing_status_options = [
			'active' => esc_html__('Active', 'rpa-listings'),
			'sold' => esc_html__('Sold', 'rpa-listings'),
		];

		$amenity_options = [
			'24/7 Gated Access with Keypad or Mobile Entry' => esc_html__('24/7 Gated Access with Keypad or Mobile Entry', 'rpa-listings'),
			'License Plate Recognition (LPR) Systems' => esc_html__('License Plate Recognition (LPR) Systems', 'rpa-listings'),
			'HD Surveillance Cameras' => esc_html__('HD Surveillance Cameras', 'rpa-listings'),
			'Perimeter Fencing' => esc_html__('Perimeter Fencing', 'rpa-listings'),
			'Individual Unit Alarms' => esc_html__('Individual Unit Alarms', 'rpa-listings'),
			'30/50-Amp Electrical Hookups' => esc_html__('30/50-Amp Electrical Hookups', 'rpa-listings'),
			'Water Access Stations' => esc_html__('Water Access Stations', 'rpa-listings'),
			'RV Dump Station' => esc_html__('RV Dump Station', 'rpa-listings'),
			'Wash-Down Stations' => esc_html__('Wash-Down Stations', 'rpa-listings'),
			'Air Compressor / Tire Inflation Stations' => esc_html__('Air Compressor / Tire Inflation Stations', 'rpa-listings'),
			'Ice Machines' => esc_html__('Ice Machines', 'rpa-listings'),
			'Trickle Chargers' => esc_html__('Trickle Chargers', 'rpa-listings'),
			'Propane Refill Stations' => esc_html__('Propane Refill Stations', 'rpa-listings'),
			'Solar Canopies' => esc_html__('Solar Canopies', 'rpa-listings'),
		];

		echo '<div class="rpa-project-meta">';
		echo '<div class="rpa-grid-2">';

		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_listing_status">' . esc_html__('Listing Status', 'rpa-listings') . '</label>';
		echo '<select id="rpa_project_listing_status" name="rpa_project_listing_status">';
		foreach ($listing_status_options as $value => $label) {
			$selected = ($value === $listing_status) ? ' selected' : '';
			echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . $label . '</option>';
		}
		echo '</select>';
		echo '</div>';

		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_nsrf">' . esc_html__('NSRF', 'rpa-listings') . '</label>';
		echo '<input class="regular-text" type="text" id="rpa_project_nsrf" name="rpa_project_nsrf" value="' . esc_attr($nsrf) . '" />';
		echo '</div>';

		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_price">' . esc_html__('Price', 'rpa-listings') . '</label>';
		echo '<input class="regular-text" type="text" id="rpa_project_price" name="rpa_project_price" placeholder="' . esc_attr__('$100k or Market Bid', 'rpa-listings') . '" value="' . esc_attr($price) . '" />';
		echo '</div>';

		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_status">' . esc_html__('Status', 'rpa-listings') . '</label>';
		echo '<select id="rpa_project_status" name="rpa_project_status">';
		echo '<option value="">' . esc_html__('Select', 'rpa-listings') . '</option>';
		foreach ($status_options as $value => $label) {
			$selected = ($value === $status) ? ' selected' : '';
			echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . $label . '</option>';
		}
		echo '</select>';
		echo '</div>';

		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_property_types">' . esc_html__('Property Type', 'rpa-listings') . '</label>';
		echo '<select id="rpa_project_property_types" name="rpa_project_property_types[]" multiple size="5">';
		foreach ($property_type_options as $value => $label) {
			$selected = in_array($value, $property_types, true) ? ' selected' : '';
			echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . $label . '</option>';
		}
		echo '</select>';
		echo '</div>';

		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_city">' . esc_html__('City', 'rpa-listings') . '</label>';
		echo '<input class="regular-text" type="text" id="rpa_project_city" name="rpa_project_city" value="' . esc_attr($city) . '" />';
		echo '</div>';

		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_state">' . esc_html__('State', 'rpa-listings') . '</label>';
		echo '<input class="regular-text" type="text" id="rpa_project_state" name="rpa_project_state" value="' . esc_attr($state) . '" />';
		echo '</div>';

		echo '</div>';

		echo '<div class="rpa-row">';
		echo '<span class="rpa-label">' . esc_html__('Property Address', 'rpa-listings') . '</span>';
		echo '<div class="rpa-addresses" data-rpa-addresses data-max="' . esc_attr((string) self::MAX_ADDRESSES) . '">';
		foreach ($addresses as $i => $address) {
			$index = (int) $i + 1;
			echo '<div class="rpa-address" data-rpa-address-row>';
			echo '<div class="rpa-address-head">';
			echo '<label>' . esc_html(sprintf(__('Address %d', 'rpa-listings'), $index)) . '</label>';
			echo '<button type="button" class="button-link-delete" data-rpa-remove-address>' . esc_html__('Remove', 'rpa-listings') . '</button>';
			echo '</div>';
			echo '<textarea class="large-text" rows="2" name="rpa_project_addresses[]">' . esc_textarea((string) $address) . '</textarea>';
			echo '</div>';
		}
		echo '</div>';
		echo '<p class="rpa-actions"><button type="button" class="button" data-rpa-add-address>' . esc_html__('Add New', 'rpa-listings') . '</button></p>';
		echo '</div>';

		echo '<hr />';

		echo '<div class="rpa-row">';
		echo '<span class="rpa-label">' . esc_html__('Site Plan', 'rpa-listings') . '</span>';
		echo '<input type="hidden" id="rpa_project_site_plan_id" name="rpa_project_site_plan_id" value="' . esc_attr((string) $site_plan_id) . '" />';
		echo '<button type="button" class="button" data-rpa-upload="site-plan">' . esc_html__('Upload / Select File', 'rpa-listings') . '</button>';
		echo '<button type="button" class="button" data-rpa-clear="site-plan">' . esc_html__('Remove', 'rpa-listings') . '</button>';
		echo '<div class="rpa-file">';
		if ($site_plan_url) {
			echo '<a href="' . esc_url($site_plan_url) . '" target="_blank" rel="noreferrer">' . esc_html(basename($site_plan_url)) . '</a>';
		} else {
			echo '<span class="description">' . esc_html__('No file selected', 'rpa-listings') . '</span>';
		}
		echo '</div>';
		echo '</div>';

		echo '<hr />';

		echo '<div class="rpa-row">';
		echo '<span class="rpa-label">' . esc_html__('Property Amenities', 'rpa-listings') . '</span>';
		echo '<div class="rpa-checkboxes">';
		foreach ($amenity_options as $value => $label) {
			$checked = in_array($value, $amenities, true) ? ' checked' : '';
			echo '<label class="rpa-checkbox">';
			echo '<input type="checkbox" name="rpa_project_amenities[]" value="' . esc_attr($value) . '"' . $checked . ' />';
			echo '<span>' . $label . '</span>';
			echo '</label>';
		}
		echo '</div>';
		echo '</div>';

		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_amenities_typed">' . esc_html__('Typed Responses', 'rpa-listings') . '</label>';
		echo '<textarea class="large-text" rows="3" id="rpa_project_amenities_typed" name="rpa_project_amenities_typed" placeholder="' . esc_attr__('Comma separated or one per line', 'rpa-listings') . '">' . esc_textarea($amenities_typed) . '</textarea>';
		echo '</div>';

		echo '<div class="rpa-row">';
		echo '<span class="rpa-label">' . esc_html__('Image Gallery', 'rpa-listings') . '</span>';
		echo '<input type="hidden" id="rpa_project_gallery_ids" name="rpa_project_gallery_ids" value="' . esc_attr(implode(',', array_map('intval', $gallery_ids))) . '" />';
		echo '<button type="button" class="button" data-rpa-upload="gallery">' . esc_html__('Add / Select Images', 'rpa-listings') . '</button>';
		echo '<button type="button" class="button" data-rpa-clear="gallery">' . esc_html__('Clear Gallery', 'rpa-listings') . '</button>';
		echo '<div class="rpa-gallery" id="rpa_project_gallery_preview">';
		foreach ($gallery_ids as $id) {
			$id = (int) $id;
			if (!$id) {
				continue;
			}
			$image = wp_get_attachment_image($id, 'thumbnail');
			if (!$image) {
				continue;
			}
			echo '<span class="rpa-gallery-item" data-id="' . esc_attr((string) $id) . '">';
			echo '<button type="button" class="rpa-gallery-remove" aria-label="' . esc_attr__('Remove image', 'rpa-listings') . '"><span class="dashicons dashicons-no-alt"></span></button>';
			echo $image;
			echo '</span>';
		}
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	public function render_descriptions(\WP_Post $post, array $box = []): void
	{
		$teaser_desc = get_post_meta($post->ID, self::META_TEASER_DESC, true);
		$full_desc = get_post_meta($post->ID, self::META_FULL_DESC, true);

		echo '<div class="rpa-project-meta">';

		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_teaser_desc">' . esc_html__('Teaser Description', 'rpa-listings') . '</label>';
		wp_editor(
			(string) $teaser_desc,
			'rpa_project_teaser_desc',
			[
				'textarea_name' => 'rpa_project_teaser_desc',
				'textarea_rows' => 6,
				'media_buttons' => true,
				'tinymce'       => true,
				'quicktags'     => true,
			]
		);
		echo '</div>';

		echo '<hr />';

		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_full_desc">' . esc_html__('Full Description', 'rpa-listings') . '</label>';
		wp_editor(
			(string) $full_desc,
			'rpa_project_full_desc',
			[
				'textarea_name' => 'rpa_project_full_desc',
				'textarea_rows' => 12,
				'media_buttons' => true,
				'tinymce'       => true,
				'quicktags'     => true,
			]
		);
		echo '</div>';

		echo '</div>';
	}

	public function render_sold_info(\WP_Post $post, array $box = []): void
	{
		$sold_summary = get_post_meta($post->ID, self::META_SOLD_SUMMARY, true);

		echo '<div class="rpa-project-meta">';
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_sold_summary">' . esc_html__('Sold summary', 'rpa-listings') . '</label>';

		wp_editor(
			(string) $sold_summary,
			'rpa_project_sold_summary',
			[
				'textarea_name' => 'rpa_project_sold_summary',
				'textarea_rows' => 10,
				'media_buttons' => true,
				'tinymce'       => true,
				'quicktags'     => true,
			]
		);

		echo '</div>';
		echo '</div>';
	}

	public function render_documents(\WP_Post $post, array $box = []): void
	{
		$documents_json = get_post_meta($post->ID, self::META_DOCUMENTS, true);
		if (!$documents_json || !is_string($documents_json)) {
			$documents_json = '[]';
		}

		echo '<div class="rpa-project-meta">';
		echo '<input type="hidden" id="rpa_project_documents_data" name="rpa_project_documents" value="' . esc_attr($documents_json) . '" />';

		echo '<div id="rpa-document-manager" class="rpa-doc-manager">';
		echo '  <div class="rpa-doc-header">';
		echo '    <div class="rpa-doc-breadcrumbs" id="rpa-doc-breadcrumbs"></div>';
		echo '    <div class="rpa-doc-actions">';
		echo '      <button type="button" class="button" id="rpa-doc-new-folder">' . esc_html__('New Folder', 'rpa-listings') . '</button>';
		echo '      <button type="button" class="button button-primary" id="rpa-doc-add-files">' . esc_html__('Add Files', 'rpa-listings') . '</button>';
		echo '    </div>';
		echo '  </div>';
		echo '  <div class="rpa-doc-grid" id="rpa-doc-grid"></div>';
		echo '</div>';

		echo '</div>';
	}

	public function save(int $post_id, \WP_Post $post, bool $update = false): void
	{
		if (!isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce((string) $_POST[self::NONCE_NAME], self::NONCE_ACTION)) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$nsrf = isset($_POST['rpa_project_nsrf']) ? sanitize_text_field((string) $_POST['rpa_project_nsrf']) : '';
		update_post_meta($post_id, self::META_NSFR, $nsrf);

		$price = isset($_POST['rpa_project_price']) ? sanitize_text_field((string) $_POST['rpa_project_price']) : '';
		update_post_meta($post_id, self::META_PRICE, $price);

		$allowed_property_types = array_keys($this->property_type_options());
		$property_types = isset($_POST['rpa_project_property_types']) && is_array($_POST['rpa_project_property_types'])
			? array_values(array_unique(array_filter(array_map('sanitize_text_field', $_POST['rpa_project_property_types']))))
			: [];
		$property_types = array_values(array_intersect($property_types, $allowed_property_types));
		update_post_meta($post_id, self::META_PROPERTY_TYPES, $property_types);

		$allowed_status = array_keys($this->status_options());
		$status = isset($_POST['rpa_project_status']) ? sanitize_text_field((string) $_POST['rpa_project_status']) : '';
		if (!in_array($status, $allowed_status, true)) {
			$status = '';
		}
		update_post_meta($post_id, self::META_STATUS, $status);

		$listing_status = isset($_POST['rpa_project_listing_status']) ? sanitize_text_field((string) $_POST['rpa_project_listing_status']) : 'active';
		if (!in_array($listing_status, ['active', 'sold'], true)) {
			$listing_status = 'active';
		}
		update_post_meta($post_id, self::META_LISTING_STATUS, $listing_status);

		$addresses = isset($_POST['rpa_project_addresses']) && is_array($_POST['rpa_project_addresses'])
			? array_slice($_POST['rpa_project_addresses'], 0, self::MAX_ADDRESSES)
			: [];
		$addresses = array_map(static fn($v) => sanitize_textarea_field((string) $v), $addresses);
		$addresses = array_values(array_filter($addresses, static fn($v) => $v !== ''));
		update_post_meta($post_id, self::META_ADDRESSES, $addresses);

		$city = isset($_POST['rpa_project_city']) ? sanitize_text_field((string) $_POST['rpa_project_city']) : '';
		update_post_meta($post_id, self::META_CITY, $city);

		$state = isset($_POST['rpa_project_state']) ? sanitize_text_field((string) $_POST['rpa_project_state']) : '';
		update_post_meta($post_id, self::META_STATE, $state);

		$site_plan_id = isset($_POST['rpa_project_site_plan_id']) ? (int) $_POST['rpa_project_site_plan_id'] : 0;
		if ($site_plan_id > 0) {
			update_post_meta($post_id, self::META_SITE_PLAN_ID, $site_plan_id);
		} else {
			delete_post_meta($post_id, self::META_SITE_PLAN_ID);
		}

		$allowed_amenities = array_keys($this->amenity_options());
		$amenities = isset($_POST['rpa_project_amenities']) && is_array($_POST['rpa_project_amenities'])
			? array_values(array_unique(array_filter(array_map('sanitize_text_field', $_POST['rpa_project_amenities']))))
			: [];
		$amenities = array_values(array_intersect($amenities, $allowed_amenities));
		update_post_meta($post_id, self::META_AMENITIES, $amenities);

		$amenities_typed = isset($_POST['rpa_project_amenities_typed']) ? sanitize_textarea_field((string) $_POST['rpa_project_amenities_typed']) : '';
		update_post_meta($post_id, self::META_AMENITIES_TYPED, $amenities_typed);

		$gallery_ids_raw = isset($_POST['rpa_project_gallery_ids']) ? (string) $_POST['rpa_project_gallery_ids'] : '';
		$gallery_ids = array_filter(array_map('intval', preg_split('/\s*,\s*/', $gallery_ids_raw)));
		$gallery_ids = array_values(array_unique(array_filter($gallery_ids, static fn($v) => $v > 0)));
		update_post_meta($post_id, self::META_GALLERY, $gallery_ids);

		if (isset($_POST['rpa_project_sold_summary'])) {
			$sold_summary = wp_kses_post(wp_unslash($_POST['rpa_project_sold_summary']));
			update_post_meta($post_id, self::META_SOLD_SUMMARY, $sold_summary);
		}

		if (isset($_POST['rpa_project_teaser_desc'])) {
			$teaser_desc = wp_kses_post(wp_unslash($_POST['rpa_project_teaser_desc']));
			update_post_meta($post_id, self::META_TEASER_DESC, $teaser_desc);
		}

		if (isset($_POST['rpa_project_full_desc'])) {
			$full_desc = wp_kses_post(wp_unslash($_POST['rpa_project_full_desc']));
			update_post_meta($post_id, self::META_FULL_DESC, $full_desc);
		}

		if (isset($_POST['rpa_project_documents'])) {
			$documents_json = wp_unslash($_POST['rpa_project_documents']);
			if (is_string($documents_json) && json_decode($documents_json) !== null) {
				update_post_meta($post_id, self::META_DOCUMENTS, $documents_json);
			}
		}
	}

	private function property_type_options(): array
	{
		return [
			'Self Storage' => true,
			'Land' => true,
			'RV & Boat Storage' => true,
			'Marina' => true,
			'Mixed Use' => true,
		];
	}

	private function status_options(): array
	{
		return [
			'Pre-Approved' => true,
			'Under Construction' => true,
			'Lease Up' => true,
			'Stabilized' => true,
			'CofO' => true,
		];
	}

	private function amenity_options(): array
	{
		return [
			'24/7 Gated Access with Keypad or Mobile Entry' => true,
			'License Plate Recognition (LPR) Systems' => true,
			'HD Surveillance Cameras' => true,
			'Perimeter Fencing' => true,
			'Individual Unit Alarms' => true,
			'30/50-Amp Electrical Hookups' => true,
			'Water Access Stations' => true,
			'RV Dump Station' => true,
			'Wash-Down Stations' => true,
			'Air Compressor / Tire Inflation Stations' => true,
			'Ice Machines' => true,
			'Trickle Chargers' => true,
			'Propane Refill Stations' => true,
			'Solar Canopies' => true,
		];
	}
}
