<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;

if (!defined('ABSPATH')) {
    exit;
}

class PropertyAmenities extends Widget_Base
{
    public function get_name()
    {
        return 'tp-property-amenities';
    }

    public function get_title()
    {
        return esc_html__('Property Amenities', 'rpa-listings');
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
            'section_content',
            [
                'label' => esc_html__('Content Settings', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'tg_heading_text',
            [
                'label' => esc_html__('Heading Text', 'rpa-listings'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Property Amenities', 'rpa-listings'),
                'placeholder' => esc_html__('Enter heading text', 'rpa-listings'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'tg_heading_tag',
            [
                'label' => esc_html__('HTML Tag', 'rpa-listings'),
                'type' => \Elementor\Controls_Manager::SELECT,
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

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $post_id = get_the_ID();
        $amenities = get_post_meta($post_id, 'rpa_project_amenities', true);
        $amenities_typed = get_post_meta($post_id, 'rpa_project_amenities_typed', true);
        
        $heading_tag = $settings['tg_heading_tag'] ?: 'h2';
        $heading_text = $settings['tg_heading_text'] ?: esc_html__('Property Amenities', 'rpa-listings');

?>
        <div class="project-info-wrap rpa-project-meta-extra">

            <?php if ((!empty($amenities) && is_array($amenities)) || !empty($amenities_typed)) : ?>
                <div class="rpa-amenities-wrap" style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="content">
                        <<?php echo esc_attr($heading_tag); ?>>
                            <?php echo esc_html($heading_text); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    </div>

                    <?php if (!empty($amenities) && is_array($amenities)) : ?>
                        <ul class="amenities-list" style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px 20px; width: 100%;">
                            <?php foreach ($amenities as $amenity) : ?>
                                <li style="display: flex; align-items: flex-start; gap: 8px; font-size: 14px; color: #111; font-weight: 500; line-height: 1.4;">
                                    <i class="flaticon-006-shield" style="color: #cda252; font-size: 13px; margin-top: 3px; font-weight: normal;"></i> <span style="flex: 1;"><?php echo esc_html($amenity); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php
                    if (!empty($amenities_typed)) :
                        // Normalize block-level tags and line breaks to newlines, then strip HTML
                        $normalized = str_ireplace(
                            ['<br>', '<br/>', '<br />', '</p>', '</li>'],
                            "\n",
                            $amenities_typed
                        );
                        $plain = wp_strip_all_tags($normalized);
                        $lines = array_filter(array_map('trim', explode("\n", $plain)));
                    ?>
                        <ul class="amenities-list" style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px 20px; width: 100%;">
                            <?php foreach ($lines as $line) : ?>
                                <li style="display: flex; align-items: flex-start; gap: 8px; font-size: 14px; color: #111; font-weight: 500; line-height: 1.4;">
                                    <i class="flaticon-006-shield" style="color: #cda252; font-size: 13px; margin-top: 3px; font-weight: normal;"></i> <span style="flex: 1;"><?php echo esc_html($line); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
<?php
    }
}
