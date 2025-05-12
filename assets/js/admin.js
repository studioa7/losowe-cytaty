/**
 * Skrypt dla panelu administracyjnego wtyczki Losowe Cytaty
 */

(function($) {
    'use strict';
    
    // Funkcja inicjalizująca
    function init() {
        // Obsługa przycisku losowania cytatu
        var $randomizeButton = $('.randomize-button');
        if ($randomizeButton.length) {
            $randomizeButton.on('click', function(e) {
                e.preventDefault();
                
                // Potwierdzenie losowania
                if (confirm(losoweCytatyAdmin.confirmRandomize)) {
                    randomizeQuote();
                }
            });
        }
        
        // Obsługa przycisku usuwania cytatu
        var $deleteQuote = $('.delete-quote');
        if ($deleteQuote.length) {
            $deleteQuote.on('click', function(e) {
                if (!confirm(losoweCytatyAdmin.confirmDelete)) {
                    e.preventDefault();
                }
            });
        }
    }
    
    // Funkcja losująca cytat przez AJAX
    function randomizeQuote() {
        // Wyświetlenie ładowania
        var $quoteDisplay = $('.current-quote-display');
        var $blockquote = $quoteDisplay.find('blockquote');
        var $quoteInfo = $quoteDisplay.find('.quote-info');
        
        $blockquote.css('opacity', '0.5');
        $quoteInfo.css('opacity', '0.5');
        
        // Wywołanie AJAX
        $.ajax({
            url: losoweCytatyAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'losowe_cytaty_randomize',
                nonce: losoweCytatyAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Aktualizacja wyświetlanego cytatu
                    var $quoteText = $blockquote.find('p');
                    var $quoteAuthor = $blockquote.find('cite');
                    
                    $quoteText.text(response.data.quote);
                    
                    if (response.data.author) {
                        if ($quoteAuthor.length) {
                            $quoteAuthor.text('— ' + response.data.author);
                        } else {
                            $blockquote.append('<cite>— ' + response.data.author + '</cite>');
                        }
                    } else {
                        $quoteAuthor.remove();
                    }
                    
                    // Aktualizacja daty zmiany
                    if ($quoteInfo.length) {
                        $quoteInfo.text('Ostatnia zmiana: ' + response.data.last_change);
                    }
                    
                    // Wyświetlenie komunikatu
                    showNotice(response.data.message, 'success');
                    
                    // Odświeżenie tabeli z cytatami
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    // Wyświetlenie błędu
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                // Wyświetlenie błędu
                showNotice('Wystąpił błąd podczas komunikacji z serwerem.', 'error');
            },
            complete: function() {
                // Przywrócenie normalnego wyglądu
                $blockquote.css('opacity', '1');
                $quoteInfo.css('opacity', '1');
            }
        });
    }
    
    // Funkcja wyświetlająca komunikat
    function showNotice(message, type) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        var $heading = $('.wrap h1');
        
        if ($heading.length) {
            // Usunięcie istniejących komunikatów
            $('.notice').remove();
            
            // Dodanie nowego komunikatu
            $heading.after($notice);
            
            // Dodanie przycisku zamykania
            $notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Zamknij</span></button>');
            
            // Obsługa zamykania
            $notice.find('.notice-dismiss').on('click', function() {
                $notice.fadeOut(300, function() {
                    $notice.remove();
                });
            });
        } else {
            console.log(message); // Fallback, jeśli nie można wyświetlić komunikatu w UI
        }
    }
    
    // Inicjalizacja po załadowaniu dokumentu
    $(document).ready(init);
    
})(jQuery);