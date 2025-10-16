<?php

namespace AcMarche\Theme\Repository;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
     * @return \stdClass
     * @throws TransportExceptionInterface
     */
    public function loadEvents():  \stdClass
    {
        $response = $this->pivotApi->query();

    }
}