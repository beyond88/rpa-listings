<?php

namespace RPAListings;

use RPAListings\Admin\Admin;

final class Plugin
{
	private static ?self $instance = null;

	private function __construct()
	{
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
	}
}
