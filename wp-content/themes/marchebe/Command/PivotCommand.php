<?php

namespace AcMarche\Theme\Command;

use AcMarche\Theme\Lib\Cache;
use AcMarche\Theme\Lib\Pivot\Entity\Event;
use AcMarche\Theme\Lib\Pivot\Enums\ContentEnum;
use AcMarche\Theme\Lib\Pivot\Parser\EventParser;
use AcMarche\Theme\Lib\Pivot\Repository\PivotApi;
use AcMarche\Theme\Lib\Pivot\Repository\PivotRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pivot:query',
    description: ' ',
)]
class PivotCommand extends Command
{
    private SymfonyStyle $io;
    private OutputInterface $output;
    private bool $purge = false;
    private bool $parse;

    protected function configure(): void
    {
        $this->setDescription('fetch pivot data');
        $this->addOption('all', "all", InputOption::VALUE_NONE, 'Fetch all data');
        $this->addOption('parse', "parse", InputOption::VALUE_NONE, 'Parse data');
        $this->addOption('purge', "purge", InputOption::VALUE_NONE, 'Purge cache');
        $this->addOption('codeCgt', "codeCgt", InputOption::VALUE_REQUIRED, 'Dump one event');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        $all = (bool)$input->getOption('all');
        $this->parse = (bool)$input->getOption('parse');
        $this->purge = (bool)$input->getOption('purge');
        $codeCgt = (string)$input->getOption('codeCgt');

        if ($codeCgt) {
            $pivotRepository = new PivotRepository();
            try {
                $event = $pivotRepository->loadOneEvent($codeCgt, $this->parse, $this->purge);
                if ($event instanceof Event) {
                    $this->displayOffers([$event]);
                } else {
                    $this->io->write($event);
                }
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());

                return Command::FAILURE;
            }

            return Command::SUCCESS;
        }

        if ($all) {
            $this->allData();

            return Command::SUCCESS;
        }

        $this->io->section("Usage: php console pivot:query --purge --parse --all --codeCgt EVT-A0-00PI-0TLH");

        return Command::SUCCESS;
    }

    private function allData(): void
    {
        if ($this->purge) {
            Cache::delete('pivot_json_file');
        }
        $jsonContent = Cache::get('pivot_json_file', function () {
            $pivotApi = new PivotApi();
            $response = $pivotApi->query(ContentEnum::LVL3->value);

            return $response->getContent();
        });

        if (!$this->parse) {
            echo $jsonContent;

            return;
        }
        $this->parseEvents($jsonContent);
    }

    private function parseEvents(string $jsonContent): void
    {
        $parser = new EventParser();
        try {
            $events = $parser->parseJsonFile($jsonContent);
        } catch (\JsonException|\Throwable$e) {
            $this->io->error($e->getMessage());

            return;
        }

        $this->displayOffers($events);
    }

    private function displayOffers(array $offres): void
    {
        $this->io->section("Liste des évènements. ".count($offres));
        $rows = [];
        $table = new Table($this->output);
        foreach ($offres as $offer) {
            $item = [$offer->nom, $offer->codeCgt, $offer->dateModification];
            $rows[] = $item;
        }
        $table
            ->setHeaders(['Nom', 'CodeCgt', 'Modifié le'])
            ->setRows($rows)
            ->render();
    }
}