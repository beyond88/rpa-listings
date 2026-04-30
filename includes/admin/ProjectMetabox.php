<?php

namespace RPAListings\Admin;

final class ProjectMetabox
{
	private const NONCE_ACTION = 'rpa_project_meta_save';
	private const NONCE_NAME = 'rpa_project_meta_nonce';
	private const MAX_ADDRESSES = 5;

	private const META_PROPERTY_TYPES = 'rpa_project_property_types';
	private const META_STATUS = 'rpa_project_status';
	private const META_LISTING_STATUS = 'rpa_project_listing_status';
	private const META_ADDRESSES = 'rpa_project_addresses';
	private const META_AMENITIES = 'rpa_project_amenities';
	private const META_SOLD_SUMMARY  = 'rpa_project_sold_summary';
	private const META_SOLD_NRSF     = 'rpa_project_sold_nrsf';
	private const META_SOLD_UNITS    = 'rpa_project_sold_units';
	private const META_SOLD_DATE     = 'rpa_project_sold_date';
	private const META_DOCUMENTS     = 'rpa_project_documents';

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

		$status = (string) get_post_meta($post->ID, self::META_STATUS, true);
		$listing_status = (string) get_post_meta($post->ID, self::META_LISTING_STATUS, true);
		if ($listing_status === '') {
			$listing_status = 'active';
		}

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

		$amenities = get_post_meta($post->ID, self::META_AMENITIES, true);
		if (!is_array($amenities)) {
			$amenities = [];
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

		echo '</div>';
	}

	public function render_sold_info(\WP_Post $post, array $box = []): void
	{
		$sold_summary = get_post_meta($post->ID, self::META_SOLD_SUMMARY, true);

		// Pull existing property data for auto-populate
		$property_name  = get_the_title($post->ID);
		$property_types = get_post_meta($post->ID, self::META_PROPERTY_TYPES, true);
		$property_type  = (is_array($property_types) && !empty($property_types)) ? implode(', ', $property_types) : '';

		// Pull ACF fields via PHP for reliability
		$nrsf  = function_exists('get_field') ? get_field('property_size', $post->ID) : '';
		$units = function_exists('get_field') ? get_field('flat_size', $post->ID) : '';
		$date  = get_post_meta($post->ID, self::META_SOLD_DATE, true);

		// No individual fields needed except for the summary editor
		echo '<div style="margin-bottom: 16px;">';
		echo '</div>'; 

		// ── Auto-populate button ────────────────────────────────────────────────
		echo '<p style="margin-bottom: 12px;">';
		echo '<button type="button" id="rpa-autopopulate-sold-summary" class="button button-secondary">';
		echo esc_html__('&#9998; Auto-populate Summary', 'rpa-listings');
		echo '</button>';
		echo '<span style="margin-left: 10px; color: #666; font-size: 12px;">' . esc_html__('Fills the editor below with the fields above — you can still edit afterwards.', 'rpa-listings') . '</span>';
		echo '</p>';

		// Encode data for JS
		$js_data = wp_json_encode([
			'name'          => $property_name,
			'property_type' => $property_type,
			'nrsf'          => $nrsf,
			'units'         => $units,
			'date'          => $date,
			'editor_id'     => 'rpa_project_sold_summary',
		]);
		echo '<script>
(function(){
	var d = ' . $js_data . ';
	
	var generateSummary = function() {
		return "Name: " + d.name + "\n" +
			"Property Type: " + d.property_type + "\n" +
			"NRSF: " + (d.nrsf || "") + "\n" +
			"Units: " + (d.units || "") + "\n" +
			"Date Sold: " + (d.date || "");
	};

	document.getElementById("rpa-autopopulate-sold-summary").addEventListener("click", function(){
		var summary = generateSummary();
		if (typeof tinyMCE !== "undefined" && tinyMCE.get(d.editor_id)) {
			tinyMCE.get(d.editor_id).setContent(summary.replace(/\n/g, "<br>"));
		} else {
			var ta = document.getElementById(d.editor_id);
			if (ta) ta.value = summary;
		}
	});
	
	// Pre-fill if empty
	window.addEventListener("load", function() {
		setTimeout(function() {
			var currentContent = "";
			if (typeof tinyMCE !== "undefined" && tinyMCE.get(d.editor_id)) {
				currentContent = tinyMCE.get(d.editor_id).getContent({format: "text"}).trim();
			} else {
				var ta = document.getElementById(d.editor_id);
				if (ta) currentContent = ta.value.trim();
			}
			
			if (currentContent === "") {
				var summary = generateSummary();
				if (typeof tinyMCE !== "undefined" && tinyMCE.get(d.editor_id)) {
					tinyMCE.get(d.editor_id).setContent(summary.replace(/\n/g, "<br>"));
				} else {
					var ta = document.getElementById(d.editor_id);
					if (ta) ta.value = summary;
				}
			}
		}, 1000);
	});
})();
</script>';

		// ── Sold summary editor ────────────────────────────────────────────────
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

		$allowed_amenities = array_keys($this->amenity_options());
		$amenities = isset($_POST['rpa_project_amenities']) && is_array($_POST['rpa_project_amenities'])
			? array_values(array_unique(array_filter(array_map('sanitize_text_field', $_POST['rpa_project_amenities']))))
			: [];
		$amenities = array_values(array_intersect($amenities, $allowed_amenities));
		update_post_meta($post_id, self::META_AMENITIES, $amenities);

		if (isset($_POST['rpa_project_sold_summary'])) {
			$sold_summary = wp_kses_post(wp_unslash($_POST['rpa_project_sold_summary']));
			update_post_meta($post_id, self::META_SOLD_SUMMARY, $sold_summary);
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
