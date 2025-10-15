<?php

namespace AcMarche\Theme\Command;

use AcMarche\Theme\Lib\Pivot\Enums\UrnEnum;
use AcMarche\Theme\Lib\Pivot\Parser\EventParser;
use AcMarche\Theme\Repository\PivotApi;
use AcMarche\Theme\Repository\PivotRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pivot:query',
    description: ' ',
)]
class PivotCommand extends Command
{
    private SymfonyStyle $io;

    protected function configure(): void
    {
        $this
            ->setDescription('fetch pivot data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->parseEvents();

        return Command::SUCCESS;
    }

    private function parseEvents(): void
    {
        $pivotApi = new PivotApi();
        $response = $pivotApi->query(2);
        $jsonContent = $response->getContent();

        $parser = new EventParser();
        $events = $parser->parseJsonFile($jsonContent);

        echo "Found ".count($events)." events with idTypeOffre = 9\n\n";

        if (!empty($events)) {
            $firstEvent = $events[0];
            $this->io->writeln("First event:");
            $this->io->writeln("Code: ".$firstEvent->codeCgt);
            $this->io->writeln("Name: ".$firstEvent->nom);
            $this->io->writeln("Type: ".$firstEvent->typeOffre->idTypeOffre);
            $this->io->writeln("Location: ".$firstEvent->adresse1->rue." ".$firstEvent->adresse1->numero);
            foreach ($firstEvent->spec as $spec) {
                if ($spec->urn === UrnEnum::DATE_OBJECT->value) {
                    $this->io->writeln("Spec: ".$spec->value);
                    foreach ($spec->spec as $childSpec) {
                        dump($childSpec['value']);
                    }
                }
            }
            echo "\n";
        }
    }

    private function allEvents(): void
    {
        $pivotRepository = new PivotRepository();
        try {
            $data = $pivotRepository->queryContent(3);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        $events = [];
        foreach ($data->offre as $offre) {
            if (str_contains($offre->codeCgt, "EVT-")) {
                // $this->io->writeln($offre->codeCgt);
                $events[] = $offre;
                if (count($events) > 10) {
                    break;
                }
            }
        }

        echo json_encode($events);
    }
}