<?php

namespace AcMarche\Theme;

use AcMarche\Theme\Lib\Twig;

get_header();
$twig = Twig::loadTwig();
try {
    echo $twig->render('@AcMarche/homepage.html.twig', [
        'statusCode' => $statusCode,
        'statusText' => $statusText,
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}
echo $statusCode;
echo $statusText;

get_footer();