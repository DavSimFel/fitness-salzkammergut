(function (wp) {
    if (!wp) {
        return;
    }

    const { createElement: el, Fragment, useState } = wp.element;
    const { __ } = wp.i18n || { __: (str) => str };
    const {
        registerBlockType,
        unregisterBlockType,
        getBlockType,
    } = wp.blocks || {};
    const {
        useBlockProps,
        InspectorControls,
        InnerBlocks,
        BlockControls,
        LinkControl,
        __experimentalLinkControl,
    } = wp.blockEditor || wp.editor || {};
    const {
        PanelBody,
        TextControl,
        ToggleControl,
        SelectControl,
        Notice,
        RangeControl,
        TextareaControl,
        ToolbarButton,
        ToolbarGroup,
        Popover,
    } = wp.components || {};
    const { link: linkIcon, linkOff } = wp.icons || {};
    const EffectiveLinkControl = LinkControl || __experimentalLinkControl;
    const ServerSideRender = wp.serverSideRender;

    if (!registerBlockType || !useBlockProps || !InspectorControls || !ServerSideRender) {
        return;
    }

    const wrapWithBlockProps = (content) => {
        const blockProps = useBlockProps({ __experimentalSkipSerialization: true });
        return el('div', blockProps, content);
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

    const LinkedContainerEdit = (props) => {
        const { attributes, setAttributes } = props;
        const { url, opensInNewTab, rel } = attributes;

        if (!useBlockProps || !InnerBlocks) {
            return null;
        }

        const [isLinkUIVisible, setIsLinkUIVisible] = useState(false);

        const blockProps = useBlockProps({
            'data-has-link': url ? 'true' : 'false',
        });

        const linkObject = {
            url: url || '',
            opensInNewTab: !!opensInNewTab,
            rel: rel || '',
        };

        const applyLink = (nextValue) => {
            if (!nextValue) {
                setAttributes({ url: '', opensInNewTab: false, rel: '' });
                return;
            }

            setAttributes({
                url: nextValue.url || '',
                opensInNewTab: !!nextValue.opensInNewTab,
                rel: nextValue.rel || '',
            });
        };

        const removeLink = () => {
            setAttributes({ url: '', opensInNewTab: false, rel: '' });
            setIsLinkUIVisible(false);
        };

        const toolbar = ToolbarGroup && ToolbarButton && BlockControls
            ? el(
                BlockControls,
                { group: 'block' },
                el(
                    ToolbarGroup,
                    null,
                    el(ToolbarButton, {
                        icon: linkIcon,
                        label: url ? __('Link bearbeiten', 'fitness-skg') : __('Link hinzufügen', 'fitness-skg'),
                        onClick: () => setIsLinkUIVisible(true),
                        isPressed: isLinkUIVisible,
                    }),
                    url
                        ? el(ToolbarButton, {
                            icon: linkOff,
                            label: __('Link entfernen', 'fitness-skg'),
                            onClick: removeLink,
                        })
                        : null
                )
            )
            : null;

        const linkPopover = isLinkUIVisible && EffectiveLinkControl && Popover
            ? el(
                Popover,
                {
                    position: 'bottom center',
                    onClose: () => setIsLinkUIVisible(false),
                    className: 'fitness-linked-container__link-popover',
                },
                el(EffectiveLinkControl, {
                    value: linkObject,
                    onChange: (nextValue) => {
                        applyLink(nextValue);
                        if (!nextValue || !nextValue.url) {
                            setIsLinkUIVisible(false);
                        }
                    },
                    onRemove: removeLink,
                    forceIsEditingLink: true,
                    showInitialSuggestions: true,
                })
            )
            : null;

        const fallbackLinkControls = !EffectiveLinkControl
            ? el(
                Fragment,
                null,
                el(TextControl, {
                    label: __('URL', 'fitness-skg'),
                    value: url || '',
                    onChange: (value) => setAttributes({ url: value }),
                    placeholder: __('https://beispiel.com', 'fitness-skg'),
                }),
                el(ToggleControl, {
                    label: __('In neuem Tab öffnen', 'fitness-skg'),
                    checked: !!opensInNewTab,
                    onChange: (value) => setAttributes({ opensInNewTab: value }),
                }),
                el(TextControl, {
                    label: __('Rel-Attribute', 'fitness-skg'),
                    value: rel || '',
                    onChange: (value) => setAttributes({ rel: value }),
                    help: __('Leer lassen, um Standardwerte zu verwenden.', 'fitness-skg'),
                })
            )
            : null;

        const inspector = el(
            InspectorControls,
            null,
            fallbackLinkControls,
            el(Notice, {
                status: 'info',
                isDismissible: false,
            }, url
                ? __('Die gesamte Box wird auf die gewählte URL verlinkt.', 'fitness-skg')
                : __('Ohne URL bleibt der Container statisch.', 'fitness-skg'))
        );

        return el(
            Fragment,
            null,
            toolbar,
            inspector,
            linkPopover,
            el(
                'div',
                blockProps,
                el(InnerBlocks, null)
            )
        );
    };

    const blocks = [
        {
            name: 'fitness/rating-badge',
            settings: {
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
            },
        },
        {
            name: 'fitness/review-card',
            settings: {
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
            },
        },
        {
            name: 'fitness/review-feed',
            settings: {
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
            },
        },
        {
            name: 'fitness/linked-container',
            settings: {
                apiVersion: 2,
                title: __('Linked Container', 'fitness-skg'),
                description: __('Macht alle enthaltenen Blöcke per Link vollständig klickbar.', 'fitness-skg'),
                category: 'design',
                icon: 'admin-links',
                attributes: {
                    url: {
                        type: 'string',
                        default: '',
                    },
                    opensInNewTab: {
                        type: 'boolean',
                        default: false,
                    },
                    rel: {
                        type: 'string',
                        default: '',
                    },
                },
                supports: {
                    html: false,
                    anchor: true,
                    align: ['wide', 'full'],
                    spacing: {
                        margin: true,
                        padding: true,
                        blockGap: true,
                    },
                    color: {
                        text: true,
                        background: true,
                        link: true,
                    },
                    typography: {
                        fontSize: true,
                        lineHeight: true,
                    },
                    shadow: true,
                    layout: {
                        allowSwitching: true,
                    },
                },
                edit: LinkedContainerEdit,
                save: () => null,
            },
        },
    ];

    const registerFitnessBlock = ({ name, settings }) => {
        if (typeof unregisterBlockType === 'function' && typeof getBlockType === 'function' && getBlockType(name)) {
            unregisterBlockType(name);
        }

        registerBlockType(name, settings);
    };

    wp.domReady(() => {
        blocks.forEach(registerFitnessBlock);
    });
})(window.wp || {});
