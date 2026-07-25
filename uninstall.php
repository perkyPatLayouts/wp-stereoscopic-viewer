<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WPStereoscopicViewer
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	$site_ids = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		delete_option( 'wpsv_settings' );
		restore_current_blog();
	}
} else {
	delete_option( 'wpsv_settings' );
}
