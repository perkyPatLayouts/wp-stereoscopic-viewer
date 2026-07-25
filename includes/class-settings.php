<?php
/**
 * Settings page and Options API integration.
 *
 * @package WPStereoscopicViewer
 */

namespace WPStereoscopicViewer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the settings page and manages plugin options.
 */
class Settings {

	const OPTION_KEY = 'wpsv_settings';
	const GROUP      = 'wpsv_settings_group';
	const PAGE_SLUG  = 'wp-stereoscopic-viewer';

	/** Valid enum values — used for sanitization. */
	const SOURCE_FORMATS = array( 'left-right', 'top-bottom', 'anaglyph-rc', 'anaglyph-rb', 'interlaced-row', 'interlaced-col', 'pair' );
	const DISPLAY_MODES  = array( 'side-by-side', 'top-bottom', 'anaglyph-rc', 'anaglyph-rb', 'interlaced-row', 'interlaced-col', 'left', 'right', 'wiggle' );
	const LOAD_METHODS   = array( 'local', 'cdn', 'self' );

	/** Regex used to validate CSS color values (hex, rgb/rgba, hsl/hsla, named). */
	const CSS_COLOR_PATTERN = '/^(#[0-9a-fA-F]{3,8}|rgba?\([\d.,\s%\/]+\)|hsla?\([\d.,\s%\/deg]+\)|[a-zA-Z]+)$/';

