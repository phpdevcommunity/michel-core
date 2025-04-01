<?php

declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core\Middlewares;

use PhpDevCommunity\Michel\Core\Debug\DebugDataCollector;
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
            (new Option('debug'))->setDefaultValue(false)->validator(function ($value) {
                return is_bool($value);
            }),
            (new Option('profiler'))->setDefaultValue(false)->validator(function ($value) {
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
        $this->debug = $options['debug'];
        $this->profiler = $options['profiler'];
        $this->env = strtolower($options['env']);
        $this->logDir = rtrim($options['log_dir'], '/') . '/';
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->debug) {
            return $handler->handle($request);
        }

        $this->initializeDevelopmentEnvironment();

        $this->requestProfiler->start($request);

        $response = $handler->handle($request);
        /**
         * @var DebugDataCollector $debugCollector
         */
        $debugCollector = $request->getAttribute('debug_collector');
        $debugCollector->add('response_code', $response->getStatusCode());
        if ($debugCollector) {
            $this->requestProfiler->withDebugDataCollector($debugCollector);
        }
        $requestProfilerData = $this->requestProfiler->stop();
        if ($this->profiler) {
            if (strpos($response->getHeaderLine('Content-Type'), 'text/html') !== false) {
                $renderer = new PhpRenderer(dirname(__DIR__) . '/../resources/debug');
                $debugBarHtml = $renderer->render('debugbar.html.php', [
                    'profiler' => $requestProfilerData,
                ]);

                $body = $response->getBody();
                $content = (string)$body;
                $pos = strripos($content, '</body>');
                if ($pos !== false) {
                    $response = $response->withBody(response(substr($content, 0, $pos) .
                        $debugBarHtml .
                        substr($content, $pos))->getBody());
                }
            } elseif (strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
                $body = $response->getBody();
                $content = (string)$body;
                if (!empty(trim($content))) {
                    $json = json_decode($content, true);
                    if (is_array($json)) {
                        unset($requestProfilerData['http.request']);
                        $requestProfilerDataFlat = array_dot($requestProfilerData);
                        foreach ($requestProfilerDataFlat as $key => $value) {
                            $key = str_replace( '@', '', $key);
                            $key = str_replace( '__', '', $key);
                            $key = str_replace( '_', '-', $key);
                            $key = str_replace( '.', '-', $key);
                            $response = $response->withAddedHeader(sprintf('X-Debug-%s', $key), $value);
                        }
                    }
                }
            }
        }

        $this->log($requestProfilerData, 'debug.log');

        return $response;
    }

    private function initializeDevelopmentEnvironment(): void
    {
        $this->requestProfiler = new RequestProfiler([
            'environment' => $this->env,
            'php_version' => PHP_VERSION,
            'php_extensions' => implode(', ', get_loaded_extensions()),
            'php_sapi' => php_sapi_name(),
            'php_memory_limit' => ini_get('memory_limit'),
            'php_timezone' => date_default_timezone_get(),
        ]);
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

        file_put_contents(filepath_join($this->logDir, $logFile), json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND|LOCK_EX);
    }
}
