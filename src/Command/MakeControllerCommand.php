<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeControllerCommand extends AbstractMakeCommand
{
    protected static $defaultName = 'make:controller';

    protected function configure()
    {
        $this
            ->setDescription('Generate a new controller')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the controller, ex : App\\Controller\\MainController');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $controllerName = $input->getArgument('name');

        $filename = $this->createClass($controllerName);
        $io->success("Class $controllerName created successfully at $filename.");
        return Command::SUCCESS;

    }

    protected function template(string $classNamespace, $curtClassName): string
    {
        return <<<PHP
<?php

namespace $classNamespace;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use PhpDevCommunity\Michel\Core\Controller\Controller;

final class $curtClassName extends Controller
{
    public function __invoke(ServerRequestInterface \$request): ResponseInterface
    {
        // TODO: Implement controller logic here
    }
}
PHP;
    }
}
