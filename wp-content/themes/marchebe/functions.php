<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 */

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