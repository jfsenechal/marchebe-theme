<?php

namespace AcMarche\Theme\Inc;

use AcMarche\Theme\Repository\WpRepository;

class Ajax
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'my_enqueue_scripts']);
        // Handle the AJAX request
        add_action('wp_ajax_my_ajax_action', [$this, 'my_ajax_handler']); // For logged-in users
        add_action('wp_ajax_nopriv_my_ajax_action', [$this, 'my_ajax_handler']); // For non-logged users
    }

    // Localize script to pass Ajax URL and nonce
    function my_enqueue_scripts()
    {
        // admin_url('admin-ajax.php'), //bug route frankenphp
        $url = 'https://'.$_ENV['WP_URL_HOME'].'/wp-admin/admin-ajax.php';

        wp_localize_script('marchebe-alpine', 'wpData', array(
            'ajaxUrl' => $url,
            'nonce' => wp_create_nonce('my_ajax_nonce'),
        ));
    }

    function my_ajax_handler()
    {
        check_ajax_referer('my_ajax_nonce', 'nonce');

        $categoryId = isset($_POST['categorySelected']) ? intval($_POST['categorySelected']) : 0;
        $currentSite = isset($_POST['currentSite']) ? intval($_POST['currentSite']) : Theme::CITOYEN;

        if (!$categoryId) {
            wp_send_json_error(['message' => 'Invalid category ID']);

            return;
        }

        $wpRepository = new WpRepository();
        switch_to_blog($currentSite);
        $posts = $wpRepository->getPostsAndFiches($categoryId);

        wp_send_json_success(['posts' => $posts]);
    }
}