<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;

if (!defined('ABSPATH')) exit;

class ProjectGallery extends Widget_Base
{
    public function get_name()
    {
        return 'tg-gallery';
    }

    public function get_script_depends()
    {
        return ['tpcore'];
    }

    public function get_title()
    {
        return esc_html__('Project Gallery', 'rpa-listings');
    }

    public function get_icon()
    {
        return 'eicon-gallery-grid';
    }

    public function get_categories()
    {
        return ['rpa-listings'];
    }

    protected function register_controls()
    {
        // Layout Panel
        $this->start_controls_section(
            'rpa_layout_section',
            [
                'label' => esc_html__('Design Layout', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'tp_design_style',
            [
                'label' => esc_html__('Select Layout', 'rpa-listings'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'layout-1' => esc_html__('Slider Layout', 'rpa-listings'),
                    'layout-2' => esc_html__('Grid Layout', 'rpa-listings'),
                ],
                'default' => 'layout-1',
            ]
        );

        $this->end_controls_section();

        // Image Gallery Section
        $this->start_controls_section(
            'tg_gallery_section',
            [
                'label' => esc_html__('Image Gallery', 'rpa-listings'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'tg_image_tab_show',
            [
                'label' => esc_html__('Show Image Gallery?', 'rpa-listings'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rpa-listings'),
                'label_off' => esc_html__('Hide', 'rpa-listings'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'tg_image_gallery_btn',
            [
                'label' => esc_html__('Button Text', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Photo gallery', 'rpa-listings'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'tg_gallery_image',
            [
                'label' => esc_html__('Choose Image', 'rpa-listings'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'tg_gallery_slides',
            [
                'label' => esc_html__('Gallery Items', 'rpa-listings'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '{{{ tg_gallery_image.url }}}',
            ]
        );

        $this->end_controls_section();

        // Video Gallery Section
        $this->start_controls_section(
            'tg_video_section',
            [
                'label' => esc_html__('Video Gallery', 'rpa-listings'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'tg_video_tab_show',
            [
                'label' => esc_html__('Show Video Gallery?', 'rpa-listings'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rpa-listings'),
                'label_off' => esc_html__('Hide', 'rpa-listings'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'tg_video_gallery_btn',
            [
                'label' => esc_html__('Button Text', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Video', 'rpa-listings'),
            ]
        );

        $video_repeater = new Repeater();

        $video_repeater->add_control(
            'tg_video_image',
            [
                'label' => esc_html__('Thumbnail Image', 'rpa-listings'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $video_repeater->add_control(
            'tg_video_url',
            [
                'label' => esc_html__('Video URL', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'default' => '#',
            ]
        );

        $this->add_control(
            'tg_video_slides',
            [
                'label' => esc_html__('Video Items', 'rpa-listings'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $video_repeater->get_controls(),
                'title_field' => '{{{ tg_video_url }}}',
            ]
        );

        $this->end_controls_section();

        // 360 Video Section
        $this->start_controls_section(
            'tg_360_section',
            [
                'label' => esc_html__('360 Video Gallery', 'rpa-listings'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'tg_360_tab_show',
            [
                'label' => esc_html__('Show 360 View?', 'rpa-listings'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rpa-listings'),
                'label_off' => esc_html__('Hide', 'rpa-listings'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'tg_video360_btn',
            [
                'label' => esc_html__('Button Text', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('360 video', 'rpa-listings'),
            ]
        );

        $video360_repeater = new Repeater();

        $video360_repeater->add_control(
            'tg_map_frame',
            [
                'label' => esc_html__('Map/360 Iframe', 'rpa-listings'),
                'description' => function_exists('tp_get_allowed_html_desc') ? tp_get_allowed_html_desc('intermediate') : '',
                'type' => Controls_Manager::TEXTAREA,
                'placeholder' => esc_html__('Paste iframe code here', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'tg_video360_slides',
            [
                'label' => esc_html__('360 Items', 'rpa-listings'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $video360_repeater->get_controls(),
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'rpa_gallery_style',
            [
                'label' => esc_html__('Style', 'rpa-listings'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'tg_image_full_show',
            [
                'label' => esc_html__('Show Image Full Width?', 'rpa-listings'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rpa-listings'),
                'label_off' => esc_html__('Hide', 'rpa-listings'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'tp_design_style' => 'layout-1',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_height',
            [
                'label' => esc_html__('Image Height', 'rpa-listings'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 700,
                ],
                'selectors' => [
                    '{{WRAPPER}} .image-height-class' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_gap',
            [
                'label' => esc_html__('Grid Gap', 'rpa-listings'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 24,
                ],
                'selectors' => [
                    '{{WRAPPER}} .reland-grid-style' => 'grid-gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'tp_design_style' => 'layout-2',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $slider_overflow = $settings['tg_image_full_show'] === 'yes' ? 'full-width-show' : 'full-width-hide';
        $is_grid = $settings['tp_design_style'] == 'layout-2';
        $rtl = is_rtl() ? 'true' : 'false';

        // --- Data Source Resolution ---
        // Priority: Post Meta (ProjectMetabox) > Widget Repeater (Elementor)
        $post_id = get_the_ID();

        // 1. Photo Gallery: post meta → widget repeater
        $gallery_slides = $settings['tg_gallery_slides'] ?? [];
        $has_meta_photos = false;
        if ($post_id) {
            $photo_meta = get_post_meta($post_id, 'rpa_project_photo_gallery_json', true);
            if ($photo_meta && is_string($photo_meta)) {
                $photo_items = json_decode($photo_meta, true);
                if (is_array($photo_items) && !empty($photo_items)) {
                    $gallery_slides = [];
                    foreach ($photo_items as $item) {
                        if (!empty($item['url'])) {
                            $gallery_slides[] = [
                                'tg_gallery_image' => ['url' => $item['url']],
                            ];
                        }
                    }
                    $has_meta_photos = !empty($gallery_slides);
                }
            }
        }

        // 2. Video Gallery: post meta → widget repeater
        $video_slides = $settings['tg_video_slides'] ?? [];
        $has_meta_videos = false;
        if ($post_id) {
            $video_meta = get_post_meta($post_id, 'rpa_project_video_gallery', true);
            if ($video_meta && is_string($video_meta)) {
                $video_items = json_decode($video_meta, true);
                if (is_array($video_items) && !empty($video_items)) {
                    $temp_video_slides = [];
                    foreach ($video_items as $item) {
                        if (!empty($item['url'])) {
                            $temp_video_slides[] = [
                                'tg_video_url'   => $item['url'],
                                'tg_video_image' => ['url' => $item['thumbnail_url'] ?? ''],
                            ];
                        }
                    }
                    if (!empty($temp_video_slides)) {
                        $video_slides = $temp_video_slides;
                        $has_meta_videos = true;
                    }
                }
            }
        }

        // 3. 360 Video Gallery: post meta → widget repeater
        $video360_slides = $settings['tg_video360_slides'] ?? [];
        $has_meta_360 = false;
        if ($post_id) {
            $v360_meta = get_post_meta($post_id, 'rpa_project_360_video_gallery', true);
            if ($v360_meta && is_string($v360_meta)) {
                $v360_items = json_decode($v360_meta, true);
                if (is_array($v360_items) && !empty($v360_items)) {
                    $temp_360_slides = [];
                    foreach ($v360_items as $item) {
                        $html = !empty($item['iframe_html']) ? $item['iframe_html'] : '';
                        // Backward compatibility for old 'url' key
                        if (empty($html) && !empty($item['url'])) {
                            $html = '<iframe src="' . esc_url($item['url']) . '" width="100%" height="100%" frameborder="0" allowfullscreen></iframe>';
                        }
                        
                        if (!empty($html)) {
                            $temp_360_slides[] = [
                                'tg_map_frame' => $html,
                            ];
                        }
                    }
                    if (!empty($temp_360_slides)) {
                        $video360_slides = $temp_360_slides;
                        $has_meta_360 = true;
                    }
                }
            }
        }

        // Visibility Logic: Show tab only if it has content AND is enabled in settings (or has metabox data)
        $show_image_tab = !empty($gallery_slides) && (!empty($settings['tg_image_tab_show']) || $has_meta_photos);
        $show_video_tab = !empty($video_slides) && (!empty($settings['tg_video_tab_show']) || $has_meta_videos);
        $show_360_tab   = !empty($video360_slides) && (!empty($settings['tg_360_tab_show']) || $has_meta_360);

        if (!$show_image_tab && !$show_video_tab && !$show_360_tab) {
            return;
        }

        $active_tab = '';
        if ($show_image_tab) {
            $active_tab = 'image';
        } elseif ($show_video_tab) {
            $active_tab = 'video';
        } elseif ($show_360_tab) {
            $active_tab = 'video360';
        }
        $widget_id = $this->get_id();
?>
        <script>
            jQuery(document).ready(function($) {
                var $scope = $('.project-gallery-instance-<?php echo $widget_id; ?>');
                var slickConfig = {
                    centerMode: true,
                    autoplay: false,
                    infinite: true,
                    speed: 500,
                    centerPadding: '0',
                    arrows: true,
                    slidesToShow: 1,
                    prevArrow: '<button class="slick-prev"><i class="fas fa-arrow-left"></i></button>',
                    nextArrow: '<button class="slick-next"><i class="fas fa-arrow-right"></i></button>',
                    rtl: <?php echo $rtl; ?>,
                    responsive: [
                        { breakpoint: 1800, settings: { slidesToShow: 1, centerPadding: '24px' } },
                        { breakpoint: 1500, settings: { slidesToShow: 1, centerPadding: '30px' } },
                        { breakpoint: 1200, settings: { slidesToShow: 1, centerPadding: '50px' } },
                        { breakpoint: 992, settings: { slidesToShow: 1, centerPadding: '0px' } },
                        { breakpoint: 767, settings: { slidesToShow: 1, centerPadding: '0px' } }
                    ]
                };

                // Re-bind Magnific Popup after Slick init (Slick clones DOM, breaking popup bindings)
                function reinitPopups($container) {
                    if (typeof $.fn.magnificPopup !== 'function') return;

                    // Unbind old popup handlers within this container
                    $container.find('.popup-image').off('click.magnificPopup');
                    $container.find('.popup-video').off('click.magnificPopup');

                    // Re-init image popup: only non-cloned slides to avoid duplicates in gallery
                    $container.find('.slick-slide:not(.slick-cloned) .popup-image').magnificPopup({
                        type: 'image',
                        gallery: { enabled: true },
                        callbacks: {
                            open: function() {
                                // Pause Slick arrow key navigation while lightbox is open
                                $container.find('.gallery-active.slick-initialized').slick('slickPause');
                            }
                        }
                    });

                    // For non-slider (grid) images
                    $container.find('.popup-image').not('.slick-slide .popup-image').magnificPopup({
                        type: 'image',
                        gallery: { enabled: true }
                    });

                    // Re-init video popup
                    $container.find('.popup-video').magnificPopup({
                        type: 'iframe'
                    });
                }

                // Initialize only the active (visible) tab's slider
                var $activeSlider = $scope.find('.tab-pane.active .gallery-active');
                if ($activeSlider.length && typeof $.fn.slick === 'function') {
                    $activeSlider.slick(slickConfig);
                }
                // Re-bind popups after Slick init
                reinitPopups($scope);

                // On tab switch: lazy-initialize or refresh the target tab's slider
                $scope.find('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                    var targetId = $(e.target).attr('data-bs-target');
                    var $targetPane = $scope.find(targetId);
                    var $targetSlider = $targetPane.find('.gallery-active');

                    if ($targetSlider.length && typeof $.fn.slick === 'function') {
                        if ($targetSlider.hasClass('slick-initialized')) {
                            $targetSlider.slick('refresh');
                        } else {
                            $targetSlider.slick(slickConfig);
                        }
                    }
                    // Re-bind popups after Slick init/refresh on this tab
                    reinitPopups($targetPane);
                });

                if ($scope.find('.reland-grid-style').length) {
                    $scope.find('.grid-gallery-item.item-5').append(
                        '<div class="see-all-btn"><span><?php echo esc_html($settings['tg_image_gallery_btn']) ?></span></div>'
                    );
                }
            });
        </script>

        <section class="project-gallery-area fix pb-50 <?php echo esc_attr($slider_overflow); ?> project-gallery-instance-<?php echo $widget_id; ?>">
            <div class="tab-content" id="galleryTabContent-<?php echo $widget_id; ?>">

                <?php if ($show_image_tab) : ?>
                    <div class="tab-pane fade <?php echo $active_tab === 'image' ? 'show active' : ''; ?>" id="image-<?php echo $widget_id; ?>" role="tabpanel" aria-labelledby="image-tab-<?php echo $widget_id; ?>">
                        <?php if ($is_grid) : ?>
                            <div class="container grid-gallery-container">
                                <div class="reland-grid-style">
                                    <?php 
                                    $i = 1;
                                    foreach ($gallery_slides as $item) : ?>
                                        <div class="grid-gallery-item item-<?php echo esc_attr($i++); ?>">
                                            <a href="<?php echo esc_url($item['tg_gallery_image']['url']); ?>" class="popup-image" data-elementor-open-lightbox="no">
                                                <img src="<?php echo esc_url($item['tg_gallery_image']['url']); ?>" alt="">
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="container">
                                <div class="row gallery-active">
                                    <?php foreach ($gallery_slides as $item) : ?>
                                        <div class="col-12">
                                            <div class="reland-gallery-item">
                                                <a href="<?php echo esc_url($item['tg_gallery_image']['url']); ?>" class="popup-image" data-elementor-open-lightbox="no">
                                                    <img src="<?php echo esc_url($item['tg_gallery_image']['url']); ?>" class="image-height-class" alt="">
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_video_tab) : ?>
                    <div class="tab-pane fade <?php echo $active_tab === 'video' ? 'show active' : ''; ?>" id="video-<?php echo $widget_id; ?>" role="tabpanel" aria-labelledby="video-tab-<?php echo $widget_id; ?>">
                        <div class="container">
                            <div class="row gallery-active">
                                <?php foreach ($video_slides as $item) : ?>
                                    <div class="col-12">
                                        <div class="reland-gallery-item video-slider-item">
                                            <?php if (!empty($item['tg_video_url'])) : ?>
                                                <a href="<?php echo esc_url($item['tg_video_url']) ?>" class="play-btn popup-video" data-elementor-open-lightbox="no"><i class="fab fa-youtube"></i></a>
                                            <?php endif; ?>
                                            <?php if (!empty($item['tg_video_image']['url'])) : ?>
                                                <img src="<?php echo esc_url($item['tg_video_image']['url']); ?>" class="image-height-class" style="min-height: 300px; object-fit: cover; width: 100%;" alt="">
                                            <?php else : ?>
                                                <div class="image-height-class" style="background: #1a1a3a; display: flex; align-items: center; justify-content: center; min-height: 300px; width: 100%;">
                                                    <i class="fab fa-youtube" style="font-size: 50px; color: rgba(255,255,255,0.2);"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($show_360_tab) : ?>
                    <div class="tab-pane fade <?php echo $active_tab === 'video360' ? 'show active' : ''; ?>" id="video360-<?php echo $widget_id; ?>" role="tabpanel" aria-labelledby="video360-tab-<?php echo $widget_id; ?>">
                        <div class="container">
                            <div class="row gallery-active">
                                <?php foreach ($video360_slides as $item) : ?>
                                    <div class="col-12">
                                        <div class="reland-gallery-item iframe-slider-item">
                                            <div class="apartment-view image-height-class" style="min-height: 300px; width: 100%;">
                                                <?php echo function_exists('tp_kses') ? tp_kses($item['tg_map_frame']) : wp_kses_post($item['tg_map_frame']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="project-nav-wrap d-flex justify-content-center">
                <ul class="nav nav-tabs" id="galleryTab-<?php echo $widget_id; ?>" role="tablist">
                    <?php if ($show_image_tab) : ?>
                        <li class="nav-item">
                            <button class="nav-link <?php echo $active_tab === 'image' ? 'active' : ''; ?>" id="image-tab-<?php echo $widget_id; ?>" data-bs-toggle="tab" data-bs-target="#image-<?php echo $widget_id; ?>" type="button" role="tab"><?php echo esc_html($settings['tg_image_gallery_btn']) ?></button>
                        </li>
                    <?php endif; ?>

                    <?php if ($show_video_tab) : ?>
                        <li class="nav-item">
                            <button class="nav-link <?php echo $active_tab === 'video' ? 'active' : ''; ?>" id="video-tab-<?php echo $widget_id; ?>" data-bs-toggle="tab" data-bs-target="#video-<?php echo $widget_id; ?>" type="button" role="tab"><?php echo esc_html($settings['tg_video_gallery_btn']) ?></button>
                        </li>
                    <?php endif; ?>

                    <?php if ($show_360_tab) : ?>
                        <li class="nav-item">
                            <button class="nav-link <?php echo $active_tab === 'video360' ? 'active' : ''; ?>" id="video360-tab-<?php echo $widget_id; ?>" data-bs-toggle="tab" data-bs-target="#video360-<?php echo $widget_id; ?>" type="button" role="tab"><?php echo esc_html($settings['tg_video360_btn']) ?></button>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </section>
<?php
    }
}
