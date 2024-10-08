<?php

namespace Test\PhpDevCommunity\Michel\Core;

use PhpDevCommunity\Michel\Core\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testGetName(): void
    {
        $route = new Route('home', '/home', 'HomeController::index', ['GET']);
        $this->assertSame('home', $route->getName());
    }

    public function testGetPath(): void
    {
        $route = new Route('home', '/home', 'HomeController::index', ['GET']);
        $this->assertSame('/home', $route->getPath());
    }

    public function testGetHandler(): void
    {
        $handler = 'HomeController::index';
        $route = new Route('home', '/home', $handler, ['GET']);
        $this->assertSame($handler, $route->getHandler());
    }

    public function testGetMethods(): void
    {
        $methods = ['GET', 'POST'];
        $route = new Route('home', '/home', 'HomeController::index', $methods);
        $this->assertSame($methods, $route->getMethods());
    }

    public function testStaticGet(): void
    {
        $route = Route::get('home', '/home', 'HomeController::index');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['GET'], $route->getMethods());
    }

    public function testStaticPost(): void
    {
        $route = Route::post('home', '/home', 'HomeController::index');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['POST'], $route->getMethods());
    }

    public function testStaticPut(): void
    {
        $route = Route::put('home', '/home', 'HomeController::index');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['PUT'], $route->getMethods());
    }

    public function testStaticDelete(): void
    {
        $route = Route::delete('home', '/home', 'HomeController::index');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['DELETE'], $route->getMethods());
    }
}
