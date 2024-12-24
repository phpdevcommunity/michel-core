<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Command;

use PhpDevCommunity\Console\Command\CommandInterface;
use PhpDevCommunity\Console\InputInterface;
use PhpDevCommunity\Console\Output\ConsoleOutput;
use PhpDevCommunity\Console\OutputInterface;

final class DebugEnvCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'debug:env';
    }

    public function getDescription(): string
    {
      return 'Lists all environment variables along with their corresponding values.';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getArguments(): array
    {
        return [];
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new ConsoleOutput($output);

        $io->title('Env Variables');

        $values = [];
        foreach ($_ENV as $key => $value) {
            $values[] = [$key, $value];
        }
        $io->table(
            ['Variable', 'Value'],
            $values
        );

        $io->writeln('');
        $io->writeln('Please note that actual values may vary between web and command-line interfaces.');
        $io->writeln('');
    }

}
