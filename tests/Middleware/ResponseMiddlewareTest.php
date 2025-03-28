<?php

namespace Test\PhpDevCommunity\Michel\Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Test\PhpDevCommunity\Michel\Core\Response\ResponseTest;

class ResponseMiddlewareTest implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new ResponseTest();
    }
}
