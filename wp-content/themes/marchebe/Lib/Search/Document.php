<?php

namespace AcMarche\Theme\Lib\Search;

use AcMarche\Theme\Repository\WpRepository;

class Document
{
    public string $id;
    public string $name;
    public ?string $excerpt = null;
    public string $content;
    public array $tags = [];
    public string $date;
    public string $url;
    public string $type;
    public int $count = 0;
    public array $ids = [];
    public array $path = [];

    public static function documentFromPost(\WP_Post $post, int $siteId): Document
    {
        list($date, $time) = explode(" ", $post->post_date);
        $categories = array();
        foreach (get_the_category($post->ID) as $category) {
            $categories[] = $category->cat_name;
        }

        $content = get_the_content(null, null, $post);
        $content = apply_filters('the_content', $content);

        $wpRepository = new WpRepository();
        $document = new Document();
        $document->id = $post->ID."-post-".$siteId;
        $document->name = Cleaner::cleandata($post->post_title);
        $document->excerpt = Cleaner::cleandata($post->post_excerpt);
        $document->content = Cleaner::cleandata($content);
        $document->tags = $categories;
        $document->path = $wpRepository->getAncestorsOfPost($post->ID);
        $document->date = $date;
        $document->type = 'article';
        $document->url = get_permalink($post->ID);

        return $document;
    }

    public static function documentFromCategory(
        \WP_Term $category,
        int $siteId,
        string $description,
        string $content,
        array $tags
    ): Document {
        $wpRepository = new WpRepository();
        $document = new Document();
        $document->id = $category->cat_ID."-category-".$siteId;
        $document->name = Cleaner::cleandata($category->name);
        $document->excerpt = $description;
        $document->content = $content;
        $document->tags = $tags;
        $document->path = $wpRepository->getAncestorsOfCategory($category->cat_ID);
        $document->date = date('Y-m-d');
        $document->type = 'catégorie';
        $document->url = get_category_link($category->cat_ID);

        return $document;
    }
}