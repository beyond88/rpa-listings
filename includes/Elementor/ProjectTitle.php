<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \Elementor\Utils;
use \Elementor\Control_Media;

use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Text_Shadow;
use \Elementor\Group_Control_Typography;
use \Elementor\Core\Schemes\Typography;
use \Elementor\Group_Control_Background;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ProjectTitle extends Widget_Base
{

    public function get_name()
    {
        return 'tp-project-title';
    }

    public function get_title()
    {
        return __('Project Title', 'tpcore');
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

        // tp_section_title
        $this->start_controls_section(
            'tp_section_title',
            [
                'label' => esc_html__('Location & Title', 'tpcore'),
            ]
        );

        // Subtitle
        $this->add_control(
            '_heading_subtitle',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __('Location', 'tpcore'),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'subtitle_spacing',
            [
                'label' => __('Bottom Spacing', 'tpcore'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .sub-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'subtitle_color',
            [
                'label' => __('Text Color', 'tpcore'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sub-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sub-title',
                'selector' => '{{WRAPPER}} .sub-title',
            ]
        );

        // Title
        $this->add_control(
            '_heading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __(
                    'Title',
                    'tpcore'
                ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => __('Bottom Spacing', 'tpcore'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .project-details-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Text Color', 'tpcore'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title',
                'selector' => '{{WRAPPER}} .title',
            ]
        );

        $this->add_responsive_control(
            'tp_align',
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
                ],
                'default' => 'left',
                'toggle' => false,
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};'
                ]
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {

        $settings = $this->get_settings_for_display();

        $project_location = get_post_meta(get_the_ID(), 'rpa_project_location', true);

?>


        <div class="project-details-title">

            <?php if (!empty($project_location)) : ?>
                <span class="sub-title"><?php echo esc_html($project_location) ?></span>
            <?php endif; ?>

            <?php
            $additional_addresses = get_post_meta(get_the_ID(), 'rpa_project_addresses', true);
            if (is_array($additional_addresses) && !empty($additional_addresses)) :
                foreach ($additional_addresses as $addr) :
                    if (!empty($addr)) :
            ?>
                <span class="sub-title"><?php echo esc_html($addr) ?></span>
            <?php
                    endif;
                endforeach;
            endif;
            ?>

            <h2 class="title"><?php the_title(); ?></h2>

        </div>


<?php
    }
}
