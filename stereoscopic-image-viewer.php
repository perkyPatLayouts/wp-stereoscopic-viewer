<?php
/**
 * Plugin Name:       Stereoscopic Image Viewer
 * Plugin URI:        https://apps.nductiv.com/wp-stereoscopic-viewer/
 * Description:       Display stereoscopic 3D images from the WordPress media library using a Gutenberg block and shortcode.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Nductiv
 * License:           GPLv2+
 * License URI:       https://opensource.org/license/gpl-2.0
 * Text Domain:       stereoscopic-image-viewer
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STERIMVI_VERSION', '1.0.0' );
define( 'STERIMVI_DIR', plugin_dir_path( __FILE__ ) );
define( 'STERIMVI_URL', plugin_dir_url( __FILE__ ) );

require_once STERIMVI_DIR . 'includes/class-plugin.php';

add_action( 'plugins_loaded', array( 'Nductiv\\StereoscopicImageViewer\\Plugin', 'get_instance' ) );
