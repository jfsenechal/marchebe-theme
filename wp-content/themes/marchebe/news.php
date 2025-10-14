<?php

/**
 * Template Name: News
 */

namespace AcMarche\Theme;

use AcMarche\Theme\Repository\ApiRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use AcMarche\Theme\Lib\Twig;

get_header();

$twig = Twig::loadTwig();
$apiRepository = new ApiRepository();
$news = $apiRepository->getNews();
$thumbnail = "https://picsum.photos/2070";
$paths = [];
try {
    echo $twig->render('@AcMarche/news/index.html.twig', [
        'news' => $news,
        'thumbnail' => $thumbnail,
        'paths' => $paths,
        'title' => "L'actualités",
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}
get_footer();
