<?php
/**
 * Widżet Elementor dla wtyczki Losowe Cytaty
 *
 * @package Losowe_Cytaty
 */

// Zabezpieczenie przed bezpośrednim dostępem do pliku
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Sprawdzenie czy wszystkie wymagane klasy Elementora istnieją
 * Ta funkcja jest bardziej szczegółowa niż ogólne sprawdzenie w głównym pliku wtyczki
 */
function losowe_cytaty_check_elementor_classes() {
    // Lista wymaganych klas Elementora
    $required_classes = array(
        'Elementor\Widget_Base',
        'Elementor\Controls_Manager',
        'Elementor\Group_Control_Typography',
        'Elementor\Group_Control_Border',
        'Elementor\Group_Control_Box_Shadow'
    );
    
    // Sprawdzenie czy wszystkie wymagane klasy istnieją
    $missing_classes = array();
    foreach ($required_classes as $class) {
        if (!class_exists($class)) {
            $missing_classes[] = $class;
        }
    }
    
    if (!empty($missing_classes)) {
        // Zapisanie brakujących klas do opcji
        update_option('losowe_cytaty_missing_elementor_classes', $missing_classes);
        return false;
    }
    
    return true;
}

/**
 * Rejestracja kategorii widżetów Elementor
 */
function losowe_cytaty_register_elementor_category($elements_manager) {
    // Sprawdzenie czy Elementor jest dostępny
    if (!class_exists('\Elementor\Plugin')) {
        return;
    }
    
    $elements_manager->add_category(
        'losowe-cytaty',
        [
            'title' => esc_html__('Losowe Cytaty', 'losowe-cytaty'),
            'icon' => 'fa fa-quote-right',
        ]
    );
}
add_action('elementor/elements/categories_registered', 'losowe_cytaty_register_elementor_category');

/**
 * Rejestracja widżetów Elementor
 */
function losowe_cytaty_register_elementor_widgets() {
    // Sprawdzenie czy Elementor jest dostępny
    if (!class_exists('\Elementor\Plugin')) {
        return;
    }
    
    // Sprawdzenie czy wszystkie wymagane klasy istnieją
    if (!losowe_cytaty_check_elementor_classes()) {
        // Dodanie komunikatu w panelu administracyjnym
        add_action('admin_notices', 'losowe_cytaty_elementor_classes_notice');
        return;
    }
    
    // Załadowanie klasy widżetu
    require_once __DIR__ . '/elementor-widget-class.php';
    
    // Sprawdzenie czy klasa widżetu została załadowana
    if (!class_exists('Losowe_Cytaty_Widget')) {
        // Zapisanie informacji o błędzie
        update_option('losowe_cytaty_widget_load_error', true);
        
        // Dodanie komunikatu w panelu administracyjnym
        add_action('admin_notices', 'losowe_cytaty_widget_load_error_notice');
        return;
    }
    
    // Rejestracja widżetu - obsługa zarówno starszych jak i nowszych wersji Elementora
    try {
        if (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.5.0', '>=')) {
            \Elementor\Plugin::instance()->widgets_manager->register(new Losowe_Cytaty_Widget());
        } else {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Losowe_Cytaty_Widget());
        }
        
        // Jeśli rejestracja się powiodła, usuwamy informację o błędzie
        delete_option('losowe_cytaty_widget_load_error');
    } catch (Exception $e) {
        // Zapisanie informacji o błędzie
        update_option('losowe_cytaty_widget_load_error', $e->getMessage());
        
        // Dodanie komunikatu w panelu administracyjnym
        add_action('admin_notices', 'losowe_cytaty_widget_load_error_notice');
    }
}

/**
 * Komunikat o błędzie ładowania widżetu
 */
function losowe_cytaty_widget_load_error_notice() {
    $error_message = get_option('losowe_cytaty_widget_load_error');
    
    if ($error_message === true) {
        $message = __('Plugin "Losowe Cytaty" - nie można załadować klasy widżetu Elementora.', 'losowe-cytaty');
    } else {
        $message = sprintf(
            /* translators: %s: komunikat błędu */
            esc_html__('Plugin "Losowe Cytaty" - błąd rejestracji widżetu Elementora: %s', 'losowe-cytaty'),
            esc_html($error_message)
        );
    }
    
    echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p>';
    echo '<p>' . esc_html__('Uwaga: Możesz nadal korzystać z funkcji shortcode [losowy_cytat] bez widżetu Elementora.', 'losowe-cytaty') . '</p></div>';
}

// Używamy odpowiedniego hooka w zależności od wersji Elementora
// Dodajemy niski priorytet, aby upewnić się, że Elementor jest w pełni załadowany
if (did_action('elementor/loaded')) {
    if (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.5.0', '>=')) {
        add_action('elementor/widgets/register', 'losowe_cytaty_register_elementor_widgets', 99);
    } else {
        add_action('elementor/widgets/widgets_registered', 'losowe_cytaty_register_elementor_widgets', 99);
    }
}

/**
 * Dodanie stylów dla widżetu Elementor
 */
function losowe_cytaty_elementor_styles() {
    // Sprawdzenie czy Elementor jest dostępny
    if (!class_exists('\Elementor\Plugin')) {
        return;
    }
    
    // Określenie czy używać wersji minifikowanych czy nie
    $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    
    wp_enqueue_style(
        'losowe-cytaty-elementor',
        LOSOWE_CYTATY_URL . 'assets/css/elementor-widget' . $suffix . '.css',
        array(),
        LOSOWE_CYTATY_VERSION
    );
}
add_action('elementor/frontend/after_enqueue_styles', 'losowe_cytaty_elementor_styles');
add_action('elementor/preview/enqueue_styles', 'losowe_cytaty_elementor_styles');