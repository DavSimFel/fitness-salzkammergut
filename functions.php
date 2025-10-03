<?php
/**
 * Theme bootstrap with CPTs, taxonomies, and Ziel filters.
 */

if (! function_exists('fitness_skg_cpt_labels')) {
    /** Provide consistent German labels for post types. */
    function fitness_skg_cpt_labels(string $singular, string $plural): array
    {
        return [
            'name'                     => $plural,
            'singular_name'            => $singular,
            'menu_name'                => $plural,
            'name_admin_bar'           => $singular,
            'add_new'                  => 'Neu hinzufügen',
            'add_new_item'             => sprintf('%s anlegen', $singular),
            'edit_item'                => sprintf('%s bearbeiten', $singular),
            'new_item'                 => sprintf('%s anlegen', $singular),
            'view_item'                => sprintf('%s ansehen', $singular),
            'view_items'               => sprintf('%s ansehen', $plural),
            'search_items'             => sprintf('%s suchen', $plural),
            'not_found'                => sprintf('Keine %s gefunden', $plural),
            'not_found_in_trash'       => sprintf('Keine %s im Papierkorb', $plural),
            'all_items'                => sprintf('Alle %s', $plural),
            'archives'                 => sprintf('%s-Archiv', $singular),
            'attributes'               => sprintf('%s-Eigenschaften', $singular),
            'insert_into_item'         => 'In Eintrag einfügen',
            'uploaded_to_this_item'    => 'Zu diesem Eintrag hochgeladen',
            'filter_items_list'        => sprintf('%s filtern', $plural),
            'items_list'               => sprintf('%s-Liste', $plural),
            'items_list_navigation'    => sprintf('%s-Navigation', $plural),
            'item_published'           => sprintf('%s veröffentlicht', $singular),
            'item_updated'             => sprintf('%s aktualisiert', $singular),
            'parent_item_colon'        => sprintf('%s übergeordnet:', $singular),
        ];
    }
}

if (! function_exists('fitness_skg_tax_labels')) {
    /** Provide consistent German labels for taxonomies. */
    function fitness_skg_tax_labels(string $singular, string $plural): array
    {
        return [
            'name'              => $plural,
            'singular_name'     => $singular,
            'menu_name'         => $plural,
            'all_items'         => sprintf('Alle %s', $plural),
            'edit_item'         => sprintf('%s bearbeiten', $singular),
            'view_item'         => sprintf('%s ansehen', $singular),
            'update_item'       => sprintf('%s aktualisieren', $singular),
            'add_new_item'      => sprintf('%s hinzufügen', $singular),
            'new_item_name'     => sprintf('Neuer Name für %s', $singular),
            'separate_items_with_commas' => sprintf('%s mit Kommas trennen', $plural),
            'add_or_remove_items'        => sprintf('%s hinzufügen oder entfernen', $plural),
            'choose_from_most_used'      => 'Aus den häufigsten wählen',
            'search_items'        => sprintf('%s durchsuchen', $plural),
            'not_found'           => sprintf('Keine %s gefunden', $plural),
            'parent_item'         => sprintf('%s übergeordnet', $singular),
            'parent_item_colon'   => sprintf('%s übergeordnet:', $singular),
        ];
    }
}

add_action('after_setup_theme', function (): void {
    add_theme_support('editor-styles');
    add_editor_style('build/tw.css');
});

function tailwind($hook) {
     $tailwind_path = get_stylesheet_directory() . '/build/tw.css';
    if (! file_exists($tailwind_path)) {
        return;
    }

    $theme    = wp_get_theme();
    $version  = $theme->get('Version') ?: filemtime($tailwind_path);
    $tailwind = get_stylesheet_directory_uri() . '/build/tw.css';

    wp_enqueue_style('fitness-skg-tailwind', $tailwind, [], $version);
}
add_action( 'admin_enqueue_scripts', 'tailwind' );
add_action('wp_enqueue_scripts', 'tailwind');

