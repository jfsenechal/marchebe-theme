<?php

namespace AcMarche\Theme\Lib;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Extension\DebugExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;

class Twig
{
    public static function loadTwig(string $path = null): Environment
    {
        if (!$path) {
            $path = get_template_directory().'/templates';
        }

        $loader = new FilesystemLoader($path);

        try {
            $loader->addPath($path, 'AcMarche');
        } catch (LoaderError $e) {

        }

        $cache = $_ENV['APP_CACHE_DIR'] ?? self::getPathCache('twig');

        $twig = new Environment($loader, [
            'strict_variables' => WP_DEBUG,
            'debug' => WP_DEBUG,
            'cache' => WP_DEBUG ? false : $cache,
            'auto_reload' => true,
            'optimizations' => 0,
            'charset' => 'UTF-8',
        ]);

        if (WP_DEBUG) {
            $twig->enableDebug();
            $twig->addExtension(new DebugExtension());
        }

        locale_set_default('fr-FR');//for format date
        $twig->addGlobal('locale', 'fr');
        $twig->addExtension(new StringExtension());
        $twig->addExtension(new IntlExtension());
        $twig->addGlobal('template_directory', get_template_directory_uri());

        return $twig;
    }

    private static function getPathCache(string $folder): string
    {
        return ABSPATH.'var/cache/'.$folder;
    }

}