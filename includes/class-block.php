<?php
/**
 * Gutenberg block registration and shared PHP renderer.
 *
 * @package WPStereoscopicViewer
 */

namespace WPStereoscopicViewer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block and provides the render_viewer() method
 * shared by both the block render callback and the shortcode.
 */
class Block {

	/** stereo-img type values for each sourceFormat + swap combination. */
	const TYPE_MAP = array(
		'left-right'  => array( false => 'left-right', true => 'right-left' ),
		'top-bottom'  => array( false => 'top-bottom',  true => 'bottom-top' ),
		'anaglyph-rc' => array( false => 'anaglyph',    true => 'anaglyph' ),
		'pair'        => array( false => 'pair',         true => 'pair' ),
	);

	/** stereo-img flat attribute values for displayMode. */
	const FLAT_MAP = array(
		'left'        => 'left',
		'right'       => 'right',
		'wiggle'      => 'wiggle',
		'anaglyph-rc' => 'anaglyph',
	);

	/**
	 * displayMode values that always use the Canvas renderer.
	 * side-by-side and top-bottom are included because stereo-img has no
	 * flat 2D composite mode for those — it only renders them in VR/3D mode.
	 */
	const CANVAS_MODES = array( 'anaglyph-rb', 'interlaced-row', 'interlaced-col', 'side-by-side', 'top-bottom' );

	/** sourceFormat values that always use the Canvas renderer. */
	const CANVAS_SOURCE_FORMATS = array( 'anaglyph-rb', 'interlaced-row', 'interlaced-col' );

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register scripts/styles and the block type from block.json.
	 *
	 * Scripts are registered here (on init) so the handles referenced in
	 * block.json already exist when WordPress processes the file.
	 *
	 * @return void
	 */
	public function register_block(): void {
		wp_register_script(
			'wpsv-renderer',
			WPSV_URL . 'assets/js/renderer.js',
			array(),
			WPSV_VERSION,
			true
		);

		wp_register_script(
			'wpsv-block-editor',
			WPSV_URL . 'assets/js/block-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wpsv-renderer' ),
			WPSV_VERSION,
			true
		);

		// Expose site-wide defaults so the editor can pre-fill new blocks correctly.
		wp_localize_script( 'wpsv-block-editor', 'wpsvDefaults', Settings::get_defaults() );

		wp_register_style(
			'wpsv-editor',
			WPSV_URL . 'assets/css/editor.css',
			array( 'wp-edit-blocks' ),
			WPSV_VERSION
		);

		wp_register_style(
			'wpsv-viewer',
			WPSV_URL . 'assets/css/viewer.css',
			array(),
			WPSV_VERSION
		);

