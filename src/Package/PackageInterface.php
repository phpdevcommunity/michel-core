<?php

namespace PhpDevCommunity\Michel\Core\Package;

/**
 * Interface PackageInterface
 *
 * This interface defines methods for retrieving various package-related configurations and definitions.
 */
interface PackageInterface
{
    /**
     * Get the definitions of services for the container.
     *
     * Example:
     * ```
     * [
     *     RouterMiddleware::class => static function (ContainerInterface $container) {
     *         return new RouterMiddleware($container->get('router'), response_factory());
     *     },
     *     ExceptionHandler::class => static function (ContainerInterface $container) {
     *         return new ExceptionHandler(response_factory(), [
     *             'debug' => $container->get('michel.debug'),
     *             'html_response' => new HtmlErrorRenderer(
     *                 response_factory(),
     *                 $container->get('michel.debug'),
     *                 $container->get('app.template_dir') . DIRECTORY_SEPARATOR . '_exception'
     *             ),
     *         ]);
     *     },
     * ]
     *
     * @return array An associative array where keys are service identifiers and values are factory functions.
     */
    public function getDefinitions(): array;

    /**
     * Get the parameters configuration.
     *
     * Example:
     * ```
     * [
     *     'app.url' => getenv('APP_URL') ?: '',
     *     'app.locale' => getenv('APP_LOCALE') ?: 'en',
     *     'app.template_dir' => getenv('APP_TEMPLATE_DIR') ?: '',
     * ]
     *
     * @return array An associative array where keys are parameter names and values are parameter values.
     */
    public function getParameters(): array;

    /**
     * Get the routes configuration.
     *
     * @return array An array of `\PhpDevCommunity\Michel\Core\Router\Route` instances defining the routes.
     */
    public function getRoutes(): array;

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
    public function getControllerSources(): array;

    /**
     * Get the event listeners configuration.
     *
     * Example:
     * ```
     * [
     *     \App\Event\ExampleEvent::class => \App\Listeners\ExampleListener::class,
     *     // For multiple listeners for the same event:
     *     // \App\Event\ExampleEvent::class => [
     *     //     \App\Listeners\ExampleListener::class,
     *     //     \App\Listeners\ExampleListener2::class,
     *     //     \App\Listeners\ExampleListener3::class,
     *     // ]
     * ]
     *
     * @return array An associative array where keys are event class names and values are listener class names or arrays of listener class names.
     */
    public function getListeners(): array;

    /**
     * Return an array of sources to load console commands from.
     *
     * Each source can be:
     * - A fully-qualified class name of a console command
     * - A directory path (string) to scan for command classes
     *
     * This allows both direct registration and dynamic discovery.
     *
     * @return string[] Array of FQCNs and/or absolute folder paths.
     */
    public function getCommandSources(): array;
}
