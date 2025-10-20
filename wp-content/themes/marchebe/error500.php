<?php

namespace AcMarche\Theme;

use AcMarche\Theme\Lib\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

get_header();
$twig = Twig::loadTwig();

try {
    $twig->render('@AcMarche/error/_error.html.twig', [
        'message' => $exception?->getMessage(),
        'file' => $exception?->getFile(),
        'line' => $exception?->getLine(),
        'statusCode' => $statusCode,
        'statusText' => $statusText,
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}

get_footer();