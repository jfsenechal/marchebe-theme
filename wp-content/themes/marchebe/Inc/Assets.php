<?php

namespace AcMarche\Theme\Inc;

class Assets
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'theme_slug_enqueue_styles']);
    }

    function theme_slug_enqueue_styles()
    {
        wp_enqueue_style(
            'theme-slug-style',
            get_stylesheet_uri()
        );
        wp_enqueue_style(
            'theme-slug-primary',
            get_parent_theme_file_uri('assets/css/tailwind.css'),
            [],
            wp_get_theme()->get('Version')
        );
        var_dump(get_parent_theme_file_uri('assets/css/primary.css'));
    }
}