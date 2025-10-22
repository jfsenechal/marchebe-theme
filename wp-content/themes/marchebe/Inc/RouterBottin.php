<?php

namespace AcMarche\Theme\Inc;

class RouterBottin
{
    public const PARAM_FICHE = 'slugfiche';
    public const FICHE_ROUTE = 'bottin/fiche/';
    public const SINGLE_FICHE = 'single_fiche';
    public const PARAM_CATEGORY = 'slugcategory';
    public const CATEGORY_ROUTE = 'bwp/categorie';
    public const SINGLE_CATEGORY = 'single_category';

    public function __construct()
    {
        add_action('init', [$this, 'add_rewrite_rule']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_filter('template_include', [$this, 'add_templates']);
        //Flush rewrite rules on theme activation (only once)
        register_activation_hook(__FILE__, [$this, 'flush_rules']);
    }

    function flush_rules(): void
    {
        $this->add_rewrite_rule();
        flush_rewrite_rules();
    }

    function add_rewrite_rule(): void
    {
        add_rewrite_rule(
            self::FICHE_ROUTE.'([a-zA-Z0-9-]+)[/]?$',
            'index.php?'.self::SINGLE_FICHE.'=1&'.self::PARAM_FICHE.'=$matches[1]',  // Query vars
            'top'  // Priority
        );
        add_rewrite_rule(
            self::CATEGORY_ROUTE.'/([a-zA-Z0-9-]+)[/]?$',
            'index.php?'.self::SINGLE_CATEGORY.'=1&'.self::PARAM_CATEGORY.'=$matches[1]',  // Query vars
            'top'  // Priority
        );
    }

    function add_query_vars($vars): array
    {
        $vars[] = self::SINGLE_FICHE;
        $vars[] = self::PARAM_FICHE;
        $vars[] = self::SINGLE_CATEGORY;
        $vars[] = self::PARAM_CATEGORY;

        return $vars;
    }

    function add_templates($template)
    {
        // Check if this is our custom route
        if (get_query_var(self::SINGLE_FICHE)) {
            $queryVar = get_query_var(self::PARAM_FICHE);

            // Check if exists
            if ($queryVar) {
                // Look for template in theme directory
                $custom_template = locate_template('single_fiche.php');

                if ($custom_template) {
                    return $custom_template;
                }
            }
        }
        if (get_query_var(self::SINGLE_CATEGORY)) {
            $queryVar = get_query_var(self::PARAM_CATEGORY);

            // Check if exists
            if ($queryVar) {
                // Look for template in theme directory
                $custom_template = locate_template('category_bottin.php');

                if ($custom_template) {
                    return $custom_template;
                }
            }
        }

        return $template;
    }

}