add_action('init', function () {
    $supports = ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'];

    $studio_labels = fitness_skg_cpt_labels('Studio', 'Studios');
    register_post_type('studio', [
        'labels'          => $studio_labels,
        'label'           => $studio_labels['name'],
        'public'          => true,
        'has_archive'     => true,
        'rewrite'         => ['slug' => 'studios'],
        'show_in_rest'    => true,
        'supports'        => $supports,
        'menu_icon'       => 'dashicons-location-alt',
    ]);

    $course_labels = fitness_skg_cpt_labels('Kurs', 'Kurse');
    register_post_type('kurs', [
        'labels'          => $course_labels,
        'label'           => $course_labels['name'],
        'public'          => true,
        'has_archive'     => true,
        'rewrite'         => ['slug' => 'kurse'],
        'show_in_rest'    => true,
        'supports'        => $supports,
        'menu_icon'       => 'dashicons-calendar-alt',
    ]);

    $tarif_labels = fitness_skg_cpt_labels('Tarif', 'Tarife');
    register_post_type('tarif', [
        'labels'             => $tarif_labels,
        'label'              => $tarif_labels['name'],
        'public'             => true,
        'has_archive'        => false,
        'publicly_queryable' => false,
        'exclude_from_search'=> true,
        'show_in_rest'       => true,
        'supports'           => $supports,
        'menu_icon'          => 'dashicons-money',
    ]);

    $team_labels = fitness_skg_cpt_labels('Teammitglied', 'Teammitglieder');
    register_post_type('team', [
        'labels'       => $team_labels,
        'label'        => $team_labels['name'],
        'public'       => true,
        'has_archive'  => false,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail'],
        'menu_icon'    => 'dashicons-groups',
    ]);

    $testimonial_labels = fitness_skg_cpt_labels('Erfahrungsbericht', 'Erfahrungsberichte');
    register_post_type('testimonial', [
        'labels'       => $testimonial_labels,
        'label'        => $testimonial_labels['name'],
        'public'       => true,
        'has_archive'  => false,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
        'menu_icon'    => 'dashicons-format-quote',
    ]);
});

add_action('init', function () {
    $capabilities = [
        'manage_terms' => 'manage_categories',
        'edit_terms'   => 'manage_categories',
        'delete_terms' => 'manage_categories',
        'assign_terms' => 'edit_posts',
    ];

    $studio_brand_labels = fitness_skg_tax_labels('Studiozuordnung', 'Studiozuordnungen');
    register_taxonomy('studio_brand', ['studio', 'kurs', 'tarif', 'post', 'page', 'testimonial', 'team'], [
        'labels'       => $studio_brand_labels,
        'label'        => $studio_brand_labels['name'],
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'rewrite'      => ['slug' => 'studio'],
        'meta_box_cb'  => 'post_categories_meta_box',
        'capabilities' => $capabilities,
    ]);

    $ziel_topic_labels = fitness_skg_tax_labels('Ziel', 'Ziele');
    register_taxonomy('ziel_topic', ['post', 'page', 'kurs', 'tarif', 'testimonial'], [
        'labels'       => $ziel_topic_labels,
        'label'        => $ziel_topic_labels['name'],
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'ziel-topic'],
        'capabilities' => $capabilities,
    ]);

    $raum_labels = fitness_skg_tax_labels('Raum', 'Raeume');
    register_taxonomy('raum', ['kurs', 'studio'], [
        'labels'       => $raum_labels,
        'label'        => $raum_labels['name'],
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'raum'],
        'capabilities' => $capabilities,
    ]);

    $ausstattung_labels = fitness_skg_tax_labels('Ausstattung', 'Ausstattungen');
    register_taxonomy('ausstattung', ['studio'], [
        'labels'       => $ausstattung_labels,
        'label'        => $ausstattung_labels['name'],
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'ausstattung'],
        'capabilities' => $capabilities,
    ]);

    $kurs_kategorie_labels = fitness_skg_tax_labels('Kurskategorie', 'Kurskategorien');
    register_taxonomy('kurs_kategorie', ['kurs'], [
        'labels'       => $kurs_kategorie_labels,
        'label'        => $kurs_kategorie_labels['name'],
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'kurs-kategorie'],
        'capabilities' => $capabilities,
    ]);

    $level_labels = fitness_skg_tax_labels('Level', 'Level');
    register_taxonomy('level', ['kurs'], [
        'labels'       => $level_labels,
        'label'        => $level_labels['name'],
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'rewrite'      => ['slug' => 'level'],
        'capabilities' => $capabilities,
    ]);

    $wochentag_labels = fitness_skg_tax_labels('Wochentag', 'Wochentage');
    register_taxonomy('wochentag', ['kurs'], [
        'labels'       => $wochentag_labels,
        'label'        => $wochentag_labels['name'],
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'wochentag'],
        'capabilities' => $capabilities,
    ]);

    $tageszeit_labels = fitness_skg_tax_labels('Tageszeit', 'Tageszeiten');
    register_taxonomy('tageszeit', ['kurs'], [
        'labels'       => $tageszeit_labels,
        'label'        => $tageszeit_labels['name'],
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
