<?php

namespace PhpDevCommunity\Michel\Core\Package;

use PhpDevCommunity\Console\CommandRunner;
use PhpDevCommunity\Listener\EventDispatcher;
use PhpDevCommunity\Listener\ListenerProvider;
use PhpDevCommunity\Renderer\PhpRenderer;
use PhpDevCommunity\Route;
use PhpDevCommunity\Router as PhpDevCommunityRouter;
use PhpDevCommunity\Michel\Core\Command\CacheClearCommand;
use PhpDevCommunity\Michel\Core\Command\DebugContainerCommand;
use PhpDevCommunity\Michel\Core\Command\DebugEnvCommand;
use PhpDevCommunity\Michel\Core\Command\DebugRouteCommand;
use PhpDevCommunity\Michel\Core\Command\MakeCommandCommand;
use PhpDevCommunity\Michel\Core\Command\MakeControllerCommand;
use PhpDevCommunity\Michel\Core\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use PhpDevCommunity\Michel\Core\ErrorHandler\ExceptionHandler;
use PhpDevCommunity\Michel\Core\Middlewares\RouterMiddleware;
use PhpDevCommunity\Michel\Core\Router\Bridge\RouteFactory;
use LogicException;
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
                'router' => static function (ContainerInterface $container): object {

                    if (!class_exists(PhpDevCommunityRouter::class)) {
                        throw new LogicException('The Router component requires the presence of a router library. You can install it by running "composer require phpdevcommunity/php-router".');
                    }

                    /**
                     * @var array<\PhpDevCommunity\Michel\Core\Router\Route> $routes
                     */
                    $routes = $container->get('michel.routes');
                    $factory = new RouteFactory();

                    $router = new PhpDevCommunityRouter([], $container->get('app.url'));
                    foreach ($routes as $route) {
                        $router->add($factory->createDevCoderRoute($route));
                    }
                    return $router;
                },
                RouterMiddleware::class => static function (ContainerInterface $container) {
                    return new RouterMiddleware($container->get('router'), response_factory());
                },
                ExceptionHandler::class => static function (ContainerInterface $container) {
                    return new ExceptionHandler(response_factory(), [
                            'debug' => $container->get('michel.debug'),
                            'html_response' => new HtmlErrorRenderer(
                                response_factory(),
                                $container->get('michel.debug'),
                                filepath_join($container->get('app.template_dir') ,'_exception')
                            )
                        ]
                    );
                }
            ];
    }

    public function getParameters(): array
    {
        return [
            'app.url' => getenv('APP_URL') ?: '',
            'app.locale' => getenv('APP_LOCALE') ?: 'en',
            'app.template_dir' => getenv('APP_TEMPLATE_DIR') ?: '',
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
