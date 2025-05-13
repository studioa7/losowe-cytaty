<?php
/**
 * Plugin Name: Losowe Cytaty
 * Plugin URI: https://wordpress.org/plugins/losowe-cytaty
 * Description: Wtyczka dodająca widżet do wyświetlania losowych cytatów. Kompatybilna z Elementorem oraz standardowym edytorem WordPress.
 * Version: 1.0.6
 * Author: Dawid Ziółkowski, Studio A7
 * Author URI: https://studioa7.pl
 * Text Domain: losowe-cytaty
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Zabezpieczenie przed bezpośrednim dostępem do pliku
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Definicje stałych
define('LOSOWE_CYTATY_VERSION', '1.0.6');
define('LOSOWE_CYTATY_PATH', plugin_dir_path(__FILE__));
define('LOSOWE_CYTATY_URL', plugin_dir_url(__FILE__));
define('LOSOWE_CYTATY_BASENAME', plugin_basename(__FILE__));

// Sprawdzenie czy Elementor jest aktywny
function losowe_cytaty_check_elementor() {
    return did_action('elementor/loaded');
}

// Komunikat o braku Elementora - informacyjny, nie błąd
function losowe_cytaty_elementor_notice() {
    $message = esc_html__('Plugin "Losowe Cytaty" działa w trybie podstawowym. Zainstaluj i aktywuj plugin Elementor, aby korzystać z zaawansowanych funkcji widżetu.', 'losowe-cytaty');
    
    printf(
        '<div class="%s"><p>%s</p></div>',
        'notice notice-info is-dismissible',
        esc_html($message)
    );
}

// Inicjalizacja wtyczki
function losowe_cytaty_init() {
    // Ładowanie plików
    require_once LOSOWE_CYTATY_PATH . 'includes/database.php';
    require_once LOSOWE_CYTATY_PATH . 'includes/admin-panel.php';
    require_once LOSOWE_CYTATY_PATH . 'includes/gutenberg-block.php';
    
    // Rejestracja shortcode niezależnie od Elementora
    add_shortcode('losowy_cytat', 'losowe_cytaty_shortcode');
    
    // Rejestracja standardowego widżetu WordPress
    add_action('widgets_init', 'losowe_cytaty_register_wp_widget');
    
    // Sprawdzenie czy Elementor jest aktywny
    if (losowe_cytaty_check_elementor()) {
        // Dodanie opóźnienia dla zapewnienia, że Elementor jest w pełni załadowany
        add_action('elementor/widgets/widgets_registered', function() {
            require_once LOSOWE_CYTATY_PATH . 'includes/elementor-widget.php';
        }, 20);
    } else {
        // Wyświetlenie informacji o braku Elementora (tylko w panelu administracyjnym)
        if (is_admin()) {
            add_action('admin_notices', 'losowe_cytaty_elementor_notice');
        }
    }
}
add_action('plugins_loaded', 'losowe_cytaty_init');

/**
 * Klasa standardowego widżetu WordPress
 */
class Losowe_Cytaty_WP_Widget extends WP_Widget {
    /**
     * Konstruktor
     */
    public function __construct() {
        parent::__construct(
            'losowe_cytaty_widget',
            esc_html__('Losowy Cytat', 'losowe-cytaty'),
            array(
                'description' => esc_html__('Wyświetla losowy cytat z bazy danych.', 'losowe-cytaty'),
                'classname' => 'losowe-cytaty-widget',
            )
        );
        
        // Dodanie stylów dla widżetu
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }
    
    /**
     * Dodanie stylów dla widżetu
     */
    public function enqueue_styles() {
        // Określenie czy używać wersji minifikowanych czy nie
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        
        wp_enqueue_style(
            'losowe-cytaty-widget',
            LOSOWE_CYTATY_URL . 'assets/css/elementor-widget' . $suffix . '.css',
            array(),
            LOSOWE_CYTATY_VERSION
        );
    }
    
