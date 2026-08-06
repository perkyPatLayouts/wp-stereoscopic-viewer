<?php
/**
 * Settings page HTML template.
 *
 * @package Nductiv\StereoscopicImageViewer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap sterimvi-settings-wrap">
	<h1><?php esc_html_e( 'Stereoscopic Image Viewer', 'stereoscopic-image-viewer' ); ?></h1>
	<form method="post" action="options.php">
		<?php
		settings_fields( Nductiv\StereoscopicImageViewer\Settings::GROUP );
		do_settings_sections( Nductiv\StereoscopicImageViewer\Settings::PAGE_SLUG );
		submit_button();
		?>
	</form>

	<hr>

	<div class="sterimvi-shortcode-docs">
		<h2><?php esc_html_e( 'Shortcode Reference', 'stereoscopic-image-viewer' ); ?></h2>
		<p><?php esc_html_e( 'Use the [sterimvi_image] shortcode anywhere shortcodes are supported: classic editor posts, widget areas, or template files via do_shortcode(). Any attribute you omit falls back to the default configured above.', 'stereoscopic-image-viewer' ); ?></p>

		<h3><?php esc_html_e( 'Quick Examples', 'stereoscopic-image-viewer' ); ?></h3>

		<h4><?php esc_html_e( 'Side-by-side source, anaglyph red-cyan output', 'stereoscopic-image-viewer' ); ?></h4>
		<pre><code>[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc"]</code></pre>

		<h4><?php esc_html_e( 'Wiggle output (no glasses needed)', 'stereoscopic-image-viewer' ); ?></h4>
		<pre><code>[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="wiggle"]</code></pre>

		<h4><?php esc_html_e( 'Separate left/right image pair', 'stereoscopic-image-viewer' ); ?></h4>
		<pre><code>[sterimvi_image src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="anaglyph-rc"]</code></pre>

		<h4><?php esc_html_e( 'Anamorphic (half-width) side-by-side source', 'stereoscopic-image-viewer' ); ?></h4>
		<pre><code>[sterimvi_image src="https://example.com/photo-hsbs.jpg" source_format="left-right" source_squeeze="1" display_mode="anaglyph-rc"]</code></pre>

		<h4><?php esc_html_e( 'Swap left and right eye sources', 'stereoscopic-image-viewer' ); ?></h4>
		<pre><code>[sterimvi_image src="https://example.com/photo-rls.jpg" source_format="left-right" swap="1" display_mode="anaglyph-rc"]</code></pre>

		<h4><?php esc_html_e( 'Fixed width with border and drop shadow', 'stereoscopic-image-viewer' ); ?></h4>
		<pre><code>[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" width="640px" border="1" border_width="2px" border_color="#333333" shadow="1" shadow_offset_x="0px" shadow_offset_y="6px" shadow_blur="16px" shadow_spread="0px" shadow_color="rgba(0,0,0,0.35)"]</code></pre>

		<h4><?php esc_html_e( 'Limit which viewer control buttons appear', 'stereoscopic-image-viewer' ); ?></h4>
		<pre><code>[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" controlslist="wiggle anaglyph"]</code></pre>

		<h3><?php esc_html_e( 'Parameter Reference', 'stereoscopic-image-viewer' ); ?></h3>

		<h4><?php esc_html_e( 'Source', 'stereoscopic-image-viewer' ); ?></h4>
		<table class="widefat striped sterimvi-params-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attribute', 'stereoscopic-image-viewer' ); ?></th>
					<th><?php esc_html_e( 'Default', 'stereoscopic-image-viewer' ); ?></th>
					<th><?php esc_html_e( 'Description', 'stereoscopic-image-viewer' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>src</code></td>
					<td>&mdash;</td>
					<td><?php esc_html_e( 'URL of the primary (or combined) source image. Required.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>src_right</code></td>
					<td>&mdash;</td>
					<td><?php esc_html_e( 'URL of the right-eye image. Required only when source_format="pair".', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>source_format</code></td>
					<td><code>left-right</code></td>
					<td><?php esc_html_e( 'How the source image encodes both eyes. Valid values:', 'stereoscopic-image-viewer' ); ?> <code>left-right</code>, <code>top-bottom</code>, <code>anaglyph-rc</code>, <code>anaglyph-rb</code>, <code>interlaced-row</code>, <code>interlaced-col</code>, <code>pair</code>.</td>
				</tr>
				<tr>
					<td><code>source_squeeze</code></td>
					<td><code>0</code></td>
					<td><?php esc_html_e( 'Set to 1 if the source is anamorphic (half-width SBS or half-height TB).', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
			</tbody>
		</table>

		<h4><?php esc_html_e( 'Display', 'stereoscopic-image-viewer' ); ?></h4>
		<table class="widefat striped sterimvi-params-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attribute', 'stereoscopic-image-viewer' ); ?></th>
					<th><?php esc_html_e( 'Default', 'stereoscopic-image-viewer' ); ?></th>
					<th><?php esc_html_e( 'Description', 'stereoscopic-image-viewer' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>display_mode</code></td>
					<td><code>anaglyph-rc</code></td>
					<td><?php esc_html_e( 'How to present the image. Valid values:', 'stereoscopic-image-viewer' ); ?> <code>anaglyph-rc</code>, <code>anaglyph-rb</code>, <code>wiggle</code>, <code>left</code>, <code>right</code>, <code>side-by-side</code>, <code>top-bottom</code>, <code>interlaced-row</code>, <code>interlaced-col</code>.</td>
				</tr>
				<tr>
					<td><code>display_squeeze</code></td>
					<td><code>0</code></td>
					<td><?php esc_html_e( 'Set to 1 for anamorphic (half-width SBS / half-height TB) output. Only effective for side-by-side and top-bottom display modes.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>swap</code></td>
					<td><code>0</code></td>
					<td><?php esc_html_e( 'Set to 1 to swap left and right eye sources.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>controlslist</code></td>
					<td><code>wiggle left right anaglyph</code></td>
					<td><?php esc_html_e( 'Space-separated list of mode-switching buttons to show. Valid tokens: wiggle, left, right, anaglyph. Leave empty to show all controls (including VR). Only applies when stereo-img handles rendering (not Canvas modes).', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
			</tbody>
		</table>

		<h4><?php esc_html_e( 'Size &amp; Style', 'stereoscopic-image-viewer' ); ?></h4>
		<table class="widefat striped sterimvi-params-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attribute', 'stereoscopic-image-viewer' ); ?></th>
					<th><?php esc_html_e( 'Default', 'stereoscopic-image-viewer' ); ?></th>
					<th><?php esc_html_e( 'Description', 'stereoscopic-image-viewer' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>width</code></td>
					<td><code>100%</code></td>
					<td><?php esc_html_e( 'CSS width of the viewer. Accepts px, %, or vw. Example: 640px.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>border</code></td>
					<td><code>0</code></td>
					<td><?php esc_html_e( 'Set to 1 to show a border.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>border_width</code></td>
					<td><code>1px</code></td>
					<td><?php esc_html_e( 'Border thickness. Example: 2px.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>border_color</code></td>
					<td><code>#000000</code></td>
					<td><?php esc_html_e( 'Hex border color.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>shadow</code></td>
					<td><code>0</code></td>
					<td><?php esc_html_e( 'Set to 1 to show a drop shadow.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>shadow_offset_x</code></td>
					<td><code>0px</code></td>
					<td><?php esc_html_e( 'Shadow horizontal offset. Negative values move the shadow left.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>shadow_offset_y</code></td>
					<td><code>4px</code></td>
					<td><?php esc_html_e( 'Shadow vertical offset.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>shadow_blur</code></td>
					<td><code>12px</code></td>
					<td><?php esc_html_e( 'Shadow blur radius.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>shadow_spread</code></td>
					<td><code>0px</code></td>
					<td><?php esc_html_e( 'Shadow spread radius.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
				<tr>
					<td><code>shadow_color</code></td>
					<td><code>rgba(0,0,0,0.25)</code></td>
					<td><?php esc_html_e( 'Any CSS color value.', 'stereoscopic-image-viewer' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
