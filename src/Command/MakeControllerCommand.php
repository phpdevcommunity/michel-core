<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Command;

use PhpDevCommunity\Console\Argument\CommandArgument;
use PhpDevCommunity\Console\Command\CommandInterface;
use PhpDevCommunity\Console\InputInterface;
use PhpDevCommunity\Console\Output\ConsoleOutput;
use PhpDevCommunity\Console\OutputInterface;

final class MakeControllerCommand extends AbstractMakeCommand implements CommandInterface
{

    public function getName(): string
    {
        return 'make:controller';
    }

    public function getDescription(): string
    {
        return 'Generate a new controller';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getArguments(): array
    {
        return [
            new CommandArgument("name", true, null, "The name of the controller, ex : App\\Controller\\MainController")
        ];
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new ConsoleOutput($output);
        $controllerName = $input->getArgumentValue('name');

        $filename = $this->createClass($controllerName);
        $io->success("Class $controllerName created successfully at $filename.");

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
