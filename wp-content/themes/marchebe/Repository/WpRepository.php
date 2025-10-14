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

    /**
     * @param int $categoryId
     * @return array<int,WP_Post>
     */
    public function getPosts(int $categoryId): array
    {
        $args = array(
            'cat' => $categoryId,
            'numberposts' => 5000,
            'orderby' => 'post_title',
            'order' => 'ASC',
            'post_status' => 'publish',
        );

        $querynews = new WP_Query($args);
        $posts = [];
        while ($querynews->have_posts()) {
            $post = $querynews->next_post();
            $post->excerpt = $post->post_excerpt;
            $post->url = get_permalink($post->ID);
            $posts[] = $post;
        }

        return $posts;
    }

    /**
     * @param int $categoryId
     *
     * @return \WP_Term|\WP_Error|array|null
     */
    public function getParentCategory(int $categoryId): \WP_Term|\WP_Error|array|null
    {
        $category = get_category($categoryId);

        if ($category) {
            if ($category->parent < 1) {
                return null;
            }

            return get_category($category->parent);
        }

        return null;

    }

    /**
     * @param int $cat_ID The ID of the parent category.
     * @return array<int,\WP_Term> Array of child categories with additional properties like URL and ID.
     */
    public function getChildrenOfCategory(int $cat_ID): array
    {
        $args = ['parent' => $cat_ID, 'hide_empty' => false];
        $children = get_categories($args);
        array_map(
            function ($category) {
                $category->url = get_category_link($category->term_id);
                $category->id = $category->term_id;
            },
            $children
        );

        return $children;
    }
}