<?php

namespace AcMarche\Theme\Inc;

class Assets
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'remove_global_styles'], 100);
        add_action('wp_enqueue_scripts', [$this, 'theme_slug_enqueue_styles']);
        add_action('wp_enqueue_scripts', [$this, 'theme_slug_enqueue_scripts']);
        add_filter('script_loader_tag', [$this, 'add_defer_attribute'], 10, 2);
    }

    function theme_slug_enqueue_styles()
    {
        wp_enqueue_style(
            'theme-slug-style',
            get_stylesheet_uri()
        );
        wp_enqueue_style(
            'marchebe-primary',
            get_parent_theme_file_uri('assets/css/marchebe.css'),
            [],
            wp_get_theme()->get('Version')
        );
        wp_enqueue_style(
            'marchebe-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css',
            [],
        );
    }

    function theme_slug_enqueue_scripts()
    {
        wp_enqueue_script(
            'marchebe-alpine',
            'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js',
            [],
            null
        );
    }

    function add_defer_attribute($tag, $handle)
    {
        // Add defer to Alpine.js script
        if ('marchebe-alpine' === $handle) {
            return str_replace(' src', ' defer src', $tag);
        }

        return $tag;
    }

    function remove_global_styles()
    {
        // Remove global styles
        wp_dequeue_style('global-styles');
        wp_deregister_style('global-styles');
        // Remove classic theme styles
        wp_dequeue_style('classic-theme-styles');
        // Remove block library
      //  wp_dequeue_style('wp-block-library');
       // wp_dequeue_style('wp-block-library-theme');

        // Remove SVG filters
        //remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
    }
}