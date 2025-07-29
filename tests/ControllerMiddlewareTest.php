<?php

namespace Test\PhpDevCommunity\Michel\Core;

use PhpDevCommunity\Michel\Core\Middlewares\ControllerMiddleware;
use PhpDevCommunity\Route;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Test\PhpDevCommunity\Michel\Core\Controller\SampleControllerTest;

class ControllerMiddlewareTest extends TestCase
{
    public function testProcessWithCallableController()
    {
        $container = $this->createMock(ContainerInterface::class);
        $controllerMiddleware = new ControllerMiddleware($container);

        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->method('getAttribute')
            ->willReturn(new Route('example', '/example', [new SampleControllerTest([])]));

        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $controllerMiddleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessWithControllerMethod()
    {
        $response = $this->testProcessWithController('fakeMethod');
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessWithControllerMethodNotExist()
    {
        $this->expectException(\BadMethodCallException::class);
        $response = $this->testProcessWithController('fakeMethodNotExist');
    }

    private function testProcessWithController(string $controllerMethodName): ResponseInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $controllerMiddleware = new ControllerMiddleware($container);

        $request = $this->createMock(ServerRequestInterface::class);

        $controllerClassName = SampleControllerTest::class;

        $request
            ->method('getAttribute')
            ->willReturn(new Route('example', '/example', [$controllerClassName, $controllerMethodName]));

        $container->expects($this->once())
            ->method('get')
            ->with($this->equalTo($controllerClassName))
            ->willReturn(new SampleControllerTest([]));

        return $controllerMiddleware->process($request, $this->createMock(RequestHandlerInterface::class));
    }
}
