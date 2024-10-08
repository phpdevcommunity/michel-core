<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugEnvCommand extends Command
{
    protected static $defaultName = 'debug:env';

    protected function configure()
    {
        $this->setDescription('Lists all environment variables along with their corresponding values.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Env Variables');

        $values = [];
        foreach ($_ENV as $key => $value) {
            $values[] = [$key, $value];
        }
        $io->table(
            ['Variable', 'Value'],
            $values
        );

        $io->comment('Please note that actual values may vary between web and command-line interfaces.');
        return Command::SUCCESS;
    }
}
