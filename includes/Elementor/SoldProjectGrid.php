<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SoldProjectGrid extends Widget_Base
{
    public function get_script_depends()
    {
        return ['tpcore'];
    }

    public function get_name()
    {
        return 'rpa-sold-project-grid';
    }

    public function get_title()
    {
        return __('Sold Project Grid', 'rpa-listings');
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
            'tg_content',
            [
                'label' => esc_html__('Content', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'heading_text',
            [
                'label' => esc_html__('Heading', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('RECENT TRANSACTIONS', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'heading_html_tag',
            [
                'label' => esc_html__('Heading HTML Tag', 'rpa-listings'),
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
                'default' => 'h2',
            ]
        );

        $this->add_control(
            'description_text',
            [
                'label' => esc_html__('Description', 'rpa-listings'),
                'type' => Controls_Manager::WYSIWYG,
                'label_block' => true,
                'default' => esc_html__('RECREATIONAL PROPERTY ADVISORS > RECENT TRANSACTIONS', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'tg_project_per_page',
            [
                'label' => esc_html__('Project Per Page', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('6', 'rpa-listings'),
                'description' => esc_html__('If you want all posts shown on the page? just change the value to "-1"', 'rpa-listings'),
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__('Style', 'rpa-listings'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'header_margin',
            [
                'label' => esc_html__('Header Margin', 'rpa-listings'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'bottom' => '40',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rpa-sold-header' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_alignment',
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
                    '{{WRAPPER}} .rpa-sold-header' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'heading_style_divider',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control(
            'heading_color',
            [
                'label' => esc_html__('Heading Color', 'rpa-listings'),
                'type' => Controls_Manager::COLOR,
                'default' => '#1a1a3a',
                'selectors' => [
                    '{{WRAPPER}} .rpa-sold-section-heading' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'heading_typography',
                'selector' => '{{WRAPPER}} .rpa-sold-section-heading',
            ]
        );

        $this->add_responsive_control(
            'heading_margin',
            [
                'label' => esc_html__('Heading Margin', 'rpa-listings'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'bottom' => '10',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rpa-sold-section-heading' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'desc_style_divider',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control(
            'desc_color',
            [
                'label' => esc_html__('Description Color', 'rpa-listings'),
                'type' => Controls_Manager::COLOR,
                'default' => '#666666',
                'selectors' => [
                    '{{WRAPPER}} .rpa-sold-section-desc' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .rpa-sold-section-desc *' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'desc_typography',
                'selector' => '{{WRAPPER}} .rpa-sold-section-desc',
            ]
        );

        $this->add_responsive_control(
            'desc_margin',
            [
                'label' => esc_html__('Description Margin', 'rpa-listings'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .rpa-sold-section-desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Overlay Style Section
        $this->start_controls_section(
            'section_overlay_style',
            [
                'label' => esc_html__('Overlay Styles', 'rpa-listings'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'overlay_bg_color',
            [
                'label' => esc_html__('Background Color', 'rpa-listings'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(26, 26, 58, 0.9)',
                'selectors' => [
                    '{{WRAPPER}} .rpa-sold-overlay' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'overlay_hover_animation',
            [
                'label' => esc_html__('Hover Animation', 'rpa-listings'),
                'type' => Controls_Manager::SELECT,
                'default' => 'fade',
                'options' => [
                    'fade' => esc_html__('Fade In', 'rpa-listings'),
                    'zoom' => esc_html__('Zoom In', 'rpa-listings'),
                    'slide-up' => esc_html__('Slide Up', 'rpa-listings'),
                    'slide-down' => esc_html__('Slide Down', 'rpa-listings'),
                ],
            ]
        );

        $this->add_control(
            'image_hover_zoom',
            [
                'label' => esc_html__('Image Zoom on Hover', 'rpa-listings'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'rpa-listings'),
                'label_off' => esc_html__('No', 'rpa-listings'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'overlay_text_divider',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control(
            'overlay_label_color',
            [
                'label' => esc_html__('Label Color', 'rpa-listings'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .rpa-sold-info-line strong' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'overlay_label_typography',
                'label' => esc_html__('Label Typography', 'rpa-listings'),
                'selector' => '{{WRAPPER}} .rpa-sold-info-line strong',
            ]
        );

        $this->add_control(
            'overlay_value_color',
            [
                'label' => esc_html__('Value Color', 'rpa-listings'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .rpa-sold-info-line' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'overlay_value_typography',
                'label' => esc_html__('Value Typography', 'rpa-listings'),
                'selector' => '{{WRAPPER}} .rpa-sold-info-line',
            ]
        );

        $this->add_responsive_control(
            'overlay_padding',
            [
                'label' => esc_html__('Overlay Padding', 'rpa-listings'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => '20',
                    'right' => '20',
                    'bottom' => '20',
                    'left' => '20',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rpa-sold-overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $wrapper_class = 'inner-project-area rpa-sold-projects-area';
        $animation_class = 'rpa-anim-' . ($settings['overlay_hover_animation'] ?? 'fade');
        $zoom_class = ($settings['image_hover_zoom'] === 'yes') ? 'rpa-has-zoom' : '';

?>
        <section class="<?php echo esc_attr($wrapper_class); ?>">
            <div class="container">
                
                <?php if (!empty($settings['heading_text']) || !empty($settings['description_text'])) : ?>
                    <div class="rpa-sold-header">
                        <?php if (!empty($settings['heading_text'])) : 
                            $tag = !empty($settings['heading_html_tag']) ? $settings['heading_html_tag'] : 'h2';
                        ?>
                            <<?php echo esc_attr($tag); ?> class="rpa-sold-section-heading"><?php echo esc_html($settings['heading_text']); ?></<?php echo esc_attr($tag); ?>>
                        <?php endif; ?>
                        <?php if (!empty($settings['description_text'])) : ?>
                            <div class="rpa-sold-section-desc">
                                <?php echo wp_kses_post(wpautop($settings['description_text'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="project-item-wrap">
                    <div class="row">
                        <?php
                        $paged = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);
                        $per_page = !empty($settings['tg_project_per_page']) ? intval($settings['tg_project_per_page']) : 6;

                        $args = new \WP_Query([
                            'post_type' => 'project',
                            'post_status' => 'publish',
                            'posts_per_page' => $per_page,
                            'paged' => $paged,
                            'ignore_listing_status_filter' => true,
                            'meta_query' => [
                                [
                                    'key' => 'rpa_project_listing_status',
                                    'value' => 'sold',
                                    'compare' => '='
                                ]
                            ]
                        ]);

                        if ($args->have_posts()) :
                            while ($args->have_posts()) : $args->the_post();
                                $post_id = get_the_ID();
                                
                                // Retrieve from sold info metabox, split by lines, format each as rpa-sold-info-line
                                $sold_summary_raw = get_post_meta($post_id, 'rpa_project_sold_summary', true);
                                $summary_lines = [];

                                if ($sold_summary_raw) {
                                    // Normalize <br> and <p> tags into newlines, then strip remaining HTML
                                    $normalized = str_ireplace(['<br>', '<br/>', '<br />', '</p>'], "\n", $sold_summary_raw);
                                    $plain_text  = wp_strip_all_tags($normalized);
                                    $lines       = array_filter(array_map('trim', explode("\n", $plain_text)));

                                    foreach ($lines as $line) {
                                        $colon_pos = strpos($line, ':');
                                        if ($colon_pos !== false) {
                                            $label = trim(substr($line, 0, $colon_pos));
                                            $value = trim(substr($line, $colon_pos + 1));
                                            $summary_lines[] = '<div class="rpa-sold-info-line"><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</div>';
                                        } else {
                                            $summary_lines[] = '<div class="rpa-sold-info-line">' . esc_html($line) . '</div>';
                                        }
                                    }
                                }
                        ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="project-item rpa-sold-project-item <?php echo esc_attr($animation_class); ?> <?php echo esc_attr($zoom_class); ?>">
                                        <div class="project-thumb">
                                            <?php
                                            if (has_post_thumbnail()) {
                                                the_post_thumbnail('full', ['class' => 'img-responsive']);
                                            } else {
                                                echo '<img src="' . esc_url(site_url('/wp-includes/images/media/default.png')) . '" class="img-responsive" alt="">';
                                            }
                                            ?>
                                            <div class="rpa-sold-name-overlay">
                                                <h3 class="rpa-sold-name-title"><?php echo esc_html(get_the_title()); ?></h3>
                                            </div>
                                            <div class="rpa-sold-overlay">
                                                <div class="rpa-sold-summary-content">
                                                    <?php foreach ($summary_lines as $line) : ?>
                                                        <?php echo $line; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php 
                            endwhile;
                        else :
                            echo '<div class="col-12"><p class="text-center">' . esc_html__('No listings available yet.', 'rpa-listings') . '</p></div>';
                        endif;
                        wp_reset_postdata(); ?>
                    </div>
                </div>

                <div class="pagination-wrap">
                    <nav class="project-pagination" aria-label="Page navigation example">
                        <?php
                        $total_pages = $args->max_num_pages;
                        if ($total_pages > 1) {
                            $current_page = max(1, get_query_var('paged'));
                            echo paginate_links([
                                'base' => get_pagenum_link(1) . '%_%',
                                'format' => 'page/%#%',
                                'current' => $current_page,
                                'total' => $total_pages,
                                'prev_text' => __('<i class="fas fa-angle-double-left"></i>', 'rpa-listings'),
                                'next_text' => __('<i class="fas fa-angle-double-right"></i>', 'rpa-listings'),
                            ]);
                        }
                        ?>
                    </nav>
                </div>
            </div>
        </section>

        <style>
            .rpa-sold-projects-area .rpa-sold-project-item {
                margin-bottom: 30px;
            }

            .rpa-sold-project-item .project-thumb {
                position: relative;
                overflow: hidden;
                border-radius: 20px;
                cursor: default;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                aspect-ratio: 1 / 1;
            }

            .rpa-sold-project-item .project-thumb img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            }

            /* Image Zoom */
            .rpa-sold-project-item.rpa-has-zoom:hover .project-thumb img {
                transform: scale(1.15);
            }

            /* Default overlay: always visible dark background + property name */
            .rpa-sold-name-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.45);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1;
                transition: opacity 0.5s ease;
                padding: 20px;
            }

            .rpa-sold-project-item:hover .rpa-sold-name-overlay {
                opacity: 0;
            }

            .rpa-sold-name-title {
                color: #fff;
                font-size: 22px;
                font-weight: 700;
                text-align: center;
                margin: 0;
                line-height: 1.3;
                text-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
            }

            .rpa-sold-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: all 0.5s ease;
                padding: 20px;
                text-align: left;
                color: #fff;
                z-index: 2;
            }

            .rpa-sold-project-item:hover .rpa-sold-overlay {
                opacity: 1;
            }

            /* Animations */
            /* Fade - already default */
            
            /* Zoom Animation */
            .rpa-anim-zoom .rpa-sold-overlay {
                transform: scale(0.8);
            }
            .rpa-anim-zoom:hover .rpa-sold-overlay {
                transform: scale(1);
            }

            /* Slide Up Animation */
            .rpa-anim-slide-up .rpa-sold-overlay {
                transform: translateY(100%);
            }
            .rpa-anim-slide-up:hover .rpa-sold-overlay {
                transform: translateY(0);
            }

            /* Slide Down Animation */
            .rpa-anim-slide-down .rpa-sold-overlay {
                transform: translateY(-100%);
            }
            .rpa-anim-slide-down:hover .rpa-sold-overlay {
                transform: translateY(0);
            }

            .rpa-sold-summary-content {
                width: 100%;
                padding-left: 10px;
            }

            .rpa-sold-info-line {
                margin-bottom: 12px;
                font-size: 16px;
                line-height: 1.4;
            }

            .rpa-sold-info-line strong {
                font-weight: 700;
                display: inline-block;
                min-width: 120px;
            }

            .rpa-sold-projects-area .project-pagination {
                display: flex;
                justify-content: center;
                margin-top: 30px;
            }

            .rpa-sold-projects-area .page-numbers {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 45px;
                height: 45px;
                border-radius: 50%;
                border: 1px solid #b39b6b;
                color: #b39b6b;
                margin: 0 5px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s;
            }

            .rpa-sold-projects-area .page-numbers:hover,
            .rpa-sold-projects-area .page-numbers.current {
                background: #b39b6b;
                color: #fff;
            }
        </style>
<?php
    }
}
