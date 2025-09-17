<?php

use Composer\Autoload\ClassLoader;
use PhpDevCommunity\Michel\Core\App;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;


if (!function_exists('michel_composer_loader')) {

    /**
     * Returns the instance of the Composer class loader.
     *
     * @return ClassLoader
     * @throws LogicException If the MICHEL_COMPOSER_AUTOLOAD_FILE constant is not defined.
     */
    function michel_composer_loader(): ClassLoader
    {
        if (!defined('MICHEL_COMPOSER_AUTOLOAD_FILE')) {
            throw new LogicException('MICHEL_COMPOSER_AUTOLOAD_FILE const must be defined!');
        }
        return require MICHEL_COMPOSER_AUTOLOAD_FILE;
    }
}

if (!function_exists('send_http_response')) {

    /**
     * Sends the HTTP response to the client.
     *
     * @param ResponseInterface $response The HTTP response to send.
     */
    function send_http_response(ResponseInterface $response)
    {
        $httpLine = sprintf('HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        if (!headers_sent()) {
            header($httpLine, true, $response->getStatusCode());

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header("$name: $value", false);
                }
            }
        }

        echo $response->getBody();
    }
}

if (!function_exists('container')) {

    /**
     * Retrieves the application's dependency injection container.
     *
     * @return ContainerInterface The dependency injection container.
     */
    function container(): ContainerInterface
    {
        return App::getContainer();
    }
}

if (!function_exists('create_request')) {

    /**
     * Creates a new HTTP request.
     *
     * @return ServerRequestInterface The HTTP response.
     */
    function create_request(): ServerRequestInterface
    {
        return App::createServerRequest();
    }
}

if (!function_exists('request_factory')) {

    /**
     * Creates a new HTTP request.
     *
     * @return ServerRequestFactoryInterface The HTTP response.
     */
    function request_factory(): ServerRequestFactoryInterface
    {
        return App::getServerRequestFactory();
    }
}

if (!function_exists('response_factory')) {

    /**
     * Retrieves the response factory.
     *
     * @return ResponseFactoryInterface The response factory.
     */
    function response_factory(): ResponseFactoryInterface
    {
        return App::getResponseFactory();
    }
}

if (!function_exists('response')) {

    /**
     * Creates a new HTTP response.
     *
     * @param string $content The response content.
     * @param int $status The HTTP status code.
     * @return ResponseInterface The HTTP response.
     */
    function response(string $content = '', int $status = 200, $contentType = 'text/html'): ResponseInterface
    {
        $response = response_factory()->createResponse($status);
        $response->getBody()->write($content);
        return $response->withHeader('Content-Type', $contentType);
    }
}

if (!function_exists('json_response')) {

    /**
     * Creates a new JSON response.
     *
     * @param array|JsonSerializable $data The data to encode to JSON.
     * @param int $status The HTTP status code.
     * @param int $flags JSON encoding flags.
     * @return ResponseInterface The JSON response.
     * @throws InvalidArgumentException If JSON encoding fails.
     */
    function json_response($data, int $status = 200, int $flags = JSON_HEX_TAG
    | JSON_HEX_APOS
    | JSON_HEX_AMP
    | JSON_HEX_QUOT
    | JSON_UNESCAPED_SLASHES): ResponseInterface
    {
        if (!is_array($data) && !is_subclass_of($data, JsonSerializable::class)) {
            throw new InvalidArgumentException(
                'Data must be an array or implement JsonSerializable interface'
            );
        }
        $response = response_factory()->createResponse($status);
        $response->getBody()->write(json_encode($data, $flags));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                sprintf('Unable to encode data to JSON: %s', json_last_error_msg())
            );
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}

if (!function_exists('redirect')) {

    /**
     * Creates a redirect response.
     *
     * @param string $url The URL to redirect to.
     * @param int $status The HTTP status code.
     * @return ResponseInterface The redirect response.
     */
    function redirect(string $url, int $status = 302): ResponseInterface
    {
        $response = response_factory()->createResponse($status);
        return $response->withHeader('Location', $url);
    }
}

if (!function_exists('render_view')) {

    /**
     * Renders a view template with the provided context.
     *
     * @param string $view The name of the view template.
     * @param array $context The context data to pass to the view.
     * @return string The rendered view.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function render_view(string $view, array $context = []): string
    {
        if (!container()->has('render')) {
            throw new LogicException('The "render_view" method requires a Renderer to be available. You can choose between installing "phpdevcommunity/php-renderer" or "twig/twig" depending on your preference.');
        }

        $renderer = container()->get('render');
        return $renderer->render($view, $context);
    }
}

if (!function_exists('render')) {

    /**
     * Renders a view template and creates an HTTP response.
     *
     * @param string $view The name of the view template.
     * @param array $context The context data to pass to the view.
     * @param int $status The HTTP status code.
     * @return ResponseInterface The HTTP response with the rendered view.
     */
    function render(string $view, array $context = [], int $status = 200): ResponseInterface
    {
        return response(render_view($view, $context), $status);
    }
}
