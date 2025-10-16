<?php

namespace AcMarche\Theme\Repository;

use AcMarche\Theme\Lib\Pivot\Enums\ContentEnum;
use Symfony\Component\HttpClient\DecoratorTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PivotApi
{
    use DecoratorTrait;

    private ?string $code_query = null;
    private ?string $base_uri = null;
    private ?string $ws_key = null;
    public ?string $url_executed = null;
    public ?string $data_raw = null;

    public function __construct()
    {
        $this->base_uri = $_ENV['PIVOT_BASE_URI'] ?? null;
        $this->ws_key = $_ENV['PIVOT_WS_KEY'] ?? null;
        $this->code_query = $_ENV['PIVOT_CODE'] ?? null;

        $headers = [
            'headers' => [
                'Accept' => 'application/json',
                'ws_key' => $this->ws_key,
            ],
            'verify_peer' => false,
            'verify_host' => false,
        ];

        $this->client = HttpClient::create($headers);
    }

    /**
     * Ces requêtes sont créées et stockées par les opérateurs de PIVOT afin de fournir des flux
     * de données. Les requêtes sont accessibles au moyen d’un code identifiant unique (codeCgt).
     * @throws TransportExceptionInterface
     */
    public function query(int $level = ContentEnum::LVL2->value): ResponseInterface
    {
        return $this->client->request(
            'GET',
            $this->base_uri.'/query/'.$this->code_query.';content='.$level
        );
    }

    /**
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function loadEvents(): ResponseInterface
    {
        return $this->client->request(
            'GET',
            $this->base_uri.'/query'
        );
    }
}