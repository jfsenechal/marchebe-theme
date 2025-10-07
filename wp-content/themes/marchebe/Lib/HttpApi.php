<?php

namespace AcMarche\Theme\Lib;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpApi
{
    private HttpClientInterface $client;

    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    /**
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function loadMenu(): ResponseInterface
    {
        return $this->client->request(
            'GET',
            'https://www.marche.be/nuxt/menu.php'
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
            'https://www.marche.be/nuxt/events.php?limit=300'
        );
    }

    /**
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function loadNews(): ResponseInterface
    {
        return $this->client->request(
            'GET',
            'https://www.marche.be/nuxt/actus.php'
        );
    }

}