<?php
/**
 * Theme bootstrap with CPTs, taxonomies, and Ziel filters.
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

    $theme    = wp_get_theme();
    $version  = $theme->get('Version') ?: filemtime($tailwind_path);
    $tailwind = get_stylesheet_directory_uri() . '/build/tw.css';

    wp_enqueue_style('fitness-skg-tailwind', $tailwind, [], $version);
});

add_action('init', function () {
    $supports = ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'];

    register_post_type('studio', [
        'label'           => 'Studios',
        'public'          => true,
        'has_archive'     => true,
        'rewrite'         => ['slug' => 'studios'],
        'show_in_rest'    => true,
        'supports'        => $supports,
    ]);

    register_post_type('kurs', [
        'label'           => 'Kurse',
        'public'          => true,
        'has_archive'     => true,
        'rewrite'         => ['slug' => 'kurse'],
        'show_in_rest'    => true,
        'supports'        => $supports,
    ]);

    register_post_type('tarif', [
        'label'              => 'Tarife',
        'public'             => true,
        'has_archive'        => false,
        'publicly_queryable' => false,
        'exclude_from_search'=> true,
        'show_in_rest'       => true,
        'supports'           => $supports,
    ]);

    register_post_type('team', [
        'label'        => 'Team',
        'public'       => true,
        'has_archive'  => false,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail'],
    ]);

    register_post_type('testimonial', [
        'label'        => 'Testimonials',
        'public'       => true,
        'has_archive'  => false,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
    ]);
});

add_action('init', function () {
    $capabilities = [
        'manage_terms' => 'manage_categories',
        'edit_terms'   => 'manage_categories',
        'delete_terms' => 'manage_categories',
        'assign_terms' => 'edit_posts',
    ];

    register_taxonomy('studio_brand', ['studio', 'kurs', 'tarif', 'post', 'page', 'testimonial', 'team'], [
        'label'        => 'Studio',
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'rewrite'      => ['slug' => 'studio'],
        'capabilities' => $capabilities,
    ]);

    register_taxonomy('ziel_topic', ['post', 'page', 'kurs', 'tarif', 'testimonial'], [
        'label'        => 'Ziel',
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'ziel-topic'],
        'capabilities' => $capabilities,
    ]);

    register_taxonomy('standort', ['studio', 'kurs'], [
        'label'        => 'Standort',
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'standort'],
        'capabilities' => $capabilities,
    ]);

    register_taxonomy('raum', ['kurs', 'studio'], [
        'label'        => 'Raum',
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'raum'],
        'capabilities' => $capabilities,
    ]);

    register_taxonomy('ausstattung', ['studio'], [
        'label'        => 'Ausstattung',
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'ausstattung'],
        'capabilities' => $capabilities,
    ]);

    register_taxonomy('kurs_kategorie', ['kurs'], [
        'label'        => 'Kurs-Kategorie',
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'kurs-kategorie'],
        'capabilities' => $capabilities,
    ]);

    register_taxonomy('level', ['kurs'], [
        'label'        => 'Level',
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'rewrite'      => ['slug' => 'level'],
        'capabilities' => $capabilities,
    ]);

    register_taxonomy('wochentag', ['kurs'], [
        'label'        => 'Wochentag',
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'wochentag'],
        'capabilities' => $capabilities,
    ]);

    register_taxonomy('tageszeit', ['kurs'], [
        'label'        => 'Tageszeit',
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'tageszeit'],
        'capabilities' => $capabilities,
    ]);
});

add_filter('query_vars', function (array $vars) {
    foreach (['ziel', 'studio', 'level', 'testimonials', 'pinned'] as $var) {
        $vars[] = $var;
    }

    return $vars;
});

function site_get_ziel_filters(): stdClass
{
    $query = (object) [
        'ziel'         => get_query_var('ziel'),
        'studio'       => get_query_var('studio'),
        'level'        => get_query_var('level'),
        'testimonials' => get_query_var('testimonials'),
        'pinned'       => (int) get_query_var('pinned') ?: 0,
    ];

    foreach (['ziel', 'studio', 'level'] as $key) {
        if (! empty($query->{$key})) {
            $query->{$key} = array_filter(array_map('sanitize_title', explode(',', (string) $query->{$key})));
        }
    }

    return $query;
}
