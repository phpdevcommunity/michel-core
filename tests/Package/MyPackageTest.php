<?php

namespace Test\PhpDevCommunity\Michel\Core\Package;

use PhpDevCommunity\Michel\Core\Command\CacheClearCommand;
use PhpDevCommunity\Michel\Core\Command\MakeCommandCommand;
use PhpDevCommunity\Michel\Core\Package\PackageInterface;
use PhpDevCommunity\Route;
use PhpDevCommunity\RouterInterface;
use Psr\Container\ContainerInterface;

class MyPackageTest implements PackageInterface
{
    public function getDefinitions(): array
    {
        return [
            RouterInterface::class => static function (ContainerInterface $container) {
                return new \stdClass();
            },
            'render' => static function (ContainerInterface $container) {
                return new \stdClass();
            },
        ];
    }

    public function getParameters(): array
    {
        return [
            'app.url' => 'https://example.com',
            'app.locale' => 'en',
            'app.template_dir' => '/path/to/templates',
        ];
    }

    public function getRoutes(): array
    {
        return [
            new Route('example', '/example', function () {}),
            new Route('another', '/another', function () {}),
        ];
    }

    public function getListeners(): array
    {
        return [
            'App\\Event\\ExampleEvent' => \stdClass::class,
            'App\\Event\\AnotherEvent' => \stdClass::class,
        ];
    }

    public function getCommands(): array
    {
        return [
            CacheClearCommand::class,
            MakeCommandCommand::class,
        ];
    }
}
