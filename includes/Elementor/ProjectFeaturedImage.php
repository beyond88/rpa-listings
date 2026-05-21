<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ProjectFeaturedImage extends Widget_Base
{
    public function get_name()
    {
        return 'tp-project-featured-image';
    }

    public function get_title()
    {
        return esc_html__('Project Featured Image', 'rpa-listings');
    }

    public function get_icon()
    {
        return 'tp-icon';
    }

    public function get_categories()
    {
        return ['tpcore'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_image',
            [
                'label' => esc_html__('Image Settings', 'rpa-listings'),
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => esc_html__('Alignment', 'rpa-listings'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'rpa-listings'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rpa-listings'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'rpa-listings'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .rpa-featured-image-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_border_radius',
            [
                'label' => esc_html__('Border Radius', 'rpa-listings'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .rpa-featured-image-wrapper img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $project_id = get_the_ID();
        if (!$project_id || !has_post_thumbnail($project_id)) {
            return;
        }

        ?>
        <div class="rpa-featured-image-wrapper">
            <?php echo get_the_post_thumbnail($project_id, 'full', ['class' => 'rpa-featured-img', 'style' => 'max-width: 100%; height: auto;']); ?>
        </div>
        <?php
    }
}
