<?php
/**
 * [sterimvi_image] shortcode.
 *
 * @package Nductiv\StereoscopicImageViewer
 */

namespace Nductiv\StereoscopicImageViewer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and handles the [sterimvi_image] shortcode.
 *
 * All rendering is delegated to Block::render_viewer() so that
 * the shortcode and Gutenberg block always produce identical HTML.
 */
class Shortcode {

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_shortcode( 'sterimvi_image', array( $this, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array|string $atts    Shortcode attributes (snake_case).
	 * @param string|null  $content Enclosed content (unused).
	 * @return string HTML output.
	 */
	public function render( $atts, ?string $content = null ): string {
		$defaults = Settings::get_defaults();

		$atts = shortcode_atts(
			array(
				'src'             => '',
				'src_right'       => '',
				'source_format'   => $defaults['source_format'],
				'source_squeeze'  => $defaults['source_squeeze_enabled'] ? '1' : '0',
				'display_mode'    => $defaults['display_mode'],
				'display_squeeze' => $defaults['display_squeeze_enabled'] ? '1' : '0',
				'swap'            => '0',
				'width'           => $defaults['width'],
				'border'          => $defaults['border_enabled'] ? '1' : '0',
				'border_width'    => $defaults['border_width'],
				'border_color'    => $defaults['border_color'],
				'shadow'          => $defaults['shadow_enabled'] ? '1' : '0',
				'shadow_offset_x' => $defaults['shadow_offset_x'],
				'shadow_offset_y' => $defaults['shadow_offset_y'],
				'shadow_blur'     => $defaults['shadow_blur'],
				'shadow_spread'   => $defaults['shadow_spread'],
				'shadow_color'    => $defaults['shadow_color'],
				'controlslist'    => $defaults['controlslist'],
			),
			$atts,
			'sterimvi_image'
		);

		// Map snake_case shortcode params → camelCase block attribute names.
		return Block::render_viewer(
			array(
				'src'                   => $atts['src'],
				'srcRight'              => $atts['src_right'],
				'sourceFormat'          => $atts['source_format'],
				'sourceSqueezeEnabled'  => '1' === $atts['source_squeeze'],
				'displayMode'           => $atts['display_mode'],
				'displaySqueezeEnabled' => '1' === $atts['display_squeeze'],
				'swapSources'           => '1' === $atts['swap'],
				'width'                 => $atts['width'],
				'borderEnabled'         => '1' === $atts['border'],
				'borderWidth'           => $atts['border_width'],
				'borderColor'           => $atts['border_color'],
				'shadowEnabled'         => '1' === $atts['shadow'],
				'shadowOffsetX'         => $atts['shadow_offset_x'],
				'shadowOffsetY'         => $atts['shadow_offset_y'],
				'shadowBlur'            => $atts['shadow_blur'],
				'shadowSpread'          => $atts['shadow_spread'],
				'shadowColor'           => $atts['shadow_color'],
				'controlslist'          => $atts['controlslist'],
			)
		);
	}
}
