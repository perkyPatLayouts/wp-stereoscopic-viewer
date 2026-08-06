# Stereoscopic Image Viewer

> Display stereoscopic (3D) images from the WordPress media library using a Gutenberg block or shortcode.

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?logo=wordpress&logoColor=white)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.0.0-brightgreen.svg)](#changelog)

---

## Contents

- [Overview](#overview)
- [Features](#features)
- [Supported Formats](#supported-formats)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Shortcode Reference](#shortcode-reference)
- [Examples](#examples)
- [FAQ](#faq)
- [Where does the stereo-img script load from?](#where-does-the-stereo-img-script-load-from)
- [Developer Reference](#developer-reference)
- [Screenshots](#screenshots)
- [Changelog](#changelog)
- [License](#license)

---

## Overview

Stereoscopic Image Viewer lets you embed 3D images on any WordPress post or page. It supports a wide range of source formats used by 3D cameras, phones, and editing software, and can display them in multiple viewing modes.

## Features

- **Gutenberg block** with live Canvas-rendered preview and full `InspectorControls`
- **`[sterimvi_image]` shortcode** for the classic editor and widget areas
- **Settings page** with site-wide defaults for every parameter
- **Swap left/right** sources with a single toggle
- **Width, border, and drop shadow** controls per block
- **Powered by [stereo-img](https://github.com/steren/stereo-img)** for hardware-accelerated 3D rendering
- **Custom Canvas 2D renderer** for anaglyph red-blue and interlaced formats
- **Zero external requests** — the stereo-img library is bundled locally by default

## Supported Formats

### Source Formats

| Format                | Description                                        |
| --------------------- | -------------------------------------------------- |
| `left-right`          | Side-by-side, left eye on the left (or swapped)    |
| `top-bottom`          | Top-bottom, left eye on top (or swapped)           |
| `anaglyph-rc`         | Anaglyph red-cyan composite                        |
| `anaglyph-rb`         | Anaglyph red-blue composite                        |
| `interlaced-row`      | Row-interlaced (polarised display source)          |
| `interlaced-col`      | Column-interlaced                                  |
| `pair`                | Two separate images, one per eye                   |

### Display Modes

| Mode              | Requires Glasses?      | Notes                                    |
| ----------------- | ---------------------- | ---------------------------------------- |
| `anaglyph-rc`     | Red-cyan glasses       | Default                                  |
| `anaglyph-rb`     | Red-blue glasses       |                                          |
| `wiggle`          | No                     | Alternating left/right frames            |
| `left`            | No                     | Left eye only                            |
| `right`           | No                     | Right eye only                           |
| `side-by-side`    | Cross-eye or headset   | Composite output                         |
| `top-bottom`      | Cross-eye or headset   | Composite output                         |
| `interlaced-row`  | Polarised 3D display   |                                          |
| `interlaced-col`  | Polarised 3D display   |                                          |

## Installation

1. Upload the `stereoscopic-image-viewer` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Visit **Stereoscopic** in the admin menu to configure site-wide defaults.
4. Add the **Stereoscopic Image** block in the Gutenberg editor, or use the `[sterimvi_image]` shortcode.

## Quick Start

The most common case — a side-by-side JPEG from a 3D camera, displayed as red-cyan anaglyph:

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc"]
```

Two separate files, wiggle output (no glasses needed):

```
[sterimvi_image src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="wiggle"]
```

Anamorphic (half-width side-by-side) source, red-cyan output, with border and drop shadow:

```
[sterimvi_image src="https://example.com/hsbs.jpg" source_format="left-right" source_squeeze="1" display_mode="anaglyph-rc" border="1" border_width="2px" border_color="#222222" shadow="1" shadow_offset_x="0px" shadow_offset_y="6px" shadow_blur="16px" shadow_spread="0px" shadow_color="rgba(0,0,0,0.4)"]
```

## Shortcode Reference

Every parameter can be overridden per-shortcode; anything you omit falls back to the site-wide default from the settings page.

### Source

| Attribute        | Default        | Description                                                                 |
| ---------------- | -------------- | --------------------------------------------------------------------------- |
| `src`            | —              | URL of the primary (or combined) source image. **Required.**                |
| `src_right`      | —              | URL of the right-eye image. Required only when `source_format="pair"`.      |
| `source_format`  | `left-right`   | How the source encodes both eyes. See [Supported Formats](#source-formats). |
| `source_squeeze` | `0`            | Set to `1` if the source is anamorphic (half-width SBS or half-height TB).  |

### Display

| Attribute         | Default                          | Description                                                                                       |
| ----------------- | -------------------------------- | ------------------------------------------------------------------------------------------------- |
| `display_mode`    | `anaglyph-rc`                    | How to present the image. See [Display Modes](#display-modes).                                    |
| `display_squeeze` | `0`                              | Set to `1` for anamorphic output. Only effective for `side-by-side` and `top-bottom`.             |
| `swap`            | `0`                              | Set to `1` to swap left and right eye sources.                                                    |
| `controlslist`    | `wiggle left right anaglyph`     | Space-separated mode-switching buttons. Tokens: `wiggle`, `left`, `right`, `anaglyph`. Leave empty to show all controls (including VR). Only applies when stereo-img handles rendering. |

### Size & Style

| Attribute         | Default              | Description                                                    |
| ----------------- | -------------------- | -------------------------------------------------------------- |
| `width`           | `100%`               | CSS width. Accepts `px`, `%`, or `vw` (e.g. `640px`).          |
| `border`          | `0`                  | Set to `1` to show a border.                                   |
| `border_width`    | `1px`                | Border thickness (e.g. `2px`).                                 |
| `border_color`    | `#000000`            | Hex border color.                                              |
| `shadow`          | `0`                  | Set to `1` to show a drop shadow.                              |
| `shadow_offset_x` | `0px`                | Horizontal offset. Negative values move the shadow left.       |
| `shadow_offset_y` | `4px`                | Vertical offset.                                               |
| `shadow_blur`     | `12px`               | Blur radius.                                                   |
| `shadow_spread`   | `0px`                | Spread radius.                                                 |
| `shadow_color`    | `rgba(0,0,0,0.25)`   | Any CSS color: hex, `rgb()`, `rgba()`, `hsl()`, `hsla()`, or a named color. |

## Examples

<details>
<summary><b>Side-by-side source, anaglyph output</b> — the most common case</summary>

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Side-by-side source, wiggle output</b> — no glasses needed</summary>

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="wiggle"]
```
</details>

<details>
<summary><b>Top-bottom source, anaglyph output</b></summary>

```
[sterimvi_image src="https://example.com/photo-tb.jpg" source_format="top-bottom" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Anamorphic (half-width) side-by-side source</b></summary>

Some 3D cameras save a squeezed (HSBS) image where each eye occupies half the frame width. Use `source_squeeze="1"` to unsqueeze it before rendering.

```
[sterimvi_image src="https://example.com/photo-hsbs.jpg" source_format="left-right" source_squeeze="1" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Anamorphic (half-height) top-bottom source</b></summary>

```
[sterimvi_image src="https://example.com/photo-htb.jpg" source_format="top-bottom" source_squeeze="1" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Separate left and right images</b></summary>

When you have two individual files — one per eye — use `source_format="pair"` and provide both `src` and `src_right`.

```
[sterimvi_image src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Separate images, wiggle output</b></summary>

```
[sterimvi_image src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="wiggle"]
```
</details>

<details>
<summary><b>Sources are stored right-eye-first</b></summary>

If your side-by-side image has the right eye on the left, add `swap="1"` to correct the eye order.

```
[sterimvi_image src="https://example.com/photo-rls.jpg" source_format="left-right" swap="1" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Anaglyph red-blue output</b> — for red-blue glasses</summary>

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rb"]
```
</details>

<details>
<summary><b>Side-by-side composite output</b> — for cross-eye viewing or VR headsets</summary>

The output places the left eye on the left and the right eye on the right.

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="side-by-side" width="800px"]
```
</details>

<details>
<summary><b>Squeezed side-by-side output</b> — HSBS for compatible players</summary>

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="side-by-side" display_squeeze="1"]
```
</details>

<details>
<summary><b>Anaglyph red-cyan source displayed as-is</b></summary>

If your source image is already an anaglyph red-cyan composite, set both source and display to `anaglyph-rc`.

```
[sterimvi_image src="https://example.com/photo-anaglyph.jpg" source_format="anaglyph-rc" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Row-interlaced source, anaglyph output</b></summary>

Row-interlaced images (common on polarised 3D monitor captures) can be decoded and re-displayed in any mode.

```
[sterimvi_image src="https://example.com/photo-interlaced.jpg" source_format="interlaced-row" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Fixed width</b></summary>

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" width="640px"]
```
</details>

<details>
<summary><b>Responsive width capped at 80% of viewport</b></summary>

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" width="80vw"]
```
</details>

<details>
<summary><b>With border and drop shadow</b></summary>

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" border="1" border_width="2px" border_color="#333333" shadow="1" shadow_offset_x="0px" shadow_offset_y="6px" shadow_blur="16px" shadow_spread="0px" shadow_color="rgba(0,0,0,0.35)"]
```
</details>

<details>
<summary><b>Limit which viewer control buttons appear</b></summary>

Show only the wiggle and anaglyph buttons; hide the left/right-eye buttons:

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" controlslist="wiggle anaglyph"]
```

Show no controls at all (viewer is static, no mode switching):

```
[sterimvi_image src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="wiggle" controlslist=""]
```
</details>

## FAQ

### What image formats are supported?

Any image format your browser supports: JPEG, PNG, WebP. Upload images through the standard WordPress media library.

### Do I need special glasses?

It depends on the display mode you choose:

| Mode                | Glasses needed?                                          |
| ------------------- | -------------------------------------------------------- |
| Anaglyph red-cyan   | Red-cyan 3D glasses                                      |
| Anaglyph red-blue   | Red-blue 3D glasses                                      |
| Wiggle              | None — creates a 3D illusion through motion              |
| Left / Right only   | None — shows one eye's perspective                       |

### My images load from an external URL and the anaglyph/interlaced modes don't work.

Anaglyph red-blue and interlaced rendering use the browser Canvas API, which requires images to be served from the same domain (or with CORS headers). Images in the WordPress media library are always same-origin and work correctly. External URLs may trigger a CORS error.

### Can I set different defaults for each image?

Yes. Every parameter can be overridden in the Gutenberg block's `InspectorControls` sidebar, or as an attribute in the shortcode. The settings page provides the site-wide defaults used when a parameter is omitted.

## Where does the stereo-img script load from?

By default the plugin serves the [stereo-img](https://github.com/steren/stereo-img) web component (and its Three.js, exifr, and parser dependencies) from a copy bundled inside the plugin at `assets/vendor/stereo-img/`. **No external requests are needed** and the viewer will work offline.

If you prefer, you can switch the **"stereo-img Load Method"** on the settings page to:

- **External CDN** — load from `https://stereo-img.steren.fr/stereo-img.js`.
- **Custom URL** — load from any URL you specify. Because stereo-img uses relative ES-module imports for its dependencies, the URL must point to a complete stereo-img release directory tree (not just the single JS file).

## Developer Reference

### Naming conventions

Every identifier the plugin registers globally is prefixed, per the WordPress.org
plugin guidelines. When adding code, follow the same scheme:

| Kind | Convention | Example |
| ---- | ---------- | ------- |
| PHP namespace | `Nductiv\StereoscopicImageViewer` | `Nductiv\StereoscopicImageViewer\Block` |
| Constants | `STERIMVI_` | `STERIMVI_DIR`, `STERIMVI_URL`, `STERIMVI_VERSION` |
| Options, settings, sections, fields | `sterimvi_` | `sterimvi_settings` |
| Shortcode | `sterimvi_` | `[sterimvi_image]` |
| Script/style handles | `sterimvi-` | `sterimvi-block-editor` |
| CSS classes | `.sterimvi-` | `.sterimvi-wrapper` |
| JS globals | `Sterimvi` / `sterimvi` | `window.SterimviRenderer` |
| Block name | `stereoscopic-image-viewer/` | `stereoscopic-image-viewer/stereo-img` |

> **Do not rename** the `<stereo-img>` custom element, the `assets/vendor/stereo-img/`
> directory, or the internal helpers `render_stereo_img()` / `resolve_stereo_img_url()`.
> The first two are the third-party library's public API; the last two are private
> method names, not globally registered identifiers.

### File layout

```
stereoscopic-image-viewer.php   Plugin header, constants, bootstrap
render.php                      Block server-side render callback
uninstall.php                   Deletes sterimvi_settings (multisite-aware)
block.json                      Block definition (apiVersion 3)
includes/
  class-plugin.php              Singleton; loads and wires the other classes
  class-settings.php            Options API, settings page, sanitisation
  class-assets.php              Script/style enqueueing, ES-module tag filter
  class-block.php               Block registration + shared HTML renderer
  class-shortcode.php           [sterimvi_image] shortcode
admin/
  settings-page.php             Settings page template + shortcode docs
  admin.css
assets/
  js/renderer.js                window.SterimviRenderer (Canvas 2D)
  js/viewer-init.js             Front-end canvas bootstrap
  js/block-editor.js            Block editor UI (plain JS, no build step)
  js/admin-settings.js          Settings page progressive disclosure
  css/viewer.css, css/editor.css
  vendor/stereo-img/            Bundled third-party library (do not edit)
```

There is **no build step**. `block-editor.js` uses the `wp.*` globals directly
rather than JSX/webpack, so the files that ship are the files in the repo.

### Architecture

The block and the shortcode never render independently — both funnel into a
single static method so their output is guaranteed identical:

```
Block (block.json → render.php) ─┐
                                 ├─→ Block::render_viewer( array $atts ): string
Shortcode::render() ─────────────┘
```

`Shortcode::render()` maps its `snake_case` attributes onto the `camelCase` block
attribute names before delegating, so `source_format` ↔ `sourceFormat`,
`src_right` ↔ `srcRight`, `border` ↔ `borderEnabled`, and so on.

`render_viewer()` merges the incoming attributes over `Settings::get_defaults()`,
validates every value (URLs, enums, CSS lengths and colours), and then picks one
of two render paths:

| Condition | Path | Output |
| --------- | ---- | ------ |
| `display_mode` ∈ `anaglyph-rb`, `interlaced-row`, `interlaced-col`, `side-by-side`, `top-bottom` | Canvas | `<canvas class="sterimvi-canvas" data-…>` |
| `source_format` ∈ `anaglyph-rb`, `interlaced-row`, `interlaced-col` | Canvas | as above |
| `source_squeeze` is on (needs unsqueezing) | Canvas | as above |
| anything else | stereo-img | `<stereo-img src type flat controlslist>` |

Both are wrapped in `<div class="sterimvi-wrapper" style="…">` carrying the
width, border and box-shadow. The routing constants live on `Block`
(`CANVAS_MODES`, `CANVAS_SOURCE_FORMATS`) and are mirrored in `block-editor.js`
so the editor preview matches the front end — **change both together**.

On the Canvas path, PHP emits only data attributes; `assets/js/viewer-init.js`
finds each `.sterimvi-wrapper canvas.sterimvi-canvas`, reads
`data-display-mode`, and dispatches to `window.SterimviRenderer`:

```js
SterimviRenderer.loadImage( url, crossOrigin )   // → Promise<HTMLImageElement>
SterimviRenderer.splitLeftRight( img, swap, squeezed )
SterimviRenderer.splitTopBottom( img, swap, squeezed )
SterimviRenderer.splitPair( leftImg, rightImg, swap )
SterimviRenderer.getSplitFromCanvas( canvas )    // reads the data-* attributes
SterimviRenderer.renderSideBySide( left, right, canvas, squeeze )
SterimviRenderer.renderTopBottom( left, right, canvas, squeeze )
SterimviRenderer.renderAnaglyphRB( left, right, canvas )
SterimviRenderer.renderInterlacedRows( left, right, canvas )
SterimviRenderer.renderInterlacedCols( left, right, canvas )
SterimviRenderer.showCanvasError( canvas, message )
```

Because this path reads pixels back out of the canvas, source images must be
same-origin or CORS-enabled — hence the FAQ caveat about external URLs.

### Settings

All options live in a single serialised array under the `sterimvi_settings`
option key, registered in the `sterimvi_settings_group` group.

```php
use Nductiv\StereoscopicImageViewer\Settings;

$defaults = Settings::get_defaults();   // saved values merged over HARDCODED_DEFAULTS
echo $defaults['display_mode'];         // 'anaglyph-rc'
```

`Settings::get_defaults()` is the only supported way to read configuration; it
guarantees a complete array even when the option is missing or partial.
`Settings::HARDCODED_DEFAULTS` defines the shipped fallbacks, and
`Settings::SOURCE_FORMATS`, `::DISPLAY_MODES` and `::LOAD_METHODS` are the
canonical enum lists used for sanitisation on both save and render.

`uninstall.php` removes `sterimvi_settings` on every site in a multisite network.

### Rendering from PHP

To output a viewer from a template or another plugin, call the shortcode:

```php
echo do_shortcode( '[sterimvi_image src="' . esc_url( $url ) . '" display_mode="wiggle"]' );
```

`Block::render_viewer()` escapes all of its own output, so the returned string is
safe to echo directly.

### Local development

There is no build or test toolchain to run — edit the files in place and reload.
`mkzip.sh` assembles the distributable archive:

```bash
./mkzip.sh          # → stereoscopic-image-viewer.zip
```

To syntax-check the PHP without a full WordPress environment:

```bash
for f in *.php includes/*.php admin/*.php; do php -l "$f"; done
```

## Screenshots

1. The Stereoscopic Image block selected in the editor, with the Source Image, Display, and Size & Style panels open in the sidebar
2. The plugin settings page, showing site-wide defaults, the stereo-img load method, and the built-in shortcode reference
3. The Stereoscopic Image block as it appears in the block inserter

## Changelog

### 1.0.0

- Initial release.

## License

Released under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

The bundled [stereo-img](https://github.com/steren/stereo-img) library is distributed under the Apache License 2.0. See `assets/vendor/stereo-img/LICENSE` for the full text. Three.js and exifr are bundled under their respective licences (see `assets/vendor/stereo-img/vendor/three/LICENSE` and `assets/vendor/stereo-img/vendor/exifr/LICENSE`).
