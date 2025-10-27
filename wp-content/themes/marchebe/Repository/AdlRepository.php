<?php

namespace AcMarche\Theme\Repository;

use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Search\Cleaner;
use AcMarche\Theme\Lib\Search\Document;
use Exception;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AdlRepository
{
    private HttpClientInterface $client;

    public function __construct()
    {
        $this->client = HttpClient::createForBaseUri($_ENV['ADL_URL']);
    }

    public function getAllPosts(): array
    {
        $dataString = $this->executeRequest('/posts/?per_page=100');
        $posts = json_decode($dataString);

        foreach ($posts as $post) {
            $categories = [];
            foreach ($post->categories as $categoryId) {
                $categories[] = ['id' => $categoryId, 'name' => ''];
            }
            $post->tags = $categories;
            $post->content = $post->content->rendered;
            $post->title = Cleaner::cleandata($post->title->rendered);
            $post->excerpt = Cleaner::cleandata($post->excerpt->rendered);
            $post->paths = WpRepository::instance()->getAncestorsOfPost($post->ID);

            Document::documentFromPost($post, Theme::ECONOMIE);
        }

        return json_decode($dataString);
    }

    public function getAllCategories(): array
    {
        $dataString = $this->executeRequest('/categories');
        $categories = json_decode($dataString);
        $data = [];
        foreach ($categories as $category) {

            $description = '';
            if ($category->description) {
                $description = Cleaner::cleandata($category->description);
            }

            $today = new \DateTime();
            $date = $today->format('Y-m-d');
            $content = $description;

            foreach ($this->getPostsByCategoryId($category->id) as $documentElastic) {
                $content .= $documentElastic->name;
                $content .= $documentElastic->excerpt;
                $content .= $documentElastic->content;
            }
            $category->description = $description;
            $category->content = $content;
            $category->created_at = $date;
            $category->paths = [];
            $category->tags = [];

            $data[] = Document::documentFromCategory($category, Theme::ECONOMIE);
        }

        return $data;
    }

    /**
     * @return Document[]
     */
    private function getPostsByCategoryId(int $categoryId): array
    {
        $dataString = $this->executeRequest('/posts/?categories='.$categoryId);

        $posts = json_decode($dataString);
        $datas = [];

        foreach ($posts as $post) {
            WpRepository::instance()->preparePost($post);
            $datas[] = Document::documentFromPost($post, Theme::ECONOMIE);
        }

        return $datas;
    }

    /**
     * @throws Exception
     */
    private function executeRequest(string $url, array $options = [], string $method = 'GET'): string
    {
        try {
            $response = $this->client->request(
                $method,
               '/wp-json/wp/v2'.$url,
                $options
            );

            return $response->getContent();
        } catch (ClientException|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $exception) {
            throw  new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}