<?php
/**
 * Panel administracyjny dla wtyczki Losowe Cytaty
 *
 * @package Losowe_Cytaty
 */

// Zabezpieczenie przed bezpośrednim dostępem do pliku
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Inicjalizacja ustawień
 */
function losowe_cytaty_settings_init() {
    // Rejestracja ustawień
    register_setting('losowe_cytaty_settings', 'losowe_cytaty_refresh_frequency');
    
    // Rejestracja ustawień stylów
    register_setting('losowe_cytaty_settings', 'losowe_cytaty_text_color');
    register_setting('losowe_cytaty_settings', 'losowe_cytaty_background_color');
    register_setting('losowe_cytaty_settings', 'losowe_cytaty_border_color');
    register_setting('losowe_cytaty_settings', 'losowe_cytaty_border_width');
    register_setting('losowe_cytaty_settings', 'losowe_cytaty_border_radius');
    register_setting('losowe_cytaty_settings', 'losowe_cytaty_author_color');
    register_setting('losowe_cytaty_settings', 'losowe_cytaty_quote_icon_color');
    register_setting('losowe_cytaty_settings', 'losowe_cytaty_show_quote_icon');
}
add_action('admin_init', 'losowe_cytaty_settings_init');

/**
 * Dodanie menu w panelu administracyjnym
 */
function losowe_cytaty_add_admin_menu() {
    add_menu_page(
        __('Losowe Cytaty', 'losowe-cytaty'),
        __('Losowe Cytaty', 'losowe-cytaty'),
        'manage_options',
        'losowe-cytaty',
        'losowe_cytaty_admin_page',
        'dashicons-format-quote',
        30
    );
    
    add_submenu_page(
        'losowe-cytaty',
        __('Zarządzaj Cytatami', 'losowe-cytaty'),
        __('Zarządzaj Cytatami', 'losowe-cytaty'),
        'manage_options',
        'losowe-cytaty',
        'losowe_cytaty_admin_page'
    );
    
    add_submenu_page(
        'losowe-cytaty',
        __('Import Cytatów', 'losowe-cytaty'),
        __('Import Cytatów', 'losowe-cytaty'),
        'manage_options',
        'losowe-cytaty-import',
        'losowe_cytaty_import_page'
    );
    
    add_submenu_page(
        'losowe-cytaty',
        __('Ustawienia', 'losowe-cytaty'),
        __('Ustawienia', 'losowe-cytaty'),
        'manage_options',
        'losowe-cytaty-settings',
        'losowe_cytaty_settings_page'
    );
}
add_action('admin_menu', 'losowe_cytaty_add_admin_menu');

/**
 * Rejestracja stylów i skryptów dla panelu administracyjnego
 */
function losowe_cytaty_admin_enqueue_scripts($hook) {
    // Sprawdzenie czy jesteśmy na stronie naszej wtyczki
    if (strpos($hook, 'losowe-cytaty') === false) {
        return;
    }
    
    // Określenie czy używać wersji minifikowanych czy nie
    $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    
    // Dodanie stylów
    wp_enqueue_style(
        'losowe-cytaty-admin',
        LOSOWE_CYTATY_URL . 'assets/css/admin' . $suffix . '.css',
        array(),
        LOSOWE_CYTATY_VERSION
    );
    
    // Dodanie skryptów
    wp_enqueue_script(
        'losowe-cytaty-admin',
        LOSOWE_CYTATY_URL . 'assets/js/admin' . $suffix . '.js',
        array('jquery'),
        LOSOWE_CYTATY_VERSION,
        true
    );
    
    // Przekazanie danych do skryptu
    wp_localize_script(
        'losowe-cytaty-admin',
        'losoweCytatyAdmin',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('losowe_cytaty_nonce'),
            'confirmDelete' => __('Czy na pewno chcesz usunąć ten cytat?', 'losowe-cytaty'),
            'confirmRandomize' => __('Czy na pewno chcesz wylosować nowy cytat? Aktualny cytat zostanie zastąpiony.', 'losowe-cytaty'),
            'lastChangeText' => __('Ostatnia zmiana:', 'losowe-cytaty')
        )
    );
}
add_action('admin_enqueue_scripts', 'losowe_cytaty_admin_enqueue_scripts');

/**
 * Główna strona panelu administracyjnego - zarządzanie cytatami
 */
