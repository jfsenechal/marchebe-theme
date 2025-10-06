<?php

namespace AcMarche\Theme\Inc;

class Assets
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'theme_slug_enqueue_styles']);
        add_action('wp_enqueue_scripts', [$this, 'theme_slug_enqueue_scripts']);
    }

    function theme_slug_enqueue_styles()
    {
        wp_enqueue_style(
            'theme-slug-style',
            get_stylesheet_uri()
        );
        wp_enqueue_style(
            'marchebe-primary',
            get_parent_theme_file_uri('assets/css/tailwind.css'),
            [],
            wp_get_theme()->get('Version')
        );
    }

    function theme_slug_enqueue_scripts()
    {
        wp_enqueue_script(
            'marchebe-alpine',
            get_parent_theme_file_uri('//unpkg.com/alpinejs'),
            [],
            wp_get_theme()->get('Version'),
            []
        );
    }
}