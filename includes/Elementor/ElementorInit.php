<?php

namespace RPAListings\Elementor;

class ElementorInit
{
    public function register(): void
    {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widgets($widgets_manager): void
    {
        require_once __DIR__ . '/SoldProjectGrid.php';
        require_once __DIR__ . '/DealRoomAccess.php';
        require_once __DIR__ . '/DealRoomManager.php';
        require_once __DIR__ . '/DealRoomModal.php';
        require_once __DIR__ . '/ProjectMetaExtra.php';

        $widgets_manager->register(new SoldProjectGrid());
        $widgets_manager->register(new DealRoomAccess());
        $widgets_manager->register(new DealRoomManager());
        $widgets_manager->register(new ProjectMetaExtra());
    }
}
