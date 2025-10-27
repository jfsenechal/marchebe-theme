<?php

namespace AcMarche\Theme\Repository;

use AcMarche\Theme\Inc\BottinCategoryMetaBox;
use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Helper\SortingHelper;
use AcMarche\Theme\Lib\Search\Document;
use WP_Post;
use WP_Query;

class WpRepository
{
    private static ?WpRepository $instance = null;

    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param int $max
     *
     * @return WP_Post[]
     */
    public static function getNews(int $max = 50): array
    {
        $news = array();

        foreach (Theme::SITES as $idSite => $name) :
            switch_to_blog($idSite);

            $args = array(
                'category_name' => 'actualites-principales',
                'orderby' => 'title',
                'post_status' => 'publish',
                'order' => 'ASC',
            );

            if ($idSite == 1) {
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
                $post->link = $permalink;

                $post->blog_id = $idSite;
                $post->blog = ucfirst($name);
                $post->color = Theme::COLORS[$idSite];
                $post->colorTailwind = 'text-'.Theme::SITES[$idSite];

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
     * @param int $catId
     * @return array<int,Document>
     */
    public function getPostsAndFiches(int $catId): array
    {
        $documents = [];
        $idSite = get_current_blog_id();
        $posts = $this->getPostsByCategory($catId);
        foreach ($posts as $post) {
            $this->preparePost($post);
            $document = Document::documentFromPost($post, $idSite);
            $documents[] = $document;
        }

        $categoryBottinId = get_term_meta($catId, BottinCategoryMetaBox::KEY_NAME, true);
        $bottinRepository = new BottinRepository();
        $fiches = [];
        if ($categoryBottinId) {
            $fiches = $bottinRepository->getFichesByCategory($categoryBottinId);
        }

        foreach ($fiches as $fiche) {
            $idSite = $bottinRepository->findSiteFiche($fiche);
            $documents[] = Document::documentFromFiche($fiche, $idSite);
        }

        return SortingHelper::sortDocuments($documents);
    }

    public function preparePost(WP_Post $post): void
    {
        $categories = [];
        foreach (get_the_category($post->ID) as $category) {
            $categories[] = ['id' => $category->term_id, 'name' => $category->name];
        }
        $post->tags = $categories;
        $content = get_the_content(null, null, $post);
        $post->content = apply_filters('the_content', $content);
        $post->paths = WpRepository::instance()->getAncestorsOfPost($post->ID);
    }

    /**
     * @param int $categoryId
     * @return array<int,WP_Post>
     */
    public function getPostsByCategory(int $categoryId): array
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


    /**
     * @return array<int,\WP_Term> Array of child categories with additional properties like URL and ID.
     */
    public function getRootCategories(): array
    {
        $children = get_categories([
            'parent' => 0,
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
        ]);
        array_map(
            function ($category) {
                $category->url = get_category_link($category->term_id);
                $category->id = $category->term_id;
            },
            $children
        );

        return $children;
    }

    /**
     * @param int $categoryId
     * @return array<int,\WP_Term>
     */
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

    /**
     * @param int $postId
     * @return array<int,\WP_Term>
     */
    public function getAncestorsOfPost(int $postId): array
    {
        $categories = get_the_category($postId);
        if (count($categories) > 0) {
            $firstCategory = $categories[0];
            $ancestors = $this->getAncestorsOfCategory($firstCategory->term_id);
            $ancestors[] = $firstCategory;

            return $ancestors;
        }

        return [];
    }
}