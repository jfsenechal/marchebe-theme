<?php
/**
 * Template Name: Event Single
 * Template for displaying external event content
 */

namespace AcMarche\Theme\Templates;

use AcMarche\Theme\Inc\RouterMarche;
use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Twig;
use AcMarche\Theme\Repository\ApiRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

$codeCgt = get_query_var(RouterMarche::PARAM_EVENT);

get_header();

if (!str_contains($codeCgt, "EVT")) {

}

$apiRepository = new ApiRepository();
$event = $apiRepository->getOneEvent($codeCgt);
$twig = Twig::loadTwig();

if (count($event['datesEvent']) === 0) {

}

$image = count(
    $event['images']
) > 0 ? $event['images'][0] : 'https://pivotmedia.tourismewallonie.be/OTH-A0-00UE-0HH1/OTH-A0-00UE-0HH1.jpg';

try {
    echo $twig->render('@AcMarche/agenda/show.html.twig', [
        'event' => $event,
        'title' => $event['nom'],
        'paths' => [],
        'site' => Theme::TOURISME,
        'tags' => ['name' => 'Agenda', 'term_id' => 5, 'url' => '/agenda'],
        'thumbnail' => $image,

    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}

get_footer();