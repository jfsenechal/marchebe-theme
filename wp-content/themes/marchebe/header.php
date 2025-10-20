<?php

namespace AcMarche\Theme;

use AcMarche\Theme\Data\Data;
use AcMarche\Theme\Lib\Twig;
use AcMarche\Theme\Repository\WpRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

$twig = Twig::loadTwig();
?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <?php
        //Twig::rendPage('footer/_analytics.html.twig');
        ?>
        <meta charset="<?php bloginfo('charset'); ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="Nucleïd">
        <meta name="author" content="Cst">
        <title>Homepage</title>
        <?php
        //Twig::rendPage('header/_favicons.html.twig');
        wp_head();
        ?>
    </head>
    <body class="bg-white" id="app">
    <?php
wp_body_open();

$wpRepository = new WpRepository();
$menu = $wpRepository->getMenu();

$twig = Twig::loadTwig();

try {
    echo $twig->render('@AcMarche/_header.html.twig', [
            'menu_data' => json_encode($menu),
            'menuItems' => Data::menuItems,
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}