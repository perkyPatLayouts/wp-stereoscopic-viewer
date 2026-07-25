<?php
/**
 * Plugin Name:       Stereoscopic Image Viewer
 * Plugin URI:        https://github.com/tonyasch/wp-stereoscopic-viewer
 * Description:       Display stereoscopic 3D images from the WordPress media library using a Gutenberg block and shortcode.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Tony Asch
 * Author URI:        https://tony.asch.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-stereoscopic-viewer
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPSV_VERSION', '1.0.0' );
define( 'WPSV_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPSV_URL', plugin_dir_url( __FILE__ ) );

require_once WPSV_DIR . 'includes/class-plugin.php';

add_action( 'plugins_loaded', array( 'WPStereoscopicViewer\\Plugin', 'get_instance' ) );
