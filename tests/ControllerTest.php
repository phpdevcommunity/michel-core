<?php

namespace Test\PhpDevCommunity\Michel\Core;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Test\PhpDevCommunity\Michel\Core\Controller\SampleControllerTest;

class ControllerTest extends TestCase
{
    public function testMiddleware()
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $controller = new SampleControllerTest([$middleware]);

        $middlewares = $controller->getMiddlewares();

        $this->assertInstanceOf(MiddlewareInterface::class, $middlewares[0]);
    }

    public function testInvalidMiddleware()
    {
        $this->expectException(\InvalidArgumentException::class);

        $invalidMiddleware = 'InvalidMiddlewareClass';
        new SampleControllerTest([$invalidMiddleware]);
    }

    public function testGet()
    {
        $controller = new SampleControllerTest([]);
        $container = $this->createMock(ContainerInterface::class);

        $controller->setContainer($container);

        $container->expects($this->once())
            ->method('get')
            ->with('service_id')
            ->willReturn('service_instance');

        $this->assertEquals('service_instance', $controller->testGet('service_id'));
    }
}
