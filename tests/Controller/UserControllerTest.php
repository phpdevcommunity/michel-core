<?php

namespace Test\PhpDevCommunity\Michel\Core\Controller;

use PhpDevCommunity\Attribute\Route;
use PhpDevCommunity\Michel\Core\Controller\Controller;
use Psr\Http\Message\ResponseInterface;
use Test\PhpDevCommunity\Michel\Core\Response\ResponseTest;

class UserControllerTest extends Controller
{
    public function __construct(array $middleware)
    {
        foreach ($middleware as $item) {
            $this->middleware($item);
        }
    }

    #[Route('/users', name: 'users')]
    public function users() :ResponseInterface
    {
        return new ResponseTest();
    }
}