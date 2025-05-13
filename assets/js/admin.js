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
        
        // Obsługa pokazywania/ukrywania opcji koloru ikony cytatu
        var $showQuoteIcon = $('input[name="losowe_cytaty_show_quote_icon"]');
        if ($showQuoteIcon.length) {
            // Inicjalizacja stanu
            toggleQuoteIconColorRow($showQuoteIcon.is(':checked'));
            
            // Obsługa zmiany stanu
            $showQuoteIcon.on('change', function() {
                toggleQuoteIconColorRow($(this).is(':checked'));
            });
        }
        
        // Inicjalizacja podglądu cytatu w ustawieniach
        initQuotePreview();
    }
    
    // Funkcja pokazująca/ukrywająca opcję koloru ikony cytatu
    function toggleQuoteIconColorRow(show) {
        var $quoteIconColorRow = $('.quote-icon-color-row');
        if (show) {
            $quoteIconColorRow.show();
        } else {
            $quoteIconColorRow.hide();
        }
    }
    
    // Funkcja inicjalizująca podgląd cytatu w ustawieniach
    function initQuotePreview() {
        // Pobieranie elementów formularza
        var $textColor = $('input[name="losowe_cytaty_text_color"]');
        var $backgroundColor = $('input[name="losowe_cytaty_background_color"]');
        var $borderColor = $('input[name="losowe_cytaty_border_color"]');
        var $borderWidth = $('input[name="losowe_cytaty_border_width"]');
        var $borderRadius = $('input[name="losowe_cytaty_border_radius"]');
        var $authorColor = $('input[name="losowe_cytaty_author_color"]');
        var $showQuoteIcon = $('input[name="losowe_cytaty_show_quote_icon"]');
        var $quoteIconColor = $('input[name="losowe_cytaty_quote_icon_color"]');
        
        // Jeśli elementy istnieją, dodaj obsługę podglądu na żywo
        if ($textColor.length && $('.current-quote-display').length) {
            // Funkcja aktualizująca style
            function updateQuoteStyles() {
                var $quote = $('.current-quote-display blockquote');
                var $quoteText = $quote.find('p');
                var $quoteAuthor = $quote.find('cite');
                
                // Aktualizacja stylów cytatu
                $quote.css({
                    'background-color': $backgroundColor.val(),
                    'border-left-color': $borderColor.val(),
                    'border-left-width': $borderWidth.val() + 'px',
                    'border-radius': $borderRadius.val() + 'px'
                });
                
                // Aktualizacja koloru tekstu
                $quoteText.css('color', $textColor.val());
                
                // Aktualizacja koloru autora
                if ($quoteAuthor.length) {
                    $quoteAuthor.css('color', $authorColor.val());
                }
                
                // Obsługa ikony cytatu
                if ($showQuoteIcon.is(':checked')) {
                    $quote.addClass('show-quote-icon');
                    $quote.css('--quote-icon-color', $quoteIconColor.val());
                } else {
                    $quote.removeClass('show-quote-icon');
                }
            }
            
            // Dodanie obsługi zdarzeń dla wszystkich pól
            $textColor.add($backgroundColor).add($borderColor).add($borderWidth)
                .add($borderRadius).add($authorColor).add($showQuoteIcon).add($quoteIconColor)
                .on('input change', updateQuoteStyles);
            
            // Inicjalizacja stylów
            updateQuoteStyles();
            
            // Dodanie stylu dla ikony cytatu
            $('<style>.current-quote-display blockquote.show-quote-icon:before { color: var(--quote-icon-color, #e0e0e0); }</style>').appendTo('head');
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