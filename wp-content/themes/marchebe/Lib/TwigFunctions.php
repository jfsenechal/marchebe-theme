<?php

namespace AcMarche\Theme\Lib;

use Twig\TwigFunction;

class TwigFunctions
{
    public static function fichePhones(): TwigFunction
    {
        return new TwigFunction(
            'fichePhones',
            function (\stdClass $fiche): array {
                return [];
            }
        );
    }

    public static function ficheEmails(): TwigFunction
    {
        return new TwigFunction(
            'FicheEmails',
            function (\stdClass $fiche): array {
                return [];
            }
        );
    }
}