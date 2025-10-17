<?php

/**
 * Template Name: Home-Page-Principal
 */

namespace AcMarche\Theme\Templates;

use AcMarche\Theme\Data\Data;
use AcMarche\Theme\Lib\Pivot\Repository\PivotRepository;
use AcMarche\Theme\Lib\Twig;
use AcMarche\Theme\Repository\ApiRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

get_header();

$apiRepository = new ApiRepository();
$pivotRepository = new PivotRepository();
$events = $pivotRepository->loadEvents();
$events = array_slice($events, 0, 8);
$news = $apiRepository->getNews();
$news = array_slice($news, 0, 6);
$twig = Twig::loadTwig();

try {
    echo $twig->render('@AcMarche/homepage.html.twig', [
            'events' => $events,
            'news' => $news,
            'shortcuts' => Data::shortcuts,
            'widgets' => Data::widgets,
            'partners' => Data::partners,
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}

get_footer();