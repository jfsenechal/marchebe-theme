<?php

namespace AcMarche\Theme\Lib\Search;

use AcMarche\Theme\Inc\RouterBottin;
use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Bottin\Bottin;
use AcMarche\Theme\Repository\BottinRepository;

class Document
{
    public string $id;
    public string $name;
    public ?string $excerpt = null;
    public string $content;
    public array $tags = [];
    public string $date;
    public string $link;
    public string $type;
    public int $count = 0;
    public array $paths = [];
    public array $site = [];

    public static function documentFromPost(\WP_Post|\stdClass $post, int $idSite): Document
    {
        list($date, $time) = explode(" ", $post->post_date);

        $nameSite = Theme::getTitleBlog($idSite);
        $document = new Document();
        $document->id = self::createId($post->ID ?? $post->id, "post", $idSite);
        $document->name = Cleaner::cleandata($post->post_title);
        $document->excerpt = Cleaner::cleandata($post->post_excerpt);
        $document->content = Cleaner::cleandata($post->content);
        $document->site = ['name' => $nameSite, 'id' => $idSite];
        $document->tags = $post->tags;
        $document->paths = $post->paths;
        $document->date = $date;
        $document->type = 'article';
        $document->link = $post->link;

        return $document;
    }

    public static function documentFromCategory(\WP_Term|\stdClass $category, int $idSite): Document
    {
        $document = new Document();
        $nameSite = Theme::getTitleBlog($idSite);
        $document->id = self::createId($category->term_id ?? $category->id, "category", $idSite);
        $document->name = Cleaner::cleandata($category->name);
        $document->excerpt = $category->description;
        $document->content = $category->content;
        $document->tags = $category->tags;
        $document->site = ['name' => $nameSite, 'id' => $idSite];
        $document->paths = $category->paths;
        $document->date = date('Y-m-d');
        $document->type = 'category';
        $document->link = $category->link;

        return $document;
    }

    public static function documentFromFiche(\stdClass $fiche, int $idSite): Document
    {
        $categories = DataForSearch::getCategoriesFiche($fiche);

        $document = new Document();
        $nameSite = Theme::getTitleBlog($idSite);
        $document->id = self::createId($fiche->id, "fiche", $idSite);
        $document->name = Cleaner::cleandata($fiche->societe);
        $document->excerpt = Bottin::getExcerpt($fiche);
        $document->content = DataForSearch::getContentFiche($fiche);
        $document->site = ['name' => $nameSite, 'id' => $idSite];
        $document->tags = $categories;
        $document->paths = [];//todo
        list($date, $heure) = explode(' ', $fiche->created_at);
        $document->date = $date;
        $document->type = 'fiche';
        $document->link = RouterBottin::getUrlFicheBottin($idSite, $fiche);

        return $document;
    }

    public static function documentFromCategoryBottin(\stdClass $category): Document
    {
        $created = explode(' ', $category->created_at);
        $document = new Document();
        $document->id = self::createId($category->id, "category-bottin", Theme::ECONOMIE);
        $document->name = $category->name;
        $document->excerpt = $category->description;
        $document->tags = [];//todo
        $document->paths = [];
        $document->date = $created[0];
        $document->type = 'category';
        $document->link = RouterBottin::getUrlCategoryBottin($category);
        $fiches = BottinRepository::instanceBottinRepository()->getFichesByCategory($category->id);
        $document->count = count($fiches);
        $document->content = DataForSearch::getContentForCategory($fiches);

        return $document;

    }

    public static function createId(int $id, string $type, ?int $siteId = 0): string
    {
        $id = $type.'-'.$id;
        if ($siteId) {
            $id .= '-'.$siteId;
        }

        return $id;
    }

}