<?php
namespace AcMarche\Theme;
use AcMarche\Theme\Lib\Twig;

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
    <body class="bg-white">
    <?php
wp_body_open();