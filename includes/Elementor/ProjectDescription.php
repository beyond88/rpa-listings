<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Core\Schemes\Typography;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ProjectDescription extends Widget_Base
{
    public function get_name()
    {
        return 'tp-project-description';
    }

    public function get_title()
    {
        return esc_html__('Property Description', 'rpa-listings');
    }

    public function get_icon()
    {
        return 'tp-icon';
    }

    public function get_categories()
    {
        return ['tpcore'];
    }

    // Prevent caching as content depends on user access
    protected function is_dynamic_content(): bool
    {
        return true;
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Content Settings', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'heading_text',
            [
                'label' => esc_html__('Heading Text', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Property Overview', 'rpa-listings'),
                'description' => esc_html__('Heading shown above the description.', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'heading_html_tag',
            [
                'label' => esc_html__('HTML Tag', 'rpa-listings'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'default' => 'h3',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => esc_html__('Access Button Text', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Deal Room Access', 'rpa-listings'),
                'description' => esc_html__('Text for the button shown on hover before gaining access.', 'rpa-listings'),
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Style', 'rpa-listings'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'heading_alignment',
            [
                'label' => esc_html__('Heading Alignment', 'rpa-listings'),
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
                    '{{WRAPPER}} .rpa-desc-heading' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'heading_color_style',
            [
                'label' => __('Heading Color', 'rpa-listings'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rpa-desc-heading' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'heading_typography_style',
                'label' => __('Heading Typography', 'rpa-listings'),
                'selector' => '{{WRAPPER}} .rpa-desc-heading',
            ]
        );

        $this->add_responsive_control(
            'heading_margin',
            [
                'label' => esc_html__('Heading Margin', 'rpa-listings'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .rpa-desc-heading' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_padding',
            [
                'label' => esc_html__('Heading Padding', 'rpa-listings'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .rpa-desc-heading' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'hr_divider',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'text_align',
            [
                'label' => esc_html__('Text Alignment', 'rpa-listings'),
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
                    'justify' => [
                        'title' => esc_html__('Justify', 'rpa-listings'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .project-description-content' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'rpa-listings'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .project-description-content' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .project-description-content *' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .project-description-content, {{WRAPPER}} .project-description-content *',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $project_id = get_the_ID();
        
        if (!$project_id) {
            return;
        }

        // Check if user has signed CA for this project
        $has_access = \RPAListings\Frontend\DealHandler::has_access($project_id);

        if (!empty($settings['heading_text'])) {
            $tag = !empty($settings['heading_html_tag']) ? $settings['heading_html_tag'] : 'h3';
            echo '<' . esc_attr($tag) . ' class="rpa-desc-heading">' . esc_html($settings['heading_text']) . '</' . esc_attr($tag) . '>';
        }

        $wrapper_class = 'project-description-wrapper';
        if (!$has_access) {
            $wrapper_class .= ' rpa-blurred-manager';
        }

        ?>
        <div class="<?php echo esc_attr($wrapper_class); ?>" data-project-id="<?php echo esc_attr($project_id); ?>" style="position: relative;">
            <?php if (!$has_access): ?>
                <div class="rpa-blur-overlay">
                    <button type="button" class="rpa-btn rpa-deal-room-btn rpa-overlay-btn" onclick="openDealRoomModal()"><?php echo esc_html($settings['button_text']); ?></button>
                </div>
            <?php endif; ?>

            <div class="project-description-content">
                <?php 
                if ($has_access) {
                    // Show real content from our custom metabox
                    $full_desc = get_post_meta($project_id, 'rpa_project_full_desc', true);
                    
                    if (!empty($full_desc) && is_string($full_desc)) {
                        echo '<div class="rpa-description-text">' . wp_kses_post(wpautop($full_desc)) . '</div>';
                    } else {
                        // Safe fallback message if no custom description is provided
                        echo '<div class="rpa-no-content-msg" style="padding: 20px 0; color: #666; font-style: italic;">' . esc_html__('No full description provided for this project.', 'rpa-listings') . '</div>';
                    }
                } else {
                    echo '<div class="rpa-text-skeleton" style="padding: 10px 0; user-select: none; pointer-events: none;">';
                    // Generate CSS lines to mimic blurred text paragraphs
                    $lines = [100, 96, 98, 94, 85, 100, 95, 99, 91, 88, 97, 93, 96, 90, 82];
                    foreach ($lines as $index => $width) {
                        $margin_bottom = ($index % 5 === 4) ? '25px' : '14px';
                        echo '<div style="height: 12px; background: #797978; border-radius: 4px; margin-bottom: ' . $margin_bottom . '; width: ' . $width . '%; filter: blur(5px); opacity: 0.3;"></div>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        
        <?php if (!$has_access): ?>
            <?php DealRoomModal::render_modal($project_id); ?>
        <?php endif; ?>
        <?php
    }
}
