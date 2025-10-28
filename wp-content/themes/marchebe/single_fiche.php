<?php
/**
 * Template Name: Event Single
 * Template for displaying external event content
 */

namespace AcMarche\Theme\Templates;

use AcMarche\Theme\Inc\RouterBottin;
use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Search\Document;
use AcMarche\Theme\Lib\Twig;
use AcMarche\Theme\Repository\BottinRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

get_header();

$slug = get_query_var(RouterBottin::PARAM_FICHE, null);

$bottinRepository = new BottinRepository();
$fiche = $bottinRepository->getFicheBySlug($slug);

if (!$fiche) {
    Twig::renderNotFoundPage('Fiche non trouvée');
    wp_footer();

    return;
}
$categories = $bottinRepository->getCategoriesOfFiche($fiche->id);
$classementPrincipal = $bottinRepository->getCategoriePrincipale($fiche);
$images = $bottinRepository->getImagesFiche($fiche->id);
$documents = $bottinRepository->getDocuments($fiche->id);
$isCentreVille = $bottinRepository->isCentreVille($fiche->id);
$logo = $bottinRepository->getLogo($fiche->id);
if ($logo) {
    unset($images[0]);
}
array_map(
    function ($category) {
        $category->url = RouterBottin::getUrlCategoryBottin($category);
    },
    $categories
);
$paths = $tags = [];
$twig = Twig::loadTwig();
$post = Document::documentFromFiche($fiche, $bottinRepository->findSiteFiche($fiche));

$content = $twig->render('@AcMarche/bottin/_body.html.twig', [
    'fiche' => $fiche,
    'isCentreVille' => $isCentreVille,
    'logo' => $logo,
    'images' => $images,
    'documents' => $documents,
    'latitude' => $fiche->latitude,
    'longitude' => $fiche->longitude,
]);

try {
    echo $twig->render('@AcMarche/article/show.html.twig', [
        'post' => $post,
        'title' => $post->name,
        'body' => $content,
        'paths' => $paths,
        'site' => Theme::TOURISME,
        'tags' => $tags,
        'thumbnail' => count($images) > 0 ? $images[0] : null,
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}

get_footer();