<?php

namespace Test\PhpDevCommunity\Michel\Core;

use PhpDevCommunity\Michel\Core\App;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppTest extends TestCase
{

    protected function setUp(): void {
        App::initWithPath(__DIR__.'/config/framework.php');
    }
    public function testInitWithValidPath()
    {
        $path = 'path/to/your/options.php';
        $this->expectException(\InvalidArgumentException::class);
        App::initWithPath($path);
    }

    public function testCreateServerRequest()
    {
        $request = App::createServerRequest();
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    public function testGetResponseFactory()
    {
        $responseFactory = App::getResponseFactory();
        $this->assertInstanceOf(ResponseFactoryInterface::class, $responseFactory);
    }

    public function testCreateContainer()
    {
        $definitions = []; // Your container definitions
        $options = []; // Your container options

        $container = App::createContainer($definitions, $options);
        $this->assertInstanceOf(ContainerInterface::class, $container);

        $container = App::getContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);
    }
    public function testGetCustomEnvironments()
    {
        $environments = App::getCustomEnvironments();
        $this->assertIsArray($environments);
        foreach ($environments as $environment) {
            $this->assertIsString($environment);
        }
    }
}
