<?php

if (!defined('ABSPATH')) {
    exit;
}

class NutritionLabels_Elementor
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

        require_once NUTRITION_LABELS_PLUGIN_DIR . 'includes/elementor/class-wine-e-label-elementor-widget.php';
        $widgets_manager->register(new NutritionLabels_Elementor_Widget());
    }
}
