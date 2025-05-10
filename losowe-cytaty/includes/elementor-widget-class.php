<?php
/**
 * Definicja klasy widżetu Elementor dla wtyczki Losowe Cytaty
 *
 * @package Losowe_Cytaty
 */

// Zabezpieczenie przed bezpośrednim dostępem do pliku
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Ten plik powinien być ładowany tylko wtedy, gdy Elementor jest aktywny i wszystkie wymagane klasy istnieją
// Sprawdzenie jest wykonywane w pliku elementor-widget.php przed załadowaniem tego pliku

/**
 * Klasa widżetu Elementor dla losowych cytatów
 */
class Losowe_Cytaty_Widget extends \Elementor\Widget_Base {
    /**
     * Nazwa widżetu
     */
    public function get_name() {
        return 'losowy_cytat';
    }
    
    /**
     * Tytuł widżetu
     */
    public function get_title() {
        return esc_html__('Losowy Cytat', 'losowe-cytaty');
    }
    
    /**
     * Ikona widżetu
     */
    public function get_icon() {
        return 'eicon-blockquote';
    }
    
    /**
     * Kategoria widżetu
     */
    public function get_categories() {
        return ['losowe-cytaty'];
    }
    
    /**
     * Słowa kluczowe
     */
    public function get_keywords() {
        return ['cytat', 'losowy', 'quote', 'random', 'blockquote'];
    }
    
