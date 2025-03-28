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
     * Get the console commands configuration.
     *
     * @return array An array of console command class names.
     */
    public function getCommands(): array;
}
