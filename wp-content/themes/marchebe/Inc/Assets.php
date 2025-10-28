<?php

namespace AcMarche\Theme\Inc;

class Assets
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'remove_global_styles'], 100);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_filter('script_loader_tag', [$this, 'add_defer_attribute'], 10, 2);
        // Fix WordPress core asset URLs in multisite subdirectory setup
        add_filter('style_loader_src', [$this, 'fix_multisite_urls'], 10, 1);
        add_filter('script_loader_src', [$this, 'fix_multisite_urls'], 10, 1);
    }

    function enqueue_styles(): void
    {
        $themeUri = self::getThemeUri();
        wp_enqueue_style(
            'theme-slug-style',
            $themeUri.'/style.css'// get_stylesheet_uri()
        );

        wp_enqueue_style(
            'marchebe-primary',
            $themeUri.'/assets/css/marchebe.css',
            [],
            wp_get_theme()->get('Version')
        );
        wp_enqueue_style(
            'marchebe-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css',
            [],
        );
    }

    function enqueue_scripts(): void
    {
        wp_enqueue_script(
            'marchebe-alpine',
            'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js',
            [],
            null
        );
    }

    function add_defer_attribute($tag, $handle): string
    {
        // Add defer to Alpine.js script
        if ('marchebe-alpine' === $handle) {
            return str_replace(' src', ' defer src', $tag);
        }

        return $tag;
    }

    function remove_global_styles(): void
    {
        // Remove global styles
        wp_dequeue_style('global-styles');
        wp_deregister_style('global-styles');
        // Remove classic theme styles
        //wp_dequeue_style('classic-theme-styles');
        // Remove block library
        //  wp_dequeue_style('wp-block-library');
        // wp_dequeue_style('wp-block-library-theme');

        // Remove SVG filters
        //remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
    }

    public static function getThemeUri(): string
    {
        //replace get_template_directory_uri()
        //https://marche.local/enfance-jeunesse/wp-content/themes/marchebe
        return 'https://'.$_ENV['WP_URL_HOME'].'/wp-content/themes/marchebe';
    }

    /**
     * Fix WordPress core asset URLs in multisite subdirectory setup
     * Removes subsite path from wp-includes and wp-content URLs
     * Example: /culture/wp-includes/css/file.css -> /wp-includes/css/file.css
     */
    public function fix_multisite_urls($src): string
    {
        if (!is_multisite() || is_main_site()) {
            return $src;
        }

        // Get the current blog details to extract the path
        $current_blog = get_blog_details();
        if ($current_blog && $current_blog->path !== '/') {
            $subsite_path = trim($current_blog->path, '/');
            // Remove subsite path from wp-includes and wp-content URLs
            $src = preg_replace('#/'.$subsite_path.'/(wp-includes|wp-content)/#', '/$1/', $src);
        }

        return $src;
    }
}