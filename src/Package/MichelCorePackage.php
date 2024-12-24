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
use PhpDevCommunity\Michel\Core\Command\MakeCommandCommand;
use PhpDevCommunity\Michel\Core\Command\MakeControllerCommand;
use PhpDevCommunity\Michel\Core\Config\ConfigProvider;
use PhpDevCommunity\Michel\Core\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use PhpDevCommunity\Michel\Core\ErrorHandler\ExceptionHandler;
use PhpDevCommunity\Michel\Core\Middlewares\ForceHttpsMiddleware;
use PhpDevCommunity\Michel\Core\Middlewares\IpRestrictionMiddleware;
use PhpDevCommunity\Michel\Core\Middlewares\MaintenanceMiddleware;
use PhpDevCommunity\Renderer\PhpRenderer;
use PhpDevCommunity\Route;
use PhpDevCommunity\Router;
use PhpDevCommunity\RouterInterface;
use PhpDevCommunity\RouterMiddleware;
use PhpDevCommunity\Session\Storage\NativeSessionStorage;
use PhpDevCommunity\Session\Storage\SessionStorageInterface;
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
            EventDispatcherInterface::class => static function (ContainerInterface $container): ?EventDispatcherInterface {
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
                return new EventDispatcher($provider);
            },
            CommandRunner::class => static function (ContainerInterface $container): CommandRunner {
                $commandList = $container->get('michel.commands');
                $commands = [];
                foreach ($commandList as $commandName) {
                    $commands[] = $container->get($commandName);
                }
                return new CommandRunner($commands);
            },
            SessionStorageInterface::class => static function (ContainerInterface $container) {
                /**
                 * @var ConfigProvider $configProvider
                 */
                $configProvider = $container->get(ConfigProvider::class);
                return new NativeSessionStorage($configProvider->getSessionConfig());
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
                return new Router($routes, $container->get('app.url'));
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
                return new ExceptionHandler(response_factory(), [
                        'debug' => $container->get('michel.debug'),
                        'html_response' => new HtmlErrorRenderer(
                            response_factory(),
                            $container->get('michel.debug'),
                            filepath_join($container->get('app.template_dir'), '_exception')
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
            'app.template_dir' => getenv('APP_TEMPLATE_DIR') ?: '', // Template directory
            'app.allowed_ips' => getenv('APP_ALLOWED_IPS') ?: '', // Allowed IP addresses
            'app.maintenance' => $_ENV['APP_MAINTENANCE'] ?? false, // Maintenance mode
            'app.force_https' => $_ENV['APP_FORCE_HTTPS'] ?? false, // Force HTTPS

            'session.save_path' => getenv('SESSION_SAVE_PATH') ?: 'var/session', // Default path for session storage
            'session.cookie_lifetime' => $_ENV['SESSION_COOKIE_LIFETIME'] ?: 86400, // Cookie lifetime (24 hours)
            'session.gc_maxlifetime' => $_ENV['SESSION_GC_MAXLIFETIME'] ?: 604800, // Server-side session lifetime (7 days)
            'session.cookie_secure' => $_ENV['SESSION_COOKIE_SECURE'] === true, // Cookie is only transmitted via HTTPS
            'session.cookie_httponly' => $_ENV['SESSION_COOKIE_HTTPONLY'] === true, // Prevents JavaScript access to the cookie
            'session.use_strict_mode' => $_ENV['SESSION_USE_STRICT_MODE'] === true, // Rejects invalid SIDs
            'session.use_only_cookies' => $_ENV['SESSION_USE_ONLY_COOKIES'] === true, // Prevents using SIDs in the URL
            'session.sid_length' => $_ENV['SESSION_SID_LENGTH'] ?? 64, // Secure SID length
            'session.sid_bits_per_character' => $_ENV['SESSION_SID_BITS_PER_CHARACTER'] ?? 6, // Bits per character (6 for maximum security)
            'session.cookie_samesite' => $_ENV['SESSION_COOKIE_SAMESITE'] ?? 'Strict', // Protection against CSRF attacks
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

    public function getCommands(): array
    {
        return [
            CacheClearCommand::class,
            MakeControllerCommand::class,
            MakeCommandCommand::class,
            DebugEnvCommand::class,
            DebugContainerCommand::class,
            DebugRouteCommand::class,
        ];
    }
}
