<?php

/**
 * Plugin Name: RPA Listings
 * Description: RPA Listings plugin.
 * Version: 1.0.0
 * Author: Mohiuddin Abulkul Kader
 * Text Domain: rpa-listings
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RPA_LISTINGS_VERSION', '0.1.2');
define('RPA_LISTINGS_FILE', __FILE__);
define('RPA_LISTINGS_DIR', plugin_dir_path(__FILE__));
define('RPA_LISTINGS_URL', plugin_dir_url(__FILE__));

$autoload = RPA_LISTINGS_DIR . 'vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    require_once RPA_LISTINGS_DIR . 'includes/Plugin.php';
    require_once RPA_LISTINGS_DIR . 'includes/Admin/Admin.php';
    require_once RPA_LISTINGS_DIR . 'includes/Admin/ProjectMetabox.php';
}

add_action('plugins_loaded', static function () {
    if (!class_exists(\RPAListings\Plugin::class)) {
        return;
    }

    \RPAListings\Plugin::instance();
});
