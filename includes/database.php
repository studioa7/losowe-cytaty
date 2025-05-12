<?php
/**
 * Obsługa bazy danych dla wtyczki Losowe Cytaty
 *
 * @package Losowe_Cytaty
 */

// Zabezpieczenie przed bezpośrednim dostępem do pliku
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Tworzenie tabeli w bazie danych
 */
function losowe_cytaty_create_database_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'losowe_cytaty';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        quote text NOT NULL,
        author varchar(255) DEFAULT '' NOT NULL,
        date_added datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Dodawanie nowego cytatu do bazy danych
 *
 * @param string $quote Treść cytatu
 * @param string $author Autor cytatu (opcjonalnie)
 * @return int|false ID dodanego cytatu lub false w przypadku błędu
 */
/**
 * Dodawanie nowego cytatu do bazy danych z sanityzacją danych wejściowych
 *
 * @param string $quote Treść cytatu
 * @param string $author Autor cytatu (opcjonalnie)
 * @return int|false ID dodanego cytatu lub false w przypadku błędu
 */
function losowe_cytaty_add_quote($quote, $author = '') {
    // Sanityzacja danych wejściowych
    $quote = sanitize_textarea_field($quote);
    $author = sanitize_text_field($author);
    global $wpdb;
    $table_name = $wpdb->prefix . 'losowe_cytaty';

    $result = $wpdb->insert(
        $table_name,
        array(
            'quote' => $quote,
            'author' => $author,
        ),
        array(
            '%s',
            '%s',
        )
    );

    if ($result) {
        // Usunięcie cache po dodaniu nowego cytatu
        wp_cache_delete('losowe_cytaty_all_quotes');
        wp_cache_delete('losowe_cytaty_count');
        return $wpdb->insert_id;
    }

    return false;
}

/**
 * Dodawanie domyślnych cytatów przy aktywacji wtyczki
 */
function losowe_cytaty_add_default_quotes() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'losowe_cytaty';
    
    // Sprawdzenie czy tabela jest pusta
    $count = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM %i", $table_name)
    );
    
    if ($count == 0) {
        // Dodanie kilku przykładowych cytatów
        losowe_cytaty_add_quote('Życie jest tym, co z nim zrobimy.', 'Eleanor Roosevelt');
        losowe_cytaty_add_quote('Bądź zmianą, którą pragniesz ujrzeć w świecie.', 'Mahatma Gandhi');
        losowe_cytaty_add_quote('Nie liczy się to, ile posiadasz, ale ile dajesz.', 'Albert Einstein');
        losowe_cytaty_add_quote('Najlepszym sposobem przewidywania przyszłości jest jej tworzenie.', 'Peter Drucker');
        losowe_cytaty_add_quote('Sukces to suma małych wysiłków, powtarzanych dzień po dniu.', 'Robert Collier');
    }
}

/**
 * Pobieranie wszystkich cytatów z bazy danych
 *
 * @return array Tablica z cytatami
 */
function losowe_cytaty_get_all_quotes() {
    // Sprawdzenie cache
    $cache_key = 'losowe_cytaty_all_quotes';
    $quotes = wp_cache_get($cache_key);
    
    if ($quotes === false) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'losowe_cytaty';
        
        $quotes = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM %i ORDER BY id DESC", $table_name),
            ARRAY_A
        );
        
        // Zapisanie do cache na 1 godzinę (3600 sekund)
        wp_cache_set($cache_key, $quotes, '', 3600);
    }
    
    return $quotes;
}

/**
 * Pobieranie pojedynczego cytatu po ID
 *
 * @param int $id ID cytatu
 * @return array|null Dane cytatu lub null jeśli nie znaleziono
 */
function losowe_cytaty_get_quote_by_id($id) {
    // Sanityzacja ID
    $id = absint($id);
    
    // Sprawdzenie cache
    $cache_key = 'losowe_cytaty_quote_' . $id;
    $quote = wp_cache_get($cache_key);
    
    if ($quote === false) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'losowe_cytaty';
        
        $quote = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM %i WHERE id = %d", $table_name, $id),
            ARRAY_A
        );
        
        // Zapisanie do cache na 1 godzinę (3600 sekund)
        if ($quote) {
            wp_cache_set($cache_key, $quote, '', 3600);
        }
    }
    
    return $quote;
}

/**
 * Usuwanie cytatu po ID
 *
 * @param int $id ID cytatu do usunięcia
 * @return bool Czy operacja się powiodła
 */
function losowe_cytaty_delete_quote($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'losowe_cytaty';
    
    $result = $wpdb->delete(
        $table_name,
        array('id' => $id),
        array('%d')
    );
    
    if ($result !== false) {
        // Usunięcie z cache
        wp_cache_delete('losowe_cytaty_quote_' . $id);
        wp_cache_delete('losowe_cytaty_all_quotes');
        wp_cache_delete('losowe_cytaty_count');
        wp_cache_delete('losowe_cytaty_random_quote');
        wp_cache_delete('losowe_cytaty_current_quote');
    }
    
    return $result !== false;
}

/**
 * Aktualizacja cytatu
 *
 * @param int $id ID cytatu do aktualizacji
 * @param string $quote Nowa treść cytatu
 * @param string $author Nowy autor cytatu
 * @return bool Czy operacja się powiodła
 */
