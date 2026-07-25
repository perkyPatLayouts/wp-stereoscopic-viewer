=== Stereoscopic Image Viewer ===
Contributors: nductiv
Tags: 3d, stereoscopic, anaglyph, stereo, vr
Requires at least: 6.0
Tested up to: 7.02
Requires PHP: 7.4
Stable tag: 1.0.0
License: Apache License Version 2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0

Display stereoscopic (3D) images from the WordPress media library using a Gutenberg block or shortcode.

== Description ==

Stereoscopic Image Viewer lets you embed 3D images on any WordPress post or page. It supports a wide range of source formats used by 3D cameras, phones, and editing software, and can display them in multiple viewing modes.

= Supported Source Formats =

* Side-by-side (left-right or right-left)
* Top-bottom (top-bottom or bottom-top)
* Anaglyph red-cyan
* Anaglyph red-blue
* Interlaced (row or column)
* Separate left/right image pair

= Supported Display Modes =

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
* `[stereo_img]` shortcode for classic editor and widget areas
* Settings page with site-wide defaults for all parameters
* Swap left/right sources with a single toggle
* Control width (px, %, vw), border, and drop shadow per block
* Powered by the [stereo-img](https://github.com/steren/stereo-img) web component for hardware-accelerated 3D rendering
* Custom Canvas 2D renderer handles anaglyph red-blue and interlaced formats not supported by the web component

== Installation ==

1. Upload the `wp-stereoscopic-viewer` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Visit **Stereoscopic** in the admin menu to configure site-wide defaults.
4. Add the **Stereoscopic Image** block in the Gutenberg editor, or use the `[stereo_img]` shortcode.

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

= How do I use the shortcode? =

Basic example:

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc"]`

Separate left/right image pair:

`[stereo_img src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="wiggle"]`

Anamorphic (half-width side-by-side) source displayed as red-cyan anaglyph, with border and shadow:

`[stereo_img src="https://example.com/hsbs.jpg" source_format="left-right" source_squeeze="1" display_mode="anaglyph-rc" border="1" border_width="2px" border_color="#222222" shadow="1" shadow_offset_x="0px" shadow_offset_y="6px" shadow_blur="16px" shadow_spread="0px" shadow_color="rgba(0,0,0,0.4)"]`

== Shortcode Parameter Reference ==

**Source**

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

**Display**

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
* `controlslist` — Space-separated list of mode-switching buttons to show in the viewer. Default: `wiggle left right anaglyph`. Valid tokens: `wiggle`, `left`, `right`, `anaglyph`. Leave empty to show all controls (including VR). Only applies when the stereo-img renderer handles the output (i.e. not Canvas modes).

**Size & Style**

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

= Can I set different defaults for each image? =

Yes. Every parameter can be overridden in the Gutenberg block's InspectorControls sidebar, or as an attribute in the shortcode.

= What is the default stereo-img URL? =

The plugin uses the stereo-img web component from `https://stereo-img.steren.fr/stereo-img.js` and is incorporated here under Apache 2.0 license: https://www.apache.org/licenses/LICENSE-2.0

== Examples ==

= Side-by-side source, anaglyph output =

The most common case: a photo taken with a 3D camera or phone that saves both eyes side-by-side in one JPEG.

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc"]`

= Side-by-side source, wiggle output (no glasses needed) =

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="wiggle"]`

= Top-bottom source, anaglyph output =

`[stereo_img src="https://example.com/photo-tb.jpg" source_format="top-bottom" display_mode="anaglyph-rc"]`

= Anamorphic (half-width) side-by-side source =

Some 3D cameras save a squeezed (HSBS) image where each eye occupies half the frame width. Use `source_squeeze="1"` to unsqueeze it before rendering.

`[stereo_img src="https://example.com/photo-hsbs.jpg" source_format="left-right" source_squeeze="1" display_mode="anaglyph-rc"]`

= Anamorphic (half-height) top-bottom source =

`[stereo_img src="https://example.com/photo-htb.jpg" source_format="top-bottom" source_squeeze="1" display_mode="anaglyph-rc"]`

= Separate left and right images =

When you have two individual files — one per eye — use `source_format="pair"` and provide both `src` and `src_right`.

`[stereo_img src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="anaglyph-rc"]`

= Separate images, wiggle output =

`[stereo_img src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="wiggle"]`

= Sources are stored right-eye-first =

If your side-by-side image has the right eye on the left, add `swap="1"` to correct the eye order.

`[stereo_img src="https://example.com/photo-rls.jpg" source_format="left-right" swap="1" display_mode="anaglyph-rc"]`

= Anaglyph red-blue output =

For viewers who only have red-blue (not red-cyan) glasses.

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rb"]`

= Side-by-side composite output =

Useful for cross-eyed free-viewing or feeding into a VR headset app. The output image places the left eye on the left and the right eye on the right.

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="side-by-side" width="800px"]`

= Squeezed side-by-side output =

Produces a half-width-per-eye (HSBS) output image suitable for players that expect that format.

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="side-by-side" display_squeeze="1"]`

= Anaglyph red-cyan source displayed as-is =

If your source image is already an anaglyph red-cyan composite and you just want to display it, set both source and display to `anaglyph-rc`.

`[stereo_img src="https://example.com/photo-anaglyph.jpg" source_format="anaglyph-rc" display_mode="anaglyph-rc"]`

= Row-interlaced source, anaglyph output =

Row-interlaced images (common on polarised 3D monitor captures) can be decoded and re-displayed in any mode.

`[stereo_img src="https://example.com/photo-interlaced.jpg" source_format="interlaced-row" display_mode="anaglyph-rc"]`

= Fixed width =

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" width="640px"]`

= Responsive width capped at 80% of viewport =

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" width="80vw"]`

= With border and drop shadow =

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" border="1" border_width="2px" border_color="#333333" shadow="1" shadow_offset_x="0px" shadow_offset_y="6px" shadow_blur="16px" shadow_spread="0px" shadow_color="rgba(0,0,0,0.35)"]`

= Limit which viewer control buttons appear =

Show only the wiggle and anaglyph buttons; hide the left/right-eye buttons.

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" controlslist="wiggle anaglyph"]`

Show no controls at all (viewer is static, no mode switching):

`[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="wiggle" controlslist=""]`

== Screenshots ==

1. Gutenberg block in the editor with InspectorControls open
2. Anaglyph red-cyan display mode on the front end
3. Plugin settings page

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
