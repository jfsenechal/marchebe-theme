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
    private array $allposts = [];

    protected function configure(): void
    {
        $this->setDescription('To fix something');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        $this->allposts = $this->getAllPosts();
        $postsToDelete = [];

        foreach (Theme::SITES as $siteId => $nom) {
            switch_to_blog($siteId);

            $args = [
                'numberposts' => 5000,
                'post_status' => 'inherit',
                'post_type' => 'attachment',
            ];
            $query = new \WP_Query($args);

            while ($query->have_posts()) {
                $attachment = $query->next_post();
                if (!$this->check($attachment)) {
                    $postsToDelete[] = $attachment;
                }
            }
        }
        foreach ($postsToDelete as $post) {
            //  wp_delete_attachment($post, true);
        }
        $this->io->success(count($postsToDelete).' posts to delete');

        return Command::SUCCESS;
    }

    private function getAllPosts(): array
    {
        $allPosts = [];

        foreach (Theme::SITES as $siteId => $nom) {
            switch_to_blog($siteId);
            $allPosts = [...$allPosts, ...get_posts(['numberposts' => 10000])];
        }

        return $allPosts;
    }

    private function ancestor()
    {
        switch_to_blog(Theme::ENFANCE);
        $post = get_post(894);
        $wpRepository = new WpRepository();
        $paths = $wpRepository->getAncestorsOfPost($post->ID);
        dd($paths);
    }

    private function check(int|\WP_Post|null $attachment): bool
    {
        foreach ($this->allposts as $post) {
            if (str_contains($post->post_content, $attachment->guid)) {
                return true;
            }
        }

        return false;
    }

}