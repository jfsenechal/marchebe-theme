<?php

namespace AcMarche\Theme\Repository;

use AcMarche\Theme\Lib\HttpApi;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ApiRepository
{
    private HttpApi $httpApi;

    public function __construct()
    {
        $this->httpApi = new HttpApi();
    }

    public function getMenu(): array
    {
        try {
            $content = $this->httpApi->loadMenu();

            return $content->toArray();
        } catch (TransportExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|DecodingExceptionInterface|ClientExceptionInterface $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getNews(): array
    {
        try {
            $content = $this->httpApi->loadNews();

            return $content->toArray();
        } catch (TransportExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|DecodingExceptionInterface|ClientExceptionInterface $e) {
            return ['error' => $e->getMessage()];
        }
    }
}