    /**
     * Rejestracja kontrolek widżetu
     */
    protected function register_controls() {
        // Sekcja treści
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Treść', 'losowe-cytaty'),
            ]
        );
        
        $this->add_control(
            'show_author',
            [
                'label' => esc_html__('Pokaż autora', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Tak', 'losowe-cytaty'),
                'label_off' => esc_html__('Nie', 'losowe-cytaty'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'info_message',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => esc_html__('Cytat jest losowany automatycznie raz dziennie. Możesz ręcznie wylosować nowy cytat w panelu administracyjnym.', 'losowe-cytaty'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );
        
        $this->end_controls_section();
        
        // Sekcja stylu cytatu
        $this->start_controls_section(
            'section_style_quote',
            [
                'label' => esc_html__('Cytat', 'losowe-cytaty'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'quote_color',
            [
                'label' => esc_html__('Kolor tekstu', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .losowe-cytaty-quote p' => 'color: {{VALUE}};',
                ],
                'default' => '#333333',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'quote_typography',
                'label' => esc_html__('Typografia cytatu', 'losowe-cytaty'),
                'selector' => '{{WRAPPER}} .losowe-cytaty-quote p',
            ]
        );
        
        $this->add_responsive_control(
            'quote_padding',
            [
                'label' => esc_html__('Padding', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .losowe-cytaty-quote' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'quote_margin',
            [
                'label' => esc_html__('Margin', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .losowe-cytaty-quote' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'quote_border',
                'label' => esc_html__('Obramowanie', 'losowe-cytaty'),
                'selector' => '{{WRAPPER}} .losowe-cytaty-quote',
            ]
        );
        
        $this->add_control(
            'quote_border_radius',
            [
                'label' => esc_html__('Zaokrąglenie narożników', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .losowe-cytaty-quote' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'quote_box_shadow',
                'label' => esc_html__('Cień', 'losowe-cytaty'),
                'selector' => '{{WRAPPER}} .losowe-cytaty-quote',
            ]
        );
        
        $this->add_control(
            'quote_background_color',
            [
                'label' => esc_html__('Kolor tła', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .losowe-cytaty-quote' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Sekcja stylu autora
        $this->start_controls_section(
            'section_style_author',
            [
                'label' => esc_html__('Autor', 'losowe-cytaty'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_author' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'author_color',
            [
                'label' => esc_html__('Kolor tekstu', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .losowe-cytaty-quote cite' => 'color: {{VALUE}};',
                ],
                'default' => '#666666',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'author_typography',
                'label' => esc_html__('Typografia autora', 'losowe-cytaty'),
                'selector' => '{{WRAPPER}} .losowe-cytaty-quote cite',
            ]
        );
        
        $this->add_responsive_control(
            'author_spacing',
            [
                'label' => esc_html__('Odstęp od cytatu', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .losowe-cytaty-quote cite' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Sekcja stylu ikony cytatu
        $this->start_controls_section(
            'section_style_icon',
            [
                'label' => esc_html__('Ikona cytatu', 'losowe-cytaty'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'show_quote_icon',
            [
                'label' => esc_html__('Pokaż ikonę cytatu', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Tak', 'losowe-cytaty'),
                'label_off' => esc_html__('Nie', 'losowe-cytaty'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'quote_icon_color',
            [
                'label' => esc_html__('Kolor ikony', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .losowe-cytaty-quote:before' => 'color: {{VALUE}};',
                ],
                'default' => '#dddddd',
                'condition' => [
                    'show_quote_icon' => 'yes',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'quote_icon_size',
            [
                'label' => esc_html__('Rozmiar ikony', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 200,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .losowe-cytaty-quote:before' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_quote_icon' => 'yes',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'quote_icon_position',
            [
                'label' => esc_html__('Pozycja ikony', 'losowe-cytaty'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .losowe-cytaty-quote:before' => 'top: {{TOP}}{{UNIT}}; right: {{RIGHT}}{{UNIT}}; bottom: {{BOTTOM}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_quote_icon' => 'yes',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Obsługa przestarzałej metody _register_controls dla kompatybilności wstecznej
     * @deprecated 3.1.0 Użyj register_controls() zamiast tego
     */
    protected function _register_controls() {
        $this->register_controls();
    }
    
    /**
     * Renderowanie widżetu
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Sprawdzenie czy funkcja istnieje
        if (!function_exists('losowe_cytaty_get_current_quote')) {
            echo '<div class="losowe-cytaty-empty">' . esc_html__('Błąd: Funkcja losowe_cytaty_get_current_quote nie istnieje.', 'losowe-cytaty') . '</div>';
            return;
        }
        
        // Pobieranie aktualnego cytatu
        $quote = losowe_cytaty_get_current_quote();
        
        if (!$quote) {
            echo '<div class="losowe-cytaty-empty">' . esc_html__('Brak cytatów w bazie danych.', 'losowe-cytaty') . '</div>';
            return;
        }
        
        $show_author = $settings['show_author'] === 'yes';
        $show_quote_icon = $settings['show_quote_icon'] === 'yes';
        
        $this->add_render_attribute('wrapper', 'class', 'losowe-cytaty-widget');
        
        if ($show_quote_icon) {
            $this->add_render_attribute('wrapper', 'class', 'show-quote-icon');
        }
        
        ?>
        <div <?php echo esc_attr($this->get_render_attribute_string('wrapper')); ?>>
            <blockquote class="losowe-cytaty-quote">
                <p><?php echo esc_html($quote['quote']); ?></p>
                
                <?php if ($show_author && !empty($quote['author'])): ?>
                    <cite>— <?php echo esc_html($quote['author']); ?></cite>
                <?php endif; ?>
            </blockquote>
        </div>
        <?php
    }
    
    /**
     * Renderowanie zawartości w edytorze
     */
    protected function content_template() {
        ?>
        <# if ( settings.show_quote_icon ) { #>
            <div class="losowe-cytaty-widget show-quote-icon">
        <# } else { #>
            <div class="losowe-cytaty-widget">
        <# } #>
            <blockquote class="losowe-cytaty-quote">
                <p><?php esc_html_e('To jest przykładowy cytat, który będzie zastąpiony losowym cytatem z bazy danych.', 'losowe-cytaty'); ?></p>
                
                <# if ( settings.show_author === 'yes' ) { #>
                    <cite>— <?php esc_html_e('Autor cytatu', 'losowe-cytaty'); ?></cite>
                <# } #>
            </blockquote>
        </div>
        <?php
    }
    
    /**
     * Obsługa przestarzałej metody _content_template dla kompatybilności wstecznej
     * @deprecated 3.1.0 Użyj content_template() zamiast tego
     */
    protected function _content_template() {
        $this->content_template();
    }
}