<?php

namespace App\Command;

use App\Entity\Character;
use App\Service\WowService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:wow:characters',
    description: 'Add a short description for your command',
)]
class WowCharactersCommand extends Command
{

    public function __construct(
        private readonly WowService $wowService,
        private readonly array $characters,

    ) {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->characters as $c) {
            $character = $this->wowService->getCharacter($c['realm'], $c['name']);
            $medias = $this->wowService->getMedias($character);
            dump($character, $medias);
        }

        return Command::SUCCESS;
    }
}
