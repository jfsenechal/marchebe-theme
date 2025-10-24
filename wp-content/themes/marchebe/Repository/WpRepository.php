<?php

namespace AcMarche\Theme\Repository;

use AcMarche\Theme\Inc\BottinCategoryMetaBox;
use AcMarche\Theme\Inc\RouterBottin;
use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Bottin\Bottin;
use WP_Post;
use WP_Query;

class WpRepository
{
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

    public function getPostsAndFiches(int $catId): array
    {
        $args = array(
            'cat' => $catId,
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

        $fiches = [];
        $categoryBottinId = get_term_meta($catId, BottinCategoryMetaBox::KEY_NAME, true);
        $bottinRepository = new BottinRepository();
        if ($categoryBottinId) {

            $fiches = $bottinRepository->getFichesByCategory($categoryBottinId);
        }

        array_map(
            function ($fiche) use ($bottinRepository) {
                $idSite = $bottinRepository->findSiteFiche($fiche);
                $fiche->fiche = true;
                $fiche->excerpt = Bottin::getExcerpt($fiche);
                $fiche->post_excerpt = Bottin::getExcerpt($fiche);
                $fiche->url = RouterBottin::getUrlFicheBottin($idSite, $fiche);
                $fiche->post_title = $fiche->societe;
            },
            $fiches
        );

        $all = array_merge($posts, $fiches);

        if (get_current_blog_id(
            ) === Theme::ADMINISTRATION && ($catId == Theme::ENQUETE_DIRECTORY_URBA || $catId == Theme::ENQUETE_DIRECTORY_INSTIT || $catId == Theme::PUBLICATIOCOMMUNAL_CATEGORY)) {

            /*$permis = Urba::getEnquetesPubliques();
            $data   = [];
            foreach ($permis as $permi) {
                $post   = Urba::permisToPost($permi);
                $data[] = $post;
            }
            $all = array_merge($all, $data);*/

            $enquetes = self::getEnquetesPubliques($catId);
            array_map(
                function ($enquete) {
                    list($yearD, $monthD, $dayD) = explode('-', $enquete->date_debut);
                    $dateDebut = $dayD.'-'.$monthD.'-'.$yearD;
                    list($yearF, $monthF, $dayF) = explode('-', $enquete->date_fin);
                    $dateFin = $dayF.'-'.$monthF.'-'.$yearF;
                    $enquete->ID = $enquete->id;
                    $enquete->excerpt = $enquete->demandeur.' à '.$enquete->localite.'<br /> Affichate: du '.$dateDebut.' au '.$dateFin;
                    $enquete->post_excerpt = $enquete->demandeur.' à '.$enquete->localite.'<br /> Affichate: du '.$dateDebut.' au '.$dateFin;
                    $enquete->url = RouterMarche::getUrlEnquete($enquete->id);
                    $enquete->post_title = $enquete->intitule.' à '.$enquete->localite;
                },
                $enquetes
            );
            $all = array_merge($all, $enquetes);
        }

        return SortUtil::sortPosts($all);
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