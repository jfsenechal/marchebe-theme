<?php


use AcMarche\Theme\Lib\Twig;
use AcMarche\Theme\Lib\WpRepository;

class PageSorting
{
    public function __construct()
    {

    }

    static function loadPages()
    {
        $position = 61;
        add_menu_page(
            'Tri des articles',
            'Tri',
            'edit_posts',
            'acmarche_trie',
            function () {
                PageSorting::pageIndex();
            },
            'dashicons-sort',
            $position
        );
        add_submenu_page(
            'acmarche_trie',
            'Trie des news',
            'Tri des news',
            'edit_posts',
            'ac_marche_tri_news',
            function () {
                PageSorting::renderPageNews();
            },
        );
        add_submenu_page(
            'acmarche_trie',
            'Trie des articles',
            'Tri des articles',
            'edit_posts',
            'acmarche_trie_post',
            function () {
                PageSorting::renderPageCategory();
            },
        );
    }

    static function pageIndex()
    {
        $urlNews = admin_url('/admin.php?page=ac_marche_tri_news');
        Twig::rendPage(
            'admin/menu.html.twig',
            [
                'urlNews' => $urlNews,
            ]
        );
    }

    static function renderPageNews()
    {
        $news = WpRepository::getAllNews(60);

        Twig::rendPage(
            'admin/tri_news.html.twig',
            [
                'news' => $news,
            ]
        );
    }

    static function renderPageCategory()
    {
        $cat_id       = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
        $category     = get_category($cat_id);
        $wpRepository = new WpRepository();
        $posts        = $wpRepository->getPostsAndFiches($cat_id);
        $posts        = AcSort::getSortedItems($cat_id, $posts);

        Twig::rendPage(
            'admin/tri_articles.html.twig',
            [
                'category' => $category,
                'posts'    => $posts,
            ]
        );
    }
}
