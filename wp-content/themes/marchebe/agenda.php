<?php

/**
 * Template Name: Agenda
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
$events = $apiRepository->getEvents();
$thumbnail = "https://picsum.photos/2070";
$paths = [];
try {
    echo $twig->render('@AcMarche/agenda/index.html.twig', [
        'events' => $events,
        'thumbnail' => $thumbnail,
        'paths' => $paths,
        'title' => "Agenda des manifestations",
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}
get_footer();
