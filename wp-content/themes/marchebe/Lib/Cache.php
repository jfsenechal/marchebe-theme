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
    final public const MENU_NAME = 'menu-top';
    final public const ICONES_NAME = 'icones-home';
    final public const EVENTS = 'events';
    final public const OFFRES = 'offres';
    final public const OFFRE = 'offre';
    final public const SEE_ALSO_OFFRES = 'see_also_offre';
    final public const FETCH_OFFRES = 'fetch_offres';
    final public const DURATION = 64800;//18heures
    final public const TAG = ['pivot'];
    private ?CacheInterface $cache;
    private SluggerInterface $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger();
        $this->cache = null;
    }

    public function instance(): CacheInterface|RedisTagAwareAdapter
    {
        if (!$this->cache) {
            $client = RedisAdapter::createConnection('redis://localhost');
            $this->cache = new RedisTagAwareAdapter($client);
        }

        return $this->cache;
    }

    public function generateKey(string $cacheKey): string
    {
        $keyUnicode = new UnicodeString($cacheKey);

        return $this->slugger->slug($keyUnicode->ascii()->toString());
    }
}
