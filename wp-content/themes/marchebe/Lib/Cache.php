<?php

namespace AcMarche\Theme\Lib;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\Cache\CacheInterface;

class Cache
{
    private static ?CacheInterface $cache = null;
    private static ?SluggerInterface $slugger = null;

    private function __construct()
    {
    }

    public static function instance(): CacheInterface|RedisTagAwareAdapter
    {
        if (!self::$cache) {
            $client = RedisAdapter::createConnection('redis://localhost');
            self::$cache = new RedisTagAwareAdapter($client);
        }

        return self::$cache;
    }

    public static function generateKey(string $cacheKey): string
    {
        if (!self::$slugger) {
            self::$slugger = new AsciiSlugger();
        }

        $keyUnicode = new UnicodeString($cacheKey);

        return self::$slugger->slug($keyUnicode->ascii()->toString());
    }

    // Helper method to get an item from cache
    public static function get(string $key, callable $callback, ?float $beta = null, ?array $tags = null)
    {
        $cacheKey = self::generateKey($key);

        return self::instance()->get($cacheKey, $callback, $beta, $tags);
    }

    // Helper method to delete an item from cache
    public static function delete(string $key): bool
    {
        $cacheKey = self::generateKey($key);

        return self::instance()->delete($cacheKey);
    }

    // Helper method to invalidate tags
    public static function invalidateTags(array $tags): bool
    {
        return self::instance()->invalidateTags($tags);
    }
}