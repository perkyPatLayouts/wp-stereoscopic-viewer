/**
 * SterimviRenderer — Canvas 2D renderer for stereoscopic display modes not
 * supported by the stereo-img web component.
 *
 * Covers: anaglyph red-blue, interlaced row/col, side-by-side composite,
 * top-bottom composite, and anamorphic squeeze/unsqueeze for all of the above.
 *
 * Exposed as window.SterimviRenderer (no module bundler required).
 */
window.SterimviRenderer = ( function () {
	'use strict';

	/**
	 * Load an image, optionally setting crossOrigin.
	 *
	 * @param {string}  url
	 * @param {boolean} crossOrigin
	 * @return {Promise<HTMLImageElement>}
	 */
	function loadImage( url, crossOrigin ) {
		return new Promise( function ( resolve, reject ) {
			var img = new Image();
			if ( crossOrigin ) {
				img.crossOrigin = 'anonymous';
			}
			img.onload  = function () { resolve( img ); };
			img.onerror = function () { reject( new Error( 'Failed to load image: ' + url ) ); };
			img.src = url;
		} );
	}

	/**
	 * Draw an image into an off-screen canvas at its natural size.
	 *
	 * @param {HTMLImageElement} img
	 * @return {HTMLCanvasElement}
	 */
	function imageToCanvas( img ) {
		var c = document.createElement( 'canvas' );
		c.width  = img.naturalWidth;
		c.height = img.naturalHeight;
		c.getContext( '2d' ).drawImage( img, 0, 0 );
		return c;
	}

	/**
	 * Split a side-by-side image into left and right canvases.
	 *
	 * @param {HTMLImageElement} img
	 * @param {boolean} swap     Swap which half is the left eye.
	 * @param {boolean} squeezed Source is anamorphic (half-width per eye).
	 *                           When true, each half is stretched to full width on output.
	 * @return {{ left: HTMLCanvasElement, right: HTMLCanvasElement }}
	 */
	function splitLeftRight( img, swap, squeezed ) {
		var srcHalfW = Math.floor( img.naturalWidth / 2 );
		var h        = img.naturalHeight;
		// When squeezed, unsqueeze by stretching each half to full source width.
		var outW = squeezed ? img.naturalWidth : srcHalfW;

		var cL = document.createElement( 'canvas' );
		var cR = document.createElement( 'canvas' );
		cL.width = cR.width = outW;
		cL.height = cR.height = h;

		cL.getContext( '2d' ).drawImage( img, 0,         0, srcHalfW, h, 0, 0, outW, h );
		cR.getContext( '2d' ).drawImage( img, srcHalfW,  0, srcHalfW, h, 0, 0, outW, h );

		return swap ? { left: cR, right: cL } : { left: cL, right: cR };
	}

	/**
	 * Split a top-bottom image into left (top) and right (bottom) canvases.
	 *
	 * @param {HTMLImageElement} img
	 * @param {boolean} swap     Swap which half is the left eye.
	 * @param {boolean} squeezed Source is anamorphic (half-height per eye).
	 *                           When true, each half is stretched to full height on output.
	 * @return {{ left: HTMLCanvasElement, right: HTMLCanvasElement }}
	 */
	function splitTopBottom( img, swap, squeezed ) {
		var w        = img.naturalWidth;
		var srcHalfH = Math.floor( img.naturalHeight / 2 );
		var outH     = squeezed ? img.naturalHeight : srcHalfH;

		var cT = document.createElement( 'canvas' );
		var cB = document.createElement( 'canvas' );
		cT.width = cB.width = w;
		cT.height = cB.height = outH;

		cT.getContext( '2d' ).drawImage( img, 0, 0,        w, srcHalfH, 0, 0, w, outH );
		cB.getContext( '2d' ).drawImage( img, 0, srcHalfH, w, srcHalfH, 0, 0, w, outH );

		return swap ? { left: cB, right: cT } : { left: cT, right: cB };
	}

	/**
	 * Split a pair of separate images into left/right canvases.
	 *
	 * @param {HTMLImageElement} imgLeft
	 * @param {HTMLImageElement} imgRight
	 * @param {boolean}          swap
	 * @return {{ left: HTMLCanvasElement, right: HTMLCanvasElement }}
	 */
	function splitPair( imgLeft, imgRight, swap ) {
		var cL = imageToCanvas( imgLeft );
		var cR = imageToCanvas( imgRight );
		return swap ? { left: cR, right: cL } : { left: cL, right: cR };
	}

	/**
	 * Derive left/right canvases from a canvas element's data-* attributes.
	 *
	 * @param {HTMLCanvasElement} canvas
	 * @return {Promise<{ left: HTMLCanvasElement, right: HTMLCanvasElement }>}
	 */
	function getSplitFromCanvas( canvas ) {
		var src          = canvas.dataset.src;
		var srcRight     = canvas.dataset.srcRight || '';
		var sourceFormat = canvas.dataset.sourceFormat || 'left-right';
		var swap         = canvas.dataset.swap === '1';
		var squeezed     = canvas.dataset.sourceSqueeze === '1';

		if ( 'pair' === sourceFormat && srcRight ) {
			return Promise.all( [
				loadImage( src, true ),
				loadImage( srcRight, true ),
			] ).then( function ( imgs ) {
				return splitPair( imgs[ 0 ], imgs[ 1 ], swap );
			} );
		}

		return loadImage( src, true ).then( function ( img ) {
			if ( 'top-bottom' === sourceFormat || 'bottom-top' === sourceFormat ) {
				return splitTopBottom( img, swap, squeezed );
			}
			// Default: treat as left-right SBS.
			return splitLeftRight( img, swap, squeezed );
		} );
	}

	// ─── Composite renderers ────────────────────────────────────────────────────

	/**
	 * Render left and right eyes side-by-side onto outputCanvas.
	 *
	 * @param {HTMLCanvasElement} leftCanvas
	 * @param {HTMLCanvasElement} rightCanvas
	 * @param {HTMLCanvasElement} outputCanvas
	 * @param {boolean}           squeezed Compress each eye to half-width in output.
	 * @return {void}
	 */
	function renderSideBySide( leftCanvas, rightCanvas, outputCanvas, squeezed ) {
		var eyeW   = leftCanvas.width;
		var h      = leftCanvas.height;
		var outEyeW = squeezed ? Math.floor( eyeW / 2 ) : eyeW;

		outputCanvas.width  = outEyeW * 2;
		outputCanvas.height = h;

		var ctx = outputCanvas.getContext( '2d' );
		ctx.drawImage( leftCanvas,  0, 0, eyeW, h, 0,        0, outEyeW, h );
		ctx.drawImage( rightCanvas, 0, 0, eyeW, h, outEyeW,  0, outEyeW, h );
	}

	/**
	 * Render left and right eyes top-over-bottom onto outputCanvas.
	 *
	 * @param {HTMLCanvasElement} leftCanvas
	 * @param {HTMLCanvasElement} rightCanvas
	 * @param {HTMLCanvasElement} outputCanvas
	 * @param {boolean}           squeezed Compress each eye to half-height in output.
	 * @return {void}
	 */
	function renderTopBottom( leftCanvas, rightCanvas, outputCanvas, squeezed ) {
		var w       = leftCanvas.width;
		var eyeH    = leftCanvas.height;
		var outEyeH = squeezed ? Math.floor( eyeH / 2 ) : eyeH;

		outputCanvas.width  = w;
		outputCanvas.height = outEyeH * 2;

		var ctx = outputCanvas.getContext( '2d' );
		ctx.drawImage( leftCanvas,  0, 0, w, eyeH, 0, 0,        w, outEyeH );
		ctx.drawImage( rightCanvas, 0, 0, w, eyeH, 0, outEyeH,  w, outEyeH );
	}

	// ─── Anaglyph ───────────────────────────────────────────────────────────────

	/**
	 * Render an anaglyph red-blue image onto outputCanvas.
	 * R channel from left eye; G+B channels from right eye.
	 *
	 * @param {HTMLCanvasElement} leftCanvas
	 * @param {HTMLCanvasElement} rightCanvas
	 * @param {HTMLCanvasElement} outputCanvas
	 * @return {void}
	 */
	function renderAnaglyphRB( leftCanvas, rightCanvas, outputCanvas ) {
		var w = leftCanvas.width;
		var h = leftCanvas.height;
		outputCanvas.width  = w;
		outputCanvas.height = h;

		var ctx       = outputCanvas.getContext( '2d' );
		var leftData  = leftCanvas.getContext( '2d', { willReadFrequently: true } ).getImageData( 0, 0, w, h ).data;
		var rightData = rightCanvas.getContext( '2d', { willReadFrequently: true } ).getImageData( 0, 0, w, h ).data;
		var out       = ctx.createImageData( w, h );
		var d         = out.data;

		for ( var i = 0; i < leftData.length; i += 4 ) {
			d[ i ]     = leftData[ i ];       // R — left eye
			d[ i + 1 ] = rightData[ i + 1 ]; // G — right eye
			d[ i + 2 ] = rightData[ i + 2 ]; // B — right eye
			d[ i + 3 ] = 255;
		}

		ctx.putImageData( out, 0, 0 );
	}

	// ─── Interlaced ─────────────────────────────────────────────────────────────

	/**
	 * Render a row-interlaced image. Even rows from left eye, odd rows from right.
	 *
	 * @param {HTMLCanvasElement} leftCanvas
	 * @param {HTMLCanvasElement} rightCanvas
	 * @param {HTMLCanvasElement} outputCanvas
	 * @return {void}
	 */
	function renderInterlacedRows( leftCanvas, rightCanvas, outputCanvas ) {
		var w = leftCanvas.width;
		var h = leftCanvas.height;
		outputCanvas.width  = w;
		outputCanvas.height = h;

		var ctx = outputCanvas.getContext( '2d' );
		for ( var row = 0; row < h; row++ ) {
			var src = ( row % 2 === 0 ) ? leftCanvas : rightCanvas;
			ctx.drawImage( src, 0, row, w, 1, 0, row, w, 1 );
		}
	}

	/**
	 * Render a column-interlaced image. Even columns from left eye, odd from right.
	 *
	 * @param {HTMLCanvasElement} leftCanvas
	 * @param {HTMLCanvasElement} rightCanvas
	 * @param {HTMLCanvasElement} outputCanvas
	 * @return {void}
	 */
	function renderInterlacedCols( leftCanvas, rightCanvas, outputCanvas ) {
		var w = leftCanvas.width;
		var h = leftCanvas.height;
		outputCanvas.width  = w;
		outputCanvas.height = h;

		var ctx = outputCanvas.getContext( '2d' );
		for ( var col = 0; col < w; col++ ) {
			var src = ( col % 2 === 0 ) ? leftCanvas : rightCanvas;
			ctx.drawImage( src, col, 0, 1, h, col, 0, 1, h );
		}
	}

	// ─── Error display ──────────────────────────────────────────────────────────

	/**
	 * Render a readable error message inside a canvas element.
	 *
	 * @param {HTMLCanvasElement} canvas
	 * @param {string}            message
	 * @return {void}
	 */
	function showCanvasError( canvas, message ) {
		canvas.width  = 480;
		canvas.height = 72;
		var ctx = canvas.getContext( '2d' );
		ctx.fillStyle = '#fff0f0';
		ctx.fillRect( 0, 0, canvas.width, canvas.height );
		ctx.strokeStyle = '#cc0000';
		ctx.lineWidth = 2;
		ctx.strokeRect( 1, 1, canvas.width - 2, canvas.height - 2 );
		ctx.fillStyle = '#cc0000';
		ctx.font = '13px monospace';
		ctx.fillText( 'Stereoscopic Viewer error:', 12, 24 );
		ctx.fillStyle = '#333';
		ctx.fillText( message, 12, 50 );
	}

	// Public API.
	return {
		loadImage:            loadImage,
		splitLeftRight:       splitLeftRight,
		splitTopBottom:       splitTopBottom,
		splitPair:            splitPair,
		getSplitFromCanvas:   getSplitFromCanvas,
		renderSideBySide:     renderSideBySide,
		renderTopBottom:      renderTopBottom,
		renderAnaglyphRB:     renderAnaglyphRB,
		renderInterlacedRows: renderInterlacedRows,
		renderInterlacedCols: renderInterlacedCols,
		showCanvasError:      showCanvasError,
	};
}() );
