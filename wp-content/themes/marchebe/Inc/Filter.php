<?php

namespace AcMarche\Theme\Inc;

use AcMarche\Theme\Lib\Search\MeiliServer;

class Filter
{
    public function __construct()
    {
        add_action("save_post22", function ($id) {
            $post = get_post($id);
            $meiliserver = new MeiliServer();
            $meiliserver->addPost($post);
            $url = get_site_url().$_SERVER["PURGE_PATH"]."/".$post->post_name."/";
            wp_remote_post($url, [
                "headers" => [
                    "X-WPSidekick-Purge-Key" => $_SERVER["PURGE_KEY"],
                ],
            ]);
        });
    }
}