    /**
     * Wyświetlanie widżetu na stronie
     */
    public function widget($args, $instance) {
        echo wp_kses_post($args['before_widget']);
        
        if (!empty($instance['title'])) {
            echo wp_kses_post($args['before_title']) . wp_kses_post(apply_filters('widget_title', $instance['title'])) . wp_kses_post($args['after_title']);
        }
        
        $show_author = !empty($instance['show_author']);
        $show_quote_icon = !empty($instance['show_quote_icon']);
        
        $quote = losowe_cytaty_get_current_quote();
        
        if (!$quote) {
            printf(
                '<div class="%s">%s</div>',
                'losowe-cytaty-empty',
                esc_html__('Brak cytatów w bazie danych.', 'losowe-cytaty')
            );
        } else {
            $classes = 'losowe-cytaty-quote';
            if ($show_quote_icon) {
                $classes .= ' show-quote-icon';
            }
            
            // Dodanie klasy dla widżetu, aby można było zastosować style dla ikony cytatu
            $widget_classes = 'losowe-cytaty-widget';
            if ($show_quote_icon) {
                $widget_classes .= ' show-quote-icon';
            }
            
            printf(
                '<div class="%s">',
                esc_attr($widget_classes)
            );
            
            printf(
                '<blockquote class="%s" aria-label="%s">',
                esc_attr($classes),
                esc_attr__('Losowy cytat', 'losowe-cytaty')
            );
            
            printf('<p>%s</p>', esc_html($quote['quote']));
            
            if ($show_author && !empty($quote['author'])) {
                printf(
                    '<cite aria-label="%s">— %s</cite>',
                    esc_attr__('Autor cytatu', 'losowe-cytaty'),
                    esc_html($quote['author'])
                );
            }
            
            echo '</blockquote></div>'; // Zamknięcie blockquote i div.losowe-cytaty-widget
        }
        
        echo wp_kses_post($args['after_widget']);
    }
    
    /**
     * Formularz ustawień widżetu w panelu administracyjnym
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $show_author = !empty($instance['show_author']);
        $show_quote_icon = !empty($instance['show_quote_icon']);
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Tytuł:', 'losowe-cytaty'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_author')); ?>" name="<?php echo esc_attr($this->get_field_name('show_author')); ?>" <?php checked($show_author); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_author')); ?>"><?php esc_html_e('Pokaż autora', 'losowe-cytaty'); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_quote_icon')); ?>" name="<?php echo esc_attr($this->get_field_name('show_quote_icon')); ?>" <?php checked($show_quote_icon); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_quote_icon')); ?>"><?php esc_html_e('Pokaż ikonę cytatu', 'losowe-cytaty'); ?></label>
        </p>
        <p>
            <?php
            $frequency = get_option('losowe_cytaty_refresh_frequency', 'daily');
            $frequency_text = '';
            
            switch ($frequency) {
                case 'daily':
                    $frequency_text = esc_html__('raz dziennie', 'losowe-cytaty');
                    break;
                case 'hourly':
                    $frequency_text = esc_html__('raz na godzinę', 'losowe-cytaty');
                    break;
                case 'half_hour':
                    $frequency_text = esc_html__('raz na pół godziny', 'losowe-cytaty');
                    break;
                case 'quarter_hour':
                    $frequency_text = esc_html__('raz na kwadrans', 'losowe-cytaty');
                    break;
                case 'five_minutes':
                    $frequency_text = esc_html__('raz na 5 minut', 'losowe-cytaty');
                    break;
                case 'on_reload':
                    $frequency_text = esc_html__('przy każdym przeładowaniu strony', 'losowe-cytaty');
                    break;
            }
            
            /* translators: %s: częstotliwość odświeżania cytatu (np. "raz dziennie", "raz na godzinę") */
            printf(
                esc_html__('Cytat jest losowany automatycznie %s. Możesz ręcznie wylosować nowy cytat w panelu administracyjnym.', 'losowe-cytaty'),
                wp_kses_post($frequency_text)
            );
            ?>
        </p>
        <?php
    }
    
    /**
     * Zapisywanie ustawień widżetu
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['show_author'] = !empty($new_instance['show_author']);
        $instance['show_quote_icon'] = !empty($new_instance['show_quote_icon']);
        
        return $instance;
    }
}

/**
 * Rejestracja standardowego widżetu WordPress
 */
