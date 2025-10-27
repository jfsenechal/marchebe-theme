<?php

namespace AcMarche\Theme\Lib\Search;

use AcMarche\Theme\Repository\BottinRepository;
use AcMarche\Theme\Repository\WpRepository;

class DataForSearch
{
    private WpRepository $wpRepository;
    private BottinRepository $bottinRepository;
    private static ?BottinRepository $bottinRepositoryStatic = null;

    public function __construct()
    {
        $this->wpRepository = new WpRepository();
        $this->bottinRepository = new BottinRepository();
    }

    public static function instanceBottinRepository(): BottinRepository
    {
        if (!self::$bottinRepositoryStatic) {
            self::$bottinRepositoryStatic = new BottinRepository();
        }

        return self::$bottinRepositoryStatic;
    }

    public function getPosts(int $idSite, int $categoryId = null): array
    {
        $args = array(
            'numberposts' => 5000,
            'orderby' => 'post_title',
            'order' => 'ASC',
            'post_status' => 'publish',
        );

        if ($categoryId) {
            $args ['category'] = $categoryId;
        }

        $posts = get_posts($args);
        $data = [];

        foreach ($posts as $post) {
            $data[] = Document::documentFromPost($post, $idSite);
        }

        return $data;
    }

    public function getCategoriesBySite(int $idSite): array
    {
        $args = array(
            'type' => 'post',
            'child_of' => 0,
            'parent' => '',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => 0,
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'number' => '',
            'taxonomy' => 'category',
            'pad_counts' => true,
        );

        $categories = get_categories($args);
        $data = [];
        $today = new \DateTime();

        foreach ($categories as $category) {

            $description = '';
            if ($category->description) {
                $description = Cleaner::cleandata($category->description);
            }

            $date = $today->format('Y-m-d');
            $content = $description;

            foreach ($this->getPosts($idSite, $category->cat_ID) as $document) {
                $content .= $document->name;
                $content .= $document->excerpt;
                $content .= $document->content;
            }

            $content .= $this->getContentFichesBottin($category);
            //$content .= $this->getContentEnquetes($category->cat_ID);

            $children = $this->wpRepository->getChildrenOfCategory($category->cat_ID);
            $tags = [];
            foreach ($children as $child) {
                $tags[] = $child->name;
            }
            $parent = $this->wpRepository->getParentCategory($category->cat_ID);
            if ($parent) {
                $tags[] = $parent->name;
            }

            $data[] = Document::documentFromCategory($category, $idSite, $description, $content, $tags, $date);
        }

        return $data;
    }

    public function getContentFichesBottin(object $category): string
    {
        return '';
        $categoryBottinId = get_term_meta($category->cat_ID, BottinCategoryMetaBox::KEY_NAME, true);

        if ($categoryBottinId) {
            $fiches = $this->bottinRepository->getFichesByCategory($categoryBottinId);

            return $this->bottinData->getContentForCategory($fiches);
        }

        return '';
    }

    /**
     * @param $fiche
     *
     * @return string[]
     */
    public static function getCategoriesFiche($fiche): array
    {
        $data       = self::instanceBottinRepository()->getCategoriesOfFiche($fiche->id);
        $categories = [];
        foreach ($data as $category) {
            $categories[] = $category->name;
        }

        return $categories;
    }
}