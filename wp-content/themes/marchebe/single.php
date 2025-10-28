<?php

namespace AcMarche\Theme\Templates;

use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Helper\BreadcrumbHelper;
use AcMarche\Theme\Lib\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

get_header();

$post = get_post();

$twig = Twig::loadTwig();
$image = null;
if (has_post_thumbnail()) {
    $images = wp_get_attachment_image_src(get_post_thumbnail_id(), 'original');
    if ($images) {
        $image = $images[0];
    }
}

$tags = [];
foreach (get_the_category($post->ID) as $category) {
    $tags[] = ['id' => $category->term_id, 'name' => $category->name, 'url' => get_category_link($category->term_id)];
}
$paths = BreadcrumbHelper::post($post->ID);

$content = get_the_content(null, null, $post);
$content = apply_filters('the_content', $content);
$content = str_replace(']]>', ']]&gt;', $content);

try {
    echo $twig->render('@AcMarche/article/show.html.twig', [
        'post' => $post,
        'title' => $post->post_title,
        'body' => $content,
        'paths' => $paths,
        'site' => Theme::TOURISME,
        'tags' => $tags,
        'thumbnail' => $image,
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}

get_footer();