function losowe_cytaty_register_wp_widget() {
    register_widget('Losowe_Cytaty_WP_Widget');
}

/**
 * Shortcode [losowy_cytat] - dostępny niezależnie od Elementora
 */
function losowe_cytaty_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_author' => 'true',
        'show_quote_icon' => get_option('losowe_cytaty_show_quote_icon', '1') ? 'true' : 'false',
    ), $atts, 'losowy_cytat');
    
    $show_author = filter_var($atts['show_author'], FILTER_VALIDATE_BOOLEAN);
    $show_quote_icon = filter_var($atts['show_quote_icon'], FILTER_VALIDATE_BOOLEAN);
    
    $quote = losowe_cytaty_get_current_quote();
    
    if (!$quote) {
        return '<div class="losowe-cytaty-empty">' . __('Brak cytatów w bazie danych.', 'losowe-cytaty') . '</div>';
    }
    
    $classes = 'losowe-cytaty-quote';
    if ($show_quote_icon) {
        $classes .= ' show-quote-icon';
    }
    
    // Dodanie klasy dla widżetu, aby można było zastosować style dla ikony cytatu
    $widget_classes = 'losowe-cytaty-widget';
    if ($show_quote_icon) {
        $widget_classes .= ' show-quote-icon';
    }
    
    $output = '<div class="' . esc_attr($widget_classes) . '">';
    $output .= '<blockquote class="' . esc_attr($classes) . '" aria-label="' . esc_attr__('Losowy cytat', 'losowe-cytaty') . '">';
    $output .= '<p>' . esc_html($quote['quote']) . '</p>';
    
    if ($show_author && !empty($quote['author'])) {
        $output .= '<cite aria-label="' . esc_attr__('Autor cytatu', 'losowe-cytaty') . '">— ' . esc_html($quote['author']) . '</cite>';
    }
    
    $output .= '</blockquote>';
    $output .= '</div>'; // Zamknięcie div.losowe-cytaty-widget
    
    return $output;
}

/**
 * Dodanie niestandardowych interwałów cron
 */
function losowe_cytaty_add_cron_intervals($schedules) {
    $schedules['half_hour'] = array(
        'interval' => 1800, // 30 minut
        'display'  => esc_html__('Co 30 minut', 'losowe-cytaty')
    );
    
    $schedules['quarter_hour'] = array(
        'interval' => 900, // 15 minut
        'display'  => esc_html__('Co 15 minut', 'losowe-cytaty')
    );
    
    $schedules['five_minutes'] = array(
        'interval' => 300, // 5 minut
        'display'  => esc_html__('Co 5 minut', 'losowe-cytaty')
    );
    
    return $schedules;
}
add_filter('cron_schedules', 'losowe_cytaty_add_cron_intervals');

// Aktywacja wtyczki
function losowe_cytaty_activate() {
    // Utworzenie tabeli w bazie danych
    require_once LOSOWE_CYTATY_PATH . 'includes/database.php';
    losowe_cytaty_create_database_table();
    
    // Dodanie domyślnych cytatów
    losowe_cytaty_add_default_quotes();
    
    // Ustawienie domyślnej częstotliwości odświeżania
    add_option('losowe_cytaty_refresh_frequency', 'daily');
    
    // Ustawienie domyślnych opcji stylizacji
    add_option('losowe_cytaty_text_color', '#333333');
    add_option('losowe_cytaty_background_color', '#f9f9f9');
    add_option('losowe_cytaty_border_color', '#2271b1');
    add_option('losowe_cytaty_border_width', '4');
    add_option('losowe_cytaty_border_radius', '0');
    add_option('losowe_cytaty_author_color', '#666666');
    add_option('losowe_cytaty_show_quote_icon', '1');
    add_option('losowe_cytaty_quote_icon_color', '#e0e0e0');
    
    // Ustawienie opcji dla losowania cytatu
    losowe_cytaty_schedule_quote_refresh();
    
    // Zapisanie daty aktywacji
    update_option('losowe_cytaty_activation_date', current_time('timestamp'));
}
register_activation_hook(__FILE__, 'losowe_cytaty_activate');

