<?php

namespace AcMarche\Theme\Lib\Search;

use AcMarche\Bottin\SearchData\Data\ElasticData;
use AcMarche\Theme\Repository\WpRepository;
use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Endpoints\Keys;

class MeiliServer
{
    use MeiliTrait;

    public function __construct()
    {
        $this->indexName = $_ENV['MEILI_INDEX_NAME'] ?? null;
        $this->masterKey = $_ENV['MEILI_MASTER_KEY'] ?? null;
    }

    /**
     *
     * @return array<'taskUid','indexUid','status','enqueuedAt'>
     */
    public function createIndex(): array
    {
        $this->client->deleteTasks((new DeleteTasksQuery())->setStatuses(['failed', 'canceled', 'succeeded']));
        $this->client->deleteIndex($this->indexName);

        return $this->client->createIndex($this->indexName, ['primaryKey' => $this->primaryKey]);
    }

    /**
     * https://raw.githubusercontent.com/meilisearch/meilisearch/latest/config.toml
     * curl -X PATCH 'http://localhost:7700/experimental-features/' -H 'Content-Type: application/json' -H 'Authorization: Bearer xxxxxx' --data-binary '{"containsFilter": true}'
     * @return array
     */
    public function settings(): array
    {
        $this->client->index($this->indexName)->updateFilterableAttributes($this->filterableAttributes);

        return $this->client->index($this->indexName)->updateSortableAttributes($this->sortableAttributes);
    }

    public function createApiKey(): Keys
    {
        return $this->client->createKey([
            'description' => 'indicateur ville API key',
            'actions' => ['*'],
            'indexes' => [$this->indexName],
            'expiresAt' => '2042-04-02T00:42:42Z',
        ]);
    }

    public function dump(): array
    {
        return $this->client->createDump();
    }

    public function addPost(array|\WP_Post|null $post): void
    {
        WpRepository::instance()->preparePost($post);
        $document = Document::documentFromPost($post, get_current_blog_id());
        $this->initClientAndIndex();
        $this->index->addDocuments([$document], $this->primaryKey);
    }


    public function deleteDocument(int $postId, string $type, int $siteId): void
    {
        $this->initClientAndIndex();
        $id = Document::createId($postId, $type, $siteId);
        $this->index->deleteDocument($id);
    }
}