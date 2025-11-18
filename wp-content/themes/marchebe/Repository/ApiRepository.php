<?php

namespace AcMarche\Theme\Repository;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiRepository
{
    private HttpClientInterface $client;

    public function __construct(?HttpClientInterface $client = null)
    {
        $this->client = $client ?? HttpClient::create();
    }

    public function getEnquetesPubliques(): array
    {
        try {
            $response = $this->client->request(
                'GET',
                'https://extranet.marche.be/enquete/api/'
            );
        } catch (TransportExceptionInterface $e) {
            return [];
        }
        try {
            $content = $response->getContent();
        } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            return [];
        }

        try {
            return json_decode($content, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [];
        }
    }

    public static function getPublications(int $wpCategoryId): array
    {
        global $wpdb;

        $category = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM publication.category WHERE publication.category.wpCategoryId = %d",
                $wpCategoryId
            ),
            OBJECT
        );

        if (empty($category)) {
            return [];
        }

        $categoryId = $category[0]->id ?? null;
        if (!$categoryId) {
            return [];
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM publication.publication WHERE publication.publication.category_id = %d ORDER BY createdAt DESC",
                $categoryId
            ),
            OBJECT
        );

        if (!$results) {
            return [];
        }

        return $results;
    }
}