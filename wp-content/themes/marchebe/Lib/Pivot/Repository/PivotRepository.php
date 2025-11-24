<?php

namespace AcMarche\Theme\Lib\Pivot\Repository;

use AcMarche\Theme\Lib\Cache;
use AcMarche\Theme\Lib\Pivot\Entity\Event;
use AcMarche\Theme\Lib\Pivot\Enums\ContentEnum;
use AcMarche\Theme\Lib\Pivot\Helper\SortHelper;
use AcMarche\Theme\Lib\Pivot\Parser\EventParser;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PivotRepository
{
    private PivotApi $pivotApi;
    //skip marche public, st loup
    private array $eventsToSkip = ['EVT-01-0AVJ-324P', 'EVT-A0-008E-101W'];

    public function __construct()
    {
        $this->pivotApi = new PivotApi();
    }

    /**
     * Ces requêtes sont créées et stockées par les opérateurs de PIVOT afin de fournir des flux
     * de données. Les requêtes sont accessibles au moyen d’un code identifiant unique (codeCgt).
     * @throws \Exception
     */
    public function queryContent(int $level = 2): \stdClass
    {
        try {
            $response = $this->pivotApi->query($level);
            $content = $response->getContent();
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            throw new \Exception($e->getMessage());
        }

        return json_decode($content, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<int,Event>
     * @throws \JsonException
     * @throws \Throwable
     */
    public function loadEvents(
        int $level = ContentEnum::LVL4->value,
        bool $purgeCache = false,
        bool $skip = false
    ): array {
        $cacheKey = Cache::generateKey('all-events-marche-be-'.$level.'-'.$skip);
        if ($purgeCache) {
            Cache::delete($cacheKey);
        }
        $jsonContent = Cache::get($cacheKey, function () use ($level) {
            $pivotApi = new PivotApi();
            try {
                $response = $pivotApi->query($level);

                return $response->getContent();
            } catch (\Exception $e) {
                return null;
            }
        });

        if (!$jsonContent) {
            return [];
        }

        $parser = new EventParser();
        $events = $parser->parseJsonFile($jsonContent);

        if ($skip) {
            $all = [];
            foreach ($events as $event) {
                if (!in_array($event->codeCgt, $this->eventsToSkip)) {
                    $all[] = $event;
                }
            }
        }

        return SortHelper::sortEvents($all);
    }

    /**
     * @param string $codeCgt
     * @param bool $parse
     * @param bool $purgeCache
     * @param int $level
     * @return Event|string|null
     * @throws \JsonException
     */
    public function loadOneEvent(
        string $codeCgt,
        bool $parse = false,
        bool $purgeCache = false,
        int $level = ContentEnum::LVL4->value
    ): Event|string|null {
        $cacheKey = Cache::generateKey('offer-'.$codeCgt.'-'.$level);
        if ($purgeCache) {
            Cache::delete($cacheKey);
        }
        $jsonContent = Cache::get($cacheKey, function () use ($codeCgt, $level) {
            $pivotApi = new PivotApi();
            try {
                $response = $pivotApi->loadEvent($codeCgt, $level);

                return $response->getContent();
            } catch (\Exception $e) {
                return null;
            }
        });

        if (!$jsonContent) {
            return null;
        }

        if (!$parse) {
            return $jsonContent;
        }

        $parser = new EventParser();
        $data = json_decode($jsonContent, associative: true, flags: JSON_THROW_ON_ERROR);

        $event = $parser->parseEvent($data['offre'][0]);

        return $event;
    }
}