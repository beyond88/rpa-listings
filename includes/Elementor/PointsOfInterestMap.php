<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class PointsOfInterestMap extends Widget_Base
{
    public function get_name()
    {
        return 'rpa_points_of_interest_map';
    }

    public function get_title()
    {
        return esc_html__('Points of Interest Map', 'rpa-listings');
    }

    public function get_icon()
    {
        return 'tp-icon';
    }

    public function get_categories()
    {
        return ['tpcore'];
    }

    protected function is_dynamic_content(): bool
    {
        return true;
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Map Settings', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'map_height',
            [
                'label'      => esc_html__('Map Height (px)', 'rpa-listings'),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 700,
                'min'        => 200,
                'max'        => 1200,
                'step'       => 10,
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $map_url  = (string) get_post_meta(get_the_ID(), 'rpa_project_map_url', true);

        if (empty($map_url)) {
            return;
        }

        $height = !empty($settings['map_height']) ? intval($settings['map_height']) : 700;
        $title  = get_the_title();
        ?>
        <div style="width:100%; height:<?php echo esc_attr($height); ?>px;">
            <iframe
                src="<?php echo esc_url($map_url); ?>"
                title="<?php echo esc_attr($title); ?>"
                style="width:100%; height:100%; border:0;"
                scrolling="no"
                loading="lazy">
            </iframe>
        </div>
        <?php
    }
}
