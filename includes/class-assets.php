<?php
/**
 * Script and style enqueueing.
 *
 * @package Nductiv\StereoscopicImageViewer
 */

namespace Nductiv\StereoscopicImageViewer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all asset registration and enqueueing, including the
 * script_loader_tag filter needed to load ES modules.
 */
class Assets {

	/** Handles that need type="module" injected. Only the stereo-img library is an ES module. */
	private array $module_handles = array( 'sterimvi-stereo-img' );

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_module_type' ), 10, 2 );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets(): void {
		$defaults = Settings::get_defaults();

		$stereo_img_url = self::resolve_stereo_img_url( $defaults );
		$is_local       = ( 'local' === $defaults['load_method'] );

		wp_enqueue_script(
			'sterimvi-stereo-img',
			$stereo_img_url,
			array(),
			$is_local ? STERIMVI_VERSION : null,
			true
		);

		wp_enqueue_script(
			'sterimvi-renderer',
			STERIMVI_URL . 'assets/js/renderer.js',
			array(),
			STERIMVI_VERSION,
			true
		);

		wp_enqueue_script(
			'sterimvi-viewer',
			STERIMVI_URL . 'assets/js/viewer-init.js',
			array( 'sterimvi-renderer' ),
			STERIMVI_VERSION,
			true
		);

		// sterimvi-viewer style is registered in Block::register_block() — just enqueue it.
		wp_enqueue_style( 'sterimvi-viewer' );
	}

	/**
	 * Enqueue Gutenberg block editor assets.
	 *
	 * Scripts and styles are registered in Block::register_block() on init.
	 * block.json references the handles so WordPress auto-enqueues them in the
	 * block editor. We call wp_enqueue here as an explicit fallback.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		wp_enqueue_script( 'sterimvi-block-editor' ); // registered in class-block.php
		wp_enqueue_style( 'sterimvi-editor' );         // registered in class-block.php
	}

	/**
	 * Enqueue admin-only assets (settings page).
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook ): void {
		if ( 'toplevel_page_stereoscopic-image-viewer' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'sterimvi-admin',
			STERIMVI_URL . 'admin/admin.css',
			array(),
			STERIMVI_VERSION
		);

		wp_enqueue_script(
			'sterimvi-admin',
			STERIMVI_URL . 'assets/js/admin-settings.js',
			array( 'jquery' ),
			STERIMVI_VERSION,
			true
		);
	}

	/**
	 * Inject type="module" on scripts that need it.
	 *
	 * @param string $tag    The script HTML tag.
	 * @param string $handle The registered script handle.
	 * @return string
	 */
	public function add_module_type( string $tag, string $handle ): string {
		if ( ! in_array( $handle, $this->module_handles, true ) ) {
			return $tag;
		}
		// Only rewrite the leading <script> tag, once — a plain str_replace
		// would also touch any later <script substrings (e.g. nested comments).
		$modified = preg_replace( '/^<script(\s)/', '<script type="module"$1', $tag, 1 );
		return null === $modified ? $tag : $modified;
	}

	/**
	 * Resolve the stereo-img script URL based on the configured load method.
	 *
	 * @param array $defaults Merged settings array from Settings::get_defaults().
	 * @return string
	 */
	public static function resolve_stereo_img_url( array $defaults ): string {
		switch ( $defaults['load_method'] ?? 'local' ) {
			case 'cdn':
				return 'https://stereo-img.steren.fr/stereo-img.js';
			case 'self':
				return $defaults['cdn_url'];
			case 'local':
			default:
				return STERIMVI_URL . 'assets/vendor/stereo-img/stereo-img.js';
		}
	}
}
