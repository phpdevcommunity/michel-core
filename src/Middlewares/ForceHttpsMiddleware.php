<?php

namespace PhpDevCommunity\Michel\Core\Middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ForceHttpsMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getScheme() !== 'https') {
            $httpsUrl = sprintf('https://%s%s', $request->getUri()->getAuthority(), $request->getUri()->getPath());
            $response = $this->responseFactory->createResponse(302);
            return $response->withHeader('Location', $httpsUrl);
        }
        return $handler->handle($request);
    }
}
