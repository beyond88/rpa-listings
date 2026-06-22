<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * RPA Project Grid
 *
 * Extends the core "Project With Search" (project-grid) widget output by
 * adding the Market Bid meta below each project in the grid.
 *
 * Registered under the same widget name ("project-grid") so existing
 * Elementor pages render this version after the core one is unregistered.
 * No core files are modified.
 */
class RPAProjectGrid extends Widget_Base
{
    public function get_script_depends()
    {
        return ['tpcore'];
    }

    public function get_name()
    {
        return 'project-grid';
    }

    public function get_title()
    {
        return __('Project With Search', 'rpa-listings');
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
        // layout Panel
        $this->start_controls_section(
            'tg_layout',
            [
                'label' => esc_html__('Design Layout', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'tg_section_search_show',
            [
                'label' => esc_html__('Hide Search and Filter', 'rpa-listings'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rpa-listings'),
                'label_off' => esc_html__('Hide', 'rpa-listings'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'tg_project_search',
            [
                'label' => esc_html__('Project Search', 'rpa-listings'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => esc_html__('Search by project name or location...', 'rpa-listings'),
                'condition' => [
                    'tg_section_search_show' => 'yes',
                ]
            ]
        );

        $this->add_control(
            'tg_project_status',
            [
                'label' => esc_html__('Project Status', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Project Status', 'rpa-listings'),
                'condition' => [
                    'tg_section_search_show' => 'yes',
                ]
            ]
        );

        $this->add_control(
            'tg_project_type',
            [
                'label' => esc_html__('Project Type', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Project Type', 'rpa-listings'),
                'condition' => [
                    'tg_section_search_show' => 'yes',
                ]
            ]
        );

        $this->add_control(
            'tg_project_location',
            [
                'label' => esc_html__('Project Location', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Project Location', 'rpa-listings'),
                'condition' => [
                    'tg_section_search_show' => 'yes',
                ]
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

        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Style', 'rpa-listings'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_transform',
            [
                'label' => __('Text Transform', 'rpa-listings'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('None', 'rpa-listings'),
                    'uppercase' => __('UPPERCASE', 'rpa-listings'),
                    'lowercase' => __('lowercase', 'rpa-listings'),
                    'capitalize' => __('Capitalize', 'rpa-listings'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .title' => 'text-transform: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Market Bid Style Section
        $this->start_controls_section(
            'section_market_bid_style',
            [
                'label' => __('Market Bid', 'rpa-listings'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'market_bid_color',
            [
                'label' => esc_html__('Color', 'rpa-listings'),
                'type' => Controls_Manager::COLOR,
                'default' => '#e8552d',
                'selectors' => [
                    '{{WRAPPER}} .rpa-market-bid' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'market_bid_typography',
                'selector' => '{{WRAPPER}} .rpa-market-bid',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $project_status = get_terms(array('taxonomy' => 'project-status', 'hide_empty' => false));
        $project_type = get_terms(array('taxonomy' => 'project-type', 'hide_empty' => false));
        $project_location = get_terms(array('taxonomy' => 'project-location', 'hide_empty' => false));

?>

        <!-- project-area -->
        <section class="inner-project-area">
            <div class="container">
                <?php if (!empty($settings['tg_section_search_show'])) : ?>
                    <div class="project-top-meta mb-50">
                        <form action="#">
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="form-grp">
                                        <input type="text" placeholder="<?php echo esc_attr($settings['tg_project_search']) ?>" id="project-search">
                                        <button type="submit"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                                <div class="col-xl-8">
                                    <div class="row">
                                        <div class="col-md-4 col-sm-6">
                                            <div class="form-grp select">
                                                <select id="project-status" name="select" class="form-select" aria-label="Default select example">
                                                    <option value=""><?php echo esc_attr($settings['tg_project_status']) ?></option>
                                                    <?php foreach ($project_status as $status) : ?>
                                                        <option value="<?php echo esc_html($status->slug); ?>"><?php echo esc_html($status->name); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="form-grp select">
                                                <select id="project-type" name="select" class="form-select" aria-label="Default select example">
                                                    <option value=""><?php echo esc_attr($settings['tg_project_type']) ?></option>
                                                    <?php foreach ($project_type as $type) : ?>
                                                        <option value="<?php echo esc_html($type->slug); ?>"><?php echo esc_html($type->name); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-grp select">
                                                <select id="project-location" name="select" class="form-select" aria-label="Default select example">
                                                    <option value=""><?php echo esc_attr($settings['tg_project_location']) ?></option>
                                                    <?php foreach ($project_location as $location) : ?>
                                                        <option value="<?php echo esc_html($location->slug); ?>"><?php echo esc_html($location->name); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="project-item-wrap">
                    <div class="row">
                        <?php
                        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

                        $args = new \WP_Query(array(
                            'post_type' => 'project',
                            'post_status' => 'publish',
                            'posts_per_page' => $settings['tg_project_per_page'],
                            'paged' => $paged,
                        ));

                        /* Start the Loop */
                        while ($args->have_posts()) : $args->the_post();

                            $project_location_meta = function_exists('get_field') ? get_field('project_location') : '';
                            $market_bid = get_post_meta(get_the_ID(), 'market_bid', true);
                        ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="project-item">
                                    <div class="project-thumb">
                                        <a href="<?php echo the_permalink(); ?>">
                                            <?php the_post_thumbnail('full', ['class' => 'img-responsive']); ?>
                                        </a>
                                    </div>
                                    <div class="project-content">
                                        <h3 class="title">
                                            <a href="<?php echo the_permalink(); ?>"><?php echo the_title(); ?></a>
                                        </h3>

                                        <?php if (!empty($project_location_meta)) : ?>
                                            <span><?php echo esc_html($project_location_meta); ?></span>
                                        <?php endif; ?>

                                        <?php if (!empty($market_bid)) : ?>
                                            <p class="rpa-market-bid"><?php echo nl2br(esc_html($market_bid)); ?></p>
                                        <?php endif; ?>

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

                            echo paginate_links(array(
                                'base' => get_pagenum_link(1) . '%_%',
                                'format' => 'page/%#%',
                                'current' => $current_page,
                                'total' => $total_pages,
                                'prev_text'    => __('<i class="fas fa-angle-double-left"></i>'),
                                'next_text'    => __('<i class="fas fa-angle-double-right"></i>'),
                            ));
                        }
                        ?>
                    </nav>
                </div>

            </div>
        </section>
        <!-- project-area-end -->

        <style>
            .inner-project-area .rpa-market-bid {
                margin-top: 8px;
                line-height: 1.4;
                font-weight: 500;
            }
        </style>

<?php
    }
}
