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

    $query_args = [
        'fields' => 'rating,userRatingCount',
        'key'    => $api_key,
    ];

    $region_code = substr(get_locale(), -2);
    if ($region_code) {
        $query_args['regionCode'] = strtoupper($region_code);
    }

    $endpoint = add_query_arg($query_args, $endpoint);

    $response = wp_remote_get($endpoint, [
        'timeout' => 8,
        'headers' => [
            'Accept' => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        $body = wp_remote_retrieve_body($response);
        return new WP_Error(
            'fitness_skg_bad_status',
            sprintf(__('Unexpected HTTP status: %1$d — %2$s', 'fitness-skg'), $code, $body ?: __('Empty response', 'fitness-skg'))
        );
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

function fitness_skg_get_place_reviews(string $place_id): array
{
    if ($place_id === '') {
        return [];
    }

    $cache_key = 'fitness_skg_reviews_' . md5($place_id);
    $cached = get_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }

    $fetched = fitness_skg_fetch_place_reviews($place_id);
    if (is_wp_error($fetched)) {
        $fallback = get_option('fitness_skg_reviews_fallback_' . $cache_key);
        if (is_array($fallback)) {
            return $fallback;
        }

        return [];
    }

    set_transient($cache_key, $fetched, 15 * MINUTE_IN_SECONDS);
    update_option('fitness_skg_reviews_fallback_' . $cache_key, $fetched, false);

    return $fetched;
}

function fitness_skg_fetch_place_reviews(string $place_id)
{
    $api_key = trim((string) get_option(FITNESS_SKG_REVIEWS_API_OPTION));
    if ($api_key === '') {
        return new WP_Error('fitness_skg_missing_api_key', __('Google Places API key not configured.', 'fitness-skg'));
    }

    $encoded_id = rawurlencode($place_id);
    $endpoint   = sprintf('https://places.googleapis.com/v1/places/%s', $encoded_id);

    $query_args = [
        'fields' => 'displayName,rating,userRatingCount,reviews.rating,reviews.text,reviews.publishTime,reviews.relativePublishTimeDescription,reviews.authorAttribution.displayName,reviews.authorAttribution.photoUri',
        'key'    => $api_key,
    ];

    $region_code = substr(get_locale(), -2);
    if ($region_code) {
        $query_args['regionCode'] = strtoupper($region_code);
    }

    $endpoint = add_query_arg($query_args, $endpoint);

    $response = wp_remote_get($endpoint, [
        'timeout' => 8,
        'headers' => [
            'Accept' => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        $body = wp_remote_retrieve_body($response);
        return new WP_Error(
            'fitness_skg_bad_status',
            sprintf(__('Unexpected HTTP status: %1$d — %2$s', 'fitness-skg'), $code, $body ?: __('Empty response', 'fitness-skg'))
        );
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

    $place_name = '';
    if (! empty($data['displayName']['text'])) {
        $place_name = (string) $data['displayName']['text'];
    }

    $reviews = [];
    foreach ($data['reviews'] ?? [] as $review) {
        $text = $review['text']['text'] ?? '';
        $rating = isset($review['rating']) ? (float) $review['rating'] : null;

        $publish_time_raw = $review['publishTime'] ?? '';
        $publish_timestamp = $publish_time_raw ? strtotime($publish_time_raw) : 0;

        $reviews[] = [
            'rating'       => $rating,
            'text'         => $text,
            'publish_raw'  => $publish_time_raw,
            'publish_ts'   => $publish_timestamp ?: 0,
            'relative'     => $review['relativePublishTimeDescription'] ?? '',
            'author'       => $review['authorAttribution']['displayName'] ?? '',
            'photo'        => $review['authorAttribution']['photoUri'] ?? '',
        ];
    }

    return [
        'place_id'   => $place_id,
        'place_name' => $place_name,
        'reviews'    => $reviews,
        'fetched_at' => time(),
    ];
}

function fitness_skg_truncate_review_text(string $text, int $max_length): string
{
    $text = trim($text);
    if ($max_length <= 0 || mb_strlen($text, 'UTF-8') <= $max_length) {
        return $text;
    }

    $truncated = mb_substr($text, 0, $max_length, 'UTF-8');
    return rtrim($truncated) . '…';
}

function fitness_skg_parse_place_ids(string $raw_place_ids, $block_context = []): array
{
    $ids = [];

    if ($raw_place_ids !== '') {
        $segments = preg_split('/[\s,]+/', $raw_place_ids);
        foreach ($segments as $segment) {
            $sanitized = fitness_skg_sanitize_place_id((string) $segment);
            if ($sanitized !== '') {
                $ids[] = $sanitized;
            }
        }
    }

    if (! $ids) {
        $context_place_id = fitness_skg_get_current_context_place_id(is_array($block_context) ? $block_context : []);
        if ($context_place_id) {
            $ids[] = $context_place_id;
        }
    }

    return array_values(array_unique($ids));
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
    $theme_dir = get_stylesheet_directory();
    $script_path = $theme_dir . '/assets/blocks/reviews.js';

    if (file_exists($script_path)) {
        wp_register_script(
            'fitness-skg-review-blocks',
            get_stylesheet_directory_uri() . '/assets/blocks/reviews.js',
            ['wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-data', 'wp-server-side-render', 'wp-dom-ready'],
            filemtime($script_path) ?: wp_get_theme()->get('Version'),
            true
        );
    }

    $block_base_dir = $theme_dir . '/blocks';

    $blocks = [
        [
            'path'     => $block_base_dir . '/rating-badge',
            'callback' => 'fitness_skg_render_rating_badge_block',
        ],
        [
            'path'     => $block_base_dir . '/review-card',
            'callback' => 'fitness_skg_render_review_card_block',
        ],
        [
            'path'     => $block_base_dir . '/review-feed',
            'callback' => 'fitness_skg_render_review_feed_block',
        ],
        [
            'path'     => $block_base_dir . '/linked-container',
            'callback' => 'fitness_skg_render_linked_container_block',
        ],
    ];

    foreach ($blocks as $block) {
        $result = register_block_type_from_metadata(
            $block['path'],
            [
                'editor_script'   => 'fitness-skg-review-blocks',
                'render_callback' => $block['callback'],
            ]
        );

        if (is_wp_error($result)) {
            error_log(sprintf('fitness_skg block registration failed for %s: %s', $block['path'], $result->get_error_message()));
        }
    }
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

    $wrapper_attributes = get_block_wrapper_attributes([
        'class'      => implode(' ', array_filter($classes)),
        'role'       => 'img',
        'aria-label' => $label,
    ]);

    $output  = sprintf('<div %s>', $wrapper_attributes);
    $output .= '<span class="fitness-rating-badge__stars" aria-hidden="true">' . $stars_html . '</span>';
    $output .= '<span class="fitness-rating-badge__values"><span class="fitness-rating-badge__rating">' . esc_html($rating_text) . '</span> <span class="fitness-rating-badge__count">' . esc_html($count_text) . '</span></span>';
    $output .= $logo_html;
    $output .= '</div>';

    if ($rating !== null && $count !== null && $place_id) {
        $output .= fitness_skg_render_rating_json_ld($block, $rating, $count);
    }

    return $output;
}

function fitness_skg_render_review_feed_block(array $attributes, string $content, $block): string
{
    $place_ids = fitness_skg_parse_place_ids($attributes['placeIds'] ?? '', $block->context ?? []);
    if (! $place_ids) {
        $wrapper = get_block_wrapper_attributes(['class' => 'is-empty']);

        return sprintf(
            '<div %s>%s</div>',
            $wrapper,
            esc_html__('Keine Google-Rezensionen gefunden.', 'fitness-skg')
        );
    }

    $limit      = max(1, (int) ($attributes['limit'] ?? 3));
    $min_rating = isset($attributes['minRating']) ? (float) $attributes['minRating'] : 0;
    $min_rating = max(0, min(5, $min_rating));
    $max_length = max(0, (int) ($attributes['maxLength'] ?? 0));

    $collected = [];

    foreach ($place_ids as $place_id) {
        $bundle = fitness_skg_get_place_reviews($place_id);
        if (empty($bundle['reviews']) || ! is_array($bundle['reviews'])) {
            continue;
        }

        $place_name = isset($bundle['place_name']) ? (string) $bundle['place_name'] : '';

        foreach ($bundle['reviews'] as $review) {
            $rating = isset($review['rating']) ? (float) $review['rating'] : null;
            if ($rating !== null && $rating < $min_rating) {
                continue;
            }

            $text_raw = (string) ($review['text'] ?? '');
            if ($text_raw === '') {
                continue;
            }

            $timestamp = isset($review['publish_ts']) ? (int) $review['publish_ts'] : 0;

            $collected[] = [
                'place_id'   => $place_id,
                'place_name' => $place_name,
                'rating'     => $rating,
                'text'       => $text_raw,
                'timestamp'  => $timestamp,
                'date_raw'   => $review['publish_raw'] ?? '',
                'relative'   => $review['relative'] ?? '',
                'author'     => $review['author'] ?? '',
            ];
        }
    }

    if (! $collected) {
        $wrapper = get_block_wrapper_attributes(['class' => 'is-empty']);

        return sprintf(
            '<div %s>%s</div>',
            $wrapper,
            esc_html__('Keine Google-Rezensionen im gewünschten Bereich.', 'fitness-skg')
        );
    }

    usort($collected, static function (array $a, array $b) {
        return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
    });

    $collected = array_slice($collected, 0, $limit);

    $items_html = '';

    foreach ($collected as $entry) {
        $rating     = $entry['rating'];
        $stars_html = fitness_skg_render_star_icons($rating);
        $label      = $rating !== null
            ? sprintf(__('Bewertung %.1f von 5 Sternen', 'fitness-skg'), (float) $rating)
            : __('Bewertung ohne Sterne', 'fitness-skg');

        $display_text = $max_length > 0
            ? fitness_skg_truncate_review_text($entry['text'], $max_length)
            : $entry['text'];

        $author_name = $entry['author'] !== '' ? $entry['author'] : __('Google Nutzer:in', 'fitness-skg');

        $date_display = '';
        $datetime_attr = '';
        if (! empty($entry['timestamp'])) {
            $datetime_attr = gmdate('c', (int) $entry['timestamp']);
            $date_display  = date_i18n(get_option('date_format'), (int) $entry['timestamp']);
        }

        $place_badge = $entry['place_name'] !== '' ? '<span class="fitness-review-feed__place">' . esc_html($entry['place_name']) . '</span>' : '';

        $items_html .= '<article class="fitness-review-feed__item">';
        $items_html .= '<div class="fitness-review-feed__header">';
        $items_html .= '<span class="fitness-review-feed__stars" aria-label="' . esc_attr($label) . '">' . $stars_html . '</span>';
        $items_html .= '<div class="fitness-review-feed__meta">';
        $items_html .= '<span class="fitness-review-feed__author">' . esc_html($author_name) . '</span>';
        if ($date_display) {
            $items_html .= '<time class="fitness-review-feed__date" datetime="' . esc_attr($datetime_attr) . '">' . esc_html($date_display) . '</time>';
        } elseif (! empty($entry['relative'])) {
            $items_html .= '<span class="fitness-review-feed__date">' . esc_html($entry['relative']) . '</span>';
        }
        $items_html .= $place_badge;
        $items_html .= '</div>';
        $items_html .= '</div>';
        $items_html .= '<p class="fitness-review-feed__text">' . esc_html($display_text) . '</p>';
        $items_html .= '</article>';
    }

    $wrapper = get_block_wrapper_attributes();

    return sprintf('<div %s>%s</div>', $wrapper, $items_html);
}

function fitness_skg_render_linked_container_block(array $attributes, string $content, $block = null): string
{
    $url            = isset($attributes['url']) ? trim((string) $attributes['url']) : '';
    $escaped_url    = $url !== '' ? esc_url($url) : '';
    $opens_in_new   = ! empty($attributes['opensInNewTab']);
    $rel_input      = isset($attributes['rel']) ? sanitize_text_field((string) $attributes['rel']) : '';
    $rel_tokens     = $rel_input !== '' ? preg_split('/\s+/', $rel_input) : [];

    if ($opens_in_new) {
        $rel_tokens[] = 'noopener';
        $rel_tokens[] = 'noreferrer';
    }

    $rel_tokens = array_unique(array_filter(array_map('sanitize_text_field', (array) $rel_tokens)));
    $rel_value  = implode(' ', $rel_tokens);

    $extra_attrs = [];

    if ($escaped_url !== '') {
        $extra_attrs['href'] = $escaped_url;

        if ($opens_in_new) {
            $extra_attrs['target'] = '_blank';
        }

        if ($rel_value !== '') {
            $extra_attrs['rel'] = $rel_value;
        }
    }

    $tag                  = $escaped_url !== '' ? 'a' : 'div';
    $wrapper_attributes   = get_block_wrapper_attributes($extra_attrs);
    $rendered_inner_block = $content !== '' ? $content : ''; // InnerBlocks already rendered by WP.

    return sprintf('<%1$s %2$s>%3$s</%1$s>', $tag, $wrapper_attributes, $rendered_inner_block);
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
        $wrapper = get_block_wrapper_attributes([
            'class' => 'fitness-review-card is-empty',
        ]);

        return sprintf('<div %s>%s</div>', $wrapper, esc_html__('Bitte wähle einen Erfahrungsbericht aus.', 'fitness-skg'));
    }

    $post = get_post($testimonial_id);
    if (! $post || $post->post_type !== 'testimonial') {
        $wrapper = get_block_wrapper_attributes([
            'class' => 'fitness-review-card is-empty',
        ]);

        return sprintf('<div %s>%s</div>', $wrapper, esc_html__('Erfahrungsbericht nicht gefunden.', 'fitness-skg'));
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

    $wrapper_attributes = get_block_wrapper_attributes([
        'class' => 'fitness-review-card',
    ]);

    $output  = sprintf('<article %s>', $wrapper_attributes);
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
        WP_CLI::warning(sprintf('Failed to refresh %1$s rating: %2$s', $place_id, $data->get_error_message()));
    } else {
        set_transient($cache_key, $data, 15 * MINUTE_IN_SECONDS);
        update_option('fitness_skg_fallback_' . $cache_key, $data, false);
        WP_CLI::success(sprintf('Rating %s: %.1f (%d)', $place_id, $data['rating'], $data['count']));
    }

    $reviews_cache_key = 'fitness_skg_reviews_' . md5($place_id);
    delete_transient($reviews_cache_key);

    $reviews = fitness_skg_fetch_place_reviews($place_id);
    if (is_wp_error($reviews)) {
        WP_CLI::warning(sprintf('Failed to refresh %1$s reviews: %2$s', $place_id, $reviews->get_error_message()));
    } else {
        set_transient($reviews_cache_key, $reviews, 15 * MINUTE_IN_SECONDS);
        update_option('fitness_skg_reviews_fallback_' . $reviews_cache_key, $reviews, false);
        $count_reviews = isset($reviews['reviews']) && is_array($reviews['reviews']) ? count($reviews['reviews']) : 0;
        WP_CLI::success(sprintf('Reviews %s: %d entries cached', $place_id, $count_reviews));
    }
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
