<?php
/**
 * Theme bootstrap.
 */

add_action('after_setup_theme', function () {
    add_theme_support('editor-styles');

    $tailwind = get_stylesheet_directory() . '/build/tw.css';
    if (file_exists($tailwind)) {
        add_editor_style('build/tw.css');
    }
});

add_action('wp_enqueue_scripts', function () {
    $tailwind_path = get_stylesheet_directory() . '/build/tw.css';
    if (! file_exists($tailwind_path)) {
        return;
    }

    $theme      = wp_get_theme();
    $version    = $theme->get('Version') ?: filemtime($tailwind_path);
    $tailwind   = get_stylesheet_directory_uri() . '/build/tw.css';

    wp_enqueue_style('fitness-skg-tailwind', $tailwind, [], $version);
});
