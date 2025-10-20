<?php

namespace AcMarche\Theme;

use AcMarche\Theme\Inc\Assets;
use AcMarche\Theme\Inc\RouterMarche;
use AcMarche\Theme\Inc\SecurityConfig;
use AcMarche\Theme\Inc\SetupTheme;
use AcMarche\Theme\Lib\Seo;
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
new SetupTheme();
new Assets();
new RouterMarche();
new SecurityConfig();
new Seo();