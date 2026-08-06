/* global jQuery */
/**
 * Admin settings page: show/hide the CDN URL field
 * based on the selected load method radio button.
 */
jQuery( function ( $ ) {
	'use strict';

	var $cdnUrlRow = $( '#sterimvi_cdn_url' ).closest( 'tr' );

	function toggleCdnUrlRow() {
		var method = $( 'input[name="sterimvi_settings[load_method]"]:checked' ).val();
		if ( method === 'self' ) {
			$cdnUrlRow.show();
		} else {
			$cdnUrlRow.hide();
		}
	}

	// Run on page load and on change.
	toggleCdnUrlRow();
	$( 'input[name="sterimvi_settings[load_method]"]' ).on( 'change', toggleCdnUrlRow );
} );
