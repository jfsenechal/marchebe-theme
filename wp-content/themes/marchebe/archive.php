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
$category->url = get_category_link($cat_ID);
$description = category_description($cat_ID);
$title = single_cat_title('', false);

$posts = $wpRepository->getPostsAndFiches($cat_ID);
$twig = Twig::loadTwig();
$thumbnail = "https://picsum.photos/2070";
$paths = BreadcrumbHelper::category($cat_ID);

try {
    echo $twig->render('@AcMarche/category/show.html.twig', [
        'category' => $category,
        'posts' => $posts,
        'postsSerialized' => json_encode($posts),
        'thumbnail' => $thumbnail,
        'paths' => $paths,
        'title' => $title,
        'description' => $description,
        'children' => $children,
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}
get_footer();