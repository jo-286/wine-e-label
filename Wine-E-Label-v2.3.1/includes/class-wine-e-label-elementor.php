<?php

if (!defined('ABSPATH')) {
    exit;
}

class Wine_E_Label_Elementor
{
    public static function init(): void
    {
        add_action('elementor/widgets/register', [__CLASS__, 'register_widgets']);
    }

    public static function register_widgets($widgets_manager): void
    {
        if (!did_action('elementor/loaded')) {
            return;
        }

        require_once WINE_E_LABEL_PLUGIN_DIR . 'includes/elementor/class-wine-e-label-elementor-widget.php';
        $widgets_manager->register(new Wine_E_Label_Elementor_Widget());
    }
}
