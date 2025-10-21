<?php

namespace AcMarche\Theme;

use AcMarche\Theme\Lib\Search\MeiliSearch;
use AcMarche\Theme\Lib\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

get_header();

$searcher = new MeiliSearch();
$searcher->initClientAndIndex();

$keyword = get_search_query();

try {
    $searching = $searcher->doSearch($keyword);
    $hits = $searching->getHits();
    $count = $searching->count();
} catch (\Exception $e) {
    Twig::renderErrorPage($e);

    get_footer();

    return;
}

$twig = Twig::loadTwig();
$thumbnail = "https://picsum.photos/2070";
$paths = [];

try {
    echo $twig->render('@AcMarche/search.html.twig', [
        'hits' => $hits,
        'count' => $count,
        'thumbnail' => $thumbnail,
        'paths' => $paths,
        'title' => 'Rechercher',
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    Twig::renderErrorPage($e);
}
get_footer();
