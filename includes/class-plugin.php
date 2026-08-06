<?php
/**
 * Core plugin class.
 *
 * @package Nductiv\StereoscopicImageViewer
 */

namespace Nductiv\StereoscopicImageViewer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Singleton that bootstraps all plugin components.
 */
class Plugin {

	/** @var Plugin|null */
	private static $instance = null;

	/**
	 * Returns the single instance, creating it on first call.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	/** Private constructor — use get_instance(). */
	private function __construct() {}

	/**
	 * Load dependencies and register hooks.
	 *
	 * @return void
	 */
	private function init(): void {
		require_once STERIMVI_DIR . 'includes/class-settings.php';
		require_once STERIMVI_DIR . 'includes/class-assets.php';
		require_once STERIMVI_DIR . 'includes/class-block.php';
		require_once STERIMVI_DIR . 'includes/class-shortcode.php';

		$settings  = new Settings();
		$assets    = new Assets();
		$block     = new Block();
		$shortcode = new Shortcode();

		$settings->register_hooks();
		$assets->register_hooks();
		$block->register_hooks();
		$shortcode->register_hooks();
	}
}
