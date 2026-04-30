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

    private function has_access($project_id)
    {
        $cookie_name = 'rpa_deal_access_' . $project_id;
        if (!isset($_COOKIE[$cookie_name])) {
            return false;
        }

        $token = sanitize_text_field($_COOKIE[$cookie_name]);

        $transient_key = 'rpa_magic_token_' . md5($token);
        $cached_project_id = get_transient($transient_key);

        if (false === $cached_project_id) {
            $args = [
                'post_type' => 'deal_entry',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => 'rpa_project_id',
                        'value' => $project_id,
                        'compare' => '='
                    ],
                    [
                        'key' => 'rpa_magic_token',
                        'value' => $token,
                        'compare' => '='
                    ]
                ]
            ];

            $query = new \WP_Query($args);
            if (empty($query->posts)) {
                return false;
            }
            set_transient($transient_key, $project_id, 30 * DAY_IN_SECONDS);
            return true;
        }

        return (string)$cached_project_id === (string)$project_id;
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $project_id = get_the_ID();
        $has_access = $this->has_access($project_id);

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
