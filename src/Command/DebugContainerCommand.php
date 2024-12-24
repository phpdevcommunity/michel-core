<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Command;

use PhpDevCommunity\Console\Command\CommandInterface;
use PhpDevCommunity\Console\InputInterface;
use PhpDevCommunity\Console\Output\ConsoleOutput;
use PhpDevCommunity\Console\OutputInterface;
use Psr\Container\ContainerInterface;

final class DebugContainerCommand implements CommandInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName(): string
    {
        return 'debug:container';
    }

    public function getDescription(): string
    {
        return 'List all service IDs registered in the container';
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

        $io->title('Registered Service IDs');

        $serviceIds = $this->container->get('michel.services_ids');
        natcasesort($serviceIds);

        $io->table(
            ['Service ID', 'Value'],
            array_map(function ($serviceId) {
                $value = null;
                if ($this->container->has($serviceId)) {
                    try {
                        $value = $this->variableToString($this->container->get($serviceId));
                    } catch (\Throwable $e) {
                        $value = $this->variableToString($e->getMessage());
                    }

                }
                return [$serviceId, $value];
            }, $serviceIds)
        );

        $io->writeln('');

    }

    private function variableToString($variable): string
    {
        if (is_object($variable)) {
            return 'Object: ' . get_class($variable);
        } elseif (is_array($variable)) {
            $variables = [];
            foreach ($variable as $item) {
                $variables[] = $this->variableToString($item);
            }
            return print_r($variables, true);
        } elseif (is_resource($variable)) {
            return (string)$variable;
        }

        return var_export($variable, true);
    }
}
