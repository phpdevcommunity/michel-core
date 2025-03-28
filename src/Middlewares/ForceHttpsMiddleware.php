<?php

namespace PhpDevCommunity\Michel\Core\Middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ForceHttpsMiddleware implements MiddlewareInterface
{
    private bool $forceHttps;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(bool $forceHttps, ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->forceHttps = $forceHttps;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->forceHttps === true && $request->getUri()->getScheme() !== 'https') {
            $httpsUrl = sprintf('https://%s%s', $request->getUri()->getAuthority(), $request->getUri()->getPath());
            $response = $this->responseFactory->createResponse(302);
            return $response->withHeader('Location', $httpsUrl);
        }
        return $handler->handle($request);
    }


}
