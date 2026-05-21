<?php

namespace RPAListings\Elementor;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles dynamic modifications to Elementor widgets.
 */
class WidgetHooks
{
    /**
     * Register hooks.
     */
    public function register(): void
    {
        // High priority (999) to ensure our changes are applied after other potential filters
        add_filter('elementor/widget/get_settings_for_display', [$this, 'filter_widget_settings'], 999, 2);
    }

    /**
     * Filters widget settings before they are used for rendering.
     *
     * @param array $settings Current widget settings.
     * @param \Elementor\Widget_Base $widget The widget instance.
     * @return array Modified settings.
     */
    public function filter_widget_settings($settings, $widget)
    {
        // Ensure we are targeting the correct widget
        if ('tg-direction' === $widget->get_name()) {
            $settings = $this->apply_project_direction_automation($settings);
        }

        return $settings;
    }

    /**
     * Automates Google Maps iframe and button link for the Project Direction widget.
     *
     * @param array $settings
     * @return array
     */
    private function apply_project_direction_automation($settings)
    {
        // The address is stored in 'tg_title'
        $raw_address = isset($settings['tg_title']) ? $settings['tg_title'] : '';
        
        // Strip any HTML tags if present to get a clean address string
        $address = trim(wp_strip_all_tags($raw_address));

        if (!empty($address)) {
            $encoded_address = urlencode($address);

            // 1. Force Iframe Mode
            $settings['tg_direction_type'] = 'iframe';
            $settings['hide_direction'] = 'yes';

            // 2. Override Map Iframe
            // We use a clean embed URL
            $settings['tg_direction_frame'] = sprintf(
                '<iframe width="100%%" height="450" frameborder="0" style="border:0" src="https://maps.google.com/maps?q=%s&output=embed" allowfullscreen></iframe>',
                $encoded_address
            );

            // 3. Override Button Link (Get direction)
            if (isset($settings['tg_btn_link']) && is_array($settings['tg_btn_link'])) {
                $settings['tg_btn_link']['url'] = 'https://www.google.com/maps/search/?api=1&query=' . $encoded_address;
                $settings['tg_button_show'] = 'yes';
            }
        }

        return $settings;
    }
}
