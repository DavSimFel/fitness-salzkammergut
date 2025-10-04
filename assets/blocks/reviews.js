(function (wp) {
    if (!wp || !wp.blocks) {
        return;
    }

    const el = wp.element.createElement;
    const Fragment = wp.element.Fragment;
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;
    const { PanelBody, TextControl, ToggleControl, SelectControl, Notice } = wp.components;
    const { InspectorControls } = wp.blockEditor;
    const ServerSideRender = wp.serverSideRender;

    function renderRatingEdit(props) {
        const attributes = props.attributes;

        return el(
            Fragment,
            null,
            el(
                InspectorControls,
                null,
                el(
                    PanelBody,
                    { title: __('Google Bewertungen', 'fitness-skg') },
                    el(TextControl, {
                        label: __('Place ID', 'fitness-skg'),
                        value: attributes.placeId || '',
                        onChange: function (value) {
                            props.setAttributes({ placeId: value });
                        },
                        help: __('Leer lassen, um Place ID vom aktuellen Beitrag zu übernehmen.', 'fitness-skg')
                    }),
                    el(SelectControl, {
                        label: __('Größe', 'fitness-skg'),
                        value: attributes.size || 'medium',
                        options: [
                            { label: __('Kompakt', 'fitness-skg'), value: 'small' },
                            { label: __('Standard', 'fitness-skg'), value: 'medium' },
                            { label: __('Groß', 'fitness-skg'), value: 'large' }
                        ],
                        onChange: function (value) {
                            props.setAttributes({ size: value });
                        }
                    }),
                    el(ToggleControl, {
                        label: __('Google Logo anzeigen', 'fitness-skg'),
                        checked: attributes.showLogo !== false,
                        onChange: function (value) {
                            props.setAttributes({ showLogo: value });
                        }
                    })
                )
            ),
            ServerSideRender
                ? el(ServerSideRender, { block: 'fitness/rating-badge', attributes: attributes })
                : el('p', {}, __('Server-Side Render nicht verfügbar.', 'fitness-skg'))
        );
    }

    function renderReviewCardEdit(props) {
        const attributes = props.attributes;

        return el(
            Fragment,
            null,
            el(
                InspectorControls,
                null,
                el(
                    PanelBody,
                    { title: __('Erfahrungsbericht', 'fitness-skg') },
                    el(TextControl, {
                        label: __('Testimonial ID', 'fitness-skg'),
                        value: attributes.testimonialId || '',
                        onChange: function (value) {
                            props.setAttributes({ testimonialId: value ? parseInt(value, 10) || 0 : 0 });
                        },
                        help: __('ID des Erfahrungsberichts (siehe Beitragsliste).', 'fitness-skg')
                    }),
                    el(TextControl, {
                        label: __('Place ID (optional)', 'fitness-skg'),
                        value: attributes.placeId || '',
                        onChange: function (value) {
                            props.setAttributes({ placeId: value });
                        },
                        help: __('Leer lassen, um Place ID vom aktuellen Beitrag zu übernehmen.', 'fitness-skg')
                    }),
                    el(ToggleControl, {
                        label: __('Sterne anzeigen', 'fitness-skg'),
                        checked: attributes.showStars !== false,
                        onChange: function (value) {
                            props.setAttributes({ showStars: value });
                        }
                    }),
                    el(Notice, {
                        status: 'info',
                        isDismissible: false
                    }, __('Text und Bild stammen aus dem Erfahrungsbericht (CPT).', 'fitness-skg'))
                )
            ),
            ServerSideRender
                ? el(ServerSideRender, { block: 'fitness/review-card', attributes: attributes })
                : el('p', {}, __('Server-Side Render nicht verfügbar.', 'fitness-skg'))
        );
    }

    registerBlockType('fitness/rating-badge', {
        title: __('Google Rating Badge', 'fitness-skg'),
        description: __('Zeigt Sternebewertung und Anzahl der Google-Rezensionen für ein Studio.', 'fitness-skg'),
        icon: 'star-filled',
        category: 'widgets',
        attributes: {
            placeId: { type: 'string', default: '' },
            showLogo: { type: 'boolean', default: true },
            size: { type: 'string', default: 'medium' }
        },
        supports: { html: false },
        edit: renderRatingEdit,
        save: function () {
            return null;
        }
    });

    registerBlockType('fitness/review-card', {
        title: __('Erfahrungsbericht Karte', 'fitness-skg'),
        description: __('Zeigt einen kuratierten Erfahrungsbericht inklusive Sternebewertung.', 'fitness-skg'),
        icon: 'format-quote',
        category: 'widgets',
        attributes: {
            testimonialId: { type: 'integer', default: 0 },
            placeId: { type: 'string', default: '' },
            showStars: { type: 'boolean', default: true }
        },
        supports: { html: false },
        edit: renderReviewCardEdit,
        save: function () {
            return null;
        }
    });
})(window.wp || {});