function losowe_cytaty_admin_page() {
    // Obsługa akcji
    if (isset($_POST['action']) && sanitize_text_field(wp_unslash($_POST['action'])) == 'add_quote' && check_admin_referer('losowe_cytaty_add_quote')) {
        $quote = isset($_POST['quote']) ? sanitize_textarea_field(wp_unslash($_POST['quote'])) : '';
        $author = isset($_POST['author']) ? sanitize_text_field(wp_unslash($_POST['author'])) : '';
        
        if (!empty($quote)) {
            $result = losowe_cytaty_add_quote($quote, $author);
            
            if ($result) {
                add_settings_error(
                    'losowe_cytaty',
                    'quote_added',
                    __('Cytat został dodany pomyślnie.', 'losowe-cytaty'),
                    'success'
                );
            } else {
                add_settings_error(
                    'losowe_cytaty',
                    'quote_error',
                    __('Wystąpił błąd podczas dodawania cytatu.', 'losowe-cytaty'),
                    'error'
                );
            }
        } else {
            add_settings_error(
                'losowe_cytaty',
                'quote_empty',
                __('Treść cytatu nie może być pusta.', 'losowe-cytaty'),
                'error'
            );
        }
    }
    
    // Obsługa usuwania cytatu
    if (isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) == 'delete' && isset($_GET['quote_id']) && check_admin_referer('delete_quote_' . wp_unslash($_GET['quote_id']))) {
        $quote_id = intval(wp_unslash($_GET['quote_id']));
        $result = losowe_cytaty_delete_quote($quote_id);
        
        if ($result) {
            add_settings_error(
                'losowe_cytaty',
                'quote_deleted',
                __('Cytat został usunięty pomyślnie.', 'losowe-cytaty'),
                'success'
            );
            
            // Jeśli usunięto aktualny cytat, wylosuj nowy
            $current_id = get_option('losowe_cytaty_current_quote_id');
            if ($current_id == $quote_id) {
                losowe_cytaty_select_random_quote();
            }
        } else {
            add_settings_error(
                'losowe_cytaty',
                'quote_delete_error',
                __('Wystąpił błąd podczas usuwania cytatu.', 'losowe-cytaty'),
                'error'
            );
        }
    }
    
    // Obsługa edycji cytatu
    if (isset($_POST['action']) && sanitize_text_field(wp_unslash($_POST['action'])) == 'edit_quote' && check_admin_referer('losowe_cytaty_edit_quote')) {
        $quote_id = isset($_POST['quote_id']) ? intval(wp_unslash($_POST['quote_id'])) : 0;
        $quote = isset($_POST['quote']) ? sanitize_textarea_field(wp_unslash($_POST['quote'])) : '';
        $author = isset($_POST['author']) ? sanitize_text_field(wp_unslash($_POST['author'])) : '';
        
        if (!empty($quote) && $quote_id > 0) {
            $result = losowe_cytaty_update_quote($quote_id, $quote, $author);
            
            if ($result) {
                add_settings_error(
                    'losowe_cytaty',
                    'quote_updated',
                    __('Cytat został zaktualizowany pomyślnie.', 'losowe-cytaty'),
                    'success'
                );
            } else {
                add_settings_error(
                    'losowe_cytaty',
                    'quote_update_error',
                    __('Wystąpił błąd podczas aktualizacji cytatu.', 'losowe-cytaty'),
                    'error'
                );
            }
        } else {
            add_settings_error(
                'losowe_cytaty',
                'quote_empty',
                __('Treść cytatu nie może być pusta.', 'losowe-cytaty'),
                'error'
            );
        }
    }
    
    // Pobieranie wszystkich cytatów
    $quotes = losowe_cytaty_get_all_quotes();
    $current_quote_id = get_option('losowe_cytaty_current_quote_id');
    
    // Wyświetlenie komunikatów
    settings_errors('losowe_cytaty');
    
    // Formularz edycji cytatu
    $edit_mode = false;
    $edit_quote = null;
    
    if (isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) == 'edit' && isset($_GET['quote_id']) && isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'edit_quote_' . wp_unslash($_GET['quote_id']))) {
        $quote_id = intval(wp_unslash($_GET['quote_id']));
        $edit_quote = losowe_cytaty_get_quote_by_id($quote_id);
        
        if ($edit_quote) {
            $edit_mode = true;
        }
    }
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="losowe-cytaty-admin-container">
            <div class="losowe-cytaty-admin-form">
                <?php if ($edit_mode && $edit_quote): ?>
                    <h2><?php esc_html_e('Edytuj Cytat', 'losowe-cytaty'); ?></h2>
                    <form method="post" action="">
                        <?php wp_nonce_field('losowe_cytaty_edit_quote'); ?>
                        <input type="hidden" name="action" value="edit_quote">
                        <input type="hidden" name="quote_id" value="<?php echo esc_attr($edit_quote['id']); ?>">
                        
                        <div class="form-field">
                            <label for="quote"><?php esc_html_e('Treść cytatu', 'losowe-cytaty'); ?> <span class="required">*</span></label>
                            <textarea name="quote" id="quote" rows="4" required><?php echo esc_textarea($edit_quote['quote']); ?></textarea>
                        </div>
                        
                        <div class="form-field">
                            <label for="author"><?php esc_html_e('Autor', 'losowe-cytaty'); ?></label>
                            <input type="text" name="author" id="author" value="<?php echo esc_attr($edit_quote['author']); ?>">
                        </div>
                        
                        <div class="form-field">
                            <button type="submit" class="button button-primary"><?php esc_html_e('Zapisz zmiany', 'losowe-cytaty'); ?></button>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=losowe-cytaty')); ?>" class="button"><?php esc_html_e('Anuluj', 'losowe-cytaty'); ?></a>
                        </div>
                    </form>
                <?php else: ?>
                    <h2><?php esc_html_e('Dodaj Nowy Cytat', 'losowe-cytaty'); ?></h2>
                    <form method="post" action="">
                        <?php wp_nonce_field('losowe_cytaty_add_quote'); ?>
                        <input type="hidden" name="action" value="add_quote">
                        
                        <div class="form-field">
                            <label for="quote"><?php esc_html_e('Treść cytatu', 'losowe-cytaty'); ?> <span class="required">*</span></label>
                            <textarea name="quote" id="quote" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-field">
                            <label for="author"><?php esc_html_e('Autor', 'losowe-cytaty'); ?></label>
                            <input type="text" name="author" id="author">
                        </div>
                        
                        <div class="form-field">
                            <button type="submit" class="button button-primary"><?php esc_html_e('Dodaj cytat', 'losowe-cytaty'); ?></button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="losowe-cytaty-admin-list">
                <h2><?php esc_html_e('Lista Cytatów', 'losowe-cytaty'); ?></h2>
                
                <?php if (empty($quotes)): ?>
                    <p><?php esc_html_e('Brak cytatów w bazie danych.', 'losowe-cytaty'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('ID', 'losowe-cytaty'); ?></th>
                                <th><?php esc_html_e('Cytat', 'losowe-cytaty'); ?></th>
                                <th><?php esc_html_e('Autor', 'losowe-cytaty'); ?></th>
                                <th><?php esc_html_e('Data dodania', 'losowe-cytaty'); ?></th>
                                <th><?php esc_html_e('Akcje', 'losowe-cytaty'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quotes as $quote): ?>
                                <tr<?php echo ($quote['id'] == $current_quote_id) ? ' class="current-quote"' : ''; ?>>
                                    <td><?php echo esc_html($quote['id']); ?></td>
                                    <td>
                                        <?php echo esc_html($quote['quote']); ?>
                                        <?php if ($quote['id'] == $current_quote_id): ?>
                                            <span class="current-quote-badge"><?php esc_html_e('Aktualny', 'losowe-cytaty'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($quote['author']); ?></td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($quote['date_added']))); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=losowe-cytaty&action=edit&quote_id=' . $quote['id']), 'edit_quote_' . $quote['id'])); ?>" class="button button-small"><?php esc_html_e('Edytuj', 'losowe-cytaty'); ?></a>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=losowe-cytaty&action=delete&quote_id=' . $quote['id']), 'delete_quote_' . $quote['id'])); ?>" class="button button-small delete-quote"><?php esc_html_e('Usuń', 'losowe-cytaty'); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Strona importu cytatów
 */