/**
 * Ustawienie harmonogramu odświeżania cytatu
 */
function losowe_cytaty_schedule_quote_refresh() {
    // Usunięcie wszystkich zaplanowanych zadań
    wp_clear_scheduled_hook('losowe_cytaty_daily_event');
    wp_clear_scheduled_hook('losowe_cytaty_refresh_event');
    
    // Pobranie ustawionej częstotliwości
    $frequency = get_option('losowe_cytaty_refresh_frequency', 'daily');
    
    // Jeśli wybrano opcję "przy przeładowaniu strony", nie ustawiamy crona
    if ($frequency === 'on_reload') {
        return;
    }
    
    // Ustawienie odpowiedniego harmonogramu
    if ($frequency === 'daily') {
        wp_schedule_event(time(), 'daily', 'losowe_cytaty_daily_event');
    } else {
        wp_schedule_event(time(), $frequency, 'losowe_cytaty_refresh_event');
    }
}

// Deaktywacja wtyczki
function losowe_cytaty_deactivate() {
    // Usunięcie wszystkich zaplanowanych zadań
    wp_clear_scheduled_hook('losowe_cytaty_daily_event');
    wp_clear_scheduled_hook('losowe_cytaty_refresh_event');
}
register_deactivation_hook(__FILE__, 'losowe_cytaty_deactivate');

// Hook dla odświeżania cytatu z różnymi częstotliwościami
add_action('losowe_cytaty_refresh_event', 'losowe_cytaty_daily_quote_selection');

// Hook dla zapisywania zmian w ustawieniach częstotliwości
function losowe_cytaty_update_refresh_frequency($old_value, $new_value) {
    if ($old_value !== $new_value) {
        losowe_cytaty_schedule_quote_refresh();
    }
}
add_action('update_option_losowe_cytaty_refresh_frequency', 'losowe_cytaty_update_refresh_frequency', 10, 2);

// Odinstalowanie wtyczki
function losowe_cytaty_uninstall() {
    // Usunięcie tabeli z bazy danych
    global $wpdb;
    $table_name = $wpdb->prefix . 'losowe_cytaty';
    
    // Sprawdzenie czy tabela istnieje przed jej usunięciem
    // Sprawdzenie cache dla istnienia tabeli
    $cache_key = 'losowe_cytaty_table_exists';
    $table_exists = wp_cache_get($cache_key);
    
    if ($table_exists === false) {
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $table_name
            )
        );
        // Zapisanie do cache na krótki czas (60 sekund) - wystarczający na przeprowadzenie operacji odinstalowania
        wp_cache_set($cache_key, $table_exists, '', 60);
    }
    
    if ($table_exists) {
        // W przypadku nazw tabel, WordPress nie zaleca używania placeholderów
        // Bezpieczne usunięcie tabeli z użyciem $wpdb->prepare i placeholdera %i dla nazwy tabeli
        // Użycie $wpdb->prepare z WPDB::prepare_table_name() dla nazwy tabeli
        $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i", $table_name));
        
        // Usunięcie cache po usunięciu tabeli
        wp_cache_delete($cache_key);
    }
    
    // Usunięcie opcji
    delete_option('losowe_cytaty_activation_date');
    delete_option('losowe_cytaty_current_quote_id');
    delete_option('losowe_cytaty_last_change_date');
    delete_option('losowe_cytaty_missing_elementor_classes');
    delete_option('losowe_cytaty_widget_load_error');
    delete_option('losowe_cytaty_refresh_frequency');
    
    // Usunięcie opcji stylizacji
    delete_option('losowe_cytaty_text_color');
    delete_option('losowe_cytaty_background_color');
    delete_option('losowe_cytaty_border_color');
    delete_option('losowe_cytaty_border_width');
    delete_option('losowe_cytaty_border_radius');
    delete_option('losowe_cytaty_author_color');
    delete_option('losowe_cytaty_show_quote_icon');
    delete_option('losowe_cytaty_quote_icon_color');
    
    // Usunięcie opcji transient dla bloku Gutenberga
    delete_transient('losowe_cytaty_block_editor_assets');
}
register_uninstall_hook(__FILE__, 'losowe_cytaty_uninstall');

