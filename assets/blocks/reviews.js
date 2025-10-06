(function (wp) {
    if (!wp) {
        return;
    }

    const { createElement: el, Fragment } = wp.element;
    const { __ } = wp.i18n || { __: (str) => str };
    const {
        registerBlockType,
        updateBlockType,
        unregisterBlockType,
        getBlockType,
    } = wp.blocks || {};
    const {
        useBlockProps,
        InspectorControls,
    } = wp.blockEditor || wp.editor || {};
    const {
        PanelBody,
        TextControl,
        ToggleControl,
        SelectControl,
        Notice,
        RangeControl,
        TextareaControl,
    } = wp.components || {};
    const ServerSideRender = wp.serverSideRender;

    if ((!registerBlockType && !updateBlockType) || !useBlockProps || !InspectorControls || !ServerSideRender) {
        return;
    }

    const ensureBlockType = (name, settings) => {
        const existing = getBlockType ? getBlockType(name) : null;

        if (existing) {
            if (typeof updateBlockType === 'function') {
                updateBlockType(name, settings);
                return;
            }

            if (typeof unregisterBlockType === 'function' && typeof registerBlockType === 'function') {
                unregisterBlockType(name);
                registerBlockType(name, settings);
                return;
            }
        }

        if (typeof registerBlockType === 'function') {
            registerBlockType(name, settings);
        }
    };

    const wrapWithBlockProps = (content) => {
        const blockProps = useBlockProps();
        return el('div', blockProps, content);
    };

    const ratingBadgeSettings = {
        apiVersion: 2,
        title: __('Google Rating Badge', 'fitness-skg'),
        description: __('Shows the Google rating and review count for a selected Place.', 'fitness-skg'),
        category: 'widgets',
        icon: 'star-filled',
        attributes: {
            placeId: {
                type: 'string',
                default: '',
            },
            showLogo: {
                type: 'boolean',
                default: true,
            },
            size: {
                type: 'string',
                default: 'medium',
            },
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
        edit: RatingBadgeEdit,
        save: () => null,
    };

    const reviewCardSettings = {
        apiVersion: 2,
        title: __('Testimonial Card', 'fitness-skg'),
        description: __('Outputs a curated testimonial with live star rating from Google.', 'fitness-skg'),
        category: 'widgets',
        icon: 'format-quote',
        attributes: {
            testimonialId: {
                type: 'integer',
                default: 0,
            },
            placeId: {
                type: 'string',
                default: '',
            },
            showStars: {
                type: 'boolean',
                default: true,
            },
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
        edit: ReviewCardEdit,
        save: () => null,
    };

    const reviewFeedSettings = {
        apiVersion: 2,
        title: __('Google Review Feed', 'fitness-skg'),
        description: __('Displays the latest Google reviews from one or multiple Place IDs.', 'fitness-skg'),
        category: 'widgets',
        icon: 'list-view',
        attributes: {
            placeIds: {
                type: 'string',
                default: '',
            },
            limit: {
                type: 'number',
                default: 3,
            },
            minRating: {
                type: 'number',
                default: 4,
            },
            maxLength: {
                type: 'number',
                default: 180,
            },
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
                },
                allowSwitching: true,
            },
        },
        edit: ReviewFeedEdit,
        save: () => null,
    };

    const RatingBadgeEdit = (props) => {
        const { attributes, setAttributes } = props;

        const inspector = el(
            InspectorControls,
            null,
            el(
                PanelBody,
                { title: __('Google Bewertungen', 'fitness-skg') },
                el(TextControl, {
                    label: __('Place ID', 'fitness-skg'),
                    value: attributes.placeId || '',
                    onChange: (value) => setAttributes({ placeId: value }),
                    help: __('Leer lassen, um die Place ID des aktuellen Beitrags zu verwenden.', 'fitness-skg'),
                }),
                el(SelectControl, {
                    label: __('Größe', 'fitness-skg'),
                    value: attributes.size || 'medium',
                    options: [
                        { label: __('Kompakt', 'fitness-skg'), value: 'small' },
                        { label: __('Standard', 'fitness-skg'), value: 'medium' },
                        { label: __('Groß', 'fitness-skg'), value: 'large' },
                    ],
                    onChange: (value) => setAttributes({ size: value }),
                }),
                el(ToggleControl, {
                    label: __('Google Logo anzeigen', 'fitness-skg'),
                    checked: attributes.showLogo !== false,
                    onChange: (value) => setAttributes({ showLogo: value }),
                })
            )
        );

        return el(
            Fragment,
            null,
            inspector,
            wrapWithBlockProps(
                el(ServerSideRender, {
                    block: 'fitness/rating-badge',
                    attributes,
                })
            )
        );
    };

    const ReviewCardEdit = (props) => {
        const { attributes, setAttributes } = props;

        const inspector = el(
            InspectorControls,
            null,
            el(
                PanelBody,
                { title: __('Erfahrungsbericht', 'fitness-skg') },
                el(TextControl, {
                    label: __('Testimonial ID', 'fitness-skg'),
                    value: attributes.testimonialId || '',
                    onChange: (value) => {
                        const parsed = value ? parseInt(value, 10) || 0 : 0;
                        setAttributes({ testimonialId: parsed });
                    },
                    help: __('ID des Erfahrungsberichts (siehe Beitragsliste).', 'fitness-skg'),
                }),
                el(TextControl, {
                    label: __('Place ID (optional)', 'fitness-skg'),
                    value: attributes.placeId || '',
                    onChange: (value) => setAttributes({ placeId: value }),
                    help: __('Leer lassen, um die Place ID des aktuellen Beitrags zu übernehmen.', 'fitness-skg'),
                }),
                el(ToggleControl, {
                    label: __('Sterne anzeigen', 'fitness-skg'),
                    checked: attributes.showStars !== false,
                    onChange: (value) => setAttributes({ showStars: value }),
                }),
                el(Notice, {
                    status: 'info',
                    isDismissible: false,
                }, __('Text und Bild stammen aus dem Erfahrungsbericht (CPT).', 'fitness-skg'))
            )
        );

        return el(
            Fragment,
            null,
            inspector,
            wrapWithBlockProps(
                el(ServerSideRender, {
                    block: 'fitness/review-card',
                    attributes,
                })
            )
        );
    };

    const ReviewFeedEdit = (props) => {
        const { attributes, setAttributes } = props;

        const inspector = el(
            InspectorControls,
            null,
            el(
                PanelBody,
                { title: __('Google Rezensionen', 'fitness-skg') },
                el(TextareaControl, {
                    label: __('Place IDs', 'fitness-skg'),
                    value: attributes.placeIds || '',
                    onChange: (value) => setAttributes({ placeIds: value }),
                    help: __('Mehrere IDs mit Komma oder Zeilenumbruch trennen. Leer lassen, um die Place ID des aktuellen Beitrags zu verwenden.', 'fitness-skg'),
                }),
                el(RangeControl, {
                    label: __('Anzahl der Rezensionen', 'fitness-skg'),
                    min: 1,
                    max: 10,
                    value: attributes.limit || 3,
                    onChange: (value) => setAttributes({ limit: value }),
                }),
                el(RangeControl, {
                    label: __('Mindestbewertung', 'fitness-skg'),
                    min: 0,
                    max: 5,
                    step: 0.5,
                    value: attributes.minRating !== undefined ? attributes.minRating : 4,
                    onChange: (value) => setAttributes({ minRating: value }),
                }),
                el(TextControl, {
                    label: __('Max. Textlänge (Zeichen, 0 = Volltext)', 'fitness-skg'),
                    type: 'number',
                    min: 0,
                    value: attributes.maxLength !== undefined ? attributes.maxLength : 180,
                    onChange: (value) => {
                        const parsed = value === '' ? 0 : parseInt(value, 10);
                        setAttributes({ maxLength: isNaN(parsed) ? 0 : parsed });
                    },
                }),
                el(Notice, {
                    status: 'info',
                    isDismissible: false,
                }, __('Die Daten werden aus der Google Places API (New) gelesen und kurzzeitig gecached.', 'fitness-skg'))
            )
        );

        return el(
            Fragment,
            null,
            inspector,
            wrapWithBlockProps(
                el(ServerSideRender, {
                    block: 'fitness/review-feed',
                    attributes,
                })
            )
        );
    };

    wp.domReady(() => {
        ensureBlockType('fitness/rating-badge', ratingBadgeSettings);

        ensureBlockType('fitness/review-card', reviewCardSettings);

        ensureBlockType('fitness/review-feed', reviewFeedSettings);
    });
})(window.wp || {});
