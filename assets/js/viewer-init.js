/**
 * Front-end initializer.
 *
 * Finds all .wpsv-canvas elements and dispatches each to the appropriate
 * WPSVRenderer function based on data-display-mode.
 *
 * Depends on renderer.js (listed as a wp_enqueue_script dependency).
 */
document.addEventListener( 'DOMContentLoaded', function () {
	'use strict';

	if ( typeof window.WPSVRenderer === 'undefined' ) {
		return;
	}

	var canvases = document.querySelectorAll( '.wpsv-wrapper canvas.wpsv-canvas' );
	if ( ! canvases.length ) {
		return;
	}

	canvases.forEach( function ( canvas ) {
		var displayMode    = canvas.dataset.displayMode || '';
		var displaySqueeze = canvas.dataset.displaySqueeze === '1';

		WPSVRenderer.getSplitFromCanvas( canvas )
			.then( function ( pair ) {
				switch ( displayMode ) {
					case 'side-by-side':
						WPSVRenderer.renderSideBySide( pair.left, pair.right, canvas, displaySqueeze );
						break;
					case 'top-bottom':
						WPSVRenderer.renderTopBottom( pair.left, pair.right, canvas, displaySqueeze );
						break;
					case 'anaglyph-rb':
						WPSVRenderer.renderAnaglyphRB( pair.left, pair.right, canvas );
						break;
					case 'interlaced-row':
						WPSVRenderer.renderInterlacedRows( pair.left, pair.right, canvas );
						break;
					case 'interlaced-col':
						WPSVRenderer.renderInterlacedCols( pair.left, pair.right, canvas );
						break;
					default:
						// Fallback: show left eye.
						canvas.width  = pair.left.width;
						canvas.height = pair.left.height;
						canvas.getContext( '2d' ).drawImage( pair.left, 0, 0 );
				}
			} )
			.catch( function ( err ) {
				var isCorsError = err instanceof DOMException && err.name === 'SecurityError';
				var msg = isCorsError
					? 'Cannot read image pixels (CORS). The image must be served from the same domain.'
					: ( err.message || 'Unknown error' );
				WPSVRenderer.showCanvasError( canvas, msg );
			} );
	} );
} );
