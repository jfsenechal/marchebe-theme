<?php

namespace AcMarche\Theme\Repository;

use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Cache;
use WP_Post;
use WP_Query;

class WpRepository
{
    const MENU_NAME = 'top-menu';

    /**
     * @param int $max
     *
     * @return WP_Post[]
     */
    public static function getNews(int $max = 50): array
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

    public function getMenu(bool $purgeCache = false): array
    {
        $cacheKey = Cache::generateKey('menu-top');

        if ($purgeCache) {
            Cache::delete($cacheKey);
        }

        return Cache::get($cacheKey, function (): array {
            $blog = get_current_blog_id();
            $data = [];
            foreach (Theme::SITES as $idSite => $site) {
                switch_to_blog($idSite);
                if (in_array($idSite, [8, 12])) {
                    continue;
                }
                $data[$idSite]['name'] = ucfirst($site);
                $data[$idSite]['slug'] = $site;
                if ($idSite == 14) {
                    $data[$idSite]['name'] = 'Enfance-Jeunesse';
                }
                $data[$idSite]['blogid'] = $idSite;
                $data[$idSite]['colorhover'] = 'hover:text-'.$site;
                $data[$idSite]['color'] = 'text-'.$site;
                $data[$idSite]['items'] = $this->getItems($idSite, $site);
            }
            switch_to_blog($blog);

            return $this->sortByName($data);
        }
        );
    }

    public function getItems(int $idSite, string $site = null): array
    {
        $menu = wp_get_nav_menu_object(self::MENU_NAME);

        $args = array(
            'order' => 'ASC',
            'orderby' => 'menu_order',
            'post_type' => 'nav_menu_item',
            'post_status' => 'publish',
            'output' => ARRAY_A,
            'output_key' => 'menu_order',
            'nopaging' => true,
            'update_post_term_cache' => false,
        );

        $data = wp_get_nav_menu_items($menu, $args);
        foreach ($data as $row) {
            $row->blog = $site;
            $row->id = (int)$row->object_id;
            if ($row->object === 'post') {
                $post = get_post($row->object_id);
                if (!$post) {
                    continue;
                }
                $row->slug = $post->post_name;
                $row->typejfs = 'article';
                $row->parents = $this->getAncestorsOfPost((int)$row->object_id);
            }
            if ($row->object === 'page') {
                $page = get_post($row->object_id);
                if (!$page) {
                    continue;
                }
                $row->slug = $page->post_name;
                $row->typejfs = 'article';
                $row->parents = [];
            }
            if ($row->object === 'category') {
                $category = get_category($row->object_id);
                if ($category) {
                    $row->slug = $category->slug;
                    $row->typejfs = 'category';
                    $row->parents = $this->getAncestorsOfCategory((int)$row->object_id);
                }
            }
            if ($row->object === 'custom') {
                $row->slug = $row->post_name;
                $row->typejfs = 'custom';
                $row->parents = [];
            }
        }

        return $data;
    }

    public function sortByName(array $data): array
    {
        usort(
            $data,
            function ($itemA, $itemB) {
                $nameA = $itemA['name'];
                $nameB = $itemB['name'];

                return $nameA > $nameB ? +1 : -1;
            }
        );

        return $data;
    }

    public function getAncestorsOfCategory(int $categoryId): array
    {
        $ancestors = get_ancestors($categoryId, 'category');
        $parents = [];
        if (is_iterable($ancestors)) {
            foreach (array_reverse($ancestors) as $id) {
                $categoryParent = get_category($id);
                $parents[] = $categoryParent;
            }
        }

        return $parents;
    }

    public function getAncestorsOfPost(int $postId): array
    {
        $categories = get_the_category($postId);
        if (count($categories) > 0) {
            $ancestors = $this->getAncestorsOfCategory($categories[0]->term_id);
            $ancestors[] = $categories[0]->slug;

            return $ancestors;
        }

        return [];
    }
}