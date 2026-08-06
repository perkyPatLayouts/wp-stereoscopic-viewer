=== Stereoscopic Image Viewer ===
Contributors: Nductiv
Tags: 3d, stereoscopic, anaglyph, stereo
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embed 3D stereoscopic images in posts and pages, with anaglyph, wiggle, side-by-side and interlaced viewing modes.

== Description ==

Stereoscopic (3D) images rely on parallax — the difference between the left and right eye views of a scene. A stereoscopic image contains both views, which can be presented using coloured glasses, polarised glasses, VR headsets, or motion.

Stereoscopic Image Viewer lets you display these images in WordPress pages and posts. Import an image into the media library, then place it with the block editor or a shortcode. Both let you declare the format of the source image and choose how it should be displayed — the plugin converts between them for you.

The plugin ships with the [stereo-img](https://github.com/steren/stereo-img) web component bundled locally, so no external requests are made. Formats that stereo-img does not handle (anaglyph red-blue, interlaced, and anamorphic sources) are rendered by a custom Canvas 2D renderer included in the plugin.

[Detailed examples and full documentation](https://apps.nductiv.com/wp-stereoscopic-viewer/)

= Supported source formats =

* Side-by-side (left-right or right-left)
* Top-bottom (top-bottom or bottom-top)
* Anaglyph red-cyan
* Anaglyph red-blue
* Interlaced (row or column)
* Separate left/right image pair
* Anamorphic ("squeezed") variants of side-by-side and top-bottom

= Supported display modes =

* Anaglyph red-cyan (for red-cyan glasses)
* Anaglyph red-blue (for red-blue glasses)
* Wiggle (alternating left/right, no glasses needed)
* Left eye only
* Right eye only
* Side-by-side
* Top-bottom
* Interlaced (row or column, for compatible displays)

= Features =

* Gutenberg block with live Canvas-rendered preview and full InspectorControls
* `[sterimvi_image]` shortcode for the classic editor and widget areas
* Settings page with site-wide defaults for every parameter
* Swap left/right sources with a single toggle
* Control width (px, %, vw), border, and drop shadow per block
* Powered by the stereo-img web component for hardware-accelerated 3D rendering
* Custom Canvas 2D renderer handles the formats the web component does not support
* No external requests — the stereo-img library is bundled with the plugin by default

== Installation ==

1. Upload the `stereoscopic-image-viewer` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Visit **Stereoscopic** in the admin menu to configure site-wide defaults.
4. Add the **Stereoscopic Image** block in the block editor, or use the `[sterimvi_image]` shortcode.

== Frequently Asked Questions ==

= What image formats are supported? =

Any image format your browser supports: JPEG, PNG, WebP. Upload images through the standard WordPress media library.

= Do I need special glasses? =

It depends on the display mode you choose:

* **Anaglyph red-cyan** — requires red-cyan 3D glasses
* **Anaglyph red-blue** — requires red-blue 3D glasses
* **Wiggle** — no glasses required (creates a 3D illusion through motion)
* **Left/Right only** — no glasses required (shows one eye's perspective only)

= My images load from an external URL and the anaglyph/interlaced modes don't work. =

Anaglyph red-blue and interlaced rendering use the browser Canvas API, which requires images to be served from the same domain (or with CORS headers). Images in the WordPress media library are always same-origin and work correctly. External URLs may trigger a CORS error.

= Can I set different defaults for each image? =

Yes. Every parameter can be overridden in the block's InspectorControls sidebar, or as an attribute on the shortcode. The settings page supplies the value used whenever a parameter is omitted.

= Where does the stereo-img script load from? =

By default the plugin serves the stereo-img web component, and its Three.js, exifr and parser dependencies, from a copy bundled inside the plugin at `assets/vendor/stereo-img/`. No external requests are made and the viewer works offline.

On the settings page you can switch the **stereo-img Load Method** to load from the external CDN at `https://stereo-img.steren.fr/stereo-img.js`, or from a custom URL. Because stereo-img uses relative ES-module imports, a custom URL must point at a complete stereo-img release directory tree, not just the single JS file.

= How do I use the shortcode? =

Basic example:

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc"]`

Separate left/right image pair:

`[sterimvi_image src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="wiggle"]`

Anamorphic (half-width side-by-side) source displayed as red-cyan anaglyph, with border and shadow:

`[sterimvi_image src="https://example.com/hsbs.jpg" source_format="left-right" source_squeeze="1" display_mode="anaglyph-rc" border="1" border_width="2px" border_color="#222222" shadow="1" shadow_offset_x="0px" shadow_offset_y="6px" shadow_blur="16px" shadow_spread="0px" shadow_color="rgba(0,0,0,0.4)"]`

See the Shortcode Parameter Reference section below for every attribute.

== Shortcode Parameter Reference ==

All attributes are optional except `src`. Anything you omit falls back to the site-wide default configured on the settings page.

= Source =

* `src` — URL of the primary (or combined) source image. Required.
* `src_right` — URL of the right-eye image. Required only when `source_format="pair"`.
* `source_format` — How the source image encodes both eyes. Default: `left-right`.
  * `left-right` — Side-by-side, left eye on the left.
  * `top-bottom` — Top-bottom, left eye on top.
  * `anaglyph-rc` — Anaglyph red-cyan composite.
  * `anaglyph-rb` — Anaglyph red-blue composite.
  * `interlaced-row` — Row-interlaced (polarised display source).
  * `interlaced-col` — Column-interlaced.
  * `pair` — Two separate images (requires `src_right`).
* `source_squeeze` — `1` if the source is anamorphic (half-width SBS or half-height TB). Default: `0`.

= Display =

* `display_mode` — How to present the image. Default: `anaglyph-rc`.
  * `anaglyph-rc` — Red-cyan anaglyph (requires red-cyan glasses).
  * `anaglyph-rb` — Red-blue anaglyph (requires red-blue glasses).
  * `wiggle` — Alternating left/right frames (no glasses needed).
  * `left` — Left eye only.
  * `right` — Right eye only.
  * `side-by-side` — Side-by-side composite output.
  * `top-bottom` — Top-bottom composite output.
  * `interlaced-row` — Row-interlaced output.
  * `interlaced-col` — Column-interlaced output.
* `display_squeeze` — `1` to output as anamorphic (half-width SBS / half-height TB). Default: `0`. Only effective for `side-by-side` and `top-bottom` display modes.
* `swap` — `1` to swap left and right eye sources. Default: `0`.
* `controlslist` — Space-separated list of mode-switching buttons to show in the viewer. Default: `wiggle left right anaglyph`. Valid tokens: `wiggle`, `left`, `right`, `anaglyph`. Leave empty to show all controls (including VR). Only applies when the stereo-img renderer handles the output, i.e. not Canvas modes.

= Size and style =

* `width` — CSS width of the viewer. Accepts `px`, `%`, or `vw`. Default: `100%`. Example: `640px`.
* `border` — `1` to show a border. Default: `0`.
* `border_width` — Border thickness. Default: `1px`. Example: `2px`.
* `border_color` — Hex border color. Default: `#000000`.
* `shadow` — `1` to show a drop shadow. Default: `0`.
* `shadow_offset_x` — Shadow horizontal offset. Default: `0px`. Negative values move the shadow left.
* `shadow_offset_y` — Shadow vertical offset. Default: `4px`.
* `shadow_blur` — Shadow blur radius. Default: `12px`.
* `shadow_spread` — Shadow spread radius. Default: `0px`.
* `shadow_color` — Shadow color (any CSS color). Default: `rgba(0,0,0,0.25)`.

== Examples ==

= Side-by-side source, anaglyph output =

The most common case: a photo taken with a 3D camera or phone that saves both eyes side-by-side in one JPEG.

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc"]`

= Side-by-side source, wiggle output (no glasses needed) =

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="wiggle"]`

= Top-bottom source, anaglyph output =

`[sterimvi_image src="https://example.com/photo-tb.jpg" source_format="top-bottom" display_mode="anaglyph-rc"]`

= Anamorphic (half-width) side-by-side source =

Some 3D cameras save a squeezed (HSBS) image where each eye occupies half the frame width. Use `source_squeeze="1"` to unsqueeze it before rendering.

`[sterimvi_image src="https://example.com/photo-hsbs.jpg" source_format="left-right" source_squeeze="1" display_mode="anaglyph-rc"]`

= Anamorphic (half-height) top-bottom source =

`[sterimvi_image src="https://example.com/photo-htb.jpg" source_format="top-bottom" source_squeeze="1" display_mode="anaglyph-rc"]`

= Separate left and right images =

When you have two individual files — one per eye — use `source_format="pair"` and provide both `src` and `src_right`.

`[sterimvi_image src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="anaglyph-rc"]`

= Separate images, wiggle output =

`[sterimvi_image src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="wiggle"]`

= Sources are stored right-eye-first =

If your side-by-side image has the right eye on the left, add `swap="1"` to correct the eye order.

`[sterimvi_image src="https://example.com/photo-rls.jpg" source_format="left-right" swap="1" display_mode="anaglyph-rc"]`

= Anaglyph red-blue output =

For viewers who only have red-blue (not red-cyan) glasses.

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rb"]`

= Side-by-side composite output =

Useful for cross-eyed free-viewing or feeding into a VR headset app. The output image places the left eye on the left and the right eye on the right.

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="side-by-side" width="800px"]`

= Squeezed side-by-side output =

Produces a half-width-per-eye (HSBS) output image suitable for players that expect that format.

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="side-by-side" display_squeeze="1"]`

= Anaglyph red-cyan source displayed as-is =

If your source image is already an anaglyph red-cyan composite and you just want to display it, set both source and display to `anaglyph-rc`.

`[sterimvi_image src="https://example.com/photo-anaglyph.jpg" source_format="anaglyph-rc" display_mode="anaglyph-rc"]`

= Row-interlaced source, anaglyph output =

Row-interlaced images (common on polarised 3D monitor captures) can be decoded and re-displayed in any mode.

`[sterimvi_image src="https://example.com/photo-interlaced.jpg" source_format="interlaced-row" display_mode="anaglyph-rc"]`

= Fixed width =

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" width="640px"]`

= Responsive width capped at 80% of viewport =

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" width="80vw"]`

= With border and drop shadow =

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" border="1" border_width="2px" border_color="#333333" shadow="1" shadow_offset_x="0px" shadow_offset_y="6px" shadow_blur="16px" shadow_spread="0px" shadow_color="rgba(0,0,0,0.35)"]`

= Limit which viewer control buttons appear =

Show only the wiggle and anaglyph buttons; hide the left/right-eye buttons.

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" controlslist="wiggle anaglyph"]`

Show no controls at all, so the viewer is static with no mode switching:

`[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="wiggle" controlslist=""]`

== Screenshots ==

1. The Stereoscopic Image block selected in the editor, with the Source Image, Display, and Size & Style panels open in the sidebar.
2. The plugin settings page, showing site-wide defaults, the stereo-img load method, and the built-in shortcode reference.
3. The Stereoscopic Image block as it appears in the block inserter.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
