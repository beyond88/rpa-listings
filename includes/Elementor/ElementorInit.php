<?php

namespace RPAListings\Elementor;

class ElementorInit
{
    public function register(): void
    {
        add_action('elementor/widgets/register', [$this, 'register_widgets'], 99);
    }

    public function register_widgets($widgets_manager): void
    {
        require_once __DIR__ . '/SoldProjectGrid.php';
        require_once __DIR__ . '/RPAProjectGrid.php';
        require_once __DIR__ . '/DealRoomAccess.php';
        require_once __DIR__ . '/DealRoomManager.php';
        require_once __DIR__ . '/DealRoomModal.php';
        require_once __DIR__ . '/PropertyAmenities.php';
        require_once __DIR__ . '/ProjectTitle.php';
        require_once __DIR__ . '/ProjectMeta.php';
        require_once __DIR__ . '/ProjectTeaser.php';
        require_once __DIR__ . '/ProjectDescription.php';
        require_once __DIR__ . '/ProjectFeaturedImage.php';
        require_once __DIR__ . '/ProjectGallery.php';
        require_once __DIR__ . '/ProjectDirection.php';
        require_once __DIR__ . '/Features.php';
        require_once __DIR__ . '/RPAFeatures.php';
        require_once __DIR__ . '/PointsOfInterestMap.php';

        // Unregister core widgets and replace with RPA versions
        $widgets_manager->unregister('tp-project-title');
        $widgets_manager->unregister('tp-project-meta');
        $widgets_manager->unregister('features');
        $widgets_manager->unregister('tg-gallery');
        $widgets_manager->unregister('tg-direction');
        $widgets_manager->unregister('project-grid');

        $widgets_manager->register(new ProjectTitle());
        $widgets_manager->register(new ProjectMeta());
        $widgets_manager->register(new ProjectTeaser());
        $widgets_manager->register(new ProjectDescription());
        $widgets_manager->register(new ProjectFeaturedImage());
        $widgets_manager->register(new RPAFeatures());
        $widgets_manager->register(new SoldProjectGrid());
        $widgets_manager->register(new RPAProjectGrid());
        $widgets_manager->register(new DealRoomAccess());
        $widgets_manager->register(new DealRoomManager());
        $widgets_manager->register(new PropertyAmenities());
        $widgets_manager->register(new ProjectGallery());
        $widgets_manager->register(new ProjectDirection());
        $widgets_manager->register(new PointsOfInterestMap());
    }
}
