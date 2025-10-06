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

require_once __DIR__ . '/inc/reviews.php';

function fitness_skg_enqueue_tailwind(): void
{
    $tailwind_path = get_stylesheet_directory() . '/build/tw.css';
    if (! file_exists($tailwind_path)) {
        return;
    }

    $theme    = wp_get_theme();
    $version  = $theme->get('Version') ?: filemtime($tailwind_path);
    $tailwind = get_stylesheet_directory_uri() . '/build/tw.css';

    wp_enqueue_style('fitness-skg-tailwind', $tailwind, [], $version);
}
add_action('wp_enqueue_scripts', 'fitness_skg_enqueue_tailwind');
add_action('enqueue_block_assets', 'fitness_skg_enqueue_tailwind');

function fitness_skg_enqueue_theme_styles(): void
{
    $style_path = get_stylesheet_directory() . '/style.css';
    if (! file_exists($style_path)) {
        return;
    }

    $theme        = wp_get_theme();
    $style_uri    = get_stylesheet_uri();
    $style_version = $theme->get('Version') ?: filemtime($style_path);

    wp_enqueue_style(
        'fitness-skg-style',
        $style_uri,
        ['fitness-skg-tailwind'],
        $style_version
    );
}
add_action('wp_enqueue_scripts', 'fitness_skg_enqueue_theme_styles');
add_action('enqueue_block_assets', 'fitness_skg_enqueue_theme_styles');

add_action('enqueue_block_editor_assets', function (): void {
    $theme_version = wp_get_theme()->get('Version') ?: '1.0.0';

    $editor_script_path = get_stylesheet_directory() . '/assets/admin/featured-video-editor.js';
    if (file_exists($editor_script_path)) {
        wp_enqueue_script(
            'fitness-skg-featured-video-editor',
            get_stylesheet_directory_uri() . '/assets/admin/featured-video-editor.js',
            ['wp-data', 'wp-element', 'wp-plugins', 'wp-block-editor'],
            filemtime($editor_script_path) ?: $theme_version,
            true
        );
    }
});

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

if (! defined('FITNESS_SKG_FEATURED_VIDEO_META_KEY')) {
    define('FITNESS_SKG_FEATURED_VIDEO_META_KEY', '_featured_video_id');
}

add_action('init', function (): void {
    register_post_meta('', FITNESS_SKG_FEATURED_VIDEO_META_KEY, [
        'type'          => 'integer',
        'single'        => true,
        'show_in_rest'  => true,
        'auth_callback' => static function () {
            return current_user_can('edit_posts');
        },
    ]);
});

add_action('add_meta_boxes', function (): void {
    add_meta_box(
        'fitness_skg_featured_video',
        __('Featured Video', 'fitness-skg'),
        'fitness_skg_render_featured_video_metabox',
        ['post', 'page'],
        'side',
        'low'
    );
});

function fitness_skg_render_featured_video_metabox(WP_Post $post): void
{
    $video_id  = (int) get_post_meta($post->ID, FITNESS_SKG_FEATURED_VIDEO_META_KEY, true);
    $thumb_id  = get_post_thumbnail_id($post);
    $video_url = $video_id ? wp_get_attachment_url($video_id) : '';
    $poster    = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';

    wp_nonce_field('fitness_skg_save_featured_video', 'fitness_skg_featured_video_nonce');
    ?>
    <div id="fitness-skg-featured-video-box" class="fitness-skg-featured-video-box">
        <p>
            <input type="hidden" id="fitness_skg_featured_video_id" name="fitness_skg_featured_video_id" value="<?php echo esc_attr($video_id); ?>">
            <button type="button" class="button button-secondary" id="fitness_skg_featured_video_set">
                <?php echo $video_id ? esc_html__('Replace featured video', 'fitness-skg') : esc_html__('Set featured video', 'fitness-skg'); ?>
            </button>
            <button type="button" class="button link-button" id="fitness_skg_featured_video_remove" <?php if (! $video_id) { echo 'style="display:none"'; } ?>>
                <?php esc_html_e('Remove', 'fitness-skg'); ?>
            </button>
        </p>

        <div id="fitness_skg_featured_video_preview" style="display:<?php echo $video_id ? 'block' : 'none'; ?>;">
            <video style="max-width:100%;height:auto;" controls playsinline <?php echo $poster ? 'poster="' . esc_url($poster) . '"' : ''; ?>>
                <source src="<?php echo esc_url($video_url); ?>" type="<?php echo esc_attr(get_post_mime_type($video_id) ?: 'video/mp4'); ?>">
            </video>
            <?php if ($video_id) : ?>
                <p style="margin-top:6px;"><a href="<?php echo esc_url(get_edit_post_link($video_id)); ?>"><?php esc_html_e('Edit video', 'fitness-skg'); ?></a></p>
            <?php endif; ?>
        </div>

        <p class="description">
            <?php esc_html_e('Tip: If you also set a Featured Image, it will be used as the video poster automatically.', 'fitness-skg'); ?>
        </p>
    </div>
    <?php
}

add_action('save_post', function (int $post_id): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (! isset($_POST['fitness_skg_featured_video_nonce']) || ! wp_verify_nonce($_POST['fitness_skg_featured_video_nonce'], 'fitness_skg_save_featured_video')) {
        return;
    }

    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $new_id = isset($_POST['fitness_skg_featured_video_id']) ? (int) $_POST['fitness_skg_featured_video_id'] : 0;

    if ($new_id) {
        $mime = get_post_mime_type($new_id);
        if (strpos((string) $mime, 'video/') !== 0) {
            $new_id = 0;
        }
    }

    if ($new_id) {
        update_post_meta($post_id, FITNESS_SKG_FEATURED_VIDEO_META_KEY, $new_id);
    } else {
        delete_post_meta($post_id, FITNESS_SKG_FEATURED_VIDEO_META_KEY);
    }
});

