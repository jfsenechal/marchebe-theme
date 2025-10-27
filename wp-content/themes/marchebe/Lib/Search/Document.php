<?php

namespace AcMarche\Theme\Lib\Search;

use AcMarche\Theme\Inc\RouterBottin;
use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Bottin\Bottin;
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
    public array $paths = [];
    public array $site = [];

    public static function documentFromPost(\WP_Post $post, int $idSite): Document
    {
        list($date, $time) = explode(" ", $post->post_date);
        $categories = array();
        foreach (get_the_category($post->ID) as $category) {
            $categories[] = $category->cat_name;
        }

        $content = get_the_content(null, null, $post);
        $content = apply_filters('the_content', $content);
        $siteName = Theme::getTitleBlog($idSite);
        $wpRepository = new WpRepository();
        $document = new Document();
        $document->id = $post->ID."-post-".$idSite;
        $document->name = Cleaner::cleandata($post->post_title);
        $document->excerpt = Cleaner::cleandata($post->post_excerpt);
        $document->content = Cleaner::cleandata($content);
        $document->tags = $categories;
        $document->site = ['name' => $siteName, 'id' => $idSite];
        $document->paths = $wpRepository->getAncestorsOfPost($post->ID);
        $document->date = $date;
        $document->type = 'article';
        $document->url = get_permalink($post->ID);

        return $document;
    }

    public static function documentFromCategory(
        \WP_Term $category,
        int $idSite,
        string $description,
        string $content,
        array $tags
    ): Document {
        $wpRepository = new WpRepository();
        $document = new Document();
        $siteName = Theme::getTitleBlog($idSite);
        $document->id = $category->cat_ID."-category-".$idSite;
        $document->name = Cleaner::cleandata($category->name);
        $document->excerpt = $description;
        $document->content = $content;
        $document->tags = $tags;
        $document->site = ['name' => $siteName, 'id' => $idSite];
        $document->paths = $wpRepository->getAncestorsOfCategory($category->cat_ID);
        $document->date = date('Y-m-d');
        $document->type = 'catégorie';
        $document->url = get_category_link($category->cat_ID);

        return $document;
    }

    public static function documentFromFiche(\stdClass $fiche, int $idSite): Document
    {
        $categories = DataForSearch::getCategoriesFiche($fiche);

        $document = new Document();
        $siteName = Theme::getTitleBlog($idSite);
        $document->id = $fiche->id."-fiche-".$idSite;
        $document->name = Cleaner::cleandata($fiche->societe);
        $document->excerpt = Bottin::getExcerpt($fiche);
        $document->content = self::getContentFiche($fiche);
        $document->site = ['name' => $siteName, 'id' => $idSite];
        $document->tags = $categories;
        $document->paths = [];
        list($date, $heure) = explode(' ', $fiche->created_at);
        $document->date = $date;
        $document->type = 'fiche';
        $document->url = RouterBottin::getUrlFicheBottin($idSite, $fiche);

        return $document;
    }

    private static function getContentFiche($fiche): string
    {
        return ' '.$fiche->societe.' '.$fiche->email.' '.$fiche->website.''.$fiche->twitter.' '.$fiche->facebook.' '.$fiche->nom.' '.$fiche->prenom.' '.$fiche->comment1.''.$fiche->comment2.' '.$fiche->comment3;
    }

}