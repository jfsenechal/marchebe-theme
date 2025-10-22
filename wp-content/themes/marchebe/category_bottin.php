<?php

namespace AcMarche\Theme\Templates;


use AcMarche\Theme\Inc\RouterBottin;
use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Twig;
use AcMarche\Theme\Repository\BottinRepository;
use AcMarche\Theme\Repository\WpRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

get_header();

$slug = get_query_var(RouterBottin::PARAM_CATEGORY, null);
dd($slug);
$bottinRepository = new BottinRepository();

$cat_ID = get_queried_object_id();
$blog_id = get_current_blog_id();
$wpRepository = new WpRepository();
$children = $wpRepository->getChildrenOfCategory($cat_ID);
$category = get_category($cat_ID);
$description = category_description($cat_ID);
$title = single_cat_title('', false);

$posts = $wpRepository->getPosts($cat_ID);
$parent = $wpRepository->getParentCategory($cat_ID);
$postsSerialized = [];
foreach ($posts as $post) {
    $postsSerialized[$post->ID] = [
        'name' => $post->ID,
        'post_title' => $post->post_title,
        'ID' => $post->ID,
        'post_excerpt' => $post->post_excerpt,
        'url' => get_permalink($post),
    ];
}
$twig = Twig::loadTwig();
$thumbnail = "https://picsum.photos/2070";
$paths = [];
if ($blog_id > Theme::CITOYEN) {
    $path = Theme::getPathBlog($blog_id);
    $blogName = Theme::getTitleBlog($blog_id);
    $paths[] = ['name' => $blogName, 'term_id' => $blog_id, 'url' => $path];
}
if ($parent) {
    $paths = ['name' => $parent->name, 'term_id' => $parent->cat_ID, 'url' => ''];
}
try {
    echo $twig->render('@AcMarche/category/show.html.twig', [
        'category' => $category,
        'posts' => $posts,
        'postsSerialized' => json_encode($postsSerialized),
        'thumbnail' => $thumbnail,
        'paths' => $paths,
        'title' => $title,
        'children' => $children,
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}
get_footer();