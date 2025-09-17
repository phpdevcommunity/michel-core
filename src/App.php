<?php

declare(strict_types=1);

namespace PhpDevCommunity\Michel\Core;

use PhpDevCommunity\Resolver\Option;
use PhpDevCommunity\Resolver\OptionsResolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package    PhpDevCommunity Michel
 * @author    PhpDevCommunity <michel@phpdevcommunity.com>
 * @license    https://opensource.org/license/mpl-2-0 Mozilla Public License v2.0
 * @link    https://www.phpdevcommunity.com
 */
final class App
{
    private array $options;
    private static App $instance;
    private ?ContainerInterface $container = null;

    private function __construct(array $options)
    {
        $resolver = new OptionsResolver([
            (new Option('server_request'))->validator(static function ($value) {
                return $value instanceof \Closure;
            }),
            (new Option('server_request_factory'))->validator(static function ($value) {
                return $value instanceof \Closure;
            }),
            (new Option('response_factory'))->validator(static function ($value) {
                return $value instanceof \Closure;
            }),
            (new Option('container'))->validator(static function ($value) {
                return $value instanceof \Closure;
            }),
            (new Option('custom_environments'))->validator(static function ($value) {
                if (is_array($value) === false) {
                    return false;
                }
                $environmentsFiltered = array_filter($value, function ($value) {
                    return is_string($value) === false;
                });
                if ($environmentsFiltered !== []) {
                    throw new \InvalidArgumentException('custom_environments array values must be string only');
                }
                return true;

            })->setDefaultValue([]),
        ]);
        $this->options = $resolver->resolve($options);
    }

    public static function initWithPath(string $path): void
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('%s does not exist', $path));
        }
        self::init(require $path);
    }

    public static function init(array $options): void
    {
        self::$instance = new self($options);
    }

    public static function createServerRequest(): ServerRequestInterface
    {
        $serverRequest = self::getApp()->options['server_request'];
        return $serverRequest();
    }

    public static function getServerRequestFactory(): ServerRequestFactoryInterface
    {
        $serverRequest = self::getApp()->options['server_request_factory'];
        return $serverRequest();
    }

    public static function getResponseFactory(): ResponseFactoryInterface
    {
        $responseFactory = self::getApp()->options['response_factory'];
        return $responseFactory();
    }

    public static function createContainer($definitions, $options): ContainerInterface
    {
        if (self::getApp()->container instanceof ContainerInterface) {
            throw new \LogicException('A container has already been built in ' . self::class);
        }
        self::getApp()->container = self::getApp()->options['container']($definitions, $options);

        return self::getContainer();
    }

    public static function getContainer(): ContainerInterface
    {
        return self::getApp()->container;
    }

    public static function getCustomEnvironments(): array
    {
        return self::getApp()->options['custom_environments'];
    }

    private static function getApp(): self
    {
        if (self::$instance === null) {
            throw new \LogicException('Please call ::init() method before get ' . self::class);
        }
        return self::$instance;
    }
}
