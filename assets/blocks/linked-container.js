(function (wp) {
    if (!wp) {
        return;
    }

    const {
        createElement: el,
        Fragment,
        useState,
        useMemo,
        useCallback,
    } = wp.element || {};
    const { __ } = wp.i18n || { __: (str) => str };
    const { registerBlockType, unregisterBlockType, getBlockType } = wp.blocks || {};
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
        Notice,
        ToolbarButton,
        ToolbarGroup,
        Popover,
    } = wp.components || {};
    const { link: linkIcon, linkOff } = wp.icons || {};

    if (!el || !Fragment || !useBlockProps || !InnerBlocks || !registerBlockType) {
        return;
    }

    const LinkControlComponent = LinkControl || __experimentalLinkControl;

    const LinkedContainerEdit = (props) => {
        const { attributes, setAttributes } = props;
        const { url, opensInNewTab, rel } = attributes;

        const [isLinkUIVisible, setIsLinkUIVisible] = useState(false);

        const blockProps = useBlockProps({
            'data-has-link': url ? 'true' : 'false',
        });

        const linkValue = useMemo(
            () => ({
                url: url || '',
                opensInNewTab: !!opensInNewTab,
                rel: rel || '',
            }),
            [url, opensInNewTab, rel]
        );

        const closeLinkUI = useCallback(() => {
            setIsLinkUIVisible(false);
        }, []);

        const applyLink = useCallback(
            (nextValue) => {
                if (!nextValue) {
                    setAttributes({ url: '', opensInNewTab: false, rel: '' });
                    return;
                }

                setAttributes({
                    url: nextValue.url || '',
                    opensInNewTab: !!nextValue.opensInNewTab,
                    rel: nextValue.rel || '',
                });
            },
            [setAttributes]
        );

        const removeLink = useCallback(() => {
            setAttributes({ url: '', opensInNewTab: false, rel: '' });
            closeLinkUI();
        }, [closeLinkUI, setAttributes]);

        const toolbar = ToolbarGroup && ToolbarButton && BlockControls
            ? el(
                  BlockControls,
                  { group: 'block' },
                  el(
                      ToolbarGroup,
                      null,
                      el(ToolbarButton, {
                          icon: linkIcon,
                          label: url
                              ? __('Link bearbeiten', 'fitness-skg')
                              : __('Link hinzufügen', 'fitness-skg'),
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

        const linkPopover =
            isLinkUIVisible && LinkControlComponent && Popover
                ? el(
                      Popover,
                      {
                          position: 'bottom center',
                          onClose: closeLinkUI,
                          className: 'fitness-linked-container__link-popover',
                      },
                      el(LinkControlComponent, {
                          key: `fitness-linked-container-link-${linkValue.url}-${linkValue.opensInNewTab}-${linkValue.rel}`,
                          value: linkValue,
                          onChange: (nextValue) => {
                              applyLink(nextValue);
                              if (nextValue?.url) {
                                  closeLinkUI();
                              }
                          },
                          onRemove: removeLink,
                      })
                  )
                : null;

        const fallbackLinkControls =
            !LinkControlComponent && PanelBody
                ? el(
                      PanelBody,
                      { title: __('Link', 'fitness-skg'), initialOpen: true },
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
            },
            url
                ? __('Die gesamte Box wird auf die gewählte URL verlinkt.', 'fitness-skg')
                : __('Ohne URL bleibt der Container statisch.', 'fitness-skg'))
        );

        return el(
            Fragment,
            null,
            toolbar,
            inspector,
            linkPopover,
            el('div', blockProps, el(InnerBlocks, null))
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
        save: (props = {}) => {
            const { attributes = {} } = props;
            const { url, opensInNewTab, rel } = attributes;
            const dataHasLink = url ? 'true' : 'false';
            const blockProps =
                (useBlockProps && useBlockProps.save && useBlockProps.save({ 'data-has-link': dataHasLink })) || {
                    'data-has-link': dataHasLink,
                };
            const innerContent =
                InnerBlocks && InnerBlocks.Content
                    ? el(InnerBlocks.Content, null)
                    : null;

            if (url) {
                const relValue = rel || (opensInNewTab ? 'noopener noreferrer' : undefined);

                return el(
                    'div',
                    blockProps,
                    el(
                        'a',
                        {
                            href: url,
                            target: opensInNewTab ? '_blank' : undefined,
                            rel: relValue,
                        },
                        innerContent
                    )
                );
            }

            return el('div', blockProps, innerContent);
        },
    };

    wp.domReady(() => {
        if (
            typeof unregisterBlockType === 'function' &&
            typeof getBlockType === 'function' &&
            getBlockType('fitness/linked-container')
        ) {
            unregisterBlockType('fitness/linked-container');
        }

        registerBlockType('fitness/linked-container', settings);
    });
})(window.wp || {});
