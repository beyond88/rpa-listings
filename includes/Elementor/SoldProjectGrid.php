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
            'tg_layout',
            [
                'label' => esc_html__('Design Layout', 'rpa-listings'),
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
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

?>
        <section class="inner-project-area rpa-sold-projects-area">
            <div class="container">
                <div class="project-item-wrap">
                    <div class="row">
                        <?php
                        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
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

                        while ($args->have_posts()) : $args->the_post();
                            $sold_summary = get_post_meta(get_the_ID(), 'rpa_project_sold_summary', true);
                            if (empty($sold_summary)) {
                                $sold_summary = '';
                            }
                        ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="project-item rpa-sold-project-item">
                                    <div class="project-thumb">
                                        <?php
                                        if (has_post_thumbnail()) {
                                            the_post_thumbnail('full', ['class' => 'img-responsive']);
                                        } else {
                                            // Fallback if no thumbnail
                                            echo '<img src="' . esc_url(site_url('/wp-includes/images/media/default.png')) . '" class="img-responsive" alt="">';
                                        }
                                        ?>
                                        <div class="rpa-sold-overlay">
                                            <div class="rpa-sold-summary-content">
                                                <?php echo wp_kses_post($sold_summary); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile;
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
                border-radius: 12px;
                cursor: default;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                aspect-ratio: 4 / 3;
            }

            .rpa-sold-project-item .project-thumb img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                transition: transform 0.4s ease;
            }

            .rpa-sold-project-item:hover .project-thumb img {
                transform: scale(1.05);
            }

            .rpa-sold-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(26, 26, 58, 0.85);
                /* Navy blue with opacity */
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.4s ease;
                padding: 30px;
                text-align: center;
                color: #fff;
            }

            .rpa-sold-project-item:hover .rpa-sold-overlay {
                opacity: 1;
            }

            .rpa-sold-summary-content {
                width: 100%;
            }

            /* Sold summary typography overrides to ensure visibility over dark background */
            .rpa-sold-summary-content * {
                color: #fff !important;
                margin-bottom: 10px;
            }

            .rpa-sold-summary-content h1,
            .rpa-sold-summary-content h2,
            .rpa-sold-summary-content h3,
            .rpa-sold-summary-content h4 {
                font-size: 42px;
                font-weight: 700;
                line-height: 1.2;
                margin-bottom: 5px;
            }

            .rpa-sold-summary-content p {
                font-size: 16px;
                font-weight: 400;
                opacity: 0.9;
            }

            .rpa-sold-summary-content *:last-child {
                margin-bottom: 0;
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
                /* Gold */
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
