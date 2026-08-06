/* global wp */
/**
 * Stereoscopic Image Viewer — Gutenberg block editor script.
 * Plain JS IIFE using wp.* globals. No JSX, no build step required.
 */
( function () {
	'use strict';

	// Guard: all required globals must exist before we register the block.
	if ( ! window.wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.i18n ) {
		if ( window.console ) {
			console.error( 'Stereoscopic Viewer: required wp.* globals not found. Block not registered.' );
		}
		return;
	}

	var el               = wp.element.createElement;
	var Fragment         = wp.element.Fragment;
	var __               = wp.i18n.__;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var MediaUpload       = wp.blockEditor.MediaUpload;
	var MediaUploadCheck  = wp.blockEditor.MediaUploadCheck;
	var useBlockProps     = wp.blockEditor.useBlockProps;
	var PanelBody         = wp.components.PanelBody;
	var SelectControl     = wp.components.SelectControl;
	var ToggleControl     = wp.components.ToggleControl;
	var TextControl       = wp.components.TextControl;
	var Button            = wp.components.Button;
	var useRef            = wp.element.useRef;
	var useEffect         = wp.element.useEffect;

	var SOURCE_FORMAT_OPTIONS = [
		{ label: __( 'Side-by-side (left-right)', 'stereoscopic-image-viewer' ), value: 'left-right' },
		{ label: __( 'Top-bottom', 'stereoscopic-image-viewer' ),               value: 'top-bottom' },
		{ label: __( 'Anaglyph (red-cyan)', 'stereoscopic-image-viewer' ),      value: 'anaglyph-rc' },
		{ label: __( 'Anaglyph (red-blue)', 'stereoscopic-image-viewer' ),      value: 'anaglyph-rb' },
		{ label: __( 'Interlaced (row)', 'stereoscopic-image-viewer' ),         value: 'interlaced-row' },
		{ label: __( 'Interlaced (column)', 'stereoscopic-image-viewer' ),      value: 'interlaced-col' },
		{ label: __( 'Left/right image pair', 'stereoscopic-image-viewer' ),    value: 'pair' },
	];

	var DISPLAY_MODE_OPTIONS = [
		{ label: __( 'Anaglyph (red-cyan)', 'stereoscopic-image-viewer' ),   value: 'anaglyph-rc' },
		{ label: __( 'Anaglyph (red-blue)', 'stereoscopic-image-viewer' ),   value: 'anaglyph-rb' },
		{ label: __( 'Wiggle', 'stereoscopic-image-viewer' ),                value: 'wiggle' },
		{ label: __( 'Left eye only', 'stereoscopic-image-viewer' ),         value: 'left' },
		{ label: __( 'Right eye only', 'stereoscopic-image-viewer' ),        value: 'right' },
		{ label: __( 'Side-by-side', 'stereoscopic-image-viewer' ),          value: 'side-by-side' },
		{ label: __( 'Top-bottom', 'stereoscopic-image-viewer' ),            value: 'top-bottom' },
		{ label: __( 'Interlaced (row)', 'stereoscopic-image-viewer' ),      value: 'interlaced-row' },
		{ label: __( 'Interlaced (column)', 'stereoscopic-image-viewer' ),   value: 'interlaced-col' },
	];

	// Only SBS/TB display modes support the squeeze option.
	var SQUEEZE_DISPLAY_MODES = [ 'side-by-side', 'top-bottom' ];
	// Only SBS/TB source formats support the squeeze option.
	var SQUEEZE_SOURCE_FORMATS = [ 'left-right', 'top-bottom' ];

	// Display modes and source formats that route to the Canvas renderer (mirrors class-block.php constants).
	var CANVAS_DISPLAY_MODES     = [ 'anaglyph-rb', 'interlaced-row', 'interlaced-col', 'side-by-side', 'top-bottom' ];
	var CANVAS_SOURCE_FORMAT_LIST = [ 'anaglyph-rb', 'interlaced-row', 'interlaced-col' ];

	// Valid controlslist tokens (mirrors class-block.php).
	var CONTROL_TOKENS = [
		{ value: 'wiggle',   label: __( 'Wiggle',            'stereoscopic-image-viewer' ) },
		{ value: 'left',     label: __( 'Left eye only',     'stereoscopic-image-viewer' ) },
		{ value: 'right',    label: __( 'Right eye only',    'stereoscopic-image-viewer' ) },
		{ value: 'anaglyph', label: __( 'Anaglyph (red-cyan)', 'stereoscopic-image-viewer' ) },
	];

	function hasControl( controlslist, token ) {
		return ( ' ' + ( controlslist || '' ) + ' ' ).indexOf( ' ' + token + ' ' ) !== -1;
	}

	function toggleControl( controlslist, token, enabled ) {
		var parts = ( controlslist || '' ).split( ' ' ).filter( function ( t ) { return t.length > 0; } );
		if ( enabled ) {
			if ( parts.indexOf( token ) === -1 ) { parts.push( token ); }
		} else {
			parts = parts.filter( function ( t ) { return t !== token; } );
		}
		return parts.join( ' ' );
	}

	// PHP site-wide defaults, injected via wp_localize_script.
	var sterimviD = window.sterimviDefaults || {};
	function siteDefault( key, fallback ) {
		return sterimviD[ key ] !== undefined ? sterimviD[ key ] : fallback;
	}

	try {
		registerBlockType( 'stereoscopic-image-viewer/stereo-img', {

			// Full attribute schema — must match block.json exactly so Gutenberg
			// serialises every attribute (including src) to the block comment.
			// Plugin-configurable attributes pull their defaults from sterimviDefaults
			// (injected via wp_localize_script) so new blocks respect site settings.
			attributes: {
				src:                   { type: 'string',  default: '' },
				srcRight:              { type: 'string',  default: '' },
				srcId:                 { type: 'number' },
				srcRightId:            { type: 'number' },
				sourceFormat:          { type: 'string',  default: siteDefault( 'source_format',           'left-right' ) },
				sourceSqueezeEnabled:  { type: 'boolean', default: !! siteDefault( 'source_squeeze_enabled', false ) },
				displayMode:           { type: 'string',  default: siteDefault( 'display_mode',            'anaglyph-rc' ) },
				displaySqueezeEnabled: { type: 'boolean', default: !! siteDefault( 'display_squeeze_enabled', false ) },
				swapSources:           { type: 'boolean', default: false },
				width:                 { type: 'string',  default: siteDefault( 'width',                   '100%' ) },
				borderEnabled:         { type: 'boolean', default: !! siteDefault( 'border_enabled',        false ) },
				borderWidth:           { type: 'string',  default: siteDefault( 'border_width',            '1px' ) },
				borderColor:           { type: 'string',  default: siteDefault( 'border_color',            '#000000' ) },
				shadowEnabled:         { type: 'boolean', default: !! siteDefault( 'shadow_enabled',        false ) },
				shadowOffsetX:         { type: 'string',  default: siteDefault( 'shadow_offset_x',         '0px' ) },
				shadowOffsetY:         { type: 'string',  default: siteDefault( 'shadow_offset_y',         '4px' ) },
				shadowBlur:            { type: 'string',  default: siteDefault( 'shadow_blur',             '12px' ) },
				shadowSpread:          { type: 'string',  default: siteDefault( 'shadow_spread',           '0px' ) },
				shadowColor:           { type: 'string',  default: siteDefault( 'shadow_color',            'rgba(0,0,0,0.25)' ) },
				controlslist:          { type: 'string',  default: siteDefault( 'controlslist',            'wiggle left right anaglyph' ) },
			},

			edit: function ( props ) {
				var attributes    = props.attributes;
				var setAttributes = props.setAttributes;

				// useBlockProps adds WP block wrapper attributes (class, data-block, etc.).
				var blockProps = useBlockProps
					? useBlockProps( { className: 'sterimvi-block-edit' } )
					: { className: 'sterimvi-block-edit' };

				// Canvas ref used for the live editor preview.
				var canvasRef = useRef( null );

				// Mirror the PHP routing logic: does this combination use the Canvas renderer?
				var isCanvasPreview = CANVAS_DISPLAY_MODES.indexOf( attributes.displayMode ) !== -1
					|| CANVAS_SOURCE_FORMAT_LIST.indexOf( attributes.sourceFormat ) !== -1
					|| attributes.sourceSqueezeEnabled;

				// ── Live canvas preview ──────────────────────────────────────────────
				useEffect( function () {
					var canvas = canvasRef.current;
					if ( ! canvas || ! attributes.src || ! isCanvasPreview || typeof window.SterimviRenderer === 'undefined' ) {
						return;
					}

					var cancelled = false;

					var splitPromise;
					if ( 'pair' === attributes.sourceFormat && attributes.srcRight ) {
						splitPromise = Promise.all( [
							SterimviRenderer.loadImage( attributes.src, true ),
							SterimviRenderer.loadImage( attributes.srcRight, true ),
						] ).then( function ( imgs ) {
							return SterimviRenderer.splitPair( imgs[ 0 ], imgs[ 1 ], attributes.swapSources );
						} );
					} else {
						splitPromise = SterimviRenderer.loadImage( attributes.src, true ).then( function ( img ) {
							if ( 'top-bottom' === attributes.sourceFormat || 'bottom-top' === attributes.sourceFormat ) {
								return SterimviRenderer.splitTopBottom( img, attributes.swapSources, attributes.sourceSqueezeEnabled );
							}
							return SterimviRenderer.splitLeftRight( img, attributes.swapSources, attributes.sourceSqueezeEnabled );
						} );
					}

					splitPromise.then( function ( split ) {
						if ( cancelled ) { return; }
						switch ( attributes.displayMode ) {
							case 'side-by-side':
								SterimviRenderer.renderSideBySide( split.left, split.right, canvas, attributes.displaySqueezeEnabled );
								break;
							case 'top-bottom':
								SterimviRenderer.renderTopBottom( split.left, split.right, canvas, attributes.displaySqueezeEnabled );
								break;
							case 'anaglyph-rb':
								SterimviRenderer.renderAnaglyphRB( split.left, split.right, canvas );
								break;
							case 'interlaced-row':
								SterimviRenderer.renderInterlacedRows( split.left, split.right, canvas );
								break;
							case 'interlaced-col':
								SterimviRenderer.renderInterlacedCols( split.left, split.right, canvas );
								break;
							default:
								canvas.width  = split.left.width;
								canvas.height = split.left.height;
								canvas.getContext( '2d' ).drawImage( split.left, 0, 0 );
						}
					} ).catch( function ( err ) {
						if ( ! cancelled ) {
							SterimviRenderer.showCanvasError( canvas, err.message || 'Image load failed' );
						}
					} );

					return function () { cancelled = true; };
				}, [
					attributes.src,
					attributes.srcRight,
					attributes.sourceFormat,
					attributes.sourceSqueezeEnabled,
					attributes.displayMode,
					attributes.displaySqueezeEnabled,
					attributes.swapSources,
				] );

				// ── Media upload helper ──────────────────────────────────────────────
				function mediaButton( openFn, hasImage, labelSelect, labelChange ) {
					return el( Button, {
						onClick: openFn,
						variant: 'secondary',
						style: { display: 'block', marginBottom: '8px' },
					}, hasImage ? labelChange : labelSelect );
				}

				// ── InspectorControls ────────────────────────────────────────────────
				var inspector = el( InspectorControls, null,

					// Panel: Source Image
					el( PanelBody, { title: __( 'Source Image', 'stereoscopic-image-viewer' ), initialOpen: true },

						el( 'p', { style: { fontWeight: 600, marginBottom: '4px' } },
							__( 'Primary Image', 'stereoscopic-image-viewer' )
						),
						el( MediaUploadCheck, null,
							el( MediaUpload, {
								onSelect: function ( media ) {
									setAttributes( { src: media.url, srcId: media.id } );
								},
								allowedTypes: [ 'image' ],
								value: attributes.srcId,
								render: function ( ref ) {
									return mediaButton(
										ref.open,
										!! attributes.src,
										__( 'Select Image', 'stereoscopic-image-viewer' ),
										__( 'Change Image', 'stereoscopic-image-viewer' )
									);
								},
							} )
						),

						el( SelectControl, {
							label:    __( 'Source Format', 'stereoscopic-image-viewer' ),
							value:    attributes.sourceFormat,
							options:  SOURCE_FORMAT_OPTIONS,
							onChange: function ( val ) { setAttributes( { sourceFormat: val } ); },
						} ),

						// Squeeze toggle — only for SBS/TB source formats.
						SQUEEZE_SOURCE_FORMATS.indexOf( attributes.sourceFormat ) !== -1 &&
							el( ToggleControl, {
								label:   __( 'Source is Anamorphic (Squeezed)', 'stereoscopic-image-viewer' ),
								help:    __( 'Enable for half-width SBS or half-height TB images.', 'stereoscopic-image-viewer' ),
								checked: attributes.sourceSqueezeEnabled,
								onChange: function ( val ) { setAttributes( { sourceSqueezeEnabled: val } ); },
							} ),

						// Right-eye image picker — only for pair mode.
						attributes.sourceFormat === 'pair' && el( Fragment, null,
							el( 'p', { style: { fontWeight: 600, marginBottom: '4px', marginTop: '12px' } },
								__( 'Right Eye Image', 'stereoscopic-image-viewer' )
							),
							el( MediaUploadCheck, null,
								el( MediaUpload, {
									onSelect: function ( media ) {
										setAttributes( { srcRight: media.url, srcRightId: media.id } );
									},
									allowedTypes: [ 'image' ],
									value: attributes.srcRightId,
									render: function ( ref ) {
										return mediaButton(
											ref.open,
											!! attributes.srcRight,
											__( 'Select Right Image', 'stereoscopic-image-viewer' ),
											__( 'Change Right Image', 'stereoscopic-image-viewer' )
										);
									},
								} )
							)
						)
					),

					// Panel: Display
					el( PanelBody, { title: __( 'Display', 'stereoscopic-image-viewer' ), initialOpen: true },

						el( SelectControl, {
							label:    __( 'Display Mode', 'stereoscopic-image-viewer' ),
							value:    attributes.displayMode,
							options:  DISPLAY_MODE_OPTIONS,
							onChange: function ( val ) { setAttributes( { displayMode: val } ); },
						} ),

						// Squeeze output toggle — only for SBS/TB display modes.
						SQUEEZE_DISPLAY_MODES.indexOf( attributes.displayMode ) !== -1 &&
							el( ToggleControl, {
								label:   __( 'Anamorphic (Squeezed) Output', 'stereoscopic-image-viewer' ),
								help:    __( 'Compresses each eye to half-width (SBS) or half-height (TB).', 'stereoscopic-image-viewer' ),
								checked: attributes.displaySqueezeEnabled,
								onChange: function ( val ) { setAttributes( { displaySqueezeEnabled: val } ); },
							} ),

						el( ToggleControl, {
							label:   __( 'Swap Left / Right', 'stereoscopic-image-viewer' ),
							help:    __( 'Reverse which side is treated as the left eye.', 'stereoscopic-image-viewer' ),
							checked: attributes.swapSources,
							onChange: function ( val ) { setAttributes( { swapSources: val } ); },
						} ),

						// Viewer controls — only relevant when stereo-img renders the output.
						! isCanvasPreview && el( Fragment, null,
							el( 'p', { style: { fontWeight: 600, marginTop: '12px', marginBottom: '4px' } },
								__( 'Viewer Controls', 'stereoscopic-image-viewer' )
							),
							el( 'p', { style: { fontSize: '12px', color: '#757575', marginBottom: '8px' } },
								__( 'Mode-switching buttons shown in the stereo-img viewer.', 'stereoscopic-image-viewer' )
							),
							CONTROL_TOKENS.map( function ( ctrl ) {
								return el( ToggleControl, {
									key:     ctrl.value,
									label:   ctrl.label,
									checked: hasControl( attributes.controlslist, ctrl.value ),
									onChange: function ( val ) {
										setAttributes( { controlslist: toggleControl( attributes.controlslist, ctrl.value, val ) } );
									},
								} );
							} )
						)
					),

					// Panel: Size & Style
					el( PanelBody, { title: __( 'Size & Style', 'stereoscopic-image-viewer' ), initialOpen: false },

						el( TextControl, {
							label:   __( 'Width', 'stereoscopic-image-viewer' ),
							help:    __( 'e.g. 640px, 100%, 80vw', 'stereoscopic-image-viewer' ),
							value:   attributes.width,
							onChange: function ( val ) { setAttributes( { width: val } ); },
						} ),

						el( ToggleControl, {
							label:   __( 'Border', 'stereoscopic-image-viewer' ),
							checked: attributes.borderEnabled,
							onChange: function ( val ) { setAttributes( { borderEnabled: val } ); },
						} ),

						attributes.borderEnabled && el( Fragment, null,
							el( TextControl, {
								label:   __( 'Border Width', 'stereoscopic-image-viewer' ),
								help:    __( 'e.g. 1px, 2px', 'stereoscopic-image-viewer' ),
								value:   attributes.borderWidth,
								onChange: function ( val ) { setAttributes( { borderWidth: val } ); },
							} ),
							el( 'div', { style: { marginBottom: '16px' } },
								el( 'label', { style: { display: 'block', marginBottom: '4px', fontWeight: 600, fontSize: '11px', textTransform: 'uppercase' } },
									__( 'Border Color', 'stereoscopic-image-viewer' )
								),
								el( 'input', {
									type: 'color',
									value: attributes.borderColor,
									onChange: function ( e ) { setAttributes( { borderColor: e.target.value } ); },
									style: { width: '48px', height: '32px', padding: '2px', cursor: 'pointer', border: '1px solid #ccc' },
								} )
							)
						),

						el( ToggleControl, {
							label:   __( 'Drop Shadow', 'stereoscopic-image-viewer' ),
							checked: attributes.shadowEnabled,
							onChange: function ( val ) { setAttributes( { shadowEnabled: val } ); },
						} ),

						attributes.shadowEnabled && el( Fragment, null,
							el( TextControl, {
								label:   __( 'Shadow Color', 'stereoscopic-image-viewer' ),
								help:    __( 'CSS color: hex, rgb(), rgba(), hsl()', 'stereoscopic-image-viewer' ),
								value:   attributes.shadowColor,
								onChange: function ( val ) { setAttributes( { shadowColor: val } ); },
							} ),
							el( TextControl, {
								label:   __( 'Shadow Horizontal Offset', 'stereoscopic-image-viewer' ),
								help:    __( 'e.g. 0px, 4px, -4px', 'stereoscopic-image-viewer' ),
								value:   attributes.shadowOffsetX,
								onChange: function ( val ) { setAttributes( { shadowOffsetX: val } ); },
							} ),
							el( TextControl, {
								label:   __( 'Shadow Vertical Offset', 'stereoscopic-image-viewer' ),
								help:    __( 'e.g. 4px', 'stereoscopic-image-viewer' ),
								value:   attributes.shadowOffsetY,
								onChange: function ( val ) { setAttributes( { shadowOffsetY: val } ); },
							} ),
							el( TextControl, {
								label:   __( 'Shadow Blur Radius', 'stereoscopic-image-viewer' ),
								help:    __( 'e.g. 12px', 'stereoscopic-image-viewer' ),
								value:   attributes.shadowBlur,
								onChange: function ( val ) { setAttributes( { shadowBlur: val } ); },
							} ),
							el( TextControl, {
								label:   __( 'Shadow Spread Radius', 'stereoscopic-image-viewer' ),
								help:    __( 'e.g. 0px', 'stereoscopic-image-viewer' ),
								value:   attributes.shadowSpread,
								onChange: function ( val ) { setAttributes( { shadowSpread: val } ); },
							} )
						)
					)
				); // end InspectorControls

				// ── Editor preview area ──────────────────────────────────────────────
				var badgeLabel = attributes.displayMode
					+ ( attributes.sourceSqueezeEnabled ? ' [src squeezed]' : '' )
					+ ( ( attributes.displaySqueezeEnabled && SQUEEZE_DISPLAY_MODES.indexOf( attributes.displayMode ) !== -1 ) ? ' [out squeezed]' : '' );

				var preview;
				if ( attributes.src ) {
					if ( isCanvasPreview ) {
						// Canvas renderer path: live rendered output via SterimviRenderer.
						preview = el( 'div', { className: 'sterimvi-editor-preview' },
							el( 'canvas', {
								ref:   canvasRef,
								style: { display: 'block', width: '100%', height: 'auto' },
							} ),
							el( 'div', { className: 'sterimvi-editor-mode-badge' }, badgeLabel )
						);
					} else {
						// stereo-img path: show raw source image with a mode badge.
						preview = el( 'div', { className: 'sterimvi-editor-preview' },
							el( 'img', {
								src:   attributes.src,
								alt:   '',
								style: { display: 'block', width: '100%', height: 'auto', maxHeight: '400px', objectFit: 'contain' },
							} ),
							el( 'div', { className: 'sterimvi-editor-mode-badge' }, badgeLabel )
						);
					}
				} else {
					preview = el( 'div', { className: 'sterimvi-placeholder' },
						el( 'span', { className: 'dashicons dashicons-format-image', style: { fontSize: '40px', width: '40px', height: '40px', color: '#aaa' } } ),
						el( 'p', null, __( 'Select a source image in the sidebar.', 'stereoscopic-image-viewer' ) )
					);
				}

				return el( 'div', blockProps,
					inspector,
					preview
				);
			},

			save: function () {
				// Dynamic block — PHP renders the frontend output via render.php.
				return null;
			},
		} );

	} catch ( err ) {
		if ( window.console ) {
			console.error( 'Stereoscopic Viewer: block registration failed —', err );
		}
	}

}() );