add_action('admin_enqueue_scripts', function (string $hook): void {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    wp_enqueue_media();

    $script_path = get_stylesheet_directory() . '/assets/admin/featured-video.js';
    $script_url  = get_stylesheet_directory_uri() . '/assets/admin/featured-video.js';
    $version     = file_exists($script_path) ? filemtime($script_path) : wp_get_theme()->get('Version');

    wp_enqueue_script(
        'fitness-skg-featured-video',
        $script_url,
        ['jquery'],
        $version ?: '1.0.0',
        true
    );
});

function fitness_skg_get_featured_media_html(?int $post_id = null, $size = 'post-thumbnail', array $attrs = []): string
{
    $post_id = $post_id ?: get_the_ID();
    $video_id = (int) get_post_meta($post_id, FITNESS_SKG_FEATURED_VIDEO_META_KEY, true);

    if ($video_id) {
        $src        = wp_get_attachment_url($video_id);
        $mime       = get_post_mime_type($video_id) ?: 'video/mp4';
        $thumb_id   = get_post_thumbnail_id($post_id);
        $poster_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, $size) : '';

        $attr_html = '';
        foreach ($attrs as $key => $value) {
            $attr_html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        return sprintf(
            '<video class="featured-video" controls playsinline preload="metadata"%s%s><source src="%s" type="%s"></video>',
            $poster_url ? ' poster="' . esc_url($poster_url) . '"' : '',
            $attr_html,
            esc_url($src),
            esc_attr($mime)
        );
    }

    return get_the_post_thumbnail($post_id, $size, $attrs);
}

add_filter('render_block', function ($block_content, $block) {
    if (($block['blockName'] ?? null) !== 'core/cover') {
        return $block_content;
    }

    $attrs = $block['attrs'] ?? [];
    if (empty($attrs['useFeaturedImage'])) {
        return $block_content;
    }

    $post_id = $block['context']['postId'] ?? get_the_ID();
    if (! $post_id) {
        return $block_content;
    }

    $video_id = (int) get_post_meta($post_id, FITNESS_SKG_FEATURED_VIDEO_META_KEY, true);
    if (! $video_id) {
        return $block_content;
    }

    // If the block already has a video background (manually set), leave it untouched.
    if (strpos($block_content, 'wp-block-cover__video-background') !== false) {
        return $block_content;
    }

    $video_url = wp_get_attachment_url($video_id);
    if (! $video_url) {
        return $block_content;
    }

    $poster_id = get_post_thumbnail_id($post_id);
    $poster_url = $poster_id ? wp_get_attachment_image_url($poster_id, 'full') : '';

    $create_video_markup = static function (?string $style_attr, ?string $object_fit) use ($video_url, $poster_url, $attrs): string {
        $boolean_attrs = ['autoplay', 'muted', 'loop', 'playsinline'];
        $attributes = [
            'class'         => 'wp-block-cover__video-background intrinsic-ignore',
            'autoplay'      => true,
            'muted'         => true,
            'loop'          => true,
            'playsinline'   => true,
            'preload'       => 'metadata',
            'src'           => esc_url($video_url),
            'data-object-fit' => $object_fit ?: 'cover',
        ];

        if ($poster_url) {
            $attributes['poster'] = esc_url($poster_url);
        }

        if ($style_attr) {
            $attributes['style'] = $style_attr;
        } elseif (isset($attrs['focalPoint']['x'], $attrs['focalPoint']['y'])) {
            $format_percent = static function (float $value): string {
                $value = max(0, min(100, $value));
                $formatted = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
                return $formatted === '' ? '0' : $formatted;
            };

            $x = $format_percent((float) $attrs['focalPoint']['x'] * 100);
            $y = $format_percent((float) $attrs['focalPoint']['y'] * 100);
            $attributes['style'] = sprintf('object-position:%s%% %s%%;', $x, $y);
        }

        $parts = [];
        foreach ($attributes as $name => $value) {
            if (in_array($name, $boolean_attrs, true)) {
                if ($value) {
                    $parts[] = $name;
                }
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $parts[] = sprintf('%s="%s"', $name, esc_attr($value));
        }

        return '<video ' . implode(' ', $parts) . '></video>';
    };

    $image_replaced = false;

    $replaced_content = preg_replace_callback(
        '#<img[^>]*wp-block-cover__image-background[^>]*>#',
        static function (array $matches) use ($create_video_markup, &$image_replaced) {
            $img_tag = $matches[0];
            $style_attr = null;
            $object_fit = null;

            if (preg_match('/style="([^"]*)"/i', $img_tag, $style_match)) {
                $style_attr = $style_match[1];
            }

            if (preg_match('/data-object-fit="([^"]*)"/i', $img_tag, $fit_match)) {
                $object_fit = $fit_match[1];
            }

            $image_replaced = true;

            return $create_video_markup($style_attr, $object_fit);
        },
        $block_content,
        1
    );

    if ($image_replaced) {
        return $replaced_content;
    }

    $background_replaced = false;

    $fallback_content = preg_replace_callback(
        '#(<span[^>]*wp-block-cover__background[^>]*></span>)#',
        static function (array $matches) use ($create_video_markup, &$background_replaced) {
            $background_replaced = true;

            return $matches[0] . $create_video_markup(null, null);
        },
        $block_content,
        1
    );

    if ($background_replaced) {
        return $fallback_content;
    }

    return $block_content . $create_video_markup(null, null);
}, 10, 2);