/**
 * Aktualizacja cytatu z sanityzacją danych wejściowych
 *
 * @param int $id ID cytatu do aktualizacji
 * @param string $quote Nowa treść cytatu
 * @param string $author Nowy autor cytatu
 * @return bool Czy operacja się powiodła
 */
function losowe_cytaty_update_quote($id, $quote, $author = '') {
    // Sanityzacja danych wejściowych
    $id = absint($id);
    $quote = sanitize_textarea_field($quote);
    $author = sanitize_text_field($author);
    global $wpdb;
    $table_name = $wpdb->prefix . 'losowe_cytaty';
    
    $result = $wpdb->update(
        $table_name,
        array(
            'quote' => $quote,
            'author' => $author,
        ),
        array('id' => $id),
        array(
            '%s',
            '%s',
        ),
        array('%d')
    );
    
    if ($result !== false) {
        // Usunięcie z cache
        wp_cache_delete('losowe_cytaty_quote_' . $id);
        wp_cache_delete('losowe_cytaty_all_quotes');
        wp_cache_delete('losowe_cytaty_current_quote');
    }
    
    return $result !== false;
}

/**
 * Losowanie cytatu i zapisanie go jako aktualny
 *
 * @return array|null Wylosowany cytat lub null w przypadku braku cytatów
 */
function losowe_cytaty_select_random_quote() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'losowe_cytaty';
    
    // Sprawdzenie liczby cytatów - użycie cache
    $cache_key = 'losowe_cytaty_count';
    $count = wp_cache_get($cache_key);
    
    if ($count === false) {
        $count = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM %i", $table_name)
        );
        wp_cache_set($cache_key, $count, '', 3600);
    }
    
    if ($count == 0) {
        return null;
    }
    
    // Losowanie cytatu - zawsze losujemy nowy cytat przy wywołaniu tej funkcji
    $quote = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM %i ORDER BY RAND() LIMIT 1", $table_name),
        ARRAY_A
    );
    
    if ($quote) {
        // Zapisanie ID wylosowanego cytatu
        update_option('losowe_cytaty_current_quote_id', $quote['id']);
        update_option('losowe_cytaty_last_change_date', current_time('timestamp'));
        
        // Wyczyszczenie cache dla aktualnego cytatu, aby wymusić pobranie nowego
        wp_cache_delete('losowe_cytaty_current_quote');
    }
    
    return $quote;
}

/**
 * Pobieranie aktualnego cytatu
 *
 * @return array|null Aktualny cytat lub null jeśli nie znaleziono
 */
function losowe_cytaty_get_current_quote() {
    // Sprawdzenie cache
    $cache_key = 'losowe_cytaty_current_quote';
    $current_quote = wp_cache_get($cache_key);
    
    if ($current_quote !== false) {
        return $current_quote;
    }
    
    $quote_id = get_option('losowe_cytaty_current_quote_id');
    
    if (!$quote_id) {
        // Jeśli nie ma zapisanego ID, wylosuj nowy cytat
        $quote = losowe_cytaty_select_random_quote();
        if ($quote) {
            wp_cache_set($cache_key, $quote, '', 3600);
        }
        return $quote;
    }
    
    $quote = losowe_cytaty_get_quote_by_id($quote_id);
    
    if (!$quote) {
        // Jeśli cytat o danym ID nie istnieje, wylosuj nowy
        $quote = losowe_cytaty_select_random_quote();
        if ($quote) {
            wp_cache_set($cache_key, $quote, '', 3600);
        }
        return $quote;
    }
    
    // Zapisanie do cache na 1 godzinę (3600 sekund)
    wp_cache_set($cache_key, $quote, '', 3600);
    
    return $quote;
}

/**
 * Importowanie cytatów z pliku tekstowego
 *
 * @param string $file_path Ścieżka do pliku
 * @return array Wynik importu (liczba zaimportowanych, błędy)
 */
/**
 * Importowanie cytatów z pliku tekstowego z sanityzacją danych
 *
 * @param string $file_path Ścieżka do pliku
 * @return array Wynik importu (liczba zaimportowanych, błędy)
 */
function losowe_cytaty_import_from_file($file_path) {
    $result = array(
        'imported' => 0,
        'errors' => 0,
        'messages' => array(),
    );
    
    if (!file_exists($file_path)) {
        $result['messages'][] = __('Plik nie istnieje.', 'losowe-cytaty');
        return $result;
    }
    
    $content = wp_kses(file_get_contents($file_path), array());
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (empty($line)) {
            continue;
        }
        
        // Sprawdzenie czy linia zawiera autora (format: "Cytat" - Autor)
        if (preg_match('/^"(.+)"\s*-\s*(.+)$/', $line, $matches)) {
            $quote = sanitize_textarea_field(trim($matches[1]));
            $author = sanitize_text_field(trim($matches[2]));
        } else {
            $quote = sanitize_textarea_field($line);
            $author = '';
        }
        
        $result_id = losowe_cytaty_add_quote($quote, $author);
        
        if ($result_id) {
            $result['imported']++;
        } else {
            $result['errors']++;
        }
    }
    
    $result['messages'][] = sprintf(
        /* translators: %1$d: liczba zaimportowanych cytatów, %2$d: liczba błędów */
        __('Zaimportowano %1$d cytatów. Błędów: %2$d.', 'losowe-cytaty'),
        $result['imported'],
        $result['errors']
    );
    
    return $result;
}