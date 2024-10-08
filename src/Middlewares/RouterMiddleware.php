<?php

declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Middlewares;

use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PhpDevCommunity\Michel\Core\Http\Exception\MethodNotAllowedException;
use PhpDevCommunity\Michel\Core\Http\Exception\NotFoundException;
use PhpDevCommunity\Michel\Core\Middlewares\Router\AuraRouterMiddleware;
use PhpDevCommunity\Michel\Core\Middlewares\Router\SymfonyRouterMiddleware;
use PhpDevCommunity\Router;
use Aura\Router\RouterContainer;
use Symfony\Component\Routing\RouteCollection;
use function array_values;
use function implode;
use function is_a;
use function sprintf;

/**
 * @author PhpDevCommunity Michel <michel@phpdevcommunity.com>
 */
final class RouterMiddleware implements MiddlewareInterface
{
    public const ROUTERS = [
        'dev_coder' => 'composer require phpdevcommunity/php-router',
        'aura' => 'composer require aura/router',
        'symfony' => 'composer require symfony/routing'
    ];

    private object $router;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(object $router, ResponseFactoryInterface $responseFactory)
    {
        $this->router = $router;
        $this->responseFactory = $responseFactory;
    }

    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler): ResponseInterface
    {

        $response = null;
        if (is_a($this->router, Router::class)) {
            $response = (new \PhpDevCommunity\RouterMiddleware($this->router, $this->responseFactory))
                ->process($request, $handler);
        } elseif (is_a($this->router, RouterContainer::class)) {
            $response = (new AuraRouterMiddleware($this->router, $this->responseFactory))
                ->process($request, $handler);
        } elseif (is_a($this->router, RouteCollection::class)) {
            $response = (new SymfonyRouterMiddleware($this->router, $this->responseFactory))
                ->process($request, $handler);
        }

        if ($response instanceof ResponseInterface) {
            if ($response->getStatusCode() === 404) {
                throw new NotFoundException();
            }
            if ($response->getStatusCode() === 405) {
                throw new MethodNotAllowedException();
            }
            return $response;
        }

        throw new LogicException(
            sprintf(
                'You cannot use "Michel Framework" as router is not installed. Try running %s.',
                implode(' OR ', array_values(self::ROUTERS))
            )
        );
    }
}
