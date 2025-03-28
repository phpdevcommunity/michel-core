<?php

declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Controller;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @author PhpDevCommunity Michel <michel@phpdevcommunity.com>
 */
abstract class Controller
{
    private ContainerInterface $container;
    protected array $middlewares = [];

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @return array<MiddlewareInterface, string>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /***
     * @param $middleware MiddlewareInterface|string
     * @return void
     */
    protected function middleware($middleware): void
    {
        if (!$middleware instanceof MiddlewareInterface && (!is_string($middleware) || !class_exists($middleware))) {
            throw new InvalidArgumentException('The Middleware must be Class name or an instance of Psr\Http\Message\ResponseInterface.');
        }
        $this->middlewares[] = $middleware;
    }

    protected function get(string $id)
    {
        return $this->container->get($id);
    }
}
