<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Command;

use PhpDevCommunity\Console\Command\CommandInterface;
use PhpDevCommunity\Console\InputInterface;
use PhpDevCommunity\Console\Output\ConsoleOutput;
use PhpDevCommunity\Console\OutputInterface;
use PhpDevCommunity\Route;
use Psr\Container\ContainerInterface;

final class DebugRouteCommand implements CommandInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName(): string
    {
        return 'debug:routes';
    }

    public function getDescription(): string
    {
        return 'List all registered routes in the application';
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

        $io->title('Registered Routes');

        /**
         * @var array<Route> $routes
         */
        $routes = $this->container->get('michel.routes');
        $formattedRoutes = array_map(function (Route $route) {
            return [
                'Name' => $route->getName(),
                'Method' => implode('|', $route->getMethods()),
                'Path' => $route->getPath(),
            ];
        }, $routes);

        uasort($formattedRoutes, function ($a, $b) {
            return strcasecmp($a['Name'], $b['Name']);
        });

        $io->table(
            ['Name', 'Method', 'Path'],
            $formattedRoutes
        );
        $io->writeln('');
    }
}
