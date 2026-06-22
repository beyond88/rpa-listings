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
	private const META_MARINA_AMENITIES = 'rpa_project_marina_amenities';
	private const META_SOLD_SUMMARY  = 'rpa_project_sold_summary';
	private const META_SOLD_NRSF     = 'rpa_project_sold_nrsf';
	private const META_SOLD_UNITS    = 'rpa_project_sold_units';
	private const META_SOLD_DATE     = 'rpa_project_sold_date';
	private const META_DOCUMENTS     = 'rpa_project_documents';

	private const META_CITY = 'rpa_project_city';
	private const META_STATE = 'rpa_project_state';
	private const META_TEASER_DESC = 'rpa_project_teaser_desc';
	private const META_FULL_DESC = 'rpa_project_full_desc';
	private const META_AMENITIES_TYPED = 'rpa_project_amenities_typed';
	private const META_GALLERY = 'rpa_project_gallery_ids';
	private const META_VIDEO_ID = 'rpa_project_video_id';
	private const META_VIDEO_URL = 'rpa_project_video_url';
	private const META_360_VIDEO_ID = 'rpa_project_360_video_id';
	private const META_360_VIDEO_URL = 'rpa_project_360_video_url';
	private const META_VIDEO_GALLERY = 'rpa_project_video_gallery';
	private const META_360_VIDEO_GALLERY = 'rpa_project_360_video_gallery';
	private const META_PHOTO_GALLERY_JSON = 'rpa_project_photo_gallery_json';
	private const META_INVESTMENT_HIGHLIGHTS = 'rpa_project_investment_highlights';
	private const META_MAP_URL               = 'rpa_project_map_url';

	public function register(): void
	{
		add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
		add_action('save_post_project', [$this, 'save'], 10, 3);
	}

	public function add_meta_boxes(): void
	{
		add_meta_box(
			'rpa-project-details',
			esc_html__('Project Details', 'rpa-listings'),
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
			esc_html__('Deal Room', 'rpa-listings'),
			[$this, 'render_documents'],
			'project',
			'normal',
			'high'
		);

		add_meta_box(
			'rpa-project-investment-highlights',
			esc_html__('Investment Highlights', 'rpa-listings'),
			[$this, 'render_investment_highlights'],
			'project',
			'normal',
			'high'
		);

		add_meta_box(
			'rpa-project-map-url',
			esc_html__('Points of Interest Map', 'rpa-listings'),
			[$this, 'render_map_url'],
			'project',
			'normal',
			'high'
		);

		add_meta_box(
			'rpa-project-ca-export',
			esc_html__('Export CA Signers', 'rpa-listings'),
			[$this, 'render_ca_export'],
			'project',
			'side',
			'high'
		);
	}

	public function render(\WP_Post $post, array $box = []): void
	{
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		// --- Load Data ---
		$addresses = get_post_meta($post->ID, self::META_ADDRESSES, true);
		if (!is_array($addresses)) { $addresses = []; }
		$addresses = array_values(array_slice($addresses, 0, self::MAX_ADDRESSES));
		if (count($addresses) === 0) { $addresses[] = ''; }

		$teaser_desc = get_post_meta($post->ID, self::META_TEASER_DESC, true);
		$full_desc = get_post_meta($post->ID, self::META_FULL_DESC, true);
		
		$property_types = get_post_meta($post->ID, self::META_PROPERTY_TYPES, true);
		if (!is_array($property_types)) { $property_types = []; }

		// ACF Fields
		$year_built = get_post_meta($post->ID, 'year_built', true);
		$units = get_post_meta($post->ID, 'number_of_units', true);
		$nrsf = get_post_meta($post->ID, 'total_nrsf', true);
		$lot_size = get_post_meta($post->ID, 'lot_size', true);
		$market_bid = get_post_meta($post->ID, 'market_bid', true);
		$total_slips = get_post_meta($post->ID, 'total_slips', true);
		$rv_lots_dwellings = get_post_meta($post->ID, 'rv_lots_dwellings', true);

		$status = (string) get_post_meta($post->ID, self::META_STATUS, true);
		$listing_status = (string) get_post_meta($post->ID, self::META_LISTING_STATUS, true) ?: 'active';

		$amenities = get_post_meta($post->ID, self::META_AMENITIES, true);
		if (!is_array($amenities)) { $amenities = []; }
		$marina_amenities = get_post_meta($post->ID, self::META_MARINA_AMENITIES, true);
		if (!is_array($marina_amenities)) { $marina_amenities = []; }
		$amenities_typed = (string) get_post_meta($post->ID, self::META_AMENITIES_TYPED, true);

		$rpa_project_location = (string) get_post_meta($post->ID, 'rpa_project_location', true);
		$rpa_project_city     = (string) get_post_meta($post->ID, self::META_CITY, true);
		$rpa_project_state    = (string) get_post_meta($post->ID, self::META_STATE, true);

		$gallery_ids = get_post_meta($post->ID, self::META_GALLERY, true);
		if (!is_array($gallery_ids)) { $gallery_ids = []; }

		$video_id = (int) get_post_meta($post->ID, self::META_VIDEO_ID, true);
		$video_file_url = $video_id ? wp_get_attachment_url($video_id) : '';
		$video_ext_url = (string) get_post_meta($post->ID, self::META_VIDEO_URL, true);

		$video_360_id = (int) get_post_meta($post->ID, self::META_360_VIDEO_ID, true);
		$video_360_file_url = $video_360_id ? wp_get_attachment_url($video_360_id) : '';
		$video_360_ext_url = (string) get_post_meta($post->ID, self::META_360_VIDEO_URL, true);

		$video_gallery_json = get_post_meta($post->ID, self::META_VIDEO_GALLERY, true);
		if (!$video_gallery_json || !is_string($video_gallery_json)) {
			$video_gallery_json = '[]';
		}

		$video_360_gallery_json = get_post_meta($post->ID, self::META_360_VIDEO_GALLERY, true);
		if (!$video_360_gallery_json || !is_string($video_360_gallery_json)) {
			$video_360_gallery_json = '[]';
		}

		$photo_gallery_json = get_post_meta($post->ID, self::META_PHOTO_GALLERY_JSON, true);
		if (!$photo_gallery_json || !is_string($photo_gallery_json)) {
			$photo_gallery_json = '[]';
		}

		// --- Options ---
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
			'Early-Stage Lease Up' => esc_html__('Early-Stage Lease Up', 'rpa-listings'),
			'Late-Stage Lease Up' => esc_html__('Late-Stage Lease Up', 'rpa-listings'),
			'Entitled Land' => esc_html__('Entitled Land', 'rpa-listings'),
			'Value-Add' => esc_html__('Value-Add', 'rpa-listings'),
			'Expansion in Progress' => esc_html__('Expansion in Progress', 'rpa-listings'),
			'Mature / Seasoned' => esc_html__('Mature / Seasoned', 'rpa-listings'),
			'Pre-Stabilized' => esc_html__('Pre-Stabilized', 'rpa-listings'),
			'Raw / Unentitled Land' => esc_html__('Raw / Unentitled Land', 'rpa-listings'),
			'Site Plan / Zoning Pending' => esc_html__('Site Plan / Zoning Pending', 'rpa-listings'),
			'Pre-Development' => esc_html__('Pre-Development', 'rpa-listings'),
			'Core / Core-Plus' => esc_html__('Core / Core-Plus', 'rpa-listings'),
			'Opportunistic' => esc_html__('Opportunistic', 'rpa-listings'),
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
			'On-Site Manager or Security Patrols' => esc_html__('On-Site Manager or Security Patrols', 'rpa-listings'),
			'Motion-Activated Lighting' => esc_html__('Motion-Activated Lighting', 'rpa-listings'),
			'Dog Wash' => esc_html__('Dog Wash', 'rpa-listings'),
			'Dog Park' => esc_html__('Dog Park', 'rpa-listings'),
			'Vacuum Stations' => esc_html__('Vacuum Stations', 'rpa-listings'),
			'Wide Drive Aisles for Easy Maneuvering' => esc_html__('Wide Drive Aisles for Easy Maneuvering', 'rpa-listings'),
		];

		$marina_amenity_options = [];
		foreach (array_keys($this->marina_amenity_options()) as $marina_value) {
			$marina_amenity_options[$marina_value] = esc_html($marina_value);
		}

		echo '<div class="rpa-project-meta">';

		// 1. Full Address
		echo '<div class="rpa-row">';
		echo '<span class="rpa-label">' . esc_html__('Full Address', 'rpa-listings') . '</span>';
		echo '<input type="text" name="rpa_project_location" value="' . esc_attr($rpa_project_location) . '" class="large-text" />';
		echo '</div>';

		// City & State
		echo '<div class="rpa-grid-2" style="margin-top: 10px;">';
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_city">' . esc_html__('City', 'rpa-listings') . '</label>';
		echo '<input type="text" id="rpa_project_city" name="rpa_project_city" value="' . esc_attr($rpa_project_city) . '" class="large-text" />';
		echo '</div>';
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_state">' . esc_html__('State', 'rpa-listings') . '</label>';
		echo '<input type="text" id="rpa_project_state" name="rpa_project_state" value="' . esc_attr($rpa_project_state) . '" class="large-text" />';
		echo '</div>';
		echo '</div>';

		// Additional Addresses (Repeater)
		echo '<div class="rpa-row rpa-addresses-repeater" style="margin-top: 15px;">';
		echo '<span class="rpa-label">' . esc_html__('Additional Addresses (Max 5)', 'rpa-listings') . '</span>';
		echo '<div id="rpa-addresses-container">';
		foreach ($addresses as $addr) {
			echo '<div class="rpa-address-item" style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">';
			echo '<input type="text" name="rpa_project_addresses[]" value="' . esc_attr($addr) . '" class="large-text" style="width: 80%;" placeholder="Enter additional address" />';
			echo '<button type="button" class="button rpa-remove-address" style="color: red;">&times;</button>';
			echo '</div>';
		}
		echo '</div>';
		echo '<button type="button" class="button" id="rpa-add-address" style="margin-top: 5px;">+ Add Address</button>';
		echo '</div>';

		echo '<hr />';

		// 2. Teaser Description
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_teaser_desc">' . esc_html__('Teaser Description', 'rpa-listings') . '</label>';
		wp_editor((string) $teaser_desc, 'rpa_project_teaser_desc', ['textarea_name' => 'rpa_project_teaser_desc', 'textarea_rows' => 4, 'media_buttons' => true]);
		echo '</div>';

		// 3. Full Property Description
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_full_desc">' . esc_html__('Full Property Description (Only shows after CA signed)', 'rpa-listings') . '</label>';
		wp_editor((string) $full_desc, 'rpa_project_full_desc', ['textarea_name' => 'rpa_project_full_desc', 'textarea_rows' => 10, 'media_buttons' => true]);
		echo '</div>';

		echo '<hr />';

		echo '<div class="rpa-grid-2">';
		// 4. Property Type Selector
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_property_types">' . esc_html__('Property Type', 'rpa-listings') . '</label>';
		echo '<select id="rpa_project_property_types" name="rpa_project_property_types[]" multiple size="5">';
		foreach ($property_type_options as $value => $label) {
			$selected = in_array($value, $property_types, true) ? ' selected' : '';
			echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . $label . '</option>';
		}
		echo '</select>';
		echo '</div>';

		// 5. Year Built (ACF)
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="year_built">' . esc_html__('Year Built', 'rpa-listings') . '</label>';
		echo '<input type="text" class="regular-text" id="year_built" name="year_built" value="' . esc_attr($year_built) . '" />';
		echo '</div>';

		// 6. Units (ACF)
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="number_of_units">' . esc_html__('Units', 'rpa-listings') . '</label>';
		echo '<input type="text" class="regular-text" id="number_of_units" name="number_of_units" value="' . esc_attr($units) . '" />';
		echo '</div>';

		// 7. NRSF (ACF)
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="total_nrsf">' . esc_html__('NRSF', 'rpa-listings') . '</label>';
		echo '<input type="text" class="regular-text" id="total_nrsf" name="total_nrsf" value="' . esc_attr($nrsf) . '" />';
		echo '</div>';

		// 8. Lot Size (ACF)
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="lot_size">' . esc_html__('Lot Size', 'rpa-listings') . '</label>';
		echo '<input type="text" class="regular-text" id="lot_size" name="lot_size" value="' . esc_attr($lot_size) . '" />';
		echo '</div>';

		// 9. Status
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

		// 10. Market Bid (ACF)
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="market_bid">' . esc_html__('Market Bid', 'rpa-listings') . '</label>';
		echo '<textarea class="regular-text" id="market_bid" name="market_bid" rows="3">' . esc_textarea($market_bid) . '</textarea>';
		echo '</div>';

		// Listing Status (Floating?)
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_listing_status">' . esc_html__('Listing Status', 'rpa-listings') . '</label>';
		echo '<select id="rpa_project_listing_status" name="rpa_project_listing_status">';
		echo '<option value="active"' . selected($listing_status, 'active', false) . '>Active</option>';
		echo '<option value="sold"' . selected($listing_status, 'sold', false) . '>Sold</option>';
		echo '<option value="private"' . selected($listing_status, 'private', false) . '>Private (Off-Market)</option>';
		echo '</select>';
		echo '</div>';

		// Total Slips (Marina) - free response, optional
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="total_slips">' . esc_html__('Total Slips', 'rpa-listings') . '</label>';
		echo '<input type="text" class="regular-text" id="total_slips" name="total_slips" value="' . esc_attr($total_slips) . '" />';
		echo '</div>';

		// RV Lots/Dwellings (RV Park) - free response, optional
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rv_lots_dwellings">' . esc_html__('RV Lots/Dwellings', 'rpa-listings') . '</label>';
		echo '<input type="text" class="regular-text" id="rv_lots_dwellings" name="rv_lots_dwellings" value="' . esc_attr($rv_lots_dwellings) . '" />';
		echo '</div>';

		echo '</div>';

		echo '<hr />';

		// 11. Property Amenities Selector
		echo '<div class="rpa-row">';
		echo '<span class="rpa-label">' . esc_html__('Property Amenities', 'rpa-listings') . '</span>';
		echo '<div class="rpa-checkboxes">';
		foreach ($amenity_options as $value => $label) {
			$checked = in_array($value, $amenities, true) ? ' checked' : '';
			echo '<label class="rpa-checkbox"><input type="checkbox" name="rpa_project_amenities[]" value="' . esc_attr($value) . '"' . $checked . ' /> <span>' . $label . '</span></label>';
		}
		echo '</div>';
		echo '</div>';

		// 11b. Marina Amenities Selector
		echo '<div class="rpa-row">';
		echo '<span class="rpa-label">' . esc_html__('Marina Amenities', 'rpa-listings') . '</span>';
		echo '<div class="rpa-checkboxes">';
		foreach ($marina_amenity_options as $value => $label) {
			$checked = in_array($value, $marina_amenities, true) ? ' checked' : '';
			echo '<label class="rpa-checkbox"><input type="checkbox" name="rpa_project_marina_amenities[]" value="' . esc_attr($value) . '"' . $checked . ' /> <span>' . $label . '</span></label>';
		}
		echo '</div>';
		echo '</div>';

		// 12. Property Amenities Free Response
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_amenities_typed">' . esc_html__('Amenities Free Response Text Box', 'rpa-listings') . '</label>';
		wp_editor((string) $amenities_typed, 'rpa_project_amenities_typed', ['textarea_name' => 'rpa_project_amenities_typed', 'textarea_rows' => 6, 'media_buttons' => true]);
		echo '</div>';

		echo '<hr />';

		// 13. Photo Gallery (Multiple Repeater)
		echo '<div class="rpa-row">';
		echo '<span class="rpa-label">' . esc_html__('Photo Gallery', 'rpa-listings') . '</span>';
		echo '<input type="hidden" id="rpa_project_photo_gallery_data" name="rpa_project_photo_gallery" value="' . esc_attr($photo_gallery_json) . '" />';
		
		echo '<div id="rpa-photo-gallery-manager" class="rpa-photo-manager">';
		echo '  <div id="rpa-photo-list" class="rpa-photo-list"></div>';
		echo '  <div class="rpa-photo-actions" style="margin-top: 10px;">';
		echo '    <button type="button" class="button" id="rpa-photo-add-row">' . esc_html__('Add Image', 'rpa-listings') . '</button>';
		echo '  </div>';
		echo '</div>';
		echo '</div>';
		echo '<hr />';

		// 14. Video Gallery (Multiple URLs)
		echo '<div class="rpa-row">';
		echo '<span class="rpa-label">' . esc_html__('Video Gallery (URLs)', 'rpa-listings') . '</span>';
		echo '<input type="hidden" id="rpa_project_video_gallery_data" name="rpa_project_video_gallery" value="' . esc_attr($video_gallery_json) . '" />';
		
		echo '<div id="rpa-video-gallery-manager" class="rpa-video-manager">';
		echo '  <div id="rpa-video-list" class="rpa-video-list"></div>';
		echo '  <div class="rpa-video-actions" style="margin-top: 10px;">';
		echo '    <button type="button" class="button" id="rpa-video-add-row">' . esc_html__('Add Video URL', 'rpa-listings') . '</button>';
		echo '  </div>';
		echo '</div>';
		echo '</div>';

		// 15. 360 Video Gallery (Multiple Iframes)
		echo '<div class="rpa-row">';
		echo '<span class="rpa-label">' . esc_html__('360 Video Gallery (Iframes)', 'rpa-listings') . '</span>';
		echo '<input type="hidden" id="rpa_project_360_video_gallery_data" name="rpa_project_360_video_gallery" value="' . esc_attr($video_360_gallery_json) . '" />';
		
		echo '<div id="rpa-360-video-gallery-manager" class="rpa-video-manager">';
		echo '  <div id="rpa-360-video-list" class="rpa-video-list"></div>';
		echo '  <div class="rpa-video-actions" style="margin-top: 10px;">';
		echo '    <button type="button" class="button" id="rpa-360-video-add-row">' . esc_html__('Add 360 Item', 'rpa-listings') . '</button>';
		echo '  </div>';
		echo '</div>';
		echo '</div>';

		echo '</div>'; // end rpa-project-meta
	}

	public function render_investment_highlights(\WP_Post $post, array $box = []): void
	{
		$highlights_json = get_post_meta($post->ID, self::META_INVESTMENT_HIGHLIGHTS, true);
		$highlights = [];
		if ($highlights_json && is_string($highlights_json)) {
			$decoded = json_decode($highlights_json, true);
			if (is_array($decoded)) {
				$highlights = $decoded;
			}
		}

		echo '<div class="rpa-project-meta">';
		echo '<p style="color: #646970; font-size: 13px; margin-bottom: 16px;">' . esc_html__('Add investment highlights. Each highlight has a headline and description.', 'rpa-listings') . '</p>';
		echo '<input type="hidden" id="rpa_project_investment_highlights_data" name="rpa_project_investment_highlights" value="' . esc_attr(json_encode($highlights)) . '" />';

		echo '<div id="rpa-highlights-list" class="rpa-highlights-list"></div>';
		echo '<div style="margin-top: 12px;">';
		echo '<button type="button" class="button" id="rpa-highlight-add-row">' . esc_html__('+ Add Highlight', 'rpa-listings') . '</button>';
		echo '</div>';
		echo '</div>';
	}


	public function render_map_url(\WP_Post $post, array $box = []): void
	{
		$map_url = (string) get_post_meta($post->ID, self::META_MAP_URL, true);

		echo '<div class="rpa-project-meta">';
		echo '<p style="color:#646970; font-size:13px; margin-bottom:12px;">'
			. esc_html__('Paste the iframe src URL from the Points of Interest map. Leave empty to hide the section on the frontend.', 'rpa-listings')
			. '</p>';
		echo '<div class="rpa-row">';
		echo '<label class="rpa-label" for="rpa_project_map_url">' . esc_html__('Map iframe src URL', 'rpa-listings') . '</label>';
		echo '<input type="url" id="rpa_project_map_url" name="rpa_project_map_url" value="' . esc_attr($map_url) . '" class="large-text" placeholder="https://atlas.cushwake.com/map_4.html?fname=..." />';
		echo '</div>';
		echo '</div>';
	}

	public function render_ca_export(\WP_Post $post, array $box = []): void
	{
		echo '<div class="rpa-project-meta" style="text-align: center; padding: 10px 0;">';
		// echo '<p>' . esc_html__('Download a CSV of all signed Confidentiality Agreements for this property.', 'rpa-listings') . '</p>';
		echo '<a href="' . esc_url(admin_url('admin-post.php?action=rpa_export_ca_signers&project_id=' . $post->ID)) . '" class="button button-primary" style="width: 100%; text-align: center;">' . esc_html__('Export CA Signers (CSV)', 'rpa-listings') . '</a>';
		echo '</div>';
	}

	public function render_sold_info(\WP_Post $post, array $box = []): void
	{
		echo '<div class="rpa-project-meta">';
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
			"Type: " + d.property_type + "\n" +
			"NRSF: " + (d.nrsf || "") + "\n" +
			"Units: " + (d.units || "") + "\n" +
			"Sold: " + (d.date || "");
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

		if (isset($_POST['rpa_project_teaser_desc'])) {
			update_post_meta($post_id, self::META_TEASER_DESC, wp_kses_post(wp_unslash($_POST['rpa_project_teaser_desc'])));
		}

		if (isset($_POST['rpa_project_full_desc'])) {
			update_post_meta($post_id, self::META_FULL_DESC, wp_kses_post(wp_unslash($_POST['rpa_project_full_desc'])));
		}

		if (isset($_POST['rpa_project_location'])) {
			update_post_meta($post_id, 'rpa_project_location', sanitize_text_field($_POST['rpa_project_location']));
		}

		if (isset($_POST['rpa_project_city'])) {
			update_post_meta($post_id, self::META_CITY, sanitize_text_field($_POST['rpa_project_city']));
		}

		if (isset($_POST['rpa_project_state'])) {
			update_post_meta($post_id, self::META_STATE, sanitize_text_field($_POST['rpa_project_state']));
		}

		if (isset($_POST['rpa_project_addresses']) && is_array($_POST['rpa_project_addresses'])) {
			$addresses = array_filter(array_map('sanitize_text_field', wp_unslash($_POST['rpa_project_addresses'])));
			$addresses = array_values(array_slice($addresses, 0, self::MAX_ADDRESSES));
			update_post_meta($post_id, self::META_ADDRESSES, $addresses);
		} else {
			delete_post_meta($post_id, self::META_ADDRESSES);
		}

		// ACF Fields - update values if present
		if (isset($_POST['year_built'])) { update_post_meta($post_id, 'year_built', sanitize_text_field($_POST['year_built'])); }
		if (isset($_POST['number_of_units'])) { update_post_meta($post_id, 'number_of_units', sanitize_text_field($_POST['number_of_units'])); }
		if (isset($_POST['total_nrsf'])) { update_post_meta($post_id, 'total_nrsf', sanitize_text_field($_POST['total_nrsf'])); }
		if (isset($_POST['lot_size'])) { update_post_meta($post_id, 'lot_size', sanitize_text_field($_POST['lot_size'])); }
		if (isset($_POST['market_bid'])) { update_post_meta($post_id, 'market_bid', sanitize_textarea_field($_POST['market_bid'])); }
		if (isset($_POST['total_slips'])) { update_post_meta($post_id, 'total_slips', sanitize_text_field($_POST['total_slips'])); }
		if (isset($_POST['rv_lots_dwellings'])) { update_post_meta($post_id, 'rv_lots_dwellings', sanitize_text_field($_POST['rv_lots_dwellings'])); }

		$allowed_property_types = array_keys($this->property_type_options());
		$property_types = isset($_POST['rpa_project_property_types']) && is_array($_POST['rpa_project_property_types'])
			? array_values(array_unique(array_filter(array_map('sanitize_text_field', $_POST['rpa_project_property_types']))))
			: [];
		$property_types = array_values(array_intersect($property_types, $allowed_property_types));
		update_post_meta($post_id, self::META_PROPERTY_TYPES, $property_types);

		$allowed_status = array_keys($this->status_options());
		$status = isset($_POST['rpa_project_status']) ? sanitize_text_field((string) $_POST['rpa_project_status']) : '';
		if (!in_array($status, $allowed_status, true)) { $status = ''; }
		update_post_meta($post_id, self::META_STATUS, $status);

		$listing_status = isset($_POST['rpa_project_listing_status']) ? sanitize_text_field((string) $_POST['rpa_project_listing_status']) : 'active';
		if (!in_array($listing_status, ['active', 'sold', 'private'], true)) { $listing_status = 'active'; }
		update_post_meta($post_id, self::META_LISTING_STATUS, $listing_status);

		$allowed_amenities = array_keys($this->amenity_options());
		$amenities = isset($_POST['rpa_project_amenities']) && is_array($_POST['rpa_project_amenities'])
			? array_values(array_unique(array_filter(array_map('sanitize_text_field', $_POST['rpa_project_amenities']))))
			: [];
		$amenities = array_values(array_intersect($amenities, $allowed_amenities));
		update_post_meta($post_id, self::META_AMENITIES, $amenities);

		$allowed_marina_amenities = array_keys($this->marina_amenity_options());
		$marina_amenities = isset($_POST['rpa_project_marina_amenities']) && is_array($_POST['rpa_project_marina_amenities'])
			? array_values(array_unique(array_filter(array_map('sanitize_text_field', $_POST['rpa_project_marina_amenities']))))
			: [];
		$marina_amenities = array_values(array_intersect($marina_amenities, $allowed_marina_amenities));
		update_post_meta($post_id, self::META_MARINA_AMENITIES, $marina_amenities);

		if (isset($_POST['rpa_project_amenities_typed'])) {
			update_post_meta($post_id, self::META_AMENITIES_TYPED, wp_kses_post(wp_unslash($_POST['rpa_project_amenities_typed'])));
		}

		if (isset($_POST['rpa_project_gallery_ids'])) {
			$gids = array_filter(array_map('intval', explode(',', $_POST['rpa_project_gallery_ids'])));
			update_post_meta($post_id, self::META_GALLERY, array_values($gids));
		}

		if (isset($_POST['rpa_project_video_id'])) { update_post_meta($post_id, self::META_VIDEO_ID, (int) $_POST['rpa_project_video_id']); }
		if (isset($_POST['rpa_project_video_url'])) { update_post_meta($post_id, self::META_VIDEO_URL, esc_url_raw($_POST['rpa_project_video_url'])); }
		if (isset($_POST['rpa_project_360_video_id'])) { update_post_meta($post_id, self::META_360_VIDEO_ID, (int) $_POST['rpa_project_360_video_id']); }
		if (isset($_POST['rpa_project_360_video_url'])) { update_post_meta($post_id, self::META_360_VIDEO_URL, esc_url_raw($_POST['rpa_project_360_video_url'])); }

		if (isset($_POST['rpa_project_video_gallery'])) {
			$video_gallery_json = wp_unslash($_POST['rpa_project_video_gallery']);
			if (is_string($video_gallery_json) && json_decode($video_gallery_json) !== null) {
				update_post_meta($post_id, self::META_VIDEO_GALLERY, $video_gallery_json);
			}
		}

		if (isset($_POST['rpa_project_360_video_gallery'])) {
			$video_360_gallery_json = wp_unslash($_POST['rpa_project_360_video_gallery']);
			if (is_string($video_360_gallery_json) && json_decode($video_360_gallery_json) !== null) {
				update_post_meta($post_id, self::META_360_VIDEO_GALLERY, $video_360_gallery_json);
			}
		}

		if (isset($_POST['rpa_project_photo_gallery'])) {
			$photo_gallery_json = wp_unslash($_POST['rpa_project_photo_gallery']);
			if (is_string($photo_gallery_json) && json_decode($photo_gallery_json) !== null) {
				update_post_meta($post_id, self::META_PHOTO_GALLERY_JSON, $photo_gallery_json);
			}
		}


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

		if (isset($_POST['rpa_project_investment_highlights'])) {
			$highlights_json = wp_unslash($_POST['rpa_project_investment_highlights']);
			if (is_string($highlights_json) && json_decode($highlights_json) !== null) {
				update_post_meta($post_id, self::META_INVESTMENT_HIGHLIGHTS, $highlights_json);
			}
		}

		$map_url = isset($_POST['rpa_project_map_url']) ? esc_url_raw(wp_unslash($_POST['rpa_project_map_url'])) : '';
		update_post_meta($post_id, self::META_MAP_URL, $map_url);
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
			'Early-Stage Lease Up' => true,
			'Late-Stage Lease Up' => true,
			'Entitled Land' => true,
			'Value-Add' => true,
			'Expansion in Progress' => true,
			'Mature / Seasoned' => true,
			'Pre-Stabilized' => true,
			'Raw / Unentitled Land' => true,
			'Site Plan / Zoning Pending' => true,
			'Pre-Development' => true,
			'Core / Core-Plus' => true,
			'Opportunistic' => true,
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
			'On-Site Manager or Security Patrols' => true,
			'Motion-Activated Lighting' => true,
			'Dog Wash' => true,
			'Dog Park' => true,
			'Vacuum Stations' => true,
			'Wide Drive Aisles for Easy Maneuvering' => true,
		];
	}

	private function marina_amenity_options(): array
	{
		return [
			'Wet Slips' => true,
			'Dry Storage' => true,
			'Fuel Dock' => true,
			'Pump-Out Station' => true,
			'Freshwater Hookups' => true,
			'Shore Power Pedestals' => true,
			'Haul-Out Area with Travel Lift or Crane' => true,
			'On-Site Boatyard for Repairs' => true,
			'Restrooms and Showers' => true,
			'Laundry' => true,
			'Ice' => true,
			'Gated Docks' => true,
			'Security Cameras' => true,
			'Restaurant or Bar' => true,
			'Fish-Cleaning Station' => true,
			'Charter and Rental Boats' => true,
			'Pool' => true,
		];
	}
}
