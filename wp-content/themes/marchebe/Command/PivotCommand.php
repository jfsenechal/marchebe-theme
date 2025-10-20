<?php

namespace AcMarche\Theme\Command;

use AcMarche\Theme\Lib\Cache;
use AcMarche\Theme\Lib\Pivot\Entity\Event;
use AcMarche\Theme\Lib\Pivot\Enums\ContentEnum;
use AcMarche\Theme\Lib\Pivot\Enums\UrnEnum;
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
    private bool $purge = false;
    private OutputInterface $output;

    protected function configure(): void
    {
        $this->setDescription('fetch pivot data');
        $this->addOption('all', "all", InputOption::VALUE_NONE, 'Fetch all');
        $this->addOption('parse', "parse", InputOption::VALUE_NONE, 'Parse data');
        $this->addOption('purge', "purge", InputOption::VALUE_NONE, 'Purge cache');
        $this->addOption('codeCgt', "codeCgt", InputOption::VALUE_REQUIRED, 'Dump one event');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        $all = (bool)$input->getOption('all');
        $parse = (bool)$input->getOption('parse');
        $this->purge = (bool)$input->getOption('purge');
        $codeCgt = (string)$input->getOption('codeCgt');

        if ($codeCgt) {
            $pivotRepository = new PivotRepository();
            try {
                $event = $pivotRepository->loadOneEvent($codeCgt, $parse, $this->purge);
                if ($event instanceof Event) {
                    $this->displayOffer($event);
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
            $this->allEvents();
        }

        if ($parse) {
            $this->parseEvents();
        }

        return Command::SUCCESS;
    }

    private function parseEvents(): void
    {
        if ($this->purge) {
            Cache::delete('pivot_json_file');
        }
        $jsonContent = Cache::get('pivot_json_file', function () {
            $pivotApi = new PivotApi();
            $response = $pivotApi->query(ContentEnum::LVL3->value);
            $jsonContent = $response->getContent();

            return $jsonContent;
        });

        $parser = new EventParser();
        $events = $parser->parseJsonFile($jsonContent);

        $this->io->writeln("Found ".count($events)." events with idTypeOffre = 9");

        if (!empty($events)) {
            $firstEvent = $events[0];
            $this->io->title("First event:");
            $this->io->writeln("Code: ".$firstEvent->codeCgt);
            $this->io->writeln("Name: ".$firstEvent->nom);
            $this->io->writeln("Type: ".$firstEvent->typeOffre->idTypeOffre);
            $this->io->writeln("Location: ".$firstEvent->adresse1?->rue." ".$firstEvent->adresse1?->numero);
            foreach ($firstEvent->spec as $spec) {
                if ($spec->urn === UrnEnum::DATE_OBJECT->value) {
                    $this->io->writeln("Spec: ".$spec->value);
                    foreach ($spec->spec as $childSpec) {
                        $this->io->writeln($childSpec['value']);
                    }
                }
            }
            foreach ($firstEvent->dates as $date) {
                $this->io->writeln("Date: ".$date->dateBegin->format('Y-m-d'));
            }
            dd($firstEvent->relOffre);
            $this->io->writeln('');
        }
    }

    private function allEvents(): void
    {
        $pivotRepository = new PivotRepository();
        try {
            $data = $pivotRepository->queryContent(ContentEnum::LVL3->value);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        $events = [];
        foreach ($data->offre as $offre) {
            if (str_contains($offre->codeCgt, "EVT-")) {
                // $this->io->writeln($offre->codeCgt);
                $events[] = $offre;
                if (count($events) > 4) {
                    break;
                }
            }
        }

        echo json_encode($events);
    }

    private function displayOffer(Event $offre): void
    {
        $this->io->section($offre->nom);
        $rows = [[$offre->nom, $offre->codeCgt,  $offre->dateModification]];

        $table = new Table($this->output);
        $table
            ->setHeaders(['Nom', 'CodeCgt', 'Modifié le'])
            ->setRows($rows);
        $table->render();

    }
}