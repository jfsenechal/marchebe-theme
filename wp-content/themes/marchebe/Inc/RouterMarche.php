<?php

namespace AcMarche\Theme\Inc;

class RouterMarche
{
    const PARAM_EVENT = 'codeCgt';

    public function __construct()
    {
        add_action('init', [$this, 'custom_event_rewrite_rule']);
        add_filter('query_vars', [$this, 'custom_event_query_vars']);
        add_filter('template_include', [$this, 'custom_event_template']);
        //Flush rewrite rules on theme activation (only once)
        register_activation_hook(__FILE__, [$this, 'custom_event_flush_rules']);
    }

    function custom_event_flush_rules(): void
    {
        $this->custom_event_rewrite_rule();
        flush_rewrite_rules();
    }

    function custom_event_rewrite_rule(): void
    {
        add_rewrite_rule(
            '^agenda/([^/]+)/?$',  // URL pattern: yoursite.com/event/EVENT_CODE
            'index.php?single_event=1&'.self::PARAM_EVENT.'=$matches[1]',  // Query vars
            'top'  // Priority
        );
    }

    function custom_event_query_vars($vars)
    {
        $vars[] = 'single_event';
        $vars[] = self::PARAM_EVENT;

        return $vars;
    }

    function custom_event_template($template)
    {
        // Check if this is our custom route
        if (get_query_var('single_event')) {
            $codeCgt = get_query_var(self::PARAM_EVENT);

            // Check if codeCgt exists
            if ($codeCgt) {
                // Look for template in theme directory
                $custom_template = locate_template('single_event.php');

                if ($custom_template) {
                    return $custom_template;
                }
            }
        }

        return $template;
    }

}