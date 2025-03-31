<?php

declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Middlewares;

use PhpDevCommunity\Michel\Core\Debug\RequestProfiler;
use PhpDevCommunity\Michel\Core\ErrorHandler\ErrorHandler;
use PhpDevCommunity\Resolver\Option;
use PhpDevCommunity\Resolver\OptionsResolver;
use PhpDevCommunity\TemplateBridge\Renderer\PhpRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DebugMiddleware implements MiddlewareInterface
{
    protected ?RequestProfiler $requestProfiler = null;
    private bool $debug = false;
    private bool $profiler = false;
    private string $env;
    private ?string $logDir = null;

    public function __construct(array $options = [])
    {
        $optionResolver = new OptionsResolver([
            (new Option('debug'))->setDefaultValue(false)->validator( function ($value) {
                return is_bool($value);
            }),
            (new Option('profiler'))->setDefaultValue(false)->validator( function ($value) {
                return is_bool($value);
            }),
            (new Option('env'))->setDefaultValue('env')->validator(function ($value) {
                return is_string($value);
            }),
            (new Option('log_dir'))->setDefaultValue(null)->validator(function ($value) {
                return file_exists($value);
            }),
        ]);

        $options = $optionResolver->resolve($options);
        $this->requestProfiler = new RequestProfiler();
        $this->debug = $options['debug'];
        $this->profiler = $options['profiler'];
        $this->env = strtolower($options['env']);
        $this->logDir = rtrim($options['log_dir'], '/') . '/';
        $this->initializeDevelopmentEnvironment();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->debug) {
            return $handler->handle($request);
        }

        $this->requestProfiler->start($request);

        $response = $handler->handle($request);
        $requestProfiler = $this->requestProfiler->stop();
        if ($this->profiler) {
            if (strpos($response->getHeaderLine('Content-Type'), 'text/html') !== false) {
                $renderer = new PhpRenderer(dirname(__DIR__).'/../resources/debug');
                $debugBarHtml = $renderer->render('debugbar.html.php', [
                    'profiler' => $requestProfiler,
                ]);

                $body = $response->getBody();
                $content = (string)$body;
                $pos = strripos($content, '</body>');
                if ($pos !== false) {
                    $response = $response->withBody(response(substr($content, 0, $pos) .
                        $debugBarHtml .
                        substr($content, $pos))->getBody());
                }
            }elseif (strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
                $body = $response->getBody();
                $content = (string)$body;
                $json = json_decode($content, true);
                if (is_array($json)) {
                    unset($requestProfiler['http.request']);
                    $json['__profiler'] = $requestProfiler;
                    $response = $response->withBody(json_response($json)->getBody());
                }
            }
        }

        $this->log($requestProfiler, 'debug.log');

        return $response;
    }

    private function initializeDevelopmentEnvironment(): void
    {

        $this->requestProfiler = new RequestProfiler([
            'environment' => $this->env
        ]);
        ErrorHandler::register();
    }

    final protected function log(array $data, string $logFile = null): void
    {
        if ($this->logDir === null) {
            return;
        }

        if (!is_dir($this->logDir)) {
            @mkdir($this->logDir, 0777, true);
        }
        if ($logFile === null) {
            $logFile = $this->env . '.log';
        }

        error_log(
            json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . PHP_EOL,
            3,
            filepath_join($this->logDir, $logFile)
        );
    }
}
