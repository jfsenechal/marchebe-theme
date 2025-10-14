<?php

namespace AcMarche\Theme;

use AcMarche\Theme\Inc\Assets;
use AcMarche\Theme\Inc\RouterMarche;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;

/**
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 */

if (WP_DEBUG === false) {
    HtmlErrorRenderer::setTemplate(get_template_directory().'/error500.php');
} else {
    Debug::enable();
}
// Adds theme support for post formats.
/**
 * Adds theme support for post formats.
 *
 * @return void
 *
 */
function marche_post_format_setup()
{
    add_theme_support(
        'post-formats',
        array('aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video')
    );
}

new Assets();
new RouterMarche();