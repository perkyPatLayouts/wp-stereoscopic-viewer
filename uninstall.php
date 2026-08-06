<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Nductiv\StereoscopicImageViewer
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	$sterimvi_site_ids = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $sterimvi_site_ids as $sterimvi_site_id ) {
		switch_to_blog( $sterimvi_site_id );
		delete_option( 'sterimvi_settings' );
		restore_current_blog();
	}
} else {
	delete_option( 'sterimvi_settings' );
}
