=== Losowe Cytaty ===
Contributors: dawidziolkowski, studioa7
Tags: cytaty, elementor, widget, losowanie, quotes
Requires at least: 6.2
Tested up to: 6.5
Requires PHP: 7.0
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Wtyczka dodająca widżet Elementor Pro do wyświetlania losowych cytatów.

== Opis ==

Wtyczka "Losowe Cytaty" dodaje do Elementor Pro widżet, który wyświetla losowo jeden cytat z bazy. Cytaty są losowane raz dziennie, ale istnieje również możliwość ręcznego wylosowania cytatu za pomocą przycisku w panelu administracyjnym.

Wtyczka została stworzona przez Studio A7 (https://studioa7.pl) - agencję specjalizującą się w tworzeniu profesjonalnych rozwiązań dla WordPress.

= Główne funkcje =

* Widżet Elementor Pro do wyświetlania losowych cytatów
* Blok Gutenberga do wyświetlania losowych cytatów
* Konfigurowalna częstotliwość odświeżania cytatu (raz dziennie, raz na godzinę, raz na pół godziny, raz na kwadrans, raz na 5 minut, przy przeładowaniu strony)
* Możliwość dostosowania wyglądu cytatu (kolory, ramka, zaokrąglenie narożników)
* Zaawansowane opcje stylizacji lewego obramowania
* Możliwość ręcznego wylosowania cytatu
* Import cytatów z pliku tekstowego
* Zarządzanie cytatami (dodawanie, edycja, usuwanie)
* Shortcode [losowy_cytat] do użycia poza Elementorem i Gutenbergiem

= Wymagania =

* WordPress 6.2 lub nowszy (wymagany dla obsługi placeholdera %i w zapytaniach SQL)
* Elementor 3.0.0 lub nowszy
* PHP 7.0 lub nowszy

== Instalacja ==

= Metoda 1: Instalacja przez panel administracyjny WordPress =

1. Pobierz plik ZIP z wtyczką.
2. Przejdź do panelu administracyjnego WordPress > Wtyczki > Dodaj nową > Wyślij wtyczkę.
3. Wybierz pobrany plik ZIP i kliknij "Zainstaluj teraz".
4. Po instalacji aktywuj wtyczkę.

= Metoda 2: Ręczna instalacja przez FTP =

1. Pobierz i rozpakuj plik ZIP z wtyczką.
2. Połącz się z serwerem za pomocą klienta FTP (np. FileZilla).
3. Przejdź do katalogu `/wp-content/plugins/` w głównym katalogu instalacji WordPress.
4. Prześlij folder `losowe-cytaty` do katalogu `/wp-content/plugins/`.
5. Zaloguj się do panelu administracyjnego WordPress.
6. Przejdź do sekcji "Wtyczki".
7. Znajdź wtyczkę "Losowe Cytaty" na liście i kliknij "Włącz".

= Po instalacji =

1. Po aktywacji wtyczki, w menu bocznym panelu administracyjnym pojawi się nowa pozycja "Losowe Cytaty".
2. Przejdź do "Losowe Cytaty" > "Zarządzaj Cytatami", aby dodać nowe cytaty lub zaimportować je z pliku tekstowego.
3. Aby użyć widżetu w Elementorze, edytuj stronę za pomocą Elementora, znajdź widżet "Losowy Cytat" w kategorii "Losowe Cytaty" i przeciągnij go na stronę.
4. Możesz również użyć shortcode `[losowy_cytat]` w dowolnym miejscu, gdzie chcesz wyświetlić aktualny cytat.

== Często zadawane pytania ==

= Jak dodać cytat? =

Przejdź do menu "Losowe Cytaty" w panelu administracyjnym i wypełnij formularz "Dodaj Nowy Cytat".

= Jak zaimportować cytaty z pliku tekstowego? =

1. Przygotuj plik tekstowy z cytatami (każdy cytat w osobnej linii).
2. Format: "Treść cytatu" - Autor (opcjonalnie)
3. Przejdź do menu "Losowe Cytaty" > "Import Cytatów" w panelu administracyjnym.
4. Wybierz przygotowany plik i kliknij "Importuj cytaty".

= Jak zmienić częstotliwość odświeżania cytatu? =

Przejdź do menu "Losowe Cytaty" > "Ustawienia" w panelu administracyjnym i wybierz odpowiednią opcję z listy rozwijanej "Częstotliwość odświeżania cytatu". Dostępne opcje to: raz dziennie, raz na godzinę, raz na pół godziny, raz na kwadrans, raz na 5 minut, przy przeładowaniu strony.

= Jak dostosować wygląd cytatu? =

Przejdź do menu "Losowe Cytaty" > "Ustawienia" w panelu administracyjnym i znajdź sekcję "Style cytatu". Tam możesz zmienić kolor tekstu, kolor tła, kolor i szerokość obramowania, zaokrąglenie narożników, kolor autora oraz włączyć/wyłączyć ikonę cytatu i zmienić jej kolor.

= Jak wylosować nowy cytat? =

Przejdź do menu "Losowe Cytaty" > "Ustawienia" w panelu administracyjnym i kliknij przycisk "Wylosuj nowy cytat".

= Jak użyć widżetu Elementor? =

1. Edytuj stronę za pomocą Elementora.
2. Znajdź widżet "Losowy Cytat" w kategorii "Losowe Cytaty".
3. Przeciągnij widżet na stronę i dostosuj jego wygląd za pomocą dostępnych opcji.

= Jak użyć bloku Gutenberga? =

1. Edytuj stronę lub wpis za pomocą edytora Gutenberga.
2. Kliknij przycisk "+" aby dodać nowy blok.
3. Wyszukaj "Losowy Cytat" i wybierz blok.
4. Dostosuj wygląd cytatu za pomocą panelu ustawień po prawej stronie.
5. Zapisz stronę lub wpis, aby zobaczyć losowy cytat na stronie.

= Jak użyć shortcode? =

Użyj shortcode [losowy_cytat] w dowolnym miejscu, gdzie chcesz wyświetlić aktualny cytat.
Możesz również użyć parametrów:
* show_author - aby kontrolować wyświetlanie autora: [losowy_cytat show_author="false"]
* show_quote_icon - aby kontrolować wyświetlanie ikony cytatu: [losowy_cytat show_quote_icon="false"]

Przykład użycia obu parametrów: [losowy_cytat show_author="true" show_quote_icon="false"]

== Rozwiązywanie problemów ==

Jeśli po instalacji wtyczka nie działa poprawnie:

1. Sprawdź, czy masz zainstalowany i aktywny Elementor w wersji 3.0.0 lub nowszej.
2. Upewnij się, że Twój serwer spełnia minimalne wymagania (PHP 7.0+).
3. Sprawdź, czy wszystkie pliki wtyczki zostały poprawnie przesłane na serwer.
4. Sprawdź logi błędów PHP, aby zidentyfikować potencjalne problemy.

== Zrzuty ekranu ==

1. Widżet Elementor wyświetlający losowy cytat.
2. Panel administracyjny - zarządzanie cytatami.
3. Panel administracyjny - import cytatów.
4. Panel administracyjny - ustawienia i ręczne losowanie cytatu.

== Changelog ==

= 1.0.7 =
* Dodano opcję odświeżania cytatu co 1 minutę dla łatwiejszego testowania.
* Poprawiono mechanizm odświeżania cytatów, aby działał niezależnie od WordPress Cron.
* Dodano panel informacji debugowania w ustawieniach wtyczki.
* Poprawiono błędy bezpieczeństwa związane z escapowaniem danych wyjściowych.

= 1.0.6 =
* Dodano możliwość zmiany koloru i szerokości lewego obramowania w widżecie Elementora.
* Dodano blok Gutenberga z pełną funkcjonalnością stylizacji.
* Zaktualizowano dokumentację o informacje o bloku Gutenberga.

= 1.0.5 =
* Dodano możliwość zmiany koloru ramki, czcionki i podstawowych stylów w ustawieniach na zapleczu.
* Dodano podgląd na żywo zmian stylów w panelu administracyjnym.
* Zaktualizowano shortcode [losowy_cytat] o nowy parametr show_quote_icon.
* Poprawiono wyświetlanie ikony cytatu w różnych kontekstach.

= 1.0.4 =
* Dodano możliwość ustawienia częstotliwości odświeżania cytatu.
* Dostępne opcje: raz dziennie, raz na godzinę, raz na pół godziny, raz na kwadrans, raz na 5 minut, przy przeładowaniu strony.
* Zoptymalizowano system buforowania w zależności od wybranej częstotliwości.
* Zaktualizowano informacje w widżecie, aby odzwierciedlały wybraną częstotliwość.

= 1.0.3 =
* Zaktualizowano wymagania do WordPress 6.2 lub nowszego.
* Wykorzystano nowoczesny placeholder %i w zapytaniach SQL.
* Poprawiono pozostałe błędy związane z bezpieczeństwem.
* Zapewniono spójność domeny tekstowej 'losowe-cytaty'.

= 1.0.2 =
* Poprawiono błędy związane z bezpieczeństwem zapytań SQL.
* Dodano prawidłową sanityzację danych wejściowych.
* Poprawiono nazwę Studio A7 we wszystkich miejscach.
* Usunięto nieużywane zmienne i zaktualizowano komentarze.

= 1.0.1 =
* Poprawiono mechanizm losowania cytatu raz dziennie.
* Dodano minifikowane wersje plików CSS i JS.
* Dodano atrybuty ARIA dla lepszej dostępności.
* Dodano Studio A7 jako współautora.

= 1.0.0 =
* Pierwsza wersja wtyczki.

== Aktualizacje ==

Wtyczka będzie regularnie aktualizowana o nowe funkcje i poprawki błędów.