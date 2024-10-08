<?php

namespace PhpDevCommunity\Michel\Core\Middlewares;

use PhpDevCommunity\Michel\Core\Helper\IpHelper;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MaintenanceMiddleware implements MiddlewareInterface
{
    private bool $maintenanceMode;
    private ResponseFactoryInterface $responseFactory;

    private array $allowedIps;
    private ?\Closure $renderer;

    public function __construct(bool $maintenanceMode, ResponseFactoryInterface $responseFactory,array $allowedIps = [], \Closure $renderer = null)
    {
        $this->maintenanceMode = $maintenanceMode;
        $this->responseFactory = $responseFactory;
        $this->allowedIps = $allowedIps;
        $this->renderer = $renderer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->maintenanceMode === true && !in_array(IpHelper::getIpFromRequest($request), $this->allowedIps)) {
            $response = $this->responseFactory->createResponse(200);

            if ($this->renderer !== null) {
                $renderer = $this->renderer;
                $response->getBody()->write($renderer($request));
            }else{
                $response->getBody()->write('We are in maintenance');
            }
            return $response;
        }

        return $handler->handle($request);
    }

}
