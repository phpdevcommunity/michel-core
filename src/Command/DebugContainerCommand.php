<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugContainerCommand extends Command
{
    protected static $defaultName = 'debug:container';
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('List all service IDs registered in the container');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Registered Service IDs');

        $serviceIds = $this->container->get('michel.services_ids');
        natcasesort($serviceIds);

        $io->table(
            ['Service ID', 'Value'],
            array_map(function ($serviceId) {
                $value = null;
                if ($this->container->has($serviceId)) {
                    $value = $this->variableToString($this->container->get($serviceId));
                }
                return [$serviceId, $value];
            }, $serviceIds)
        );

        return Command::SUCCESS;
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
