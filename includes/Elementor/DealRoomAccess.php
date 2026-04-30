<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class DealRoomAccess extends Widget_Base
{
    public function get_name()
    {
        return 'rpa_deal_room_access';
    }

    public function get_title()
    {
        return esc_html__('Deal Room Button', 'rpa-listings');
    }

    public function get_icon()
    {
        return 'eicon-lock-user';
    }

    public function get_categories()
    {
        return ['tpcore'];
    }

    // Prevent Elementor element caching — content depends on per-user cookie state
    protected function is_dynamic_content(): bool
    {
        return true;
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Content', 'rpa-listings'),
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => esc_html__('Button Text', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Deal Room Access', 'rpa-listings'),
            ]
        );

        $this->end_controls_section();
    }


    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $project_id = get_the_ID();
        $has_access = \RPAListings\Frontend\DealHandler::has_access($project_id);

        if ($has_access) {
            echo '<div class="rpa-access-granted-msg">You already have access to the Deal Room.</div>';
        } else {
            $this->render_access_button_and_modal($settings['button_text'], $project_id);
        }
    }

    private function render_access_button_and_modal($button_text, $project_id)
    {
?>
        <button type="button" class="rpa-btn rpa-deal-room-btn" onclick="openDealRoomModal()"><?php echo esc_html($button_text); ?></button>
<?php
        DealRoomModal::render_modal($project_id);
    }
}
