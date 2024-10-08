<?php

declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Middlewares\Router;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PhpDevCommunity\Michel\Core\Middlewares\ControllerMiddleware;

/**
 * @author PhpDevCommunity Michel <michel@phpdevcommunity.com>
 */
final class AuraRouterMiddleware implements MiddlewareInterface
{
    /**
     * @var \Aura\Router\RouterContainer
     */
    private \Aura\Router\RouterContainer $routerContainer;
    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    public function __construct(\Aura\Router\RouterContainer $routerContainer, ResponseFactoryInterface $responseFactory)
    {
        $this->routerContainer = $routerContainer;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $matcher = $this->routerContainer->getMatcher();
        $route = $matcher->match($request);
        if ($route === false) {
            return $this->responseFactory->createResponse(404);
        }

        $controller = $route->handler;
        $attributes = array_merge([
            ControllerMiddleware::CONTROLLER => $controller[0],
            ControllerMiddleware::ACTION => $controller[1] ?? null,
            ControllerMiddleware::NAME => $route->name,
        ], $route->attributes);

        foreach ($attributes as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }
        return $handler->handle($request);
    }
}
