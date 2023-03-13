<?php

namespace App\Command;

use App\Service\WowService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:wow:keys',
    description: 'Add a short description for your command',
)]
class WowKeysCommand extends Command
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

        $dungeons = $this->wowService->getDungeons();

        foreach ($this->characters as $c) {
            $character = $this->wowService->getCharacter($c['realm'], $c['name']);
            $this->wowService->getKeys($character);
            dump($character);

        }

        return Command::SUCCESS;
    }
}