	const HARDCODED_DEFAULTS = array(
		'source_format'          => 'left-right',
		'source_squeeze_enabled' => false,
		'display_mode'           => 'anaglyph-rc',
		'display_squeeze_enabled' => false,
		'width'                  => '100%',
		'border_enabled'         => false,
		'border_width'           => '1px',
		'border_color'           => '#000000',
		'shadow_enabled'         => false,
		'shadow_offset_x'        => '0px',
		'shadow_offset_y'        => '4px',
		'shadow_blur'            => '12px',
		'shadow_spread'          => '0px',
		'shadow_color'           => 'rgba(0,0,0,0.25)',
		'controlslist'           => 'wiggle left right anaglyph',
		'load_method'            => 'local',
		'cdn_url'                => 'https://stereo-img.steren.fr/stereo-img.js',
	);

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
	}

	/**
	 * Add top-level admin menu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		add_menu_page(
			__( 'Stereoscopic Viewer Settings', 'wp-stereoscopic-viewer' ),
			__( 'Stereoscopic', 'wp-stereoscopic-viewer' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' ),
			'dashicons-format-image',
			80
		);
	}

	/**
	 * Register setting, sections, and fields.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			self::GROUP,
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::HARDCODED_DEFAULTS,
			)
		);

		add_settings_section(
			'wpsv_defaults_section',
			__( 'Default Display Settings', 'wp-stereoscopic-viewer' ),
			'__return_false',
			self::PAGE_SLUG
		);

		add_settings_section(
			'wpsv_library_section',
			__( 'Library Settings', 'wp-stereoscopic-viewer' ),
			'__return_false',
			self::PAGE_SLUG
		);

		$this->add_fields();
	}

	/**
	 * Register all settings fields.
	 *
	 * @return void
	 */
	private function add_fields(): void {
		$source_format_options = array(
			'left-right'    => __( 'Side-by-side (left-right)', 'wp-stereoscopic-viewer' ),
			'top-bottom'    => __( 'Top-bottom', 'wp-stereoscopic-viewer' ),
			'anaglyph-rc'   => __( 'Anaglyph (red-cyan)', 'wp-stereoscopic-viewer' ),
			'anaglyph-rb'   => __( 'Anaglyph (red-blue)', 'wp-stereoscopic-viewer' ),
			'interlaced-row' => __( 'Interlaced (row)', 'wp-stereoscopic-viewer' ),
			'interlaced-col' => __( 'Interlaced (column)', 'wp-stereoscopic-viewer' ),
			'pair'           => __( 'Left/right image pair', 'wp-stereoscopic-viewer' ),
		);

		$display_mode_options = array(
			'anaglyph-rc'    => __( 'Anaglyph (red-cyan)', 'wp-stereoscopic-viewer' ),
			'anaglyph-rb'    => __( 'Anaglyph (red-blue)', 'wp-stereoscopic-viewer' ),
			'wiggle'         => __( 'Wiggle', 'wp-stereoscopic-viewer' ),
			'left'           => __( 'Left eye only', 'wp-stereoscopic-viewer' ),
			'right'          => __( 'Right eye only', 'wp-stereoscopic-viewer' ),
			'side-by-side'   => __( 'Side-by-side', 'wp-stereoscopic-viewer' ),
			'top-bottom'     => __( 'Top-bottom', 'wp-stereoscopic-viewer' ),
			'interlaced-row' => __( 'Interlaced (row)', 'wp-stereoscopic-viewer' ),
			'interlaced-col' => __( 'Interlaced (column)', 'wp-stereoscopic-viewer' ),
		);

		$fields = array(
			array(
				'id'      => 'source_format',
				'label'   => __( 'Default Source Format', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'select',
				'options' => $source_format_options,
			),
			array(
				'id'      => 'source_squeeze_enabled',
				'label'   => __( 'Source Is Anamorphic (Squeezed) by Default', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'checkbox',
				'desc'    => __( 'Enable for half-width SBS or half-height TB source images.', 'wp-stereoscopic-viewer' ),
			),
			array(
				'id'      => 'display_mode',
				'label'   => __( 'Default Display Mode', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'select',
				'options' => $display_mode_options,
			),
			array(
				'id'      => 'display_squeeze_enabled',
				'label'   => __( 'Anamorphic (Squeezed) Output by Default', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'checkbox',
				'desc'    => __( 'Only applies to side-by-side and top-bottom display modes.', 'wp-stereoscopic-viewer' ),
			),
			array(
				'id'      => 'controlslist',
				'label'   => __( 'Default Viewer Controls', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'checkboxlist',
				'desc'    => __( 'Mode-switching buttons shown in the stereo-img viewer. Only applies when stereo-img renders the output (not Canvas modes).', 'wp-stereoscopic-viewer' ),
				'options' => array(
					'wiggle'   => __( 'Wiggle', 'wp-stereoscopic-viewer' ),
					'left'     => __( 'Left eye only', 'wp-stereoscopic-viewer' ),
					'right'    => __( 'Right eye only', 'wp-stereoscopic-viewer' ),
					'anaglyph' => __( 'Anaglyph (red-cyan)', 'wp-stereoscopic-viewer' ),
				),
			),
			array(
				'id'      => 'width',
				'label'   => __( 'Default Width', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'text',
				'desc'    => __( 'e.g. 100%, 640px, 80vw', 'wp-stereoscopic-viewer' ),
			),
			array(
				'id'      => 'border_enabled',
				'label'   => __( 'Enable Border by Default', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'checkbox',
			),
			array(
				'id'      => 'border_width',
				'label'   => __( 'Default Border Width', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'text',
				'desc'    => __( 'e.g. 1px, 2px', 'wp-stereoscopic-viewer' ),
			),
			array(
				'id'      => 'border_color',
				'label'   => __( 'Default Border Color', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'color',
			),
			array(
				'id'      => 'shadow_enabled',
				'label'   => __( 'Enable Drop Shadow by Default', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'checkbox',
			),
			array(
				'id'      => 'shadow_offset_x',
				'label'   => __( 'Shadow Horizontal Offset', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'text',
				'desc'    => __( 'e.g. 0px, 4px, -4px', 'wp-stereoscopic-viewer' ),
			),
			array(
				'id'      => 'shadow_offset_y',
				'label'   => __( 'Shadow Vertical Offset', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'text',
				'desc'    => __( 'e.g. 4px', 'wp-stereoscopic-viewer' ),
			),
			array(
				'id'      => 'shadow_blur',
				'label'   => __( 'Shadow Blur Radius', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'text',
				'desc'    => __( 'e.g. 12px', 'wp-stereoscopic-viewer' ),
			),
			array(
				'id'      => 'shadow_spread',
				'label'   => __( 'Shadow Spread Radius', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'text',
				'desc'    => __( 'e.g. 0px', 'wp-stereoscopic-viewer' ),
			),
			array(
				'id'      => 'shadow_color',
				'label'   => __( 'Shadow Color', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_defaults_section',
				'type'    => 'text',
				'desc'    => __( 'CSS color, e.g. rgba(0,0,0,0.25) or #333333', 'wp-stereoscopic-viewer' ),
			),
			array(
				'id'      => 'load_method',
				'label'   => __( 'stereo-img Load Method', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_library_section',
				'type'    => 'radio',
				'options' => array(
					'local' => __( 'Bundled with plugin (recommended, no external requests)', 'wp-stereoscopic-viewer' ),
					'cdn'   => __( 'External CDN (stereo-img.steren.fr)', 'wp-stereoscopic-viewer' ),
					'self'  => __( 'Custom URL (provide below)', 'wp-stereoscopic-viewer' ),
				),
			),
			array(
				'id'      => 'cdn_url',
				'label'   => __( 'stereo-img URL', 'wp-stereoscopic-viewer' ),
				'section' => 'wpsv_library_section',
				'type'    => 'url',
				'desc'    => __( 'Only used when "Custom URL" is selected above. Must point to a complete stereo-img release (the script imports lib/, parsers/, and vendor/ files relative to its own path).', 'wp-stereoscopic-viewer' ),
			),
		);

		foreach ( $fields as $field ) {
			add_settings_field(
				'wpsv_' . $field['id'],
				$field['label'],
				array( $this, 'render_field' ),
				self::PAGE_SLUG,
				$field['section'],
				$field
			);
		}
	}

	/**
	 * Render a settings field.
	 *
	 * @param array $args Field definition array.
	 * @return void
	 */
	public function render_field( array $args ): void {
		$options = get_option( self::OPTION_KEY, array() );
		$id      = $args['id'];
		$value   = isset( $options[ $id ] ) ? $options[ $id ] : ( self::HARDCODED_DEFAULTS[ $id ] ?? '' );
		$name    = self::OPTION_KEY . '[' . $id . ']';

		switch ( $args['type'] ) {
			case 'select':
				echo '<select id="wpsv_' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
				foreach ( $args['options'] as $opt_val => $opt_label ) {
					echo '<option value="' . esc_attr( $opt_val ) . '"' . selected( $value, $opt_val, false ) . '>' . esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
				break;

			case 'text':
			case 'url':
				echo '<input type="text" id="wpsv_' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
				if ( ! empty( $args['desc'] ) ) {
					echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
				}
				break;

			case 'checkbox':
				echo '<input type="checkbox" id="wpsv_' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"' . checked( $value, true, false ) . '>';
				break;

			case 'color':
				echo '<input type="color" id="wpsv_' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
				break;

			case 'checkboxlist':
				$selected = is_string( $value ) ? array_filter( explode( ' ', $value ) ) : array();
				foreach ( $args['options'] as $opt_val => $opt_label ) {
					$is_checked = in_array( $opt_val, $selected, true );
					echo '<label><input type="checkbox" name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $opt_val ) . '"' . checked( $is_checked, true, false ) . '> ' . esc_html( $opt_label ) . '</label><br>';
				}
				if ( ! empty( $args['desc'] ) ) {
					echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
				}
				break;

			case 'radio':
				foreach ( $args['options'] as $opt_val => $opt_label ) {
					$extra = ( 'self' === $opt_val ) ? ' id="wpsv_load_method_self"' : '';
					echo '<label><input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $opt_val ) . '"' . checked( $value, $opt_val, false ) . $extra . '> ' . esc_html( $opt_label ) . '</label><br>';
				}
				break;
		}
	}

	/**
	 * Sanitize the incoming settings array.
	 *
	 * @param mixed $raw Raw POST data.
	 * @return array Sanitized settings.
	 */
	public function sanitize( $raw ): array {
		if ( ! is_array( $raw ) ) {
			return self::HARDCODED_DEFAULTS;
		}

		$clean = array();

		$clean['source_format']  = in_array( $raw['source_format'] ?? '', self::SOURCE_FORMATS, true )
			? $raw['source_format']
			: self::HARDCODED_DEFAULTS['source_format'];

		$clean['source_squeeze_enabled'] = ! empty( $raw['source_squeeze_enabled'] );

		$clean['display_mode'] = in_array( $raw['display_mode'] ?? '', self::DISPLAY_MODES, true )
			? $raw['display_mode']
			: self::HARDCODED_DEFAULTS['display_mode'];

		$clean['display_squeeze_enabled'] = ! empty( $raw['display_squeeze_enabled'] );

		$clean['width'] = preg_match( '/^\d+(\.\d+)?(px|%|vw)$/', $raw['width'] ?? '' )
			? sanitize_text_field( $raw['width'] )
			: self::HARDCODED_DEFAULTS['width'];

		$clean['border_enabled'] = ! empty( $raw['border_enabled'] );

		$clean['border_width'] = preg_match( '/^\d+(\.\d+)?px$/', $raw['border_width'] ?? '' )
			? sanitize_text_field( $raw['border_width'] )
			: self::HARDCODED_DEFAULTS['border_width'];

		$sanitized_color       = sanitize_hex_color( $raw['border_color'] ?? '' );
		$clean['border_color'] = $sanitized_color ?: self::HARDCODED_DEFAULTS['border_color'];

		$clean['shadow_enabled'] = ! empty( $raw['shadow_enabled'] );

		// Shadow length values — allow negative for offsets.
		$len_pattern = '/^-?\d+(\.\d+)?(px|em|rem|%)$/';
		$clean['shadow_offset_x'] = preg_match( $len_pattern, $raw['shadow_offset_x'] ?? '' )
			? sanitize_text_field( $raw['shadow_offset_x'] )
			: self::HARDCODED_DEFAULTS['shadow_offset_x'];

		$clean['shadow_offset_y'] = preg_match( $len_pattern, $raw['shadow_offset_y'] ?? '' )
			? sanitize_text_field( $raw['shadow_offset_y'] )
			: self::HARDCODED_DEFAULTS['shadow_offset_y'];

		$clean['shadow_blur'] = preg_match( '/^\d+(\.\d+)?(px|em|rem|%)$/', $raw['shadow_blur'] ?? '' )
			? sanitize_text_field( $raw['shadow_blur'] )
			: self::HARDCODED_DEFAULTS['shadow_blur'];

		$clean['shadow_spread'] = preg_match( $len_pattern, $raw['shadow_spread'] ?? '' )
			? sanitize_text_field( $raw['shadow_spread'] )
			: self::HARDCODED_DEFAULTS['shadow_spread'];

		$clean['shadow_color'] = self::sanitize_css_color( $raw['shadow_color'] ?? '', self::HARDCODED_DEFAULTS['shadow_color'] );

		// controlslist: submitted as an array of checkbox values; stored as space-separated string.
		$valid_controls   = array( 'wiggle', 'left', 'right', 'anaglyph' );
		$raw_controls     = is_array( $raw['controlslist'] ?? null ) ? $raw['controlslist'] : array();
		$clean_controls   = array_values( array_filter( $raw_controls, function ( $t ) use ( $valid_controls ) {
			return in_array( $t, $valid_controls, true );
		} ) );
		$clean['controlslist'] = implode( ' ', $clean_controls );

		$clean['load_method'] = in_array( $raw['load_method'] ?? '', self::LOAD_METHODS, true )
			? $raw['load_method']
			: self::HARDCODED_DEFAULTS['load_method'];

		$clean['cdn_url'] = esc_url_raw( $raw['cdn_url'] ?? self::HARDCODED_DEFAULTS['cdn_url'] );
		if ( empty( $clean['cdn_url'] ) ) {
			$clean['cdn_url'] = self::HARDCODED_DEFAULTS['cdn_url'];
		}

		return $clean;
	}

	/**
	 * Validate a CSS color string against CSS_COLOR_PATTERN.
	 * Prevents CSS injection through the inline style attribute when the
	 * value is concatenated into a box-shadow declaration.
	 *
	 * @param mixed  $value    Raw input.
	 * @param string $fallback Value to return when validation fails.
	 * @return string
	 */
	public static function sanitize_css_color( $value, string $fallback ): string {
		$value = sanitize_text_field( (string) $value );
		return preg_match( self::CSS_COLOR_PATTERN, $value ) ? $value : $fallback;
	}

	/**
	 * Returns merged saved settings with hardcoded fallbacks.
	 *
	 * @return array
	 */
	public static function get_defaults(): array {
		$saved = get_option( self::OPTION_KEY, array() );
		return array_merge( self::HARDCODED_DEFAULTS, is_array( $saved ) ? $saved : array() );
	}

	/**
	 * Render the settings page HTML.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		require_once WPSV_DIR . 'admin/settings-page.php';
	}
}
