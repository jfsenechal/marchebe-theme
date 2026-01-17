<?php

namespace AcMarche\Theme\Command;

use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Search\DataForSearch;
use AcMarche\Theme\Lib\Search\MeiliServer;
use AcMarche\Theme\Repository\AdlRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'meili:server',
    description: ' ',
)]
class MeiliCommand extends Command
{
    private DataForSearch $dataForSearch;
    private readonly MeiliServer $meiliServer;

    protected function configure(): void
    {
        $this->setDescription('Manage server meilisearch');
        $this->addOption('key', "key", InputOption::VALUE_NONE, 'Create a key');
        $this->addOption('tasks', "tasks", InputOption::VALUE_NONE, 'Display tasks');
        $this->addOption('reset', "reset", InputOption::VALUE_NONE, 'Search engine reset');
        $this->addOption('update', "update", InputOption::VALUE_NONE, 'Update data');
        $this->addOption('dump', "dump", InputOption::VALUE_NONE, 'migrate data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = (bool)$input->getOption('key');
        $tasks = (bool)$input->getOption('tasks');
        $reset = (bool)$input->getOption('reset');
        $update = (bool)$input->getOption('update');
        $dump = (bool)$input->getOption('dump');

        $this->meiliServer = new MeiliServer();
        $this->meiliServer->initClientAndIndex();

        if ($key) {
            dump($this->meiliServer->createApiKey());

            return Command::SUCCESS;
        }

        if ($tasks) {
            $this->tasks($output);

            return Command::SUCCESS;
        }

        if ($reset) {
            $result = $this->meiliServer->createIndex();
            dump($result);
            $result = $this->meiliServer->settings();
            dump($result);

            return Command::SUCCESS;
        }

        if ($update) {
            $this->dataForSearch = new DataForSearch();
            $this->indexPosts();
            $this->indexCategories();
            $this->indexBottin();
            $this->indexEnquetes();
            $this->indexPublications();
            $this->indexAdl();

            return Command::SUCCESS;
        }

        if ($dump) {
            dump($this->meiliServer->dump());
        }

        return Command::SUCCESS;
    }

    private function indexPosts(): void
    {
        $documents = [];
        foreach (Theme::SITES as $idSite => $nom) {
            switch_to_blog($idSite);
            $posts = $this->dataForSearch->getPosts($idSite);
            foreach ($posts as $document) {
                $documents[] = $document;
            }
            restore_current_blog();
        }

        $this->indexInBatches($documents);
    }

    private function indexCategories(): void
    {
        $documents = [];
        foreach (Theme::SITES as $idSite => $nom) {
            switch_to_blog($idSite);
            $categories = $this->dataForSearch->getCategoriesBySite($idSite);
            foreach ($categories as $document) {
                $documents[] = $document;
            }
            restore_current_blog();
        }
        $this->indexInBatches($documents);
    }

    private function indexBottin(): void
    {
        $documents = $this->dataForSearch->fiches();
        $categories = $this->dataForSearch->indexCategoriesBottin();
        $this->indexInBatches(array_merge($documents, $categories));
    }

    private function indexEnquetes(): void
    {
        $documents = [];
        switch_to_blog(Theme::ADMINISTRATION);
        foreach ($this->dataForSearch->getEnqueteDocuments() as $document) {
            $documents[] = $document;
        }
        restore_current_blog();
        $this->indexInBatches($documents);
    }

    private function indexPublications(): void
    {
        $documents = [];
        switch_to_blog(Theme::ADMINISTRATION);
        foreach ($this->dataForSearch->getAllPublications() as $document) {
            $documents[] = $document;
        }
        restore_current_blog();
        $this->indexInBatches($documents);
    }

    private function indexAdl(): void
    {
        $documents = [];
        $adlIndexer = new AdlRepository();
        foreach ($adlIndexer->getAllCategories() as $document) {
            $documents[] = $document;
        }

        foreach ($adlIndexer->getAllPosts() as $document) {
            $documents[] = $document;
        }
        $this->indexInBatches($documents);
    }

    private function indexInBatches(array $documents, int $batchSize = 1000): void
    {
        foreach (array_chunk($documents, $batchSize) as $batch) {
            $this->meiliServer->index->addDocuments($batch, $this->meiliServer->primaryKey);
        }
    }

    private function tasks(OutputInterface $output): void
    {
        $tasks = $this->meiliServer->client->getTasks();
        $data = [];
        foreach ($tasks->getResults() as $result) {
            $t = [$result['uid'], $result['status'], $result['type'], $result['startedAt']];
            $t['error'] = null;
            $t['url'] = null;
            if ($result['status'] == 'failed') {
                if (isset($result['error'])) {
                    $t['error'] = $result['error']['message'];
                    $t['link'] = $result['error']['link'];
                }
            }
            $data[] = $t;
        }
        $table = new Table($output);
        $table
            ->setHeaders(['Uid', 'status', 'Type', 'Date', 'Error', 'Url'])
            ->setRows($data);
        $table->render();
    }
}