<?php

namespace AcMarche\Theme\Lib\Helper;

class CookieHelper
{
    const COOKIE_PREFERENCES = 'cookiePreferences';
    private static string $analytics = 'analytics';
    public static string $encapsulated = 'encapsulated';

    public static function getAll(): array
    {
        if (!isset($_COOKIE[self::COOKIE_PREFERENCES])) {
            self::setAll([]);

            return [];
        }

        $decoded = json_decode($_COOKIE[self::COOKIE_PREFERENCES], true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function isAuthorizedByName(string $name): bool
    {
        $preferences = self::getAll();

        return isset($preferences[$name]) && $preferences[$name] === true;
    }

    public static function hasSetPreferences(): bool
    {
        return !(count(self::getAll()) == 0);
    }

    public static function setByName(string $name, bool $value): void
    {
        $preferences = self::getAll();
        $preferences[$name] = $value;
        self::setAll($preferences);
    }

    public static function setAll(array $preferences): void
    {
        // Set cookie for 365 days
        $expiry = time() + (365 * 24 * 60 * 60);
        setcookie(
            'cookiePreferences',
            urlencode(json_encode($preferences)),
            $expiry,
            '/',
            '',
            true, // Secure (HTTPS only)
            false  // HttpOnly - set to false so JavaScript can read it
        );
    }
}