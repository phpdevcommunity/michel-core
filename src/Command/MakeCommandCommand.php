<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeCommandCommand extends AbstractMakeCommand
{
    protected static $defaultName = 'make:command';

    protected function configure()
    {
        $this
            ->setDescription('Generate a new command')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the command, ex : App\\Command\\CreateUserCommand');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $commandName = $input->getArgument('name');

        $filename = $this->createClass($commandName);
        $io->success("Class $commandName created successfully at $filename.");
        return Command::SUCCESS;

    }

    protected function template(string $classNamespace, string $curtClassName): string
    {
        return <<<PHP
<?php

namespace $classNamespace;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class $curtClassName extends Command
{

    protected static \$defaultName = 'my:command';
    
    protected function configure()
    {
        \$this->setDescription('Description of my command');
    }
    
    protected function execute(InputInterface \$input, OutputInterface \$output)
    {
        \$io = new SymfonyStyle(\$input, \$output);
        \$io->success("successfully message");
        return Command::SUCCESS;
    }
}
PHP;

    }
}
