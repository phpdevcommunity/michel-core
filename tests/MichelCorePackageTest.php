<?php

namespace Test\PhpDevCommunity\Michel\Core;

use PhpDevCommunity\Michel\Core\ErrorHandler\ExceptionHandler;
use PhpDevCommunity\Michel\Core\Middlewares\RouterMiddleware;
use PhpDevCommunity\Michel\Core\Package\MichelCorePackage;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Application;

class MichelCorePackageTest extends TestCase
{
    public function testGetDefinitions()
    {
        $package = new MichelCorePackage();

        $definitions = $package->getDefinitions();
        $this->assertNotEmpty($definitions);

        $this->assertArrayHasKey(EventDispatcherInterface::class, $definitions);
        $this->assertTrue(is_callable($definitions[EventDispatcherInterface::class]));

        $this->assertArrayHasKey('router', $definitions);
        $this->assertTrue(is_callable($definitions['router']));

        $this->assertArrayHasKey('render', $definitions);
        $this->assertTrue(is_callable($definitions['render']));

        $this->assertArrayHasKey(Application::class, $definitions);
        $this->assertTrue(is_callable($definitions[Application::class]));

        $this->assertArrayHasKey(RouterMiddleware::class, $definitions);
        $this->assertTrue(is_callable($definitions[RouterMiddleware::class]));

        $this->assertArrayHasKey(ExceptionHandler::class, $definitions);
        $this->assertTrue(is_callable($definitions[ExceptionHandler::class]));
    }

    public function testGetParameters()
    {
        $package = new MichelCorePackage();

        $parameters = $package->getParameters();

        $this->assertNotEmpty($parameters);
        $this->assertArrayHasKey('app.url', $parameters);
        $this->assertArrayHasKey('app.locale', $parameters);
        $this->assertArrayHasKey('app.template_dir', $parameters);
    }

    public function testGetRoutes()
    {
        $package = new MichelCorePackage();
        $routes = $package->getRoutes();
        $this->assertEmpty($routes);
    }

    public function testGetListeners()
    {
        $package = new MichelCorePackage();
        $listeners = $package->getListeners();
        $this->assertEmpty($listeners);
    }

    public function testGetCommands()
    {
        $package = new MichelCorePackage();
        $commands = $package->getCommands();
        $this->assertNotEmpty($commands);
        foreach ($commands as $command) {
            $this->assertIsString($command);
        }
    }
}
