<?php

namespace AcMarche\Theme\Repository;

use AcMarche\Theme\Inc\Theme;
use WP_Post;
use WP_Query;

class WpRepository
{
    /**
     * @param int $max
     *
     * @return WP_Post[]
     */
    public static function getAllNews(int $max = 50): array
    {
        $news = array();

        foreach (Theme::SITES as $siteId => $name) :
            switch_to_blog($siteId);

            $args = array(
                'category_name' => 'actualites-principales',
                'orderby' => 'title',
                'post_status' => 'publish',
                'order' => 'ASC',
            );

            if ($siteId == 1) {
                $args = array(
                    'category_name' => 'actualites',
                    'orderby' => 'title',
                    'post_status' => 'publish',
                    'order' => 'ASC',
                );
            }

            $querynews = new WP_Query($args);

            while ($querynews->have_posts()) :

                $post = $querynews->next_post();
                $id = $post->ID;

                if (has_post_thumbnail($id)) {
                    $attachment_id = get_post_thumbnail_id($id);
                    $images = wp_get_attachment_image_src($attachment_id, 'original');
                    $post_thumbnail_url = $images[0];
                } else {
                    $post_thumbnail_url = get_template_directory_uri().'/assets/images/404.jpg';
                }

                $post->post_thumbnail_url = $post_thumbnail_url;

                $permalink = get_permalink($id);
                $post->url = $permalink;

                $post->blog_id = $siteId;
                $post->blog = ucfirst($name);
                $post->color = Theme::COLORS[$siteId];
                $post->colorTailwind = 'text-'.Theme::SITES[$siteId];

                $news[] = $post;
            endwhile;
        endforeach;

        switch_to_blog(1);
        wp_reset_postdata();

       // $news = AcSort::trieNews($news);

        if (count($news) > $max) {
            $news = array_slice($news, 0, $max);
        }

        return $news;
    }
}