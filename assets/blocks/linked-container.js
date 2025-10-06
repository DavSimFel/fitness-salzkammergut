(function (wp) {
    if (!wp) {
        return;
    }

    const { createElement: el, Fragment, useState } = wp.element || {};
    const { __ } = wp.i18n || { __: (str) => str };
    const { registerBlockType, unregisterBlockType, getBlockType } = wp.blocks || {};
    const { useBlockProps, InspectorControls, InnerBlocks, BlockControls, LinkControl, __experimentalLinkControl } = wp.blockEditor || wp.editor || {};
    const { TextControl, ToggleControl, Notice, ToolbarButton, ToolbarGroup, Popover } = wp.components || {};
    const { link: linkIcon, linkOff } = wp.icons || {};

    if (!el || !Fragment || !useBlockProps || !InnerBlocks || !registerBlockType) {
        return;
    }

    const EffectiveLinkControl = LinkControl || __experimentalLinkControl;

    const LinkedContainerEdit = (props) => {
        const { attributes, setAttributes } = props;
        const { url, opensInNewTab, rel } = attributes;

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

    const settings = {
        apiVersion: 2,
        title: __('Linked Container', 'fitness-skg'),
        description: __('Wraps inner blocks in an optional link for fully clickable cards.', 'fitness-skg'),
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
        },
        edit: LinkedContainerEdit,
        save: () => null,
    };

    wp.domReady(() => {
        if (typeof unregisterBlockType === 'function' && typeof getBlockType === 'function' && getBlockType('fitness/linked-container')) {
            unregisterBlockType('fitness/linked-container');
        }

        registerBlockType('fitness/linked-container', settings);
    });
})(window.wp || {});
