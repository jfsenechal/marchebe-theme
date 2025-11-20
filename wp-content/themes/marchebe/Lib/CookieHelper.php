<?php

namespace AcMarche\Theme\Lib;

class CookieHelper
{
    private static string $analytics = 'analytics';
    public static string $encapsulated = 'encapsulated';

    public static function get(): null|array
    {
        if (isset($_COOKIE['cookiePreferences'])) {
            try {
                return json_decode(urldecode($_COOKIE['cookiePreferences']), true, flags: JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                return null;
            }
        }

        return null;
    }

    public static function getByName(string $name): bool
    {
        $preferences = CookieHelper::get();
        if ($preferences && isset($preferences[$name])) {
            return true;
        }

        return false;
    }
}