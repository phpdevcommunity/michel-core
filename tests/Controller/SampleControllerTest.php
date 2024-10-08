<?php

namespace Test\PhpDevCommunity\Michel\Core\Controller;

use PhpDevCommunity\Michel\Core\Controller\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Test\PhpDevCommunity\Michel\Core\Response\ResponseTest;

class SampleControllerTest extends Controller
{
    public function __construct(array $middleware)
    {
        foreach ($middleware as $item) {
            $this->middleware($item);
        }
    }

    public function __invoke() :ResponseInterface
    {
        return new ResponseTest();
    }

    public function testGet(string $id)
    {
        return $this->get($id);
    }

    public function fakeMethod() :ResponseInterface
    {
        return new ResponseTest();
    }
}
