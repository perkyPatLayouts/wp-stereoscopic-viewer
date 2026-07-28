# Stereoscopic Image Viewer

> Display stereoscopic (3D) images from the WordPress media library using a Gutenberg block or shortcode.

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?logo=wordpress&logoColor=white)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)]()https://www.gnu.org/licenses/gpl-2.0.html
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
- [Screenshots](#screenshots)
- [Changelog](#changelog)
- [License](#license)

---

## Overview

Stereoscopic Image Viewer lets you embed 3D images on any WordPress post or page. It supports a wide range of source formats used by 3D cameras, phones, and editing software, and can display them in multiple viewing modes.

## Features

- **Gutenberg block** with live Canvas-rendered preview and full `InspectorControls`
- **`[stereo_img]` shortcode** for the classic editor and widget areas
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

1. Upload the `wp-stereoscopic-viewer` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Visit **Stereoscopic** in the admin menu to configure site-wide defaults.
4. Add the **Stereoscopic Image** block in the Gutenberg editor, or use the `[stereo_img]` shortcode.

## Quick Start

The most common case — a side-by-side JPEG from a 3D camera, displayed as red-cyan anaglyph:

```
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc"]
```

Two separate files, wiggle output (no glasses needed):

```
[stereo_img src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="wiggle"]
```

Anamorphic (half-width side-by-side) source, red-cyan output, with border and drop shadow:

```
[stereo_img src="https://example.com/hsbs.jpg" source_format="left-right" source_squeeze="1" display_mode="anaglyph-rc" border="1" border_width="2px" border_color="#222222" shadow="1" shadow_offset_x="0px" shadow_offset_y="6px" shadow_blur="16px" shadow_spread="0px" shadow_color="rgba(0,0,0,0.4)"]
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
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Side-by-side source, wiggle output</b> — no glasses needed</summary>

```
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="wiggle"]
```
</details>

<details>
<summary><b>Top-bottom source, anaglyph output</b></summary>

```
[stereo_img src="https://example.com/photo-tb.jpg" source_format="top-bottom" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Anamorphic (half-width) side-by-side source</b></summary>

Some 3D cameras save a squeezed (HSBS) image where each eye occupies half the frame width. Use `source_squeeze="1"` to unsqueeze it before rendering.

```
[stereo_img src="https://example.com/photo-hsbs.jpg" source_format="left-right" source_squeeze="1" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Anamorphic (half-height) top-bottom source</b></summary>

```
[stereo_img src="https://example.com/photo-htb.jpg" source_format="top-bottom" source_squeeze="1" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Separate left and right images</b></summary>

When you have two individual files — one per eye — use `source_format="pair"` and provide both `src` and `src_right`.

```
[stereo_img src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Separate images, wiggle output</b></summary>

```
[stereo_img src="https://example.com/left.jpg" src_right="https://example.com/right.jpg" source_format="pair" display_mode="wiggle"]
```
</details>

<details>
<summary><b>Sources are stored right-eye-first</b></summary>

If your side-by-side image has the right eye on the left, add `swap="1"` to correct the eye order.

```
[stereo_img src="https://example.com/photo-rls.jpg" source_format="left-right" swap="1" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Anaglyph red-blue output</b> — for red-blue glasses</summary>

```
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rb"]
```
</details>

<details>
<summary><b>Side-by-side composite output</b> — for cross-eye viewing or VR headsets</summary>

The output places the left eye on the left and the right eye on the right.

```
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="side-by-side" width="800px"]
```
</details>

<details>
<summary><b>Squeezed side-by-side output</b> — HSBS for compatible players</summary>

```
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="side-by-side" display_squeeze="1"]
```
</details>

<details>
<summary><b>Anaglyph red-cyan source displayed as-is</b></summary>

If your source image is already an anaglyph red-cyan composite, set both source and display to `anaglyph-rc`.

```
[stereo_img src="https://example.com/photo-anaglyph.jpg" source_format="anaglyph-rc" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Row-interlaced source, anaglyph output</b></summary>

Row-interlaced images (common on polarised 3D monitor captures) can be decoded and re-displayed in any mode.

```
[stereo_img src="https://example.com/photo-interlaced.jpg" source_format="interlaced-row" display_mode="anaglyph-rc"]
```
</details>

<details>
<summary><b>Fixed width</b></summary>

```
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" width="640px"]
```
</details>

<details>
<summary><b>Responsive width capped at 80% of viewport</b></summary>

```
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" width="80vw"]
```
</details>

<details>
<summary><b>With border and drop shadow</b></summary>

```
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" border="1" border_width="2px" border_color="#333333" shadow="1" shadow_offset_x="0px" shadow_offset_y="6px" shadow_blur="16px" shadow_spread="0px" shadow_color="rgba(0,0,0,0.35)"]
```
</details>

<details>
<summary><b>Limit which viewer control buttons appear</b></summary>

Show only the wiggle and anaglyph buttons; hide the left/right-eye buttons:

```
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="anaglyph-rc" controlslist="wiggle anaglyph"]
```

Show no controls at all (viewer is static, no mode switching):

```
[stereo_img src="https://example.com/photo-sbs.jpg" source_format="left-right" display_mode="wiggle" controlslist=""]
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

## Screenshots

1. Gutenberg block in the editor with `InspectorControls` open
2. Anaglyph red-cyan display mode on the front end
3. Plugin settings page

## Changelog

### 1.0.0

- Initial release.

## License

Released under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

The bundled [stereo-img](https://github.com/steren/stereo-img) library is distributed under the Apache License 2.0. See `assets/vendor/stereo-img/LICENSE` for the full text. Three.js and exifr are bundled under their respective licences (see `assets/vendor/stereo-img/vendor/three/LICENSE` and `assets/vendor/stereo-img/vendor/exifr/LICENSE`).
