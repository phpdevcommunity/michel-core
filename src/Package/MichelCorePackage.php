<?php

namespace PhpDevCommunity\Michel\Core\Package;

use LogicException;
use PhpDevCommunity\Console\CommandRunner;
use PhpDevCommunity\Listener\EventDispatcher;
use PhpDevCommunity\Listener\ListenerProvider;
use PhpDevCommunity\Michel\Core\Command\CacheClearCommand;
use PhpDevCommunity\Michel\Core\Command\DebugContainerCommand;
use PhpDevCommunity\Michel\Core\Command\DebugEnvCommand;
use PhpDevCommunity\Michel\Core\Command\DebugRouteCommand;
use PhpDevCommunity\Michel\Core\Command\LogClearCommand;
use PhpDevCommunity\Michel\Core\Command\MakeCommandCommand;
use PhpDevCommunity\Michel\Core\Command\MakeControllerCommand;
use PhpDevCommunity\Michel\Core\Config\ConfigProvider;
use PhpDevCommunity\Michel\Core\Debug\DebugDataCollector;
use PhpDevCommunity\Michel\Core\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use PhpDevCommunity\Michel\Core\ErrorHandler\ExceptionHandler;
use PhpDevCommunity\Michel\Core\Middlewares\DebugMiddleware;
use PhpDevCommunity\Michel\Core\Middlewares\ForceHttpsMiddleware;
use PhpDevCommunity\Michel\Core\Middlewares\IpRestrictionMiddleware;
use PhpDevCommunity\Michel\Core\Middlewares\MaintenanceMiddleware;
use PhpDevCommunity\Michel\Package\PackageInterface;
use PhpDevCommunity\Route;
use PhpDevCommunity\Router;
use PhpDevCommunity\RouterInterface;
use PhpDevCommunity\RouterMiddleware;
use PhpDevCommunity\TemplateBridge\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use function getenv;

final class MichelCorePackage implements PackageInterface
{
    public function getDefinitions(): array
    {
        return [
            ConfigProvider::class => static function (ContainerInterface $container): ConfigProvider {
                return new ConfigProvider($container);
            },
            EventDispatcherInterface::class => static function (ContainerInterface $container): EventDispatcherInterface {
                $events = $container->get('michel.listeners');
                $provider = new ListenerProvider();
                foreach ($events as $event => $listeners) {
                    if (is_array($listeners)) {
                        foreach ($listeners as $listener) {
                            $provider->addListener($event, $container->get($listener));
                        }
                    } elseif (is_object($listeners)) {
                        $provider->addListener($event, $listeners);
                    } else {
                        $provider->addListener($event, $container->get($listeners));
                    }
                }
                unset($events);
                return new EventDispatcher($provider);
            },
            CommandRunner::class => static function (ContainerInterface $container): CommandRunner {
                $commandList = $container->get('michel.commands');
                $commands = [];
                foreach ($commandList as $commandName) {
                    $commands[] = $container->get($commandName);
                }
                unset($commandList);
                return new CommandRunner($commands);
            },
            'render' => static function (ContainerInterface $container) {
                if (class_exists(Environment::class)) {
                    $loader = new FilesystemLoader($container->get('app.template_dir'));
                    return new Environment($loader, [
                        'debug' => $container->get('michel.debug'),
                        'cache' => $container->get('michel.environment') == 'dev' ? false : $container->get('michel.cache_dir'),
                    ]);
                } elseif (class_exists(PhpRenderer::class)) {
                    return new PhpRenderer($container->get('app.template_dir'));
                }

                throw new LogicException('The "render" requires a Renderer to be available. You can choose between installing "phpdevcommunity/template-bridge" or "twig/twig" depending on your preference.');
            },
            RouterInterface::class => static function (ContainerInterface $container): object {
                /**
                 * @var array<Route> $routes
                 */
                $routes = $container->get('michel.routes');
                $router = new Router($routes, $container->get('app.url'));
                unset($routes);
                return $router;
            },
            DebugDataCollector::class => static function (ContainerInterface $container): DebugDataCollector {
                return new DebugDataCollector($container->get('michel.debug'));
            },
            DebugMiddleware::class => static function (ContainerInterface $container) {
                return new DebugMiddleware([
                    'debug' => $container->get('michel.debug'),
                    'profiler' => $container->get('app.profiler'),
                    'env' => $container->get('michel.environment'),
                    'log_dir' => $container->get('michel.logs_dir'),
                ]);
            },
            RouterMiddleware::class => static function (ContainerInterface $container) {
                return new RouterMiddleware($container->get(RouterInterface::class), response_factory());
            },
            ForceHttpsMiddleware::class => static function (ContainerInterface $container) {
                /**
                 * @var ConfigProvider $configProvider
                 */
                $configProvider = $container->get(ConfigProvider::class);
                return new ForceHttpsMiddleware($configProvider->isForceHttps(), response_factory());
            },
            IpRestrictionMiddleware::class => static function (ContainerInterface $container) {
                /**
                 * @var ConfigProvider $configProvider
                 */
                $configProvider = $container->get(ConfigProvider::class);
                return new IpRestrictionMiddleware($configProvider->getAllowedIps(), response_factory());
            },
            MaintenanceMiddleware::class => static function (ContainerInterface $container) {
                /**
                 * @var ConfigProvider $configProvider
                 */
                $configProvider = $container->get(ConfigProvider::class);
                return new MaintenanceMiddleware(
                    $configProvider->isMaintenance(),
                    response_factory(),
                    $configProvider->getAllowedIps()
                );
            },
            ExceptionHandler::class => static function (ContainerInterface $container) {
                /**
                 * @var ConfigProvider $configProvider
                 */
                $configProvider = $container->get(ConfigProvider::class);

                return new ExceptionHandler(response_factory(), [
                        'debug' => $container->get('michel.debug'),
                        'html_response' => new HtmlErrorRenderer(
                            response_factory(),
                            $container->get('michel.debug'),
                            filepath_join($configProvider->getTemplateDir(), '_exception')
                        )
                    ]
                );
            }
        ];
    }

