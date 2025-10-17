<?php

namespace AcMarche\Theme\Lib\Pivot\Repository;

use AcMarche\Theme\Lib\Cache;
use AcMarche\Theme\Lib\Pivot\Entity\Event;
use AcMarche\Theme\Lib\Pivot\Enums\ContentEnum;
use AcMarche\Theme\Lib\Pivot\Parser\EventParser;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PivotRepository
{
    private PivotApi $pivotApi;

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
     */
    public function loadEvents(bool $purgeCache = false): array
    {
        if ($purgeCache) {
            Cache::delete('pivot_json_file');
        }
        $jsonContent = Cache::get('pivot_json_file', function () {
            $pivotApi = new PivotApi();
            try {
                $response = $pivotApi->query(ContentEnum::LVL3->value);

                return $response->getContent();
            } catch (\Exception $e) {
                return null;
            }
        });
        if (!$jsonContent) {
            return [];
        }

        $parser = new EventParser();

        return $parser->parseJsonFile($jsonContent);
    }

    /**
     * @param string $codeCgt
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function loadOneEvent(string $codeCgt): ResponseInterface
    {
        return $this->client->request(
            'GET',
            'https://www.visitmarche.be/api/event.php?code='.$codeCgt
        );
    }
}