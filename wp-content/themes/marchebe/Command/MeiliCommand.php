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
       /*     $this->indexPosts();
            $this->indexCategories();
            $this->indexBottin();
            $this->indexEnquetes();*/
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
            $documents = $this->dataForSearch->getPosts($idSite);
            foreach ($documents as $document) {
                $documents[] = $document;
            }
        }

        $this->meiliServer->index->addDocuments($documents, $this->meiliServer->primaryKey);
    }

    private function indexCategories(): void
    {
        $documents = [];
        foreach (Theme::SITES as $idSite => $nom) {
            switch_to_blog($idSite);
            $categories = $this->dataForSearch->getCategoriesBySite($idSite);
            foreach ($categories as $document) {
                $document->id = 'category-'.$document->id.'-'.$idSite;
                $documents[] = $document;
            }
        }
        $this->meiliServer->index->addDocuments($documents, $this->meiliServer->primaryKey);
    }

    private function indexBottin(): void
    {
        $documents = $this->dataForSearch->fiches();
        $this->meiliServer->index->addDocuments($documents, $this->meiliServer->primaryKey);
        $documents = $this->dataForSearch->indexCategoriesBottin();
        $this->meiliServer->index->addDocuments($documents, $this->meiliServer->primaryKey);
    }

    private function indexEnquetes(): void
    {
        $documents = [];
        switch_to_blog(Theme::ADMINISTRATION);
        foreach ($this->dataForSearch->getEnqueteDocuments() as $documentElastic) {
            $documentElastic->id = 'enquete_'.$documentElastic->id;
            $documents[] = $documentElastic;
        }
        $this->meiliServer->index->addDocuments($documents, $this->meiliServer->primaryKey);
    }

    private function indexAdl(): void
    {
        $documents = [];
        $adlIndexer = new AdlRepository();
        foreach ($adlIndexer->getAllCategories() as $documentElastic) {
            $documentElastic->id = 'adl_cat_'.$documentElastic->id;
            $documents[] = $documentElastic;
        }

        foreach ($adlIndexer->getAllPosts() as $documentElastic) {
            $documentElastic->id = 'adl_post_'.$documentElastic->id;
            $documents[] = $documentElastic;
        }
        $this->meiliServer->index->addDocuments($documents, $this->meiliServer->primaryKey);
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