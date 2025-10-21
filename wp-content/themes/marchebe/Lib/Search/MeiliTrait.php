<?php

namespace AcMarche\Theme\Lib\Search;

use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;

trait MeiliTrait
{
    public ?Client $client = null;
    public string $indexName;
    public string $masterKey;
    public ?Indexes $index = null;
    public string $primaryKey = 'id';
    private array $filterableAttributes = [];
    private array $sortableAttributes = [];

    public function initClientAndIndex(): void
    {
        if (!$this->client) {
            $this->client = new Client('http://127.0.0.1:7700', $this->masterKey);
        }

        if (!$this->index) {
            $this->index = $this->client->index($this->indexName);
        }
    }
}