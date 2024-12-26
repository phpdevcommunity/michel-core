<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Command;

use PhpDevCommunity\Console\Command\CommandInterface;
use PhpDevCommunity\Console\InputInterface;
use PhpDevCommunity\Console\Output\ConsoleOutput;
use PhpDevCommunity\Console\OutputInterface;
use Psr\Container\ContainerInterface;

final class LogClearCommand implements CommandInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName(): string
    {
        return 'log:clear';
    }

    public function getDescription(): string
    {
        return 'Deletes all log files in the specified log directory.';
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

        $logDir = $this->container->get('michel.logs_dir');
        if (!is_writable($logDir)) {
            throw new \RuntimeException(sprintf('Unable to write in the "%s" directory.', $logDir));
        }

        $io->title(sprintf('Clearing log files in directory: %s', $logDir));

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($logDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        /**
         * @var \SplFileInfo $file
         */
        foreach ($files as $file) {
            if ($file->getFilename() === '.gitignore') {
                continue;
            }
            if ($file->isFile()) {
                if (!unlink($file->getPathname())) {
                    throw new \RuntimeException("Failed to unlink {$file->getPathname()} : " . var_export(error_get_last(), true));
                }
            }elseif ($file->isDir()) {
                if (!rmdir($file->getPathname())) {
                    throw new \RuntimeException("Failed to remove {$file->getPathname()} : " . var_export(error_get_last(), true));
                }
            }
        }

        $io->success('All log files have been successfully cleared.');
    }
}
