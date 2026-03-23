<?php

if (!defined('ABSPATH')) {
    exit;
}

class Wine_E_Label_Elementor_Widget extends \Elementor\Widget_Base
{
    public function get_name(): string
    {
        return 'wine_e_label_nutrition_table';
    }

    public function get_title(): string
    {
        return esc_html__('Wein E-Label Nährwerttabelle', 'wine-e-label');
    }

    public function get_icon(): string
    {
        return 'eicon-table';
    }

    public function get_categories(): array
    {
        return ['general'];
    }

    public function get_keywords(): array
    {
        return ['wein', 'elabel', 'e-label', 'naehrwert', 'nährwert', 'nutrition', 'woocommerce'];
    }

    public function get_style_depends(): array
    {
        return ['wine-e-label-frontend-widget'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Inhalt', 'wine-e-label'),
            ]
        );

        $this->add_control(
            'source_mode',
            [
                'label' => esc_html__('Datenquelle', 'wine-e-label'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'current',
                'options' => [
                    'current' => esc_html__('Aktuelles Produkt', 'wine-e-label'),
                    'manual' => esc_html__('Produkt-ID manuell', 'wine-e-label'),
                ],
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => esc_html__('Produkt-ID', 'wine-e-label'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'condition' => [
                    'source_mode' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => esc_html__('Titel anzeigen', 'wine-e-label'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_minor',
            [
                'label' => esc_html__('Zusätzliche Nährwertangaben anzeigen', 'wine-e-label'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_ingredients',
            [
                'label' => esc_html__('Zutaten unter der Tabelle anzeigen', 'wine-e-label'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'display_mode',
            [
                'label' => esc_html__('Darstellung', 'wine-e-label'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'static',
                'options' => [
                    'static' => esc_html__('Statisch', 'wine-e-label'),
                    'accordion' => esc_html__('Aufklappbar', 'wine-e-label'),
                ],
            ]
        );

        $this->add_control(
            'accordion_open',
            [
                'label' => esc_html__('Standardmäßig geöffnet', 'wine-e-label'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => '',
                'condition' => [
                    'display_mode' => 'accordion',
                ],
            ]
        );

        $this->add_control(
            'accordion_heading',
            [
                'label' => esc_html__('Accordion-Überschrift', 'wine-e-label'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'NÄHRWERTANGABEN',
                'condition' => [
                    'display_mode' => 'accordion',
                ],
            ]
        );

        $this->add_control(
            'show_link',
            [
                'label' => esc_html__('Link zum E-Label anzeigen', 'wine-e-label'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $settings = $this->get_settings_for_display();
        $manual_id = ($settings['source_mode'] ?? 'current') === 'manual' ? (int) ($settings['product_id'] ?? 0) : 0;
        $product_id = Wine_E_Label_Frontend::resolve_product_id($manual_id);

        echo Wine_E_Label_Frontend::render_table($product_id, [
            'show_title' => ($settings['show_title'] ?? '') === 'yes',
            'show_minor' => ($settings['show_minor'] ?? '') === 'yes',
            'show_ingredients' => ($settings['show_ingredients'] ?? '') === 'yes',
            'show_link' => ($settings['show_link'] ?? '') === 'yes',
            'display_mode' => ($settings['display_mode'] ?? 'static') === 'accordion' ? 'accordion' : 'static',
            'accordion_open' => ($settings['accordion_open'] ?? '') === 'yes',
            'accordion_heading' => sanitize_text_field((string) ($settings['accordion_heading'] ?? 'NÄHRWERTANGABEN')),
            'context' => 'elementor',
        ]);
    }
}
