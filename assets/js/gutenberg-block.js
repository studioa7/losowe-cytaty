/**
 * Blok Gutenberga dla wtyczki Losowe Cytaty
 */

(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, ToggleControl, RangeControl, ColorPalette } = wp.components;
    const { Fragment } = wp.element;
    const { __ } = wp.i18n;
    
    // Pobieranie domyślnych ustawień
    const defaultSettings = window.losoweCytatyBlock ? window.losoweCytatyBlock.defaultSettings : {
        textColor: '#333333',
        backgroundColor: '#f9f9f9',
        borderColor: '#2271b1',
        borderWidth: 4,
        borderRadius: 0,
        authorColor: '#666666',
        showQuoteIcon: true,
        quoteIconColor: '#e0e0e0'
    };
    
    // Rejestracja bloku
    registerBlockType('losowe-cytaty/cytat', {
        title: __('Losowy Cytat', 'losowe-cytaty'),
        icon: 'format-quote',
        category: 'widgets',
        description: __('Wyświetla losowy cytat z bazy danych.', 'losowe-cytaty'),
        keywords: [
            __('cytat', 'losowe-cytaty'),
            __('losowy', 'losowe-cytaty'),
            __('quote', 'losowe-cytaty')
        ],
        supports: {
            html: false,
            align: ['wide', 'full']
        },
        attributes: {
            showAuthor: {
                type: 'boolean',
                default: true
            },
            showQuoteIcon: {
                type: 'boolean',
                default: defaultSettings.showQuoteIcon
            },
            textColor: {
                type: 'string',
                default: defaultSettings.textColor
            },
            backgroundColor: {
                type: 'string',
                default: defaultSettings.backgroundColor
            },
            borderColor: {
                type: 'string',
                default: defaultSettings.borderColor
            },
            borderWidth: {
                type: 'number',
                default: parseInt(defaultSettings.borderWidth)
            },
            borderRadius: {
                type: 'number',
                default: parseInt(defaultSettings.borderRadius)
            },
            authorColor: {
                type: 'string',
                default: defaultSettings.authorColor
            },
            quoteIconColor: {
                type: 'string',
                default: defaultSettings.quoteIconColor
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();
            
            // Przygotowanie klas CSS
            const classes = 'losowe-cytaty-quote';
            const showQuoteIcon = attributes.showQuoteIcon;
            
            // Przygotowanie klas dla kontenera
            const widgetClasses = 'losowe-cytaty-widget' + (showQuoteIcon ? ' show-quote-icon' : '');
            
            // Przygotowanie stylów inline
            const styles = {
                '--quote-text-color': attributes.textColor,
                '--quote-background-color': attributes.backgroundColor,
                '--quote-border-color': attributes.borderColor,
                '--quote-border-width': attributes.borderWidth + 'px',
                '--quote-border-radius': attributes.borderRadius + 'px',
                '--quote-author-color': attributes.authorColor,
                '--quote-icon-color': attributes.quoteIconColor
            };
            
            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Ustawienia cytatu', 'losowe-cytaty')} initialOpen={true} className="losowe-cytaty-inspector-controls">
                            <ToggleControl
                                label={__('Pokaż autora', 'losowe-cytaty')}
                                checked={attributes.showAuthor}
                                onChange={(value) => setAttributes({ showAuthor: value })}
                            />
                            
                            <ToggleControl
                                label={__('Pokaż ikonę cytatu', 'losowe-cytaty')}
                                checked={attributes.showQuoteIcon}
                                onChange={(value) => setAttributes({ showQuoteIcon: value })}
                            />
                            
                            <div className="components-panel__row">
                                <label>{__('Kolor tekstu', 'losowe-cytaty')}</label>
                                <ColorPalette
                                    value={attributes.textColor}
                                    onChange={(color) => setAttributes({ textColor: color })}
                                    disableCustomColors={false}
                                />
                            </div>
                            
                            <div className="components-panel__row">
                                <label>{__('Kolor tła', 'losowe-cytaty')}</label>
                                <ColorPalette
                                    value={attributes.backgroundColor}
                                    onChange={(color) => setAttributes({ backgroundColor: color })}
                                    disableCustomColors={false}
                                />
                            </div>
                            
                            <div className="components-panel__row">
                                <label>{__('Kolor obramowania', 'losowe-cytaty')}</label>
                                <ColorPalette
                                    value={attributes.borderColor}
                                    onChange={(color) => setAttributes({ borderColor: color })}
                                    disableCustomColors={false}
                                />
                            </div>
                            
                            <RangeControl
                                label={__('Szerokość obramowania (px)', 'losowe-cytaty')}
                                value={attributes.borderWidth}
                                onChange={(value) => setAttributes({ borderWidth: value })}
                                min={0}
                                max={20}
                                step={1}
                            />
                            
                            <RangeControl
                                label={__('Zaokrąglenie narożników (px)', 'losowe-cytaty')}
                                value={attributes.borderRadius}
                                onChange={(value) => setAttributes({ borderRadius: value })}
                                min={0}
                                max={50}
                                step={1}
                            />
                            
                            <div className="components-panel__row">
                                <label>{__('Kolor autora', 'losowe-cytaty')}</label>
                                <ColorPalette
                                    value={attributes.authorColor}
                                    onChange={(color) => setAttributes({ authorColor: color })}
                                    disableCustomColors={false}
                                />
                            </div>
                            
                            {attributes.showQuoteIcon && (
                                <div className="components-panel__row">
                                    <label>{__('Kolor ikony cytatu', 'losowe-cytaty')}</label>
                                    <ColorPalette
                                        value={attributes.quoteIconColor}
                                        onChange={(color) => setAttributes({ quoteIconColor: color })}
                                        disableCustomColors={false}
                                    />
                                </div>
                            )}
                        </PanelBody>
                    </InspectorControls>
                    
                    <div {...blockProps}>
                        <div className={widgetClasses} style={styles}>
                            <blockquote className={classes}>
                                <p>{__('To jest przykładowy cytat, który będzie zastąpiony losowym cytatem z bazy danych.', 'losowe-cytaty')}</p>
                                {attributes.showAuthor && (
                                    <cite>— {__('Autor cytatu', 'losowe-cytaty')}</cite>
                                )}
                            </blockquote>
                        </div>
                    </div>
                </Fragment>
            );
        },
        
        save: function() {
            // Renderowanie odbywa się po stronie serwera
            return null;
        }
    });
})(window.wp);