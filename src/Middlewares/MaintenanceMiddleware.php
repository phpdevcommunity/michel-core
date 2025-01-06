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
                $response->getBody()->write('
    <html>
        <head>
            <title>Maintenance Mode</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    background-color: #f8f9fa;
                    color: #333;
                    padding: 50px;
                }
                h1 {
                    color: #601D17FF;
                    font-size: 2.5em;
                }
                p {
                    font-size: 1.2em;
                    line-height: 1.5;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>We\'re Undergoing Maintenance</h1>
                <p>Our website is currently down for scheduled maintenance to improve your experience. We\'ll be back online shortly.</p>
                <p>Thank you for your patience.</p>
            </div>
        </body>
    </html>
');
            }
            return $response;
        }

        return $handler->handle($request);
    }

}
