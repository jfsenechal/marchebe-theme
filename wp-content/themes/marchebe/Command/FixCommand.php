<?php

namespace AcMarche\Theme\Command;

use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Repository\WpRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'marche:fix',
    description: ' ',
)]
class FixCommand extends Command
{
    private SymfonyStyle $io;
    private OutputInterface $output;

    protected function configure(): void
    {
        $this->setDescription('To fix something');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        switch_to_blog(Theme::ENFANCE);
        $post = get_post(894);
        $wpRepository = new WpRepository();
        $paths = $wpRepository->getAncestorsOfPost($post->ID);
        dd($paths);


        return Command::SUCCESS;
    }

}