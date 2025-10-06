<?php
/**
 * Google review helpers, settings, and dynamic blocks.
 */

if (! defined('ABSPATH')) {
    exit;
}

define('FITNESS_SKG_REVIEWS_API_OPTION', 'fitness_skg_reviews_api_key');
define('FITNESS_SKG_PLACE_ID_META_KEY', '_fitness_skg_google_place_id');

fitness_skg_bootstrap_reviews();

function fitness_skg_bootstrap_reviews(): void
{
    add_action('admin_init', 'fitness_skg_register_reviews_settings');
    add_action('add_meta_boxes', 'fitness_skg_register_place_id_metabox');
    add_action('save_post', 'fitness_skg_save_place_id_meta');
    add_action('init', 'fitness_skg_register_place_id_meta');
    add_action('init', 'fitness_skg_register_review_blocks');
}

function fitness_skg_register_place_id_meta(): void
{
    register_post_meta('studio', FITNESS_SKG_PLACE_ID_META_KEY, [
        'type'              => 'string',
        'single'            => true,
        'sanitize_callback' => 'fitness_skg_sanitize_place_id',
        'show_in_rest'      => [
            'schema' => [
                'type' => 'string',
            ],
        ],
        'auth_callback'     => static function () {
            return current_user_can('edit_posts');
        },
    ]);
}

function fitness_skg_register_reviews_settings(): void
{
    register_setting('general', FITNESS_SKG_REVIEWS_API_OPTION, [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ]);

    add_settings_field(
        'fitness_skg_reviews_api_key_field',
        __('Google Places API Key', 'fitness-skg'),
        'fitness_skg_render_reviews_api_field',
        'general',
        'default'
    );
}

function fitness_skg_render_reviews_api_field(): void
{
    $value = get_option(FITNESS_SKG_REVIEWS_API_OPTION, '');
    echo '<input type="text" id="fitness_skg_reviews_api_key" name="' . esc_attr(FITNESS_SKG_REVIEWS_API_OPTION) . '" value="' . esc_attr($value) . '" class="regular-text" autocomplete="off" />';
    echo '<p class="description">' . esc_html__('Use a restricted Places API key (Details API enabled).', 'fitness-skg') . '</p>';
}

function fitness_skg_register_place_id_metabox(): void
{
    add_meta_box(
        'fitness_skg_place_id',
        __('Google Place ID', 'fitness-skg'),
        'fitness_skg_render_place_id_metabox',
        'studio',
        'side',
        'default'
    );
}

function fitness_skg_render_place_id_metabox(WP_Post $post): void
{
    wp_nonce_field('fitness_skg_save_place_id', 'fitness_skg_place_id_nonce');
    $value = get_post_meta($post->ID, FITNESS_SKG_PLACE_ID_META_KEY, true);
    echo '<p><label for="fitness_skg_place_id_input">' . esc_html__('Copy the Place ID from Google Places.', 'fitness-skg') . '</label></p>';
    echo '<input type="text" id="fitness_skg_place_id_input" name="fitness_skg_place_id" value="' . esc_attr($value) . '" class="widefat" />';
    echo '<p class="description">' . esc_html__('Example: ChIJp0lN6FDmc0cRKoZzTxqHwX0', 'fitness-skg') . '</p>';
}

function fitness_skg_save_place_id_meta(int $post_id): void
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (! isset($_POST['fitness_skg_place_id_nonce']) || ! wp_verify_nonce($_POST['fitness_skg_place_id_nonce'], 'fitness_skg_save_place_id')) {
        return;
    }

    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $raw = isset($_POST['fitness_skg_place_id']) ? wp_unslash($_POST['fitness_skg_place_id']) : '';
    $value = fitness_skg_sanitize_place_id($raw);

    if ($value) {
        update_post_meta($post_id, FITNESS_SKG_PLACE_ID_META_KEY, $value);
    } else {
        delete_post_meta($post_id, FITNESS_SKG_PLACE_ID_META_KEY);
    }
}

function fitness_skg_sanitize_place_id(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    return preg_replace('/[^A-Za-z0-9_\-]/', '', $value);
}

function fitness_skg_get_place_rating_data(?string $place_id): array
{
    if (! $place_id) {
        return ['rating' => null, 'count' => null, 'fetched_at' => null];
    }

    $cache_key = 'fitness_skg_place_' . md5($place_id);
    $cached = get_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }

    $fetched = fitness_skg_fetch_place_rating($place_id);
    if (is_wp_error($fetched)) {
        $fallback = get_option('fitness_skg_fallback_' . $cache_key);
        if (is_array($fallback)) {
            return $fallback;
        }

        return ['rating' => null, 'count' => null, 'fetched_at' => null];
    }

    set_transient($cache_key, $fetched, 15 * MINUTE_IN_SECONDS);
    update_option('fitness_skg_fallback_' . $cache_key, $fetched, false);

    return $fetched;
}

function fitness_skg_fetch_place_rating(string $place_id)
{
    $api_key = trim((string) get_option(FITNESS_SKG_REVIEWS_API_OPTION));
    if ($api_key === '') {
        return new WP_Error('fitness_skg_missing_api_key', __('Google Places API key not configured.', 'fitness-skg'));
    }

    $encoded_id = rawurlencode($place_id);
    $endpoint   = sprintf('https://places.googleapis.com/v1/places/%s', $encoded_id);

    $response = wp_remote_get($endpoint, [
        'timeout' => 8,
        'headers' => [
            'X-Goog-Api-Key'    => $api_key,
            'X-Goog-FieldMask'  => 'rating,userRatingCount',
        ],
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        return new WP_Error('fitness_skg_bad_status', sprintf(__('Unexpected HTTP status: %d', 'fitness-skg'), $code));
    }

    $body = wp_remote_retrieve_body($response);
    if (! $body) {
        return new WP_Error('fitness_skg_empty_body', __('Empty response from Places API.', 'fitness-skg'));
    }

    $data = json_decode($body, true);
    if (! is_array($data)) {
        return new WP_Error('fitness_skg_json_error', __('Invalid JSON in Places API response.', 'fitness-skg'));
    }

    if (isset($data['error'])) {
        $status         = $data['error']['status'] ?? 'UNKNOWN';
        $error_message  = $data['error']['message'] ?? '';
        $details        = $error_message ? sprintf('%s — %s', (string) $status, $error_message) : (string) $status;

        return new WP_Error('fitness_skg_api_error', sprintf(__('Places API error: %s', 'fitness-skg'), $details));
    }

    $rating = isset($data['rating']) ? (float) $data['rating'] : null;
    $count  = isset($data['userRatingCount']) ? (int) $data['userRatingCount'] : null;

    return [
        'rating'     => $rating,
        'count'      => $count,
        'fetched_at' => time(),
    ];
}

function fitness_skg_get_current_context_place_id(array $block_context = []): ?string
{
    if (! empty($block_context['placeId'])) {
        return fitness_skg_sanitize_place_id((string) $block_context['placeId']);
    }

    $post_id = $block_context['postId'] ?? get_the_ID();
    if ($post_id) {
        $meta = get_post_meta((int) $post_id, FITNESS_SKG_PLACE_ID_META_KEY, true);
        if ($meta) {
            return fitness_skg_sanitize_place_id((string) $meta);
        }
    }

    return null;
}

function fitness_skg_register_review_blocks(): void
{
    $dir = get_stylesheet_directory();
    $editor_script = $dir . '/assets/blocks/reviews.js';
    $editor_handle = null;
    if (file_exists($editor_script)) {
        wp_register_script(
            'fitness-skg-review-blocks-editor',
            get_stylesheet_directory_uri() . '/assets/blocks/reviews.js',
            ['wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-data', 'wp-server-side-render'],
            filemtime($editor_script) ?: wp_get_theme()->get('Version'),
            true
        );
        $editor_handle = 'fitness-skg-review-blocks-editor';
    }

    $rating_args = [
        'api_version'     => 2,
        'category'        => 'widgets',
        'icon'            => 'star-filled',
        'render_callback' => 'fitness_skg_render_rating_badge_block',
        'attributes'      => [
            'placeId' => [
                'type'    => 'string',
                'default' => '',
            ],
            'showLogo' => [
                'type'    => 'boolean',
                'default' => true,
            ],
            'size' => [
                'type'    => 'string',
                'default' => 'medium',
            ],
        ],
        'supports' => [
            'html' => false,
        ],
    ];

    if ($editor_handle) {
        $rating_args['editor_script'] = $editor_handle;
    }

    register_block_type('fitness/rating-badge', $rating_args);

    $review_args = [
        'api_version'     => 2,
        'category'        => 'widgets',
        'icon'            => 'format-quote',
        'render_callback' => 'fitness_skg_render_review_card_block',
        'attributes'      => [
            'testimonialId' => [
                'type'    => 'integer',
                'default' => 0,
            ],
            'placeId' => [
                'type'    => 'string',
                'default' => '',
            ],
            'showStars' => [
                'type'    => 'boolean',
                'default' => true,
            ],
        ],
        'supports' => [
            'html' => false,
        ],
    ];

    if ($editor_handle) {
        $review_args['editor_script'] = $editor_handle;
    }

    register_block_type('fitness/review-card', $review_args);
}

function fitness_skg_render_rating_badge_block(array $attributes, string $content, $block): string
{
    $place_id = fitness_skg_sanitize_place_id($attributes['placeId'] ?? '') ?: fitness_skg_get_current_context_place_id($block->context ?? []);
    $data = fitness_skg_get_place_rating_data($place_id);

    $rating = $data['rating'];
    $count  = $data['count'];

    $size_class = 'rating-badge--' . sanitize_html_class($attributes['size'] ?? 'medium');

    $classes = ['fitness-rating-badge', $size_class];
    if ($rating === null || $count === null) {
        $classes[] = 'is-empty';
    }

    $stars_html = fitness_skg_render_star_icons($rating);

    $rating_text = $rating !== null ? number_format_i18n((float) $rating, 1) : '—';
    $count_text  = $count !== null ? sprintf('(%s)', number_format_i18n((int) $count)) : __('Keine Bewertungen', 'fitness-skg');

    $logo_html = '';
    if (! empty($attributes['showLogo'])) {
        $logo_html = '<span class="fitness-rating-badge__logo" aria-hidden="true">Google</span>';
    }

    $label = $rating !== null && $count !== null
        ? sprintf(__('Google-Bewertung %.1f von 5 Sternen, %s Rezensionen', 'fitness-skg'), (float) $rating, number_format_i18n((int) $count))
        : __('Google-Bewertung derzeit nicht verfügbar', 'fitness-skg');

    $output  = '<div class="' . esc_attr(implode(' ', array_filter($classes))) . '" role="img" aria-label="' . esc_attr($label) . '">';
    $output .= '<span class="fitness-rating-badge__stars" aria-hidden="true">' . $stars_html . '</span>';
    $output .= '<span class="fitness-rating-badge__values"><span class="fitness-rating-badge__rating">' . esc_html($rating_text) . '</span> <span class="fitness-rating-badge__count">' . esc_html($count_text) . '</span></span>';
    $output .= $logo_html;
    $output .= '</div>';

    if ($rating !== null && $count !== null && $place_id) {
        $output .= fitness_skg_render_rating_json_ld($block, $rating, $count);
    }

    return $output;
}

function fitness_skg_render_star_icons(?float $rating): string
{
    if ($rating === null) {
        return str_repeat('<span class="fitness-rating-badge__star is-empty" aria-hidden="true">★</span>', 5);
    }

    $rounded = round($rating * 2) / 2;
    $icons   = [];

    for ($i = 1; $i <= 5; $i++) {
        if ($rounded >= $i) {
            $icons[] = '<span class="fitness-rating-badge__star is-full">★</span>';
            continue;
        }

        if (($rounded + 0.5) >= $i) {
            $icons[] = '<span class="fitness-rating-badge__star is-half">★</span>';
            continue;
        }

        $icons[] = '<span class="fitness-rating-badge__star is-empty">★</span>';
    }

    return implode('', $icons);
}

function fitness_skg_render_rating_json_ld($block, float $rating, int $count): string
{
    $post_id = $block->context['postId'] ?? get_the_ID();
    if (! $post_id) {
        return '';
    }

    static $output_for = [];
    if (isset($output_for[$post_id])) {
        return '';
    }

    $output_for[$post_id] = true;

    $data = [
        '@context'        => 'https://schema.org',
        '@type'           => 'LocalBusiness',
        'name'            => get_the_title($post_id),
        'url'             => get_permalink($post_id),
        'aggregateRating' => [
            '@type'       => 'AggregateRating',
            'ratingValue' => round($rating, 1),
            'reviewCount' => (int) $count,
        ],
    ];

    return '<script type="application/ld+json">' . wp_json_encode($data) . '</script>';
}

function fitness_skg_render_review_card_block(array $attributes, string $content, $block): string
{
    $testimonial_id = isset($attributes['testimonialId']) ? (int) $attributes['testimonialId'] : 0;
    if (! $testimonial_id) {
        return '<div class="fitness-review-card is-empty">' . esc_html__('Bitte wähle einen Erfahrungsbericht aus.', 'fitness-skg') . '</div>';
    }

    $post = get_post($testimonial_id);
    if (! $post || $post->post_type !== 'testimonial') {
        return '<div class="fitness-review-card is-empty">' . esc_html__('Erfahrungsbericht nicht gefunden.', 'fitness-skg') . '</div>';
    }

    $excerpt = $post->post_excerpt ?: wp_trim_words($post->post_content, 35);
    $date    = get_the_date(get_option('date_format'), $post);
    $name    = get_the_title($post);

    $place_id = fitness_skg_sanitize_place_id($attributes['placeId'] ?? '') ?: fitness_skg_get_current_context_place_id($block->context ?? []);
    $rating_data = ! empty($attributes['showStars']) ? fitness_skg_get_place_rating_data($place_id) : ['rating' => null, 'count' => null];
    $rating_html = '';
    if (! empty($attributes['showStars'])) {
        $rating_label = $rating_data['rating'] !== null
            ? sprintf(
                __('Google-Bewertung %.1f von 5 Sternen', 'fitness-skg'),
                (float) $rating_data['rating']
            )
            : __('Google-Bewertung derzeit nicht verfügbar', 'fitness-skg');

        $stars_classes = ['fitness-review-card__stars'];
        if ($rating_data['rating'] === null) {
            $stars_classes[] = 'is-empty';
        }

        $rating_html = sprintf(
            '<div class="%1$s" aria-label="%2$s">%3$s</div>',
            esc_attr(implode(' ', $stars_classes)),
            esc_attr($rating_label),
            fitness_skg_render_star_icons($rating_data['rating'])
        );
    }

    $image = get_the_post_thumbnail($post, 'thumbnail', ['class' => 'fitness-review-card__image', 'loading' => 'lazy']);
    $image_html = $image ? '<div class="fitness-review-card__photo">' . $image . '</div>' : '';

    $output  = '<article class="fitness-review-card">';
    $output .= $rating_html;
    $output .= '<blockquote class="fitness-review-card__quote">' . wp_kses_post($excerpt) . '</blockquote>';
    $output .= '<footer class="fitness-review-card__footer">';
    $output .= $image_html;
    $output .= '<div class="fitness-review-card__meta">';
    $output .= '<span class="fitness-review-card__name">' . esc_html($name) . '</span>';
    $output .= '<span class="fitness-review-card__date">' . esc_html($date) . '</span>';
    $output .= '</div>';
    $output .= '</footer>';
    $output .= '</article>';

    return $output;
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('reviews refresh', 'fitness_skg_cli_refresh_reviews');
}

function fitness_skg_cli_refresh_reviews(array $args, array $assoc_args): void
{
    if (empty($args[0])) {
        WP_CLI::error('Provide a Place ID or "all".');
    }

    $target = $args[0];

    if ($target === 'all') {
        $place_ids = fitness_skg_get_all_place_ids();
        if (! $place_ids) {
            WP_CLI::warning('No Place IDs found.');
            return;
        }

        foreach ($place_ids as $place_id) {
            fitness_skg_cli_refresh_single($place_id);
        }

        return;
    }

    $place_id = fitness_skg_sanitize_place_id($target);
    if (! $place_id) {
        WP_CLI::error('Invalid Place ID.');
    }

    fitness_skg_cli_refresh_single($place_id);
}

function fitness_skg_cli_refresh_single(string $place_id): void
{
    $cache_key = 'fitness_skg_place_' . md5($place_id);
    delete_transient($cache_key);

    $data = fitness_skg_fetch_place_rating($place_id);
    if (is_wp_error($data)) {
        WP_CLI::warning(sprintf('Failed to refresh %1$s: %2$s', $place_id, $data->get_error_message()));
        return;
    }

    set_transient($cache_key, $data, 15 * MINUTE_IN_SECONDS);
    update_option('fitness_skg_fallback_' . $cache_key, $data, false);

    WP_CLI::success(sprintf('Refreshed %s: %.1f (%d)', $place_id, $data['rating'], $data['count']));
}

function fitness_skg_get_all_place_ids(): array
{
    global $wpdb;
    $meta_key = FITNESS_SKG_PLACE_ID_META_KEY;
    $results = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND pm.meta_value <> '' AND p.post_type = 'studio' AND p.post_status IN ('publish','draft','pending','future','private')",
        $meta_key
    ));

    if (! $results) {
        return [];
    }

    return array_map('fitness_skg_sanitize_place_id', $results);
}
