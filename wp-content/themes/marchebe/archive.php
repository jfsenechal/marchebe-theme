<?php

namespace AcMarche\Theme\Templates;


use AcMarche\Theme\Lib\Helper\BreadcrumbHelper;
use AcMarche\Theme\Lib\Twig;
use AcMarche\Theme\Repository\WpRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

get_header();

$cat_ID = get_queried_object_id();
$wpRepository = new WpRepository();
$children = $wpRepository->getChildrenOfCategory($cat_ID);
$category = get_category($cat_ID);
$description = category_description($cat_ID);
$title = single_cat_title('', false);

$posts = $wpRepository->getPostsAndFiches($cat_ID);
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
$paths = BreadcrumbHelper::category($cat_ID);

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