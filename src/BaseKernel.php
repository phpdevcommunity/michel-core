<?php
declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core;

use DateTimeImmutable;
use PhpDevCommunity\Attribute\AttributeRouteCollector;
use PhpDevCommunity\DotEnv;
use PhpDevCommunity\Michel\Core\Debug\DebugDataCollector;
use PhpDevCommunity\Michel\Core\Debug\ExecutionProfiler;
use PhpDevCommunity\Michel\Core\Debug\RequestProfiler;
use PhpDevCommunity\Michel\Core\ErrorHandler\ErrorHandler;
use PhpDevCommunity\Michel\Core\ErrorHandler\ExceptionHandler;
use PhpDevCommunity\Michel\Core\Handler\RequestHandler;
use PhpDevCommunity\Michel\Core\Http\Exception\HttpExceptionInterface;
use InvalidArgumentException;
use PhpDevCommunity\Michel\Core\Routing\ControllerFinder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Throwable;
use function array_filter;
use function array_keys;
use function array_merge;
use function date_default_timezone_set;
use function error_reporting;
use function getenv;
use function implode;
use function in_array;
use function json_encode;
use function sprintf;

/**
 * @package    PhpDevCommunity Michel
 * @author    PhpDevCommunity <michel@phpdevcommunity.com>
 * @license    https://opensource.org/license/mpl-2-0 Mozilla Public License v2.0
 * @link    https://www.phpdevcommunity.com
 */
abstract class BaseKernel
{
    private const DEFAULT_ENV = 'prod';
    public const VERSION = '1.0.0-alpha';
    public const NAME = 'MICHEL';
    private const DEFAULT_ENVIRONMENTS = [
        'dev',
        'prod'
    ];
    private string $env = self::DEFAULT_ENV;
    private bool $debug = false;

    protected ContainerInterface $container;
    /**
     * @var array<MiddlewareInterface>|array<string>
     */
    private array $middlewareCollection = [];
    private ?DebugDataCollector $debugDataCollector = null;

    /**
     * BaseKernel constructor.
     */
    public function __construct()
    {
        App::init($this->loadConfigurationIfExists('framework.php'));
        $this->boot();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Throwable
     */
    final public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $request = $request->withAttribute('request_id', strtoupper(uniqid('REQ')));
            $request = $request->withAttribute('debug_collector', $this->debugDataCollector);

            $requestHandler = new RequestHandler($this->container, $this->middlewareCollection);
            return $requestHandler->handle($request);
        } catch (Throwable $exception) {
            if (!$exception instanceof HttpExceptionInterface) {
                $this->logException($exception, $request);
            }

            $exceptionHandler = $this->container->get(ExceptionHandler::class);
            return $exceptionHandler->render($request, $exception);
        }
    }

    final public function getEnv(): string
    {
        return $this->env;
    }

    final public function isDebug(): bool
    {
        return $this->debug;
    }

    final public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    abstract public function getProjectDir(): string;

    abstract public function getCacheDir(): string;

    abstract public function getLogDir(): string;

    abstract public function getConfigDir(): string;

    abstract public function getPublicDir(): string;

    abstract public function getEnvFile(): string;

    abstract protected function afterBoot(): void;

    protected function loadContainer(array $definitions): ContainerInterface
    {
        return App::createContainer($definitions, ['cache_dir' => $this->getCacheDir()]);
    }

    final protected function logException(Throwable $exception, ServerRequestInterface  $request): void
    {
        $this->log([
            '@timestamp' => (new DateTimeImmutable())->format('c'),
            'log.level' => 'error',
            'id' => $request->getAttribute('request_id'),
            'http.request' => [
                'method' => $request->getMethod(),
                'url' => $request->getUri()->__toString(),
            ],
            'message' => $exception->getMessage(),
            'error' => [
                'code' => $exception->getCode(),
                'stack_trace' => $exception->getTrace(),
                'class' => get_class($exception),
            ],
            'source' => [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
        ]);
    }

    final protected function log(array $data, string $logFile = null): void
    {
        $logDir = $this->getLogDir();
        if (empty($logDir)) {
            throw new InvalidArgumentException('The log dir is empty, please set it in the Kernel.');
        }

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        if ($logFile === null) {
            $logFile = $this->getEnv() . '.log';
        }
        error_log(
            json_encode($data, JSON_UNESCAPED_SLASHES) . PHP_EOL,
            3,
            filepath_join( $logDir, $logFile)
        );
    }

    private function boot(): void
    {
        $this->initEnv();
        $this->configureErrorHandling();
        $this->configureTimezone();

        $middleware = $this->loadConfigurationIfExists('middleware.php');
        $middleware = array_filter($middleware, function ($environments) {
            return in_array($this->getEnv(), $environments);
        });
        $this->middlewareCollection = array_keys($middleware);

        $this->loadDependencies();
        $this->afterBoot();
    }

    private function initEnv(): void
    {
        (new DotEnv($this->getEnvFile()))->load();
        foreach (['APP_ENV' => self::DEFAULT_ENV, 'APP_TIMEZONE' => 'UTC', 'APP_LOCALE' => 'en', 'APP_DEBUG' => false] as $k => $value) {
            if (getenv($k) === false) {
                self::putEnv($k, $value);
            }
        }

        $environments = self::getAvailableEnvironments();
        if (!in_array(getenv('APP_ENV'), $environments)) {
            throw new InvalidArgumentException(sprintf(
                    'The env "%s" do not exist. Defined environments are: "%s".',
                    getenv('APP_ENV'),
                    implode('", "', $environments))
            );
        }
        $this->env =  strtolower($_ENV['APP_ENV']);
        $this->debug = $_ENV['APP_DEBUG'] ?: ($this->env === 'dev');
    }

    private function configureErrorHandling(): void
    {
        if ($this->getEnv() === 'dev') {
            ErrorHandler::register();
            return;
        }
        ini_set("log_errors", '1');
        ini_set("error_log", $this->getLogDir() . '/error_log.log');

        ini_set("display_startup_errors", '0');
        ini_set("display_errors", '0');
        ini_set("html_errors", '0');
        ini_set("track_errors", '0');

        error_reporting(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
    }

    private function configureTimezone(): void
    {
        $timezone = getenv('APP_TIMEZONE');
        if ($timezone === false) {
            throw new \RuntimeException('APP_TIMEZONE environment variable is not set.');
        }
        date_default_timezone_set($timezone);
    }

    final public function loadConfigurationIfExists(string $fileName): array
    {
        $filePath = filepath_join( $this->getConfigDir(), $fileName);
        if (file_exists($filePath)) {
            return require $filePath;
        }

        return [];
    }

    private function loadDependencies(): void
    {
        list($services, $parameters, $listeners, $routes, $commands, $packages, $controllers) = (new Dependency($this))->load();
        $definitions = array_merge(
            $parameters,
            $services,
            [
                'michel.packages' => $packages,
                'michel.commands' => $commands,
                'michel.listeners' => $listeners,
                'michel.middleware' => $this->middlewareCollection,
                BaseKernel::class => $this
            ]
        );
        $definitions['michel.services_ids'] = array_keys($definitions);
        $definitions['michel.controllers'] = static function (ContainerInterface $container) use ($controllers) {
            $scanner = new ControllerFinder($controllers, $container->get('michel.current_cache'));
            return $scanner->findControllerClasses();
        };
        $definitions['michel.routes'] = static function (ContainerInterface $container) use ($routes) {
            $collector = null;
            if (PHP_VERSION_ID >= 80000) {
                $controllers = $container->get('michel.controllers');
                $collector = new AttributeRouteCollector(
                    $controllers,
                    $container->get('michel.current_cache')
                );
            }
            return array_merge($routes, $collector ? $collector->collect() : []);
        };

        $this->container = $this->loadContainer($definitions);
        $this->debugDataCollector = $this->container->get(DebugDataCollector::class);
        unset($services, $parameters, $listeners, $routes, $commands, $packages, $controllers, $definitions);
    }

    private static function getAvailableEnvironments(): array
    {
        return array_unique(array_merge(self::DEFAULT_ENVIRONMENTS, App::getCustomEnvironments()));
    }

    private static function putEnv(string $name, $value): void
    {
        putenv(sprintf('%s=%s', $name, is_bool($value) ? ($value ? '1' : '0') : $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
