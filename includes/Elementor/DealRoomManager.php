<?php

namespace RPAListings\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class DealRoomManager extends Widget_Base
{
    public function get_name()
    {
        return 'rpa_deal_room_manager';
    }

    public function get_title()
    {
        return esc_html__('Deal Room Manager', 'rpa-listings');
    }

    public function get_icon()
    {
        return 'eicon-folder-o';
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
                'label' => esc_html__('Access Button Text', 'rpa-listings'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Deal Room Access', 'rpa-listings'),
                'description' => esc_html__('Text for the button shown on hover before gaining access.', 'rpa-listings'),
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
            $this->render_document_manager($project_id, false, $settings['button_text']);
        } else {
            $this->render_document_manager($project_id, true, $settings['button_text']);
            DealRoomModal::render_modal($project_id);
        }
    }

    private function enrich_documents_data($items)
    {
        foreach ($items as &$item) {
            if (isset($item['type']) && $item['type'] === 'folder' && isset($item['children'])) {
                $item['children'] = $this->enrich_documents_data($item['children']);
            } elseif (isset($item['type']) && $item['type'] === 'file' && isset($item['attachment_id'])) {
                $att_id = intval($item['attachment_id']);
                if ($att_id > 0) {
                    if (empty($item['size'])) {
                        $file_path = get_attached_file($att_id);
                        if ($file_path && file_exists($file_path)) {
                            $item['size'] = filesize($file_path);
                        }
                    }
                    if (empty($item['date'])) {
                        $item['date'] = get_the_date('M j, Y', $att_id);
                    }
                }
            }
        }
        return $items;
    }

    private function render_document_manager($project_id, $is_blurred, $button_text)
    {
        if ($is_blurred) {
            $mock_docs = [
                ['id' => 'mock1', 'name' => 'Confidential_Offering_Memorandum.pdf', 'type' => 'file', 'size' => '5.2 MB', 'date' => 'Recent'],
                ['id' => 'mock2', 'name' => 'Financial_Statements.xlsx', 'type' => 'file', 'size' => '1.8 MB', 'date' => 'Recent'],
                ['id' => 'mock3', 'name' => 'Property_Photos', 'type' => 'folder', 'children' => []],
                ['id' => 'mock4', 'name' => 'Lease_Agreements.zip', 'type' => 'file', 'size' => '12.4 MB', 'date' => 'Recent'],
                ['id' => 'mock5', 'name' => 'Market_Analysis.pdf', 'type' => 'file', 'size' => '3.1 MB', 'date' => 'Recent'],
                ['id' => 'mock6', 'name' => 'Due_Diligence', 'type' => 'folder', 'children' => []],
            ];
            $documents_json = wp_json_encode($mock_docs);
        } else {
            $documents_json = get_post_meta($project_id, 'rpa_project_documents', true);
            if (!$documents_json || !is_string($documents_json)) {
                $documents_json = '[]';
            }

            // Parse and enrich documents with file size and date if missing
            $docs = json_decode($documents_json, true);
            if (is_array($docs)) {
                $docs = $this->enrich_documents_data($docs);
                $documents_json = wp_json_encode($docs);
            }
        }

        $wrapper_class = 'rpa-frontend-doc-manager';
        if ($is_blurred) {
            $wrapper_class .= ' rpa-blurred-manager';
        }
?>
        <div class="<?php echo esc_attr($wrapper_class); ?>" data-project-id="<?php echo esc_attr($project_id); ?>" data-docs="<?php echo esc_attr($documents_json); ?>">
            <?php if ($is_blurred): ?>
                <div class="rpa-blur-overlay">
                    <button type="button" class="rpa-btn rpa-deal-room-btn rpa-overlay-btn" onclick="openDealRoomModal()"><?php echo esc_html($button_text); ?></button>
                </div>
            <?php endif; ?>

            <div class="rpa-doc-content-wrapper">
                <div class="rpa-doc-toolbar rpa-doc-toolbar-top">
                    <div class="rpa-doc-toolbar-left">
                        <label class="rpa-doc-select-all-label">
                            <input type="checkbox" id="rpa-frontend-select-all" <?php echo $is_blurred ? 'disabled' : ''; ?>> Select All
                        </label>
                        <div class="rpa-doc-sort-group">
                            <span class="rpa-doc-sort-label">Sort By</span>
                            <select id="rpa-frontend-sort" class="rpa-doc-sort-select" <?php echo $is_blurred ? 'disabled' : ''; ?>>
                                <option value="name">Name</option>
                                <option value="date">Date</option>
                            </select>
                        </div>
                    </div>
                    <div class="rpa-doc-toolbar-right">
                        <button class="rpa-view-btn rpa-view-list" title="List View" <?php echo $is_blurred ? 'disabled' : ''; ?>>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                        </button>
                        <button class="rpa-view-btn rpa-view-grid active" title="Grid View" <?php echo $is_blurred ? 'disabled' : ''; ?>>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="rpa-doc-grid-container">
                    <div class="rpa-doc-breadcrumbs" id="rpa-frontend-breadcrumbs" style="margin-bottom: 20px; font-weight: 500; color: #374151;"></div>
                    <div class="rpa-doc-grid" id="rpa-frontend-grid"></div>
                </div>

                <div class="rpa-doc-toolbar rpa-doc-toolbar-bottom">
                    <div class="rpa-doc-selected-info">
                        <span class="rpa-doc-selected-badge" id="rpa-frontend-selected-count">0</span>
                        <span class="rpa-doc-selected-text">Items Selected</span>
                    </div>
                    <div class="rpa-doc-actions">
                        <button type="button" class="rpa-btn rpa-btn-download" id="rpa-frontend-download" disabled>Download</button>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}
