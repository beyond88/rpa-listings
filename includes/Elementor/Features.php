<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Repeater;
use \Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Features extends Widget_Base {

	public function get_name() {
		return 'features';
	}

	public function get_title() {
		return __( 'Features', 'tpcore' );
	}

	public function get_icon() {
		return 'tp-icon';
	}

	public function get_categories() {
		return [ 'tpcore' ];
	}

	public function get_script_depends() {
		return [ 'tpcore' ];
	}

	protected function register_controls() {

        $this->start_controls_section(
            'tg_features',
            [
                'label' => esc_html__('Features List', 'tpcore'),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'tpcore' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        if (function_exists('tp_is_elementor_version') && tp_is_elementor_version('<', '2.6.0')) {
            $repeater->add_control(
                'tg_features_icon',
                [
                    'show_label' => false,
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'fa fa-star',
                ]
            );
        } else {
            $repeater->add_control(
                'tg_features_selected_icon',
                [
                    'show_label' => false,
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'fas fa-star',
                        'library' => 'solid',
                    ],
                ]
            );
        }

        $repeater->add_control(
            'tg_features_title', [
                'label' => esc_html__('Title', 'tpcore'),
                'description' => function_exists('tp_get_allowed_html_desc') ? tp_get_allowed_html_desc( 'basic' ) : '',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('High Quality Products', 'tpcore'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'tg_features_description',
            [
                'label' => esc_html__('Description', 'tpcore'),
                'description' => function_exists('tp_get_allowed_html_desc') ? tp_get_allowed_html_desc( 'intermediate' ) : '',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'Magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet sed quia',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'tg_features_list',
            [
                'label' => esc_html__('Features List', 'tpcore'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'tg_features_title' => esc_html__('High Quality Products', 'tpcore'),
                    ],
                    [
                        'tg_features_title' => esc_html__('Green Environment', 'tpcore')
                    ],
                    [
                        'tg_features_title' => esc_html__('Comprehensive Amenities', 'tpcore')
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'tg_features_align',
            [
                'label' => esc_html__( 'Alignment', 'tpcore' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'text-start' => [
                        'title' => esc_html__( 'Left', 'tpcore' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'text-center' => [
                        'title' => esc_html__( 'Center', 'tpcore' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'text-end' => [
                        'title' => esc_html__( 'Right', 'tpcore' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'toggle' => true,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};'
                ]
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'tpcore' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_transform',
			[
				'label' => __( 'Text Transform', 'tpcore' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'None', 'tpcore' ),
					'uppercase' => __( 'UPPERCASE', 'tpcore' ),
					'lowercase' => __( 'lowercase', 'tpcore' ),
					'capitalize' => __( 'Capitalize', 'tpcore' ),
				],
				'selectors' => [
					'{{WRAPPER}} .title' => 'text-transform: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
            <section class="features-area">
                <div class="container">
                    <div class="row justify-content-center">
                        <?php foreach ($settings['tg_features_list'] as $item) : ?>
                        <div class="col-lg-3 col-md-6 col-sm-10"> <!-- Changed from col-lg-4 to col-lg-3 for 4 columns -->
                            <div class="features-item <?php echo !empty($settings['tg_features_align']) ? esc_attr( $settings['tg_features_align'] ) : ''; ?>">
                                <?php if (!empty($item['tg_features_icon']) || !empty($item['tg_features_selected_icon']['value'])) : ?>
                                    <div class="feature-icon">
                                        <?php 
                                        if (function_exists('tp_render_icon')) {
                                            tp_render_icon($item, 'tg_features_icon', 'tg_features_selected_icon'); 
                                        } else {
                                            \Elementor\Icons_Manager::render_icon( $item['tg_features_selected_icon'], [ 'aria-hidden' => 'true' ] );
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <div class="feature-content">
                                    <?php if (!empty($item['tg_features_title' ])): ?>
                                        <h2 class="title"><?php echo function_exists('tp_kses') ? tp_kses( $item['tg_features_title'] ) : wp_kses_post($item['tg_features_title']); ?></h2>
                                    <?php endif; ?>

                                    <?php if (!empty($item['tg_features_description'])): ?>
                                        <p><?php echo function_exists('tp_kses') ? tp_kses( $item['tg_features_description'] ) : wp_kses_post($item['tg_features_description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
    <?php
	}
}
