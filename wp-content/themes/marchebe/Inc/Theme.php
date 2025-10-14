<?php


namespace AcMarche\Theme\Inc;

use Symfony\Component\HttpFoundation\Request;

class Theme
{
    const CITOYEN = 1;
    const ADMINISTRATION = 2;
    const ECONOMIE = 3;
    const TOURISME = 4;
    const SOCIAL = 7;
    const SPORT = 5;
    const SANTE = 6;
    const ENFANCE = 14;
    const CULTURE = 11;
    const ROMAN = 12;

    const SITES = [
        self::CITOYEN        => 'citoyen',
        self::ADMINISTRATION => 'administration',
        self::ECONOMIE       => 'economie',
        self::TOURISME       => 'tourisme',
        self::SPORT          => 'sport',
        self::SANTE          => 'sante',
        self::SOCIAL         => 'social',
        8                    => 'marchois',
        self::CULTURE        => 'culture',
        self::ROMAN          => 'roman',
        self::ENFANCE        => 'enfance',
    ];
    const COLORS = [
        self::CITOYEN        => 'color-cat-cit',
        self::ADMINISTRATION => 'color-cat-adm',
        self::ECONOMIE       => 'color-cat-eco',
        self::TOURISME       => 'color-cat-tou',
        5                    => 'color-cat-spo',
        6                    => 'color-cat-san',
        7                    => 'color-cat-soc',
        8                    => 'color-cat-cit',
        self::ROMAN          => 'color-cat-cul',//=>roman
        self::CULTURE        => 'color-cat-cul',
        14                   => 'color-cat-enf',
    ];
    const PAGE_ALERT = 5087;
    const PAGE_CARTO = 20644;//tourisme
    const ENQUETE_DIRECTORY_URBA = 61;
    const ENQUETE_DIRECTORY_INSTIT = 60;
    const PUBLICATIOCOMMUNAL_CATEGORY = 15;

    static function isHomePage(): bool
    {
        $request = Request::createFromGlobals();

        $uri = $request->getPathInfo();
        if ($uri === '/') {
            return true;
        }

        return false;
    }

    static function isContactPage(): bool
    {
        if (get_current_blog_id() === Theme::ADMINISTRATION && get_queried_object_id() == 277) {
            return true;
        }


        return false;
    }

    static function getColorBlog(int $blodId)
    {
        return self::COLORS[$blodId] ?? 'color-cat-cit';
    }

    static function getPathBlog(int $blodId)
    {
        if ($blodId === 1) {
            return '';
        } else {
            return get_blog_details($blodId)->path;
        }
    }

    static function getTitleBlog(int $blodId)
    {
        return get_blog_details($blodId)->blogname;
    }

}
