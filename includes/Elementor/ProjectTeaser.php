<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Core\Schemes\Typography;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ProjectTeaser extends Widget_Base
{

    public function get_name()
    {
        return 'tp-project-teaser';
    }

    public function get_title()
    {
        return __('Teaser', 'tpcore');
    }

    public function get_icon()
    {
        return 'tp-icon';
    }

    public function get_categories()
    {
        return ['tpcore'];
    }

    public function get_script_depends()
    {
        return ['tpcore'];
    }

    protected function register_controls()
    {
        // Content Section
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Teaser Content', 'tpcore'),
            ]
        );

        $this->add_control(
            'teaser_notice',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__('This widget automatically pulls the "Teaser Description" from the Project Details metabox.', 'tpcore'),
                'content_classes' => 'elementor-descriptor',
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Style', 'tpcore'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'text_align',
            [
                'label' => esc_html__('Alignment', 'tpcore'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'tpcore'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'tpcore'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'tpcore'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__('Justify', 'tpcore'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .project-teaser-content' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'tpcore'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .project-teaser-content' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .project-teaser-content *' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .project-teaser-content, {{WRAPPER}} .project-teaser-content *',
            ]
        );

        $this->add_responsive_control(
            'spacing',
            [
                'label' => __('Bottom Spacing', 'tpcore'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .project-teaser-content' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $post_id = get_the_ID();
        if (!$post_id) {
            return;
        }

        $teaser_desc = get_post_meta($post_id, 'rpa_project_teaser_desc', true);

        if (empty($teaser_desc)) {
            return;
        }
        ?>
        <div class="project-teaser-content">
            <?php echo wp_kses_post(wpautop($teaser_desc)); ?>
        </div>
        <?php
    }
}