    public function getParameters(): array
    {
        return [
            'app.url' => getenv('APP_URL') ?: '', // Application URL
            'app.locale' => getenv('APP_LOCALE') ?: 'en', // Default locale
            'app.template_dir' => getenv('APP_TEMPLATE_DIR') ?: function (ContainerInterface $container) {
                return filepath_join($container->get('michel.project_dir'), 'templates');
            }, // Template directory
            'app.allowed_ips' => getenv('APP_ALLOWED_IPS') ?: '', // Allowed IP addresses
            'app.secret_key' => getenv('APP_SECRET_KEY') ?: '', // Secret
            'app.maintenance' => $_ENV['APP_MAINTENANCE'] ?? false, // Maintenance mode
            'app.force_https' => $_ENV['APP_FORCE_HTTPS'] ?? false, // Force HTTPS
            'app.profiler' => $_ENV['APP_PROFILER'] ?? function (ContainerInterface $container) {
                    return $container->get('michel.environment') == 'dev';
                }, // Debug mode
        ];
    }

    public function getRoutes(): array
    {
        return [];
    }

    public function getListeners(): array
    {
        return [];
    }

    /**
     * Return an array of controller sources to scan for attribute-based routes.
     *
     * Each source can be either:
     * - A fully-qualified class name (FQCN), e.g. App\Controller\PingController::class
     * - A directory path (string), e.g. __DIR__ . '/../src/Controller'
     *
     * This allows the router to scan specific controllers or entire folders.
     *
     * @return string[] Array of class names and/or absolute folder paths.
     */
    public function getControllerSources(): array
    {
        return [];
    }

    public function getCommandSources(): array
    {
        return [
            CacheClearCommand::class,
            LogClearCommand::class,
            MakeControllerCommand::class,
            MakeCommandCommand::class,
            DebugEnvCommand::class,
            DebugContainerCommand::class,
            DebugRouteCommand::class,
        ];
    }
}
