<?php
/**
 * Rejestracja bloku Gutenberga dla wtyczki Losowe Cytaty
 *
 * @package Losowe_Cytaty
 */

// Zabezpieczenie przed bezpośrednim dostępem do pliku
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Rejestracja bloku Gutenberga
 */
function losowe_cytaty_register_gutenberg_block() {
    // Sprawdzenie, czy funkcja register_block_type istnieje (dostępna od WordPress 5.0)
    if (!function_exists('register_block_type')) {
        return;
    }

    // Rejestracja skryptów i stylów
    wp_register_script(
        'losowe-cytaty-block-editor',
        LOSOWE_CYTATY_URL . 'assets/js/gutenberg-block.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-data'),
        LOSOWE_CYTATY_VERSION,
        true
    );

    wp_register_style(
        'losowe-cytaty-block-editor',
        LOSOWE_CYTATY_URL . 'assets/css/gutenberg-block-editor.css',
        array(),
        LOSOWE_CYTATY_VERSION
    );

    wp_register_style(
        'losowe-cytaty-block-frontend',
        LOSOWE_CYTATY_URL . 'assets/css/elementor-widget.css', // Używamy tych samych stylów co dla widżetu Elementor
        array(),
        LOSOWE_CYTATY_VERSION
    );

    // Przekazanie ustawień do skryptu
    wp_localize_script('losowe-cytaty-block-editor', 'losoweCytatyBlock', array(
        'defaultSettings' => array(
            'textColor' => get_option('losowe_cytaty_text_color', '#333333'),
            'backgroundColor' => get_option('losowe_cytaty_background_color', '#f9f9f9'),
            'borderColor' => get_option('losowe_cytaty_border_color', '#2271b1'),
            'borderWidth' => get_option('losowe_cytaty_border_width', '4'),
            'borderRadius' => get_option('losowe_cytaty_border_radius', '0'),
            'authorColor' => get_option('losowe_cytaty_author_color', '#666666'),
            'showQuoteIcon' => get_option('losowe_cytaty_show_quote_icon', '1') === '1',
            'quoteIconColor' => get_option('losowe_cytaty_quote_icon_color', '#e0e0e0'),
        ),
    ));

    // Rejestracja bloku
    register_block_type('losowe-cytaty/cytat', array(
        'editor_script' => 'losowe-cytaty-block-editor',
        'editor_style' => 'losowe-cytaty-block-editor',
        'style' => 'losowe-cytaty-block-frontend',
        'render_callback' => 'losowe_cytaty_render_gutenberg_block',
        'attributes' => array(
            'showAuthor' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'showQuoteIcon' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'textColor' => array(
                'type' => 'string',
                'default' => get_option('losowe_cytaty_text_color', '#333333'),
            ),
            'backgroundColor' => array(
                'type' => 'string',
                'default' => get_option('losowe_cytaty_background_color', '#f9f9f9'),
            ),
            'borderColor' => array(
                'type' => 'string',
                'default' => get_option('losowe_cytaty_border_color', '#2271b1'),
            ),
            'borderWidth' => array(
                'type' => 'number',
                'default' => intval(get_option('losowe_cytaty_border_width', '4')),
            ),
            'borderRadius' => array(
                'type' => 'number',
                'default' => intval(get_option('losowe_cytaty_border_radius', '0')),
            ),
            'authorColor' => array(
                'type' => 'string',
                'default' => get_option('losowe_cytaty_author_color', '#666666'),
            ),
            'quoteIconColor' => array(
                'type' => 'string',
                'default' => get_option('losowe_cytaty_quote_icon_color', '#e0e0e0'),
            ),
        ),
    ));
}
add_action('init', 'losowe_cytaty_register_gutenberg_block');

/**
 * Renderowanie bloku Gutenberga
 *
 * @param array $attributes Atrybuty bloku.
 * @return string Zawartość bloku.
 */
function losowe_cytaty_render_gutenberg_block($attributes) {
    // Pobieranie aktualnego cytatu
    $quote = losowe_cytaty_get_current_quote();
    
    if (!$quote) {
        return '<div class="losowe-cytaty-empty">' . __('Brak cytatów w bazie danych.', 'losowe-cytaty') . '</div>';
    }
    
    // Przygotowanie klas CSS
    $classes = 'losowe-cytaty-quote';
    if (isset($attributes['showQuoteIcon']) && $attributes['showQuoteIcon']) {
        $classes .= ' show-quote-icon';
    }
    
    // Przygotowanie klas dla kontenera
    $widget_classes = 'losowe-cytaty-widget';
    if (isset($attributes['showQuoteIcon']) && $attributes['showQuoteIcon']) {
        $widget_classes .= ' show-quote-icon';
    }
    
    // Przygotowanie stylów inline
    $styles = '';
    if (isset($attributes['textColor'])) {
        $styles .= '--quote-text-color: ' . esc_attr($attributes['textColor']) . ';';
    }
    if (isset($attributes['backgroundColor'])) {
        $styles .= '--quote-background-color: ' . esc_attr($attributes['backgroundColor']) . ';';
    }
    if (isset($attributes['borderColor'])) {
        $styles .= '--quote-border-color: ' . esc_attr($attributes['borderColor']) . ';';
    }
    if (isset($attributes['borderWidth'])) {
        $styles .= '--quote-border-width: ' . esc_attr($attributes['borderWidth']) . 'px;';
    }
    if (isset($attributes['borderRadius'])) {
        $styles .= '--quote-border-radius: ' . esc_attr($attributes['borderRadius']) . 'px;';
    }
    if (isset($attributes['authorColor'])) {
        $styles .= '--quote-author-color: ' . esc_attr($attributes['authorColor']) . ';';
    }
    if (isset($attributes['quoteIconColor'])) {
        $styles .= '--quote-icon-color: ' . esc_attr($attributes['quoteIconColor']) . ';';
    }
    
    // Generowanie HTML
    $output = '<div class="' . esc_attr($widget_classes) . '" style="' . esc_attr($styles) . '">';
    $output .= '<blockquote class="' . esc_attr($classes) . '" aria-label="' . esc_attr__('Losowy cytat', 'losowe-cytaty') . '">';
    $output .= '<p>' . esc_html($quote['quote']) . '</p>';
    
    if (isset($attributes['showAuthor']) && $attributes['showAuthor'] && !empty($quote['author'])) {
        $output .= '<cite aria-label="' . esc_attr__('Autor cytatu', 'losowe-cytaty') . '">— ' . esc_html($quote['author']) . '</cite>';
    }
    
    $output .= '</blockquote>';
    $output .= '</div>';
    
    return $output;
}