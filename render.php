<?php
/**
 * Block server-side render callback.
 *
 * WordPress passes block attributes as $attributes and block content as $content.
 *
 * @package Nductiv\StereoscopicImageViewer
 * @var array  $attributes Block attribute values.
 * @var string $content    Inner block content (unused — this block has no inner blocks).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Autoload may not have run if this file is executed outside of the plugin bootstrap.
if ( ! class_exists( 'Nductiv\\StereoscopicImageViewer\\Block' ) ) {
	require_once __DIR__ . '/includes/class-settings.php';
	require_once __DIR__ . '/includes/class-block.php';
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_viewer() escapes all output internally.
echo Nductiv\StereoscopicImageViewer\Block::render_viewer( $attributes );