		register_block_type( WPSV_DIR );
	}

	/**
	 * Produce the HTML for a stereoscopic viewer.
	 *
	 * Called by render.php (block render callback) and by Shortcode::render().
	 *
	 * @param array $atts Attribute key => value pairs (camelCase block attribute names).
	 * @return string HTML output.
	 */
	public static function render_viewer( array $atts ): string {
		$defaults = Settings::get_defaults();

		$atts = array_merge(
			array(
				'src'                  => '',
				'srcRight'             => '',
				'sourceFormat'         => $defaults['source_format'],
				'sourceSqueezeEnabled' => $defaults['source_squeeze_enabled'],
				'displayMode'          => $defaults['display_mode'],
				'displaySqueezeEnabled' => $defaults['display_squeeze_enabled'],
				'swapSources'          => false,
				'width'                => $defaults['width'],
				'borderEnabled'        => $defaults['border_enabled'],
				'borderWidth'          => $defaults['border_width'],
				'borderColor'          => $defaults['border_color'],
				'shadowEnabled'        => $defaults['shadow_enabled'],
				'shadowOffsetX'        => $defaults['shadow_offset_x'],
				'shadowOffsetY'        => $defaults['shadow_offset_y'],
				'shadowBlur'           => $defaults['shadow_blur'],
				'shadowSpread'         => $defaults['shadow_spread'],
				'shadowColor'          => $defaults['shadow_color'],
				'controlslist'         => $defaults['controlslist'],
			),
			$atts
		);

		// Sanitize.
		$src                   = esc_url( $atts['src'] );
		$src_right             = esc_url( $atts['srcRight'] );
		$source_format         = in_array( $atts['sourceFormat'], Settings::SOURCE_FORMATS, true ) ? $atts['sourceFormat'] : 'left-right';
		$source_squeeze        = (bool) $atts['sourceSqueezeEnabled'];
		$display_mode          = in_array( $atts['displayMode'], Settings::DISPLAY_MODES, true ) ? $atts['displayMode'] : 'anaglyph-rc';
		$display_squeeze       = (bool) $atts['displaySqueezeEnabled'];
		$swap                  = (bool) $atts['swapSources'];
		$width                 = preg_match( '/^\d+(\.\d+)?(px|%|vw)$/', $atts['width'] ) ? $atts['width'] : '100%';
		$border_enabled        = (bool) $atts['borderEnabled'];
		$border_width          = preg_match( '/^\d+(\.\d+)?px$/', $atts['borderWidth'] ) ? $atts['borderWidth'] : '1px';
		$border_color          = sanitize_hex_color( $atts['borderColor'] ) ?: '#000000';
		$shadow_enabled        = (bool) $atts['shadowEnabled'];
		$shadow_offset_x       = self::sanitize_css_length( $atts['shadowOffsetX'], '0px' );
		$shadow_offset_y       = self::sanitize_css_length( $atts['shadowOffsetY'], '4px' );
		$shadow_blur           = self::sanitize_css_length( $atts['shadowBlur'], '12px' );
		$shadow_spread         = self::sanitize_css_length( $atts['shadowSpread'], '0px' );
		$shadow_color          = Settings::sanitize_css_color( $atts['shadowColor'], $defaults['shadow_color'] );

		// Controlslist: keep only recognised tokens; order is preserved.
		$valid_controls = array( 'wiggle', 'left', 'right', 'anaglyph' );
		$controlslist   = implode( ' ', array_values( array_filter(
			explode( ' ', (string) $atts['controlslist'] ),
			function ( $t ) use ( $valid_controls ) { return in_array( $t, $valid_controls, true ); }
		) ) );

		if ( empty( $src ) ) {
			return '';
		}

		// Build wrapper inline CSS.
		$style_parts = array( 'width:' . $width );
		if ( $border_enabled ) {
			$style_parts[] = 'border:' . $border_width . ' solid ' . $border_color;
		}
		if ( $shadow_enabled ) {
			$style_parts[] = 'box-shadow:' . $shadow_offset_x . ' ' . $shadow_offset_y . ' ' . $shadow_blur . ' ' . $shadow_spread . ' ' . $shadow_color;
		}
		$wrapper_style = implode( ';', $style_parts );

		// Determine render path.
		$use_canvas = in_array( $display_mode, self::CANVAS_MODES, true )
			|| in_array( $source_format, self::CANVAS_SOURCE_FORMATS, true )
			|| $source_squeeze; // Squeezed source always needs Canvas to unsqueeze.

		if ( $use_canvas ) {
			$inner = self::render_canvas( $src, $src_right, $source_format, $source_squeeze, $display_mode, $display_squeeze, $swap );
		} else {
			$inner = self::render_stereo_img( $src, $src_right, $source_format, $display_mode, $swap, $controlslist );
		}

		return '<div class="wpsv-wrapper" style="' . esc_attr( $wrapper_style ) . '">' . $inner . '</div>';
	}

	/**
	 * Validate a CSS length value. Returns $fallback if invalid.
	 *
	 * @param mixed  $value
	 * @param string $fallback
	 * @return string
	 */
	private static function sanitize_css_length( $value, string $fallback ): string {
		$value = (string) $value;
		// Allow 0, or a number followed by a unit, optionally negative for offsets.
		if ( preg_match( '/^-?\d+(\.\d+)?(px|em|rem|%)$/', $value ) || '0' === $value ) {
			return sanitize_text_field( $value );
		}
		return $fallback;
	}

	/**
	 * Build a <stereo-img> element.
	 *
	 * @param string $src           Primary image URL.
	 * @param string $src_right     Right-eye image URL (pair mode).
	 * @param string $source_format Source format identifier.
	 * @param string $display_mode  Display mode identifier.
	 * @param bool   $swap          Whether to swap left/right.
	 * @param string $controlslist  Space-separated list of control tokens to show.
	 * @return string
	 */
	private static function render_stereo_img( string $src, string $src_right, string $source_format, string $display_mode, bool $swap, string $controlslist = '' ): string {
		$type = '';
		if ( isset( self::TYPE_MAP[ $source_format ] ) ) {
			$type = self::TYPE_MAP[ $source_format ][ $swap ];
		}

		$flat = self::FLAT_MAP[ $display_mode ] ?? '';

		$attrs = array( 'src="' . esc_attr( $src ) . '"' );

		if ( $type ) {
			$attrs[] = 'type="' . esc_attr( $type ) . '"';
		}
		if ( $flat ) {
			$attrs[] = 'flat="' . esc_attr( $flat ) . '"';
		}

		if ( 'pair' === $source_format && $src_right ) {
			if ( $swap ) {
				$attrs[0] = 'src="' . esc_attr( $src_right ) . '"';
				$attrs[]  = 'src-right="' . esc_attr( $src ) . '"';
			} else {
				$attrs[] = 'src-right="' . esc_attr( $src_right ) . '"';
			}
		}

		// Empty string means "no controls" — use the sentinel value "none".
		$attrs[] = 'controlslist="' . esc_attr( $controlslist ?: 'none' ) . '"';

		return '<stereo-img ' . implode( ' ', $attrs ) . '></stereo-img>';
	}

	/**
	 * Build a <canvas> element for custom Canvas 2D rendering.
	 *
	 * @param string $src            Primary image URL.
	 * @param string $src_right      Right-eye image URL (pair mode).
	 * @param string $source_format  Source format identifier.
	 * @param bool   $source_squeeze Source is anamorphic/squeezed.
	 * @param string $display_mode   Display mode identifier.
	 * @param bool   $display_squeeze Output should be anamorphic/squeezed.
	 * @param bool   $swap           Whether to swap left/right.
	 * @return string
	 */
	private static function render_canvas( string $src, string $src_right, string $source_format, bool $source_squeeze, string $display_mode, bool $display_squeeze, bool $swap ): string {
		$data_attrs = array(
			'data-src="' . esc_attr( $src ) . '"',
			'data-source-format="' . esc_attr( $source_format ) . '"',
			'data-source-squeeze="' . ( $source_squeeze ? '1' : '0' ) . '"',
			'data-display-mode="' . esc_attr( $display_mode ) . '"',
			'data-display-squeeze="' . ( $display_squeeze ? '1' : '0' ) . '"',
			'data-swap="' . ( $swap ? '1' : '0' ) . '"',
		);

		if ( $src_right ) {
			$data_attrs[] = 'data-src-right="' . esc_attr( $src_right ) . '"';
		}

		return '<canvas class="wpsv-canvas" ' . implode( ' ', $data_attrs ) . '></canvas>';
	}
}
