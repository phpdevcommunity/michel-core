<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Command;

use PhpDevCommunity\Console\Argument\CommandArgument;
use PhpDevCommunity\Console\Command\CommandInterface;
use PhpDevCommunity\Console\InputInterface;
use PhpDevCommunity\Console\Output\ConsoleOutput;
use PhpDevCommunity\Console\OutputInterface;

final class MakeCommandCommand extends AbstractMakeCommand implements CommandInterface
{

    public function getName(): string
    {
        return 'make:command';
    }

    public function getDescription(): string
    {
        return 'Generate a new command';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getArguments(): array
    {
       return [
           new CommandArgument("name", true, null, "The name of the command, ex : App\\Command\\CreateUserCommand")
       ];
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new ConsoleOutput($output);
        $commandName = $input->getArgumentValue('name');

        $filename = $this->createClass($commandName);
        $io->success("Class $commandName created successfully at $filename.");
    }

    protected function template(string $classNamespace, string $curtClassName): string
    {
        return <<<PHP
<?php

namespace $classNamespace;

use PhpDevCommunity\Console\Command\CommandInterface;
use PhpDevCommunity\Console\InputInterface;
use PhpDevCommunity\Console\Output\ConsoleOutput;
use PhpDevCommunity\Console\OutputInterface;

final class $curtClassName implements CommandInterface
{

    public function getName(): string
    {
        return 'my:command';
    }

    public function getDescription(): string
    {
        return 'Description of my command';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getArguments(): array
    {
       return [];
    }
    
    public function execute(InputInterface \$input, OutputInterface \$output): void
    {
        \$io = new ConsoleOutput(\$output);
        \$io->success("successfully message");
    }
}
PHP;

    }

}
