<?php

/**
 * Template Name: Agenda
 */

namespace AcMarche\Theme;

use AcMarche\Theme\Lib\Pivot\Repository\PivotRepository;
use AcMarche\Theme\Lib\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

get_header();

$twig = Twig::loadTwig();
$pivotRepository = new PivotRepository();

try {
    $events = $pivotRepository->loadEvents();
} catch (\Exception $e) {
    Twig::renderErrorPage($e);

    get_footer();
    return;
}
foreach ($events as $event) {
    //dump($event->adresse1->localite);
}
//dd(123);
$defaultImage = 'https://www.visitmarche.be/wp-content/uploads/2021/02/bg_events.png';
$thumbnail = "https://picsum.photos/2070";
$paths = [];

try {
    echo $twig->render('@AcMarche/agenda/index.html.twig', [
        'events' => $events,
        'thumbnail' => $thumbnail,
        'defaultImage' => $defaultImage,
        'paths' => $paths,
        'title' => "Agenda des manifestations",
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}
get_footer();
