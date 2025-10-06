(function (wp) {
    if (!wp || !wp.blocks) {
        return;
    }

    const el = wp.element.createElement;
    const Fragment = wp.element.Fragment;
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;
    const { PanelBody, TextControl, ToggleControl, SelectControl, Notice, RangeControl, TextareaControl } = wp.components;
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
        supports: {
            html: false,
            align: ['left', 'center', 'right', 'wide', 'full'],
            spacing: {
                margin: true,
                padding: true,
            },
            color: {
                text: true,
                background: true,
            },
            typography: {
                fontSize: true,
            },
            shadow: true,
        },
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
        supports: {
            html: false,
            align: ['left', 'center', 'right', 'wide'],
            spacing: {
                margin: true,
                padding: true,
            },
            color: {
                text: true,
                background: true,
            },
            typography: {
                fontSize: true,
                lineHeight: true,
            },
            shadow: true,
        },
        edit: renderReviewCardEdit,
        save: function () {
            return null;
        }
    });

    function renderReviewFeedEdit(props) {
        const attributes = props.attributes;

        const onPlaceIdsChange = function (value) {
            props.setAttributes({ placeIds: value });
        };

        return el(
            Fragment,
            null,
            el(
                InspectorControls,
                null,
                el(
                    PanelBody,
                    { title: __('Google Rezensionen', 'fitness-skg') },
                    el(TextareaControl, {
                        label: __('Place IDs', 'fitness-skg'),
                        value: attributes.placeIds || '',
                        onChange: onPlaceIdsChange,
                        help: __('Mehrere IDs mit Komma oder Zeilenumbruch trennen. Leer lassen, um die Place ID des aktuellen Beitrags zu verwenden.', 'fitness-skg')
                    }),
                    el(RangeControl, {
                        label: __('Anzahl der Rezensionen', 'fitness-skg'),
                        min: 1,
                        max: 10,
                        value: attributes.limit || 3,
                        onChange: function (value) {
                            props.setAttributes({ limit: value });
                        }
                    }),
                    el(RangeControl, {
                        label: __('Mindestbewertung', 'fitness-skg'),
                        min: 0,
                        max: 5,
                        step: 0.5,
                        value: attributes.minRating !== undefined ? attributes.minRating : 4,
                        onChange: function (value) {
                            props.setAttributes({ minRating: value });
                        }
                    }),
                    el(TextControl, {
                        label: __('Max. Textlänge (Zeichen, 0 = Volltext)', 'fitness-skg'),
                        type: 'number',
                        min: 0,
                        value: attributes.maxLength !== undefined ? attributes.maxLength : 180,
                        onChange: function (value) {
                            const parsed = value === '' ? 0 : parseInt(value, 10);
                            props.setAttributes({ maxLength: isNaN(parsed) ? 0 : parsed });
                        }
                    }),
                    el(Notice, {
                        status: 'info',
                        isDismissible: false
                    }, __('Die Daten werden aus der Google Places API (New) gelesen und kurzzeitig gecached.', 'fitness-skg'))
                )
            ),
            ServerSideRender
                ? el(ServerSideRender, { block: 'fitness/review-feed', attributes: attributes })
                : el('p', {}, __('Server-Side Render nicht verfügbar.', 'fitness-skg'))
        );
    }

    registerBlockType('fitness/review-feed', {
        title: __('Google Rezensionen Liste', 'fitness-skg'),
        description: __('Zeigt die neuesten Google-Bewertungen über mehrere Standorte hinweg.', 'fitness-skg'),
        icon: 'list-view',
        category: 'widgets',
        attributes: {
            placeIds: { type: 'string', default: '' },
            limit: { type: 'number', default: 3 },
            minRating: { type: 'number', default: 4 },
            maxLength: { type: 'number', default: 180 }
        },
        supports: {
            html: false,
            align: ['wide', 'full'],
            spacing: {
                margin: true,
                padding: true,
                blockGap: true,
            },
            color: {
                text: true,
                background: true,
            },
            typography: {
                fontSize: true,
                lineHeight: true,
            },
            layout: {
                default: {
                    type: 'flex',
                    orientation: 'vertical',
                    justifyContent: 'flex-start',
                },
                allowSwitching: true,
            },
        },
        edit: renderReviewFeedEdit,
        save: function () {
            return null;
        }
    });
})(window.wp || {});