function losowe_cytaty_import_page() {
    // Obsługa importu
    if (isset($_POST['action']) && sanitize_text_field(wp_unslash($_POST['action'])) == 'import_quotes' && check_admin_referer('losowe_cytaty_import')) {
        if (!empty($_FILES['import_file']['tmp_name'])) {
            $file_path = sanitize_text_field(wp_unslash($_FILES['import_file']['tmp_name']));
            $result = losowe_cytaty_import_from_file($file_path);
            
            if ($result['imported'] > 0) {
                add_settings_error(
                    'losowe_cytaty_import',
                    'import_success',
                    $result['messages'][0],
                    'success'
                );
            } else {
                add_settings_error(
                    'losowe_cytaty_import',
                    'import_error',
                    $result['messages'][0],
                    'error'
                );
            }
        } else {
            add_settings_error(
                'losowe_cytaty_import',
                'import_no_file',
                __('Nie wybrano pliku do importu.', 'losowe-cytaty'),
                'error'
            );
        }
    }
    
    // Wyświetlenie komunikatów
    settings_errors('losowe_cytaty_import');
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="losowe-cytaty-admin-container">
            <div class="losowe-cytaty-admin-form">
                <h2><?php esc_html_e('Import Cytatów z Pliku', 'losowe-cytaty'); ?></h2>
                
                <p><?php esc_html_e('Wybierz plik tekstowy (TXT) zawierający cytaty. Każdy cytat powinien znajdować się w osobnej linii.', 'losowe-cytaty'); ?></p>
                <p><?php esc_html_e('Format: "Treść cytatu" - Autor (opcjonalnie)', 'losowe-cytaty'); ?></p>
                <p><?php esc_html_e('Przykład: "Życie jest tym, co z nim zrobimy." - Eleanor Roosevelt', 'losowe-cytaty'); ?></p>
                
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('losowe_cytaty_import'); ?>
                    <input type="hidden" name="action" value="import_quotes">
                    
                    <div class="form-field">
                        <label for="import_file"><?php esc_html_e('Plik z cytatami', 'losowe-cytaty'); ?> <span class="required">*</span></label>
                        <input type="file" name="import_file" id="import_file" accept=".txt" required>
                    </div>
                    
                    <div class="form-field">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Importuj cytaty', 'losowe-cytaty'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Strona ustawień
 */
function losowe_cytaty_settings_page() {
    // Obsługa ręcznego losowania cytatu
    if (isset($_POST['action']) && sanitize_text_field(wp_unslash($_POST['action'])) == 'randomize_quote' && check_admin_referer('losowe_cytaty_randomize')) {
        $quote = losowe_cytaty_select_random_quote();
        
        if ($quote) {
            add_settings_error(
                'losowe_cytaty_settings',
                'randomize_success',
                __('Nowy cytat został wylosowany pomyślnie.', 'losowe-cytaty'),
                'success'
            );
        } else {
            add_settings_error(
                'losowe_cytaty_settings',
                'randomize_error',
                __('Nie udało się wylosować nowego cytatu. Sprawdź czy baza cytatów nie jest pusta.', 'losowe-cytaty'),
                'error'
            );
        }
    }
    
    // Pobieranie aktualnego cytatu
    $current_quote = losowe_cytaty_get_current_quote();
    $last_change_date = get_option('losowe_cytaty_last_change_date');
    
    // Wyświetlenie komunikatów
    settings_errors('losowe_cytaty_settings');
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="losowe-cytaty-admin-container">
            <div class="losowe-cytaty-admin-settings">
                <h2><?php esc_html_e('Aktualny Cytat', 'losowe-cytaty'); ?></h2>
                
                <?php if ($current_quote): ?>
                    <div class="current-quote-display">
                        <blockquote>
                            <p><?php echo esc_html($current_quote['quote']); ?></p>
                            <?php if (!empty($current_quote['author'])): ?>
                                <cite>— <?php echo esc_html($current_quote['author']); ?></cite>
                            <?php endif; ?>
                        </blockquote>
                        
                        <?php if ($last_change_date): ?>
                            <p class="quote-info">
                                <?php printf(
                                    /* translators: %s: data i czas ostatniej zmiany cytatu */
                                    esc_html__('Ostatnia zmiana: %s', 'losowe-cytaty'),
                                    esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_change_date))
                                ); ?>
                            </p>
                        <?php endif; ?>
                        
                        <form method="post" class="randomize-form">
                            <?php wp_nonce_field('losowe_cytaty_randomize'); ?>
                            <input type="hidden" name="action" value="randomize_quote">
                            <button type="submit" class="button button-primary randomize-button">
                                <span class="dashicons dashicons-randomize"></span>
                                <?php esc_html_e('Wylosuj nowy cytat', 'losowe-cytaty'); ?>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><?php esc_html_e('Brak cytatów w bazie danych. Dodaj cytaty, aby móc je wyświetlać.', 'losowe-cytaty'); ?></p>
                <?php endif; ?>
                
                <h2><?php esc_html_e('Ustawienia', 'losowe-cytaty'); ?></h2>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('losowe_cytaty_settings');
                    do_settings_sections('losowe_cytaty_settings');
                    ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Częstotliwość odświeżania cytatu', 'losowe-cytaty'); ?></th>
                            <td>
                                <select name="losowe_cytaty_refresh_frequency">
                                    <?php
                                    $current_frequency = get_option('losowe_cytaty_refresh_frequency', 'daily');
                                    $frequencies = array(
                                        'daily' => esc_html__('Raz dziennie', 'losowe-cytaty'),
                                        'hourly' => esc_html__('Raz na godzinę', 'losowe-cytaty'),
                                        'half_hour' => esc_html__('Raz na pół godziny', 'losowe-cytaty'),
                                        'quarter_hour' => esc_html__('Raz na kwadrans', 'losowe-cytaty'),
                                        'five_minutes' => esc_html__('Raz na 5 minut', 'losowe-cytaty'),
                                        'one_minute' => esc_html__('Raz na 1 minutę', 'losowe-cytaty'),
                                        'on_reload' => esc_html__('Przy przeładowaniu strony', 'losowe-cytaty'),
                                    );
                                    
                                    foreach ($frequencies as $value => $label) {
                                        echo '<option value="' . esc_attr($value) . '" ' . selected($current_frequency, $value, false) . '>' . esc_html($label) . '</option>';
                                    }
                                    ?>
                                </select>
                                <p class="description"><?php esc_html_e('Wybierz, jak często cytat ma być automatycznie odświeżany.', 'losowe-cytaty'); ?></p>
                                
                                <?php if (current_user_can('manage_options')): ?>
                                <div class="debug-info" style="margin-top: 10px; padding: 10px; background: #f8f8f8; border-left: 4px solid #646970;">
                                    <h4><?php esc_html_e('Informacje debugowania', 'losowe-cytaty'); ?></h4>
                                    <?php
                                    $last_change = get_option('losowe_cytaty_last_change_date', 0);
                                    $frequency = get_option('losowe_cytaty_refresh_frequency', 'daily');
                                    $cache_time = 0;
                                    
                                    if ($frequency !== 'on_reload') {
                                        require_once LOSOWE_CYTATY_PATH . 'includes/database.php';
                                        $cache_time = losowe_cytaty_get_cache_time($frequency);
                                    }
                                    
                                    $current_time = current_time('timestamp');
                                    $time_diff = $current_time - $last_change;
                                    $time_to_next = $cache_time - $time_diff;
                                    
                                    if ($frequency === 'on_reload') {
                                        printf(
                                            '<p>%s</p>',
                                            esc_html__('Tryb odświeżania: przy przeładowaniu strony (cytat jest losowany przy każdym odświeżeniu strony)', 'losowe-cytaty')
                                        );
                                    } else {
                                        printf(
                                            '<p>%s: %s</p>',
                                            esc_html__('Ostatnie odświeżenie', 'losowe-cytaty'),
                                            esc_html(date_i18n('Y-m-d H:i:s', $last_change))
                                        );
                                        
                                        printf(
                                            '<p>%s: %s</p>',
                                            esc_html__('Czas od ostatniego odświeżenia', 'losowe-cytaty'),
                                            esc_html(human_time_diff($last_change, $current_time))
                                        );
                                        
                                        if ($time_to_next > 0) {
                                            printf(
                                                '<p>%s: %s</p>',
                                                esc_html__('Czas do następnego odświeżenia', 'losowe-cytaty'),
                                                esc_html(human_time_diff($current_time, $current_time + $time_to_next))
                                            );
                                        } else {
                                            printf(
                                                '<p>%s</p>',
                                                esc_html__('Cytat powinien zostać odświeżony przy następnym odświeżeniu strony', 'losowe-cytaty')
                                            );
                                        }
                                    }
                                    ?>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    
                    <h3><?php esc_html_e('Style cytatu', 'losowe-cytaty'); ?></h3>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Kolor tekstu', 'losowe-cytaty'); ?></th>
                            <td>
                                <input type="color" name="losowe_cytaty_text_color" value="<?php echo esc_attr(get_option('losowe_cytaty_text_color', '#333333')); ?>" class="color-picker" />
                                <p class="description"><?php esc_html_e('Wybierz kolor tekstu cytatu.', 'losowe-cytaty'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Kolor tła', 'losowe-cytaty'); ?></th>
                            <td>
                                <input type="color" name="losowe_cytaty_background_color" value="<?php echo esc_attr(get_option('losowe_cytaty_background_color', '#f9f9f9')); ?>" class="color-picker" />
                                <p class="description"><?php esc_html_e('Wybierz kolor tła cytatu.', 'losowe-cytaty'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Kolor obramowania', 'losowe-cytaty'); ?></th>
                            <td>
                                <input type="color" name="losowe_cytaty_border_color" value="<?php echo esc_attr(get_option('losowe_cytaty_border_color', '#2271b1')); ?>" class="color-picker" />
                                <p class="description"><?php esc_html_e('Wybierz kolor obramowania cytatu.', 'losowe-cytaty'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Szerokość obramowania (px)', 'losowe-cytaty'); ?></th>
                            <td>
                                <input type="number" name="losowe_cytaty_border_width" value="<?php echo esc_attr(get_option('losowe_cytaty_border_width', '4')); ?>" min="0" max="20" step="1" />
                                <p class="description"><?php esc_html_e('Ustaw szerokość obramowania w pikselach.', 'losowe-cytaty'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Zaokrąglenie narożników (px)', 'losowe-cytaty'); ?></th>
                            <td>
                                <input type="number" name="losowe_cytaty_border_radius" value="<?php echo esc_attr(get_option('losowe_cytaty_border_radius', '0')); ?>" min="0" max="50" step="1" />
                                <p class="description"><?php esc_html_e('Ustaw zaokrąglenie narożników w pikselach.', 'losowe-cytaty'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Kolor autora', 'losowe-cytaty'); ?></th>
                            <td>
                                <input type="color" name="losowe_cytaty_author_color" value="<?php echo esc_attr(get_option('losowe_cytaty_author_color', '#666666')); ?>" class="color-picker" />
                                <p class="description"><?php esc_html_e('Wybierz kolor tekstu autora.', 'losowe-cytaty'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Pokaż ikonę cytatu', 'losowe-cytaty'); ?></th>
                            <td>
                                <input type="checkbox" name="losowe_cytaty_show_quote_icon" value="1" <?php checked('1', get_option('losowe_cytaty_show_quote_icon', '1')); ?> />
                                <p class="description"><?php esc_html_e('Zaznacz, aby wyświetlać ikonę cytatu.', 'losowe-cytaty'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top" class="quote-icon-color-row" <?php echo get_option('losowe_cytaty_show_quote_icon', '1') ? '' : 'style="display:none;"'; ?>>
                            <th scope="row"><?php esc_html_e('Kolor ikony cytatu', 'losowe-cytaty'); ?></th>
                            <td>
                                <input type="color" name="losowe_cytaty_quote_icon_color" value="<?php echo esc_attr(get_option('losowe_cytaty_quote_icon_color', '#e0e0e0')); ?>" class="color-picker" />
                                <p class="description"><?php esc_html_e('Wybierz kolor ikony cytatu.', 'losowe-cytaty'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>
                </form>
                
                <h2><?php esc_html_e('Informacje', 'losowe-cytaty'); ?></h2>
                <p><?php esc_html_e('Aby wyświetlić cytat na stronie, użyj widżetu Elementor "Losowy Cytat" lub shortcode [losowy_cytat].', 'losowe-cytaty'); ?></p>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Obsługa AJAX dla ręcznego losowania cytatu
 */
function losowe_cytaty_ajax_randomize() {
    // Sprawdzenie nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'losowe_cytaty_nonce')) {
        wp_send_json_error(array('message' => __('Błąd bezpieczeństwa.', 'losowe-cytaty')));
    }
    
    // Sprawdzenie uprawnień
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Brak uprawnień.', 'losowe-cytaty')));
    }
    
    // Losowanie cytatu
    $quote = losowe_cytaty_select_random_quote();
    
    if ($quote) {
        wp_send_json_success(array(
            'quote' => esc_html($quote['quote']),
            'author' => esc_html($quote['author']),
            'last_change' => esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp'))),
            'message' => esc_html__('Nowy cytat został wylosowany pomyślnie.', 'losowe-cytaty')
        ));
    } else {
        wp_send_json_error(array('message' => esc_html__('Nie udało się wylosować nowego cytatu. Sprawdź czy baza cytatów nie jest pusta.', 'losowe-cytaty')));
    }
}
add_action('wp_ajax_losowe_cytaty_randomize', 'losowe_cytaty_ajax_randomize');

// Shortcode został przeniesiony do głównego pliku wtyczki, aby był dostępny niezależnie od Elementora
// Funkcja shortcode została przeniesiona do głównego pliku wtyczki