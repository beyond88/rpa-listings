<?php

namespace RPAListings;

use RPAListings\Admin\Admin;

final class Plugin
{
	private static ?self $instance = null;

	private function __construct() {}

	public static function activate(): void
	{
		// 1. Flush WordPress Rewrite Rules & Object Cache
		if (function_exists('flush_rewrite_rules')) {
			flush_rewrite_rules();
		}
		
		if (function_exists('wp_cache_flush')) {
			@wp_cache_flush(); // Use @ to suppress potential errors from broken object cache drop-ins
		}

		// 2. Clear PHP OPcache (Server-level script cache)
		if (function_exists('opcache_reset')) {
			@opcache_reset();
		}

		// 3. Clear Elementor CSS Cache
		if (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance->files_manager)) {
			try {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			} catch (\Exception $e) {
				// Silently fail if Elementor is not fully initialized
			}
		}

		// 4. Clear Popular Caching Plugins (wrapped in function_exists/class_exists already)
		
		// WP Rocket
		if (function_exists('rocket_clean_domain')) {
			rocket_clean_domain();
		}
		// W3 Total Cache
		if (function_exists('w3tc_flush_all')) {
			w3tc_flush_all();
		}
		// WP Fastest Cache
		if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
			$GLOBALS['wp_fastest_cache']->deleteCache(true);
		}
		// Autoptimize
		if (class_exists('\autoptimizeCache') && method_exists('\autoptimizeCache', 'clearall')) {
			\autoptimizeCache::clearall();
		}
		// LiteSpeed Cache
		if (class_exists('\LiteSpeed\Purge') && method_exists('\LiteSpeed\Purge', 'purge_all')) {
			\LiteSpeed\Purge::purge_all();
		}
		// SG Optimizer (SiteGround)
		if (function_exists('sg_cachepress_purge_cache')) {
			sg_cachepress_purge_cache();
		}
	}

	public static function instance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
			self::$instance->register();
		}

		return self::$instance;
	}

	private function register(): void
	{
		(new Admin())->register();
		(new \RPAListings\Frontend\DealHandler())->register();

		if (did_action('elementor/loaded')) {
			(new \RPAListings\Elementor\ElementorInit())->register();
		} else {
			add_action('elementor/loaded', function () {
				(new \RPAListings\Elementor\ElementorInit())->register();
			});
		}
	}
}
