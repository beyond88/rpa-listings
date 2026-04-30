<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;

if (!defined('ABSPATH')) {
    exit;
}

class ProjectMetaExtra extends Widget_Base
{
    public function get_name()
    {
        return 'tp-project-meta-extra';
    }

    public function get_title()
    {
        return esc_html__('Project Meta Extra', 'rpa-listings');
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
        // No custom controls needed for now.
    }

    protected function render()
    {
        $post_id = get_the_ID();
        $property_types = get_post_meta($post_id, 'rpa_project_property_types', true);
        $addresses = get_post_meta($post_id, 'rpa_project_addresses', true);
        $addresses = is_array($addresses) ? array_filter(array_map('trim', $addresses)) : [];
        $amenities = get_post_meta($post_id, 'rpa_project_amenities', true);
        $status = get_post_meta($post_id, 'rpa_project_status', true);
?>
        <div class="project-info-wrap rpa-project-meta-extra">
            <ul class="list-wrap" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px; list-style: none; padding: 0;">
                <?php if (!empty($property_types) && is_array($property_types)) : ?>
                    <li class="info-item" style="display: flex; align-items: center; gap: 15px;">
                        <div class="icon" style="color: #cda252; font-size: 40px; line-height: 1;">
                            <i class="flaticon-017-apartment"></i>
                        </div>
                        <div class="content">
                            <p style="margin: 0; font-size: 13px; font-weight: 500; text-transform: uppercase; color: #555; letter-spacing: 1px;">
                                <?php echo esc_html__('Property Type', 'rpa-listings') ?>
                                <span style="display: block; font-size: 16px; font-weight: 400; color: #222; text-transform: none; letter-spacing: 0; margin-top: 5px;">
                                    <?php echo esc_html(implode(', ', $property_types)) ?>
                                </span>
                            </p>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if (!empty($status)) : ?>
                    <li class="info-item" style="display: flex; align-items: center; gap: 15px;">
                        <div class="icon" style="color: #cda252; font-size: 40px; line-height: 1;">
                            <i class="flaticon-009-crane-truck"></i>
                        </div>
                        <div class="content">
                            <p style="margin: 0; font-size: 13px; font-weight: 500; text-transform: uppercase; color: #555; letter-spacing: 1px;">
                                <?php echo esc_html__('Status', 'rpa-listings') ?>
                                <span style="display: block; font-size: 16px; font-weight: 400; color: #222; text-transform: none; letter-spacing: 0; margin-top: 5px;">
                                    <?php echo esc_html($status) ?>
                                </span>
                            </p>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if (!empty($addresses)) : ?>
                    <li class="info-item" style="display: flex; align-items: center; gap: 15px;">
                        <div class="icon" style="color: #cda252; font-size: 40px; line-height: 1;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="content">
                            <p style="margin: 0; font-size: 13px; font-weight: 500; text-transform: uppercase; color: #555; letter-spacing: 1px;">
                                <?php echo esc_html__('Property Address', 'rpa-listings') ?>
                                <span style="display: block; font-size: 16px; font-weight: 400; color: #222; text-transform: none; letter-spacing: 0; margin-top: 5px;">
                                    <?php echo implode('<br><br>', array_map('nl2br', array_map('esc_html', $addresses))); ?>
                                </span>
                            </p>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>

            <?php if (!empty($amenities) && is_array($amenities)) : ?>
                <div class="rpa-amenities-wrap" style="border-top: 1px solid #e1e1e1; margin-top: 20px; padding-top: 20px; display: flex; align-items: flex-start; gap: 30px; flex-wrap: wrap;">
                    <div class="info-item" style="flex-shrink: 0; display: flex; align-items: center; gap: 15px; width: 200px;">
                        <div class="icon" style="color: #cda252; font-size: 30px; line-height: 1;">
                            <i class="flaticon-001-sofa" style="font-weight: normal;"></i>
                        </div>
                        <div class="content">
                            <p style="margin:0; text-transform: uppercase; font-size: 12px; font-weight: 500; letter-spacing: 1px; color: #555;">
                                <?php echo esc_html__('Amenities', 'rpa-listings'); ?>
                                <span style="color:#222; font-weight: 400; font-size: 14px; text-transform: none; letter-spacing: 0; display: block; margin-top: 3px;">
                                    <?php echo count($amenities); ?> <?php echo esc_html__('available', 'rpa-listings'); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <ul class="amenities-list" style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px 20px; width: 100%; flex: 1;">
                        <?php foreach ($amenities as $amenity) : ?>
                            <li style="display: flex; align-items: flex-start; gap: 8px; font-size: 14px; color: #111; font-weight: 500; line-height: 1.4;">
                                <i class="flaticon-006-shield" style="color: #cda252; font-size: 13px; margin-top: 3px; font-weight: normal;"></i> <span style="flex: 1;"><?php echo esc_html($amenity); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
<?php
    }
}
