<?php

namespace Test\PhpDevCommunity\Michel\Core;

use PhpDevCommunity\Michel\Core\Middlewares\ControllerMiddleware;
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
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(function (string $key) {

                if ($key === ControllerMiddleware::CONTROLLER) {
                    return new SampleControllerTest([]);
                }

                if ($key === ControllerMiddleware::ACTION) {
                    return null;
                }

                throw new \LogicException();
            });

        $request
            ->method('getAttributes')
            ->willReturn([
                ControllerMiddleware::CONTROLLER => new SampleControllerTest([]),
                ControllerMiddleware::ACTION => null
            ]);

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
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(function (string $key)  use($controllerClassName, $controllerMethodName){

                if ($key === ControllerMiddleware::CONTROLLER) {
                    return $controllerClassName;
                }

                if ($key === ControllerMiddleware::ACTION) {
                    return $controllerMethodName;
                }

                throw new \LogicException();
            });

        $request
            ->method('getAttributes')
            ->willReturn([
                ControllerMiddleware::CONTROLLER => $controllerClassName,
                ControllerMiddleware::ACTION => $controllerMethodName
            ]);

        $container->expects($this->once())
            ->method('get')
            ->with($this->equalTo($controllerClassName))
            ->willReturn(new SampleControllerTest([]));

        return $controllerMiddleware->process($request, $this->createMock(RequestHandlerInterface::class));
    }
}
