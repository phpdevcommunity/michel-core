<?php

namespace Test\PhpDevCommunity\Michel\Core;

use PhpDevCommunity\Michel\Core\Handler\RequestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Test\PhpDevCommunity\Michel\Core\Middleware\ResponseMiddlewareTest;

class RequestHandlerTest extends TestCase
{
    public function testResponseOk()
    {
        /**
         * @var ContainerInterface $container
         * @var ServerRequestInterface $request
         */
        $container = $this->createMock(ContainerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = new RequestHandler($container, [new ResponseMiddlewareTest()]);
        $this->assertEquals(200, $handler->handle($request)->getStatusCode());
    }

    public function testInvalidMiddleware()
    {
        /**
         * @var ContainerInterface $container
         * @var ServerRequestInterface $request
         */
        $container = $this->createMock(ContainerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = new RequestHandler($container, [new \stdClass()]);
        $this->expectException(\LogicException::class);
        $handler->handle($request);
    }

    public function testThenArgument()
    {
        /**
         * @var ContainerInterface $container
         * @var ServerRequestInterface $request
         */
        $container = $this->createMock(ContainerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = new RequestHandler($container, [],   function (ServerRequestInterface $request) {
            return $this->createMock(ResponseInterface::class);
        });
        $response = $handler->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
