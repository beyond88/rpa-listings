<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Repeater;

if (!defined('ABSPATH')) exit;

/**
 * Project Direction Widget - RPA Override
 *
 * Replaces the core TG_Project_Direction widget to add dynamic
 * Google Maps automation based on the address in the Title field.
 */
class ProjectDirection extends Widget_Base
{
    public function get_name()
    {
        return 'tg-direction';
    }

    public function get_title()
    {
        return __('Project Direction', 'tpcore');
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
        // _tg_image
        $this->start_controls_section(
            '_tg_image_section',
            [
                'label' => esc_html__('Direction Image', 'tpcore'),
            ]
        );

        $this->add_control(
            'hide_direction',
            [
                'label' => esc_html__('Hide Direction', 'tpcore'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'tpcore'),
                'label_off' => esc_html__('Hide', 'tpcore'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'tg_direction_type',
            [
                'label' => esc_html__('Select Direction Type', 'tpcore'),
                'type' => Controls_Manager::SELECT,
                'label_block' => true,
                'options' => [
                    'image' => esc_html__('Image', 'tpcore'),
                    'iframe' => esc_html__('Iframe', 'tpcore'),
                ],
                'default' => 'image',
            ]
        );

        $this->add_control(
            'tg_img',
            [
                'label' => esc_html__('Choose Image', 'tpcore'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'tg_direction_type' => 'image',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'tg_img_size',
                'default' => 'full',
                'exclude' => ['custom'],
                'condition' => [
                    'tg_direction_type' => 'image',
                ]
            ]
        );

        $this->add_control(
            'tg_direction_frame',
            [
                'label' => esc_html__('Map Iframe', 'tpcore'),
                'description' => function_exists('tp_get_allowed_html_desc') ? tp_get_allowed_html_desc('intermediate') : '',
                'type' => Controls_Manager::TEXTAREA,
                'default' => '',
                'placeholder' => esc_html__('Auto-generated from Title address, or paste your own iframe code', 'tpcore'),
                'condition' => [
                    'tg_direction_type' => 'iframe',
                ]
            ]
        );

        $this->end_controls_section();


        // tp_section_title
        $this->start_controls_section(
            'tp_section_title',
            [
                'label' => esc_html__('Title & Content', 'tpcore'),
            ]
        );

        $this->add_control(
            'tg_section_title_show',
            [
                'label' => esc_html__('Section Title & Content', 'tpcore'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'tpcore'),
                'label_off' => esc_html__('Hide', 'tpcore'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'tg_sub_title',
            [
                'label' => esc_html__('Sub Title', 'tpcore'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Baltimore, MD', 'tpcore'),
                'placeholder' => esc_html__('Type Sub Text', 'tpcore'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'tg_title',
            [
                'label' => esc_html__('Title (Address)', 'tpcore'),
                'description' => esc_html__('Enter an address here. It will be used to auto-generate the Google Map and direction button link.', 'tpcore'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('8601 Honeygo Boulevard,', 'tpcore'),
                'placeholder' => esc_html__('Type Address', 'tpcore'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'tg_title_tag',
            [
                'label' => esc_html__('Title HTML Tag', 'tpcore'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'h1' => [
                        'title' => esc_html__('H1', 'tpcore'),
                        'icon' => 'eicon-editor-h1'
                    ],
                    'h2' => [
                        'title' => esc_html__('H2', 'tpcore'),
                        'icon' => 'eicon-editor-h2'
                    ],
                    'h3' => [
                        'title' => esc_html__('H3', 'tpcore'),
                        'icon' => 'eicon-editor-h3'
                    ],
                    'h4' => [
                        'title' => esc_html__('H4', 'tpcore'),
                        'icon' => 'eicon-editor-h4'
                    ],
                    'h5' => [
                        'title' => esc_html__('H5', 'tpcore'),
                        'icon' => 'eicon-editor-h5'
                    ],
                    'h6' => [
                        'title' => esc_html__('H6', 'tpcore'),
                        'icon' => 'eicon-editor-h6'
                    ]
                ],
                'default' => 'h2',
                'toggle' => false,
            ]
        );

        $this->add_responsive_control(
            'tp_align',
            [
                'label' => esc_html__('Alignment', 'tpcore'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'text-left' => [
                        'title' => esc_html__('Left', 'tpcore'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'text-center' => [
                        'title' => esc_html__('Center', 'tpcore'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'text-right' => [
                        'title' => esc_html__('Right', 'tpcore'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'toggle' => false,
            ]
        );

        $this->end_controls_section();


        // Repeater
        $this->start_controls_section(
            'tg_direction_section',
            [
                'label' => __('Project Direction', 'tpcore'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'tg_direction_item',
            [
                'label' => esc_html__('Direction Text', 'tpcore'),
                'description' => function_exists('tp_get_allowed_html_desc') ? tp_get_allowed_html_desc('intermediate') : '',
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Supermarket: <span>200M</span>',
            ]
        );

        $this->add_control(
            'tg_direction_list',
            [
                'title_field' => esc_html__('Direction Lists', 'tpcore'),
                'show_label' => false,
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    ['tg_direction_item' => 'Supermarket: <span>200M</span>'],
                    ['tg_direction_item' => 'Railway Station: <span>1,800M</span>'],
                    ['tg_direction_item' => 'Airport: <span>2,790M</span>'],
                    ['tg_direction_item' => 'University: <span>250M</span>'],
                    ['tg_direction_item' => 'Hospital: <span>500M</span>'],
                    ['tg_direction_item' => 'Bus Station: <span>150M</span>'],
                    ['tg_direction_item' => 'Park: <span>1,500M</span>'],
                ]
            ]
        );

        $this->end_controls_section();

        // tg_button_group
        $this->start_controls_section(
            'tg_button_group',
            [
                'label' => esc_html__('Button', 'tpcore'),
            ]
        );

        $this->add_control(
            'tg_button_show',
            [
                'label' => esc_html__('Show Button', 'tpcore'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'tpcore'),
                'label_off' => esc_html__('Hide', 'tpcore'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'tg_btn_text',
            [
                'label' => esc_html__('Button Text', 'tpcore'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Get direction', 'tpcore'),
                'title' => esc_html__('Enter button text', 'tpcore'),
                'label_block' => true,
                'condition' => [
                    'tg_button_show' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'tg_btn_link_type',
            [
                'label' => esc_html__('Button Link Type', 'tpcore'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'label_block' => true,
                'condition' => [
                    'tg_button_show' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'tg_btn_link',
            [
                'label' => esc_html__('Button link', 'tpcore'),
                'type' => Controls_Manager::URL,
                'dynamic' => [
                    'active' => true,
                ],
                'placeholder' => esc_html__('https://your-link.com', 'tpcore'),
                'show_external' => false,
                'default' => [
                    'url' => '#',
                    'is_external' => true,
                    'nofollow' => true,
                    'custom_attributes' => '',
                ],
                'condition' => [
                    'tg_btn_link_type' => '1',
                    'tg_button_show' => 'yes'
                ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'tg_btn_page_link',
            [
                'label' => esc_html__('Select Button Page', 'tpcore'),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => function_exists('tp_get_all_pages') ? tp_get_all_pages() : [],
                'condition' => [
                    'tg_btn_link_type' => '2',
                    'tg_button_show' => 'yes'
                ]
            ]
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Style', 'tpcore'),
                'tab' => Controls_Manager::TAB_STYLE,
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
                    '{{WRAPPER}} .title' => 'text-transform: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget output on the frontend.
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        // --- Dynamic Google Maps Automation ---
        $post_id = get_the_ID();
        $project_location = get_post_meta($post_id, 'rpa_project_location', true);

        $raw_address = !empty($project_location) ? $project_location : (isset($settings['tg_title']) ? $settings['tg_title'] : '');
        $address = trim(wp_strip_all_tags($raw_address));
        $encoded_address = '';
        $dynamic_map_url = '';

        if (!empty($address)) {
            $encoded_address = urlencode($address);
            $dynamic_map_url = 'https://www.google.com/maps/search/?api=1&query=' . $encoded_address;
        }

        // --- Image ---
        $tg_img_url = '';
        $tg_img_alt = '';
        if (!empty($settings['tg_img']['url'])) {
            $tg_img_url = !empty($settings['tg_img']['id']) ? wp_get_attachment_image_url($settings['tg_img']['id'], $settings['tg_img_size_size']) : $settings['tg_img']['url'];
            $tg_img_alt = get_post_meta($settings["tg_img"]["id"], "_wp_attachment_image_alt", true);
        }

        // --- Button Link ---
        if ('2' == $settings['tg_btn_link_type']) {
            $this->add_render_attribute('tg-button-arg', 'href', get_permalink($settings['tg_btn_page_link']));
            $this->add_render_attribute('tg-button-arg', 'target', '_self');
            $this->add_render_attribute('tg-button-arg', 'rel', 'nofollow');
            $this->add_render_attribute('tg-button-arg', 'class', 'btn transparent-btn');
        } else {
            // If we have a dynamic map URL from the address, use it; otherwise use the manual link
            $btn_url = '';
            if (!empty($dynamic_map_url)) {
                $btn_url = $dynamic_map_url;
            } elseif (!empty($settings['tg_btn_link']['url'])) {
                $btn_url = $settings['tg_btn_link']['url'];
            }

            if (!empty($btn_url)) {
                $this->add_render_attribute('tg-button-arg', 'href', esc_url($btn_url));
                $this->add_render_attribute('tg-button-arg', 'target', '_blank');
                $this->add_render_attribute('tg-button-arg', 'rel', 'nofollow noopener');
                $this->add_render_attribute('tg-button-arg', 'class', 'btn transparent-btn');
            }
        }

        $this->add_render_attribute('title_args', 'class', 'title');

        // Helper for kses
        $kses = function ($html) {
            return function_exists('tp_kses') ? tp_kses($html) : wp_kses_post($html);
        };

        ?>

        <!-- direction-area -->
        <section class="direction-area">
            <div class="container">
                <div class="row align-items-center">

                    <?php if (!empty($settings['hide_direction'])) : ?>
                        <div class="col-lg-6">
                            <?php
                            // Determine what to show: dynamic map from address OR manual image/iframe
                            if (!empty($encoded_address)) :
                                // Dynamic Google Map from address
                            ?>
                                <div class="direction-map">
                                    <iframe
                                        width="100%"
                                        height="450"
                                        frameborder="0"
                                        style="border:0"
                                        src="https://maps.google.com/maps?q=<?php echo esc_attr($encoded_address); ?>&output=embed"
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            <?php elseif ($settings['tg_direction_type'] == 'image') : ?>
                                <div class="direction-img text-center">
                                    <img src="<?php echo esc_url($tg_img_url); ?>" alt="<?php echo esc_attr($tg_img_alt); ?>">
                                </div>
                            <?php else : ?>
                                <div class="direction-map">
                                    <?php echo $kses($settings['tg_direction_frame']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="col-lg-6">
                        <div class="direction-content">

                            <?php if (!empty($settings['tg_section_title_show'])) : ?>
                            <div class="section-title mb-40">

                                <?php if (!empty($settings['tg_sub_title'])) : ?>
                                    <span class="sub-title"><?php echo $kses($settings['tg_sub_title']); ?></span>
                                <?php endif; ?>

                                <?php
                                    $display_title = !empty($project_location) ? $project_location : (isset($settings['tg_title']) ? $settings['tg_title'] : '');
                                    if (!empty($display_title)) :
                                        printf(
                                            '<%1$s %2$s>%3$s</%1$s>',
                                            tag_escape($settings['tg_title_tag']),
                                            $this->get_render_attribute_string('title_args'),
                                            $kses($display_title)
                                        );
                                    endif;
                                ?>

                            </div>
                            <?php endif; ?>

                            <ul class="list-wrap">
                                <?php foreach ($settings['tg_direction_list'] as $item) : ?>
                                <li><?php echo $kses($item['tg_direction_item']); ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if (!empty($settings['tg_button_show'])) : ?>
                            <a <?php echo $this->get_render_attribute_string('tg-button-arg'); ?>>
                                <div class="btn_m">
                                    <div class="btn_c">
                                        <div class="btn_t1"><?php echo esc_html($settings['tg_btn_text']); ?></div>
                                        <div class="btn_t2"><?php echo esc_html($settings['tg_btn_text']); ?></div>
                                    </div>
                                </div>
                            </a>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- direction-area-end -->

        <?php
    }
}
