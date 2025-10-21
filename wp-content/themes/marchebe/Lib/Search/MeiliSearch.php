<?php

namespace AcMarche\Theme\Lib\Search;

use Meilisearch\Search\SearchResult;

class MeiliSearch
{
    use MeiliTrait;

    public function __construct()
    {
        $this->indexName = $_ENV['MEILI_INDEX_NAME'] ?? null;
        $this->masterKey = $_ENV['MEILI_MASTER_KEY'] ?? null;
    }

    /**
     * https://www.meilisearch.com/docs/learn/fine_tuning_results/filtering
     * @param string $keyword
     * @return iterable|SearchResult
     */
    public function doSearch(string $keyword): iterable|SearchResult
    {
        $limit = 100;
        $this->initClientAndIndex();

        return $this->index->search($keyword, [
            'limit' => $limit,
        ]);
    }
}