<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \Elementor\Utils;
use \Elementor\Control_Media;
use \Elementor\Repeater;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Text_Shadow;
use \Elementor\Group_Control_Typography;
use \Elementor\Core\Schemes\Typography;
use \Elementor\Group_Control_Background;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ProjectMeta extends Widget_Base
{

    public function get_name()
    {
        return 'tp-project-meta';
    }

    public function get_title()
    {
        return __('Project Meta', 'tpcore');
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

        // tp_project_meta
        $this->start_controls_section(
            'tp_section_meta',
            [
                'label' => esc_html__('Project Meta', 'tpcore'),
            ]
        );

        $this->add_control(
            'tg_custom_meta_show',
            [
                'label' => esc_html__('Add Custom Meta?', 'tpcore'),
                'type' => Controls_Manager::SWITCHER,
                'description' => esc_html__('This meta comes from the project post type. If you want to add a custom click on the switcher', 'tpcore'),
                'label_on' => esc_html__('Show', 'tpcore'),
                'label_off' => esc_html__('Hide', 'tpcore'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $repeater = new Repeater();

        if (function_exists('tp_is_elementor_version') && tp_is_elementor_version('<', '2.6.0')) {
            $repeater->add_control(
                'tg_icon',
                [
                    'show_label' => false,
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'flaticon-018-rescale',
                ]
            );
        } else {
            $repeater->add_control(
                'tg_selected_icon',
                [
                    'show_label' => false,
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'flaticon-018-rescale',
                        'library' => 'solid',
                    ],
                ]
            );
        }

        $repeater->add_control(
            'tg_meta_label',
            [
                'label' => esc_html__('Label', 'tpcore'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Property size', 'tpcore'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'tg_meta_value',
            [
                'label' => esc_html__('Value', 'tpcore'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('174,000 sqft', 'tpcore'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'tg_meta_list',
            [
                'title_field' => esc_html__('Meta List', 'tpcore'),
                'show_label' => false,
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'tg_meta_label' => esc_html__('Property size', 'tpcore'),
                        'tg_meta_value' => esc_html__('174,000 sqft', 'tpcore'),
                    ],
                ],
                'condition' => [
                    'tg_custom_meta_show' => 'yes'
                ]
            ]
        );

        $this->end_controls_section();

        // STYLE TAB
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Style', 'tpcore'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'tp_align',
            [
                'label' => esc_html__('Alignment', 'tpcore'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__('Left', 'tpcore'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'tpcore'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__('Right', 'tpcore'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'toggle' => false,
                'selectors' => [
                    '{{WRAPPER}} .list-wrap' => 'justify-content: {{VALUE}};'
                ]
            ]
        );

        $this->add_control(
            'text_transform',
            [
                'label' => __('Text Transform', 'tpcore'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('None', 'tpcore'),
                    'uppercase' => __('UPPERCASE', 'tpcore'),
                    'lowercase' => __('lowercase', 'tpcore'),
                    'capitalize' => __('Capitalize', 'tpcore'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .content p' => 'text-transform: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            '__item_width',
            [
                'label' => __('Item Width', 'tpcore'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 5,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                ],
                'selectors' => [
                    '{{WRAPPER}} .list-wrap .info-item' => 'width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}}; flex-basis: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_spacing',
            [
                'label' => __('Item Spacing (px)', 'tpcore'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .project-info-wrap .list-wrap' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => __('Icon Spacing (px)', 'tpcore'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .info-item .icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $post_id = get_the_ID();

        // Get fields from our custom metaboxes
        $property_types = get_post_meta($post_id, 'rpa_project_property_types', true);
        $project_status = get_post_meta($post_id, 'rpa_project_status', true);
        
        $year_built = get_post_meta($post_id, 'year_built', true);
        $number_of_units = get_post_meta($post_id, 'number_of_units', true);
        $total_nrsf = get_post_meta($post_id, 'total_nrsf', true);
        $lot_size = get_post_meta($post_id, 'lot_size', true);
        $market_bid = get_post_meta($post_id, 'market_bid', true);

        // Process property types
        $property_type_str = '';
        if (is_array($property_types) && !empty($property_types)) {
            $property_type_str = implode(', ', $property_types);
        }
?>

        <?php if ($settings['tg_custom_meta_show'] == 'yes') : ?>

            <div class="project-info-wrap">
                <ul class="list-wrap">
                    <?php foreach ($settings['tg_meta_list'] as $item) : ?>
                        <li class="info-item">
                            <?php if (!empty($item['tg_icon']) || !empty($item['tg_selected_icon']['value'])) : ?>
                                <div class="icon">
                                    <?php if (function_exists('tp_render_icon')) { tp_render_icon($item, 'tg_icon', 'tg_selected_icon'); } ?>
                                </div>
                            <?php endif; ?>
                            <div class="content">
                                <p><?php echo wp_kses_post($item['tg_meta_label']); ?> <span><?php echo wp_kses_post($item['tg_meta_value']); ?></span></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php else : ?>

            <div class="project-info-wrap">
                <ul class="list-wrap">

                    <?php if (!empty($property_type_str)) : ?>
                        <li class="info-item">
                            <div class="icon">
                                <i class="flaticon-017-apartment"></i>
                            </div>
                            <div class="content">
                                <p><?php echo esc_html__('Property Type', 'tpcore') ?> <span><?php echo esc_html($property_type_str) ?></span></p>
                            </div>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($year_built)) : ?>
                        <li class="info-item">
                            <div class="icon">
                                <i class="flaticon-009-crane-truck"></i>
                            </div>
                            <div class="content">
                                <p><?php echo esc_html__('Year Built', 'tpcore') ?> <span><?php echo esc_html($year_built) ?></span></p>
                            </div>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($number_of_units)) : ?>
                        <li class="info-item">
                            <div class="icon">
                                <i class="flaticon-017-apartment"></i>
                            </div>
                            <div class="content">
                                <p><?php echo esc_html__('Units', 'tpcore') ?> <span><?php echo esc_html($number_of_units) ?></span></p>
                            </div>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($total_nrsf)) : ?>
                        <li class="info-item">
                            <div class="icon">
                                <i class="flaticon-018-rescale"></i>
                            </div>
                            <div class="content">
                                <p><?php echo esc_html__('NRSF', 'tpcore') ?> <span><?php echo esc_html($total_nrsf) ?></span></p>
                            </div>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($lot_size)) : ?>
                        <li class="info-item">
                            <div class="icon">
                                <i class="flaticon-016-puzzle"></i>
                            </div>
                            <div class="content">
                                <p><?php echo esc_html__('Lot Size', 'tpcore') ?> <span><?php echo esc_html($lot_size) ?></span></p>
                            </div>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($project_status)) : ?>
                        <li class="info-item">
                            <div class="icon">
                                <i class="flaticon-009-crane-truck"></i>
                            </div>
                            <div class="content">
                                <p><?php echo esc_html__('Status', 'tpcore') ?> <span><?php echo esc_html($project_status) ?></span></p>
                            </div>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($market_bid)) : ?>
                        <li class="info-item">
                            <div class="icon">
                                <i class="flaticon-008-money-bag"></i>
                            </div>
                            <div class="content">
                                <p><?php echo esc_html__('Market Bid', 'tpcore') ?> <span><?php echo esc_html($market_bid) ?></span></p>
                            </div>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>

        <?php endif; ?>

<?php
    }
}