// Funkcja do codziennego losowania cytatu
function losowe_cytaty_daily_quote_selection() {
    require_once LOSOWE_CYTATY_PATH . 'includes/database.php';
    losowe_cytaty_select_random_quote();
}
add_action('losowe_cytaty_daily_event', 'losowe_cytaty_daily_quote_selection');

// Dodanie linku do ustawień w panelu wtyczek
function losowe_cytaty_add_settings_link($links) {
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=losowe-cytaty-settings')) . '">' . esc_html__('Ustawienia', 'losowe-cytaty') . '</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . LOSOWE_CYTATY_BASENAME, 'losowe_cytaty_add_settings_link');

// Ładowanie tekstów tłumaczeń
function losowe_cytaty_load_textdomain() {
    load_plugin_textdomain('losowe-cytaty', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/**
 * Sprawdzenie czy katalog tłumaczeń istnieje, jeśli nie - utwórz go
 */
function losowe_cytaty_check_languages_dir() {
    $languages_dir = plugin_dir_path(__FILE__) . 'languages';
    if (!file_exists($languages_dir)) {
        wp_mkdir_p($languages_dir);
    }
}
add_action('plugins_loaded', 'losowe_cytaty_check_languages_dir', 5);
add_action('plugins_loaded', 'losowe_cytaty_load_textdomain');

/**
 * Generowanie stylów CSS na podstawie opcji z panelu administracyjnego
 */
function losowe_cytaty_generate_custom_css() {
    // Pobieranie opcji
    $text_color = get_option('losowe_cytaty_text_color', '#333333');
    $background_color = get_option('losowe_cytaty_background_color', '#f9f9f9');
    $border_color = get_option('losowe_cytaty_border_color', '#2271b1');
    $border_width = get_option('losowe_cytaty_border_width', '4');
    $border_radius = get_option('losowe_cytaty_border_radius', '0');
    $author_color = get_option('losowe_cytaty_author_color', '#666666');
    $show_quote_icon = get_option('losowe_cytaty_show_quote_icon', '1');
    $quote_icon_color = get_option('losowe_cytaty_quote_icon_color', '#e0e0e0');
    
    // Generowanie CSS
    $css = "
    .losowe-cytaty-quote {
        background-color: {$background_color};
        border-left: {$border_width}px solid {$border_color};
        border-radius: {$border_radius}px;
    }
    
    .losowe-cytaty-quote p {
        color: {$text_color};
    }
    
    .losowe-cytaty-quote cite {
        color: {$author_color};
    }
    ";
    
    // Dodanie stylów dla ikony cytatu
    if ($show_quote_icon) {
        $css .= "
        .losowe-cytaty-widget.show-quote-icon .losowe-cytaty-quote:before,
        .losowe-cytaty-quote.show-quote-icon:before {
            color: {$quote_icon_color};
        }
        ";
    }
    
    return $css;
}

/**
 * Dodanie niestandardowych stylów do strony
 */
function losowe_cytaty_add_custom_styles() {
    $custom_css = losowe_cytaty_generate_custom_css();
    
    if (!empty($custom_css)) {
        $safe_css = wp_strip_all_tags($custom_css);
        printf(
            '<style id="losowe-cytaty-custom-styles">%s</style>',
            esc_html($safe_css)
        );
    }
}
add_action('wp_head', 'losowe_cytaty_add_custom_styles');