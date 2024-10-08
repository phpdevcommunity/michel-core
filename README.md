# Michel Framework Core

The Michel Framework Core is the core library of the Michel Framework, a lightweight and versatile PHP framework designed for simplifying web development tasks.

## Installation

You can install the Michel Framework Core via Composer:

```bash
composer require phpdevcommunity/michel-core
```
## Usage

To use the Michel Framework Core, you can follow these steps:

1. **Require the Composer Autoloader**: Include Composer's autoloader in your project's entry point (e.g., `index.php`).

```php
define('MICHEL_COMPOSER_AUTOLOAD_FILE', dirname(__DIR__) .'/vendor/autoload.php');
require_once MICHEL_COMPOSER_AUTOLOAD_FILE;
```

2. **Create a .env File**

Create a `.env` file in the directory defined by `getProjectDir` in your Kernel class. This file should contain essential environment configuration variables. At a minimum, include the following variables:
```env
APP_ENV=prod
```
Customize the `.env` file further with any additional environment-specific variables your application requires.

3. **Configuration Files**:

Before creating the kernel and booting up the Michel Framework, it's crucial to ensure that your project's `config` directory contains the necessary configuration files. These files define various aspects of your application, such as services, routes, middleware, and more.

Here is a list of the essential configuration files that should be present in your `config` directory:

- `commands.php`: This file defines custom artisan commands for your application.

- `framework.php`: Framework-specific configuration, including options related to request handling, response generation, and other core settings.

- `listeners.php`: Contains event listeners and their associated event-handling logic.

- `middleware.php`: Lists middleware classes.

- `packages.php`: Defines the packages or external dependencies your application relies on.

- `parameters.php`: Stores parameters, often used to configure services or other parts of your application.

- `routes.php`: Specifies the routes and associated controllers for your application's endpoints.

- `services.php`: Contains definitions for services, their dependencies, and how they should be instantiated.

Ensure that these configuration files are correctly populated and tailored to your project's requirements. Proper configuration is essential for the Michel Framework to function as expected and deliver the desired behavior.

With the necessary configuration files in place, you can proceed with creating the kernel and launching your Michel Framework-powered application.
 
4. **Create a Kernel Class**: Create a `Kernel` class that extends `BaseKernel` from the Michel Framework Core. This class is the heart of your application and is responsible for configuration and bootstrapping.
```php
final class Kernel extends BaseKernel
{
    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache';
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    public function getConfigDir(): string
    {
        return $this->getProjectDir() . '/config';
    }

    public function getPublicDir(): string
    {
        return $this->getProjectDir() . '/public';
    }

    protected function afterBoot(): void
    {
        // You can perform additional setup or actions here after the framework has booted.
    }
}

```
You can customize the `Kernel` class by implementing methods like `getCacheDir`, `getProjectDir`, `getLogDir`, `getConfigDir`, and `getPublicDir` to define the directories used by your application.

5. **Configuration Array**: To configure the framework, you must define its settings using a configuration array. This array should be stored in a file named `framework.php` within the directory defined by `public function getConfigDir(): string` in the kernel.

```php
return [

    // Framework Settings

    'server_request' => static function (): ServerRequestInterface {
        return ServerRequestFactory::fromGlobals();
    },

    'response_factory' => static function (): ResponseFactoryInterface {
        return new ResponseFactory();
    },

    'container' => static function (array $definitions, array $options): ContainerInterface {

        // Customize the container configuration here
        
        return new Container(
            $definitions,
            new ReflectionResolver()
        );
    },

    'custom_environments' => [],

];
```

6. **Initialize the Framework**: Instantiate the `Kernel` class and configure it to meet your application's requirements.

```php
$kernel = new Kernel();
if (php_sapi_name() !== 'cli') {
    $response = $kernel->handle(App::createServerRequest());
    \send_http_response($response);
}
```
# Michel Framework Core Configuration

The configuration file for the Michel Framework Core, named `framework.php`, allows you to customize critical aspects of the framework's operation. It defines several key components and functions that are used by the framework to process HTTP requests and manage dependencies.

## Server Request Configuration

The `server_request` section is a pivotal aspect of configuring the Michel Framework Core for PSR-7 compliance. In order to use this section effectively, you must first install a PSR-7-compatible library of your choice, as the framework does not have a default PSR-7 implementation.

Once you've installed a PSR-7 library, you can define a custom function within the `server_request` section to specify how the framework should create instances of the `ServerRequestInterface`. This customization allows you to tailor the request instantiation process to align with the PSR-7 specification and your application's specific requirements.

Here's an example of how you might configure the `server_request` section to use the Laminas Diactoros library for request instantiation:

```php
'server_request' => static function (): ServerRequestInterface {
    // Instantiate a ServerRequest using Laminas Diactoros or your preferred PSR-7 library.
    return \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
},
```

## Response Factory Configuration

The `response_factory` section is a critical component responsible for generating HTTP responses within the Michel Framework Core, and it operates in alignment with the PSR-17 standard (HTTP Factories). However, to use this section effectively, it's essential to install a PSR-17-compatible library of your choice since the framework does not include a default PSR-17 implementation.

Once you've installed a PSR-17 library, you can define a custom function within the `response_factory` section to specify how the framework should create instances of the `ResponseFactoryInterface`. This customization allows you to tailor the response generation process to comply with the PSR-17 specification and your application's specific needs.

Here's an example of how you might configure the `response_factory` section to use the Laminas Diactoros library for response instantiation:

```php
'response_factory' => static function (): ResponseFactoryInterface {
    // Instantiate a ResponseFactory using Laminas Diactoros or your preferred PSR-17 library.
    return new \Laminas\Diactoros\ResponseFactory();
},
```

## Container Configuration

The `container` section is integral to managing dependencies and services within the Michel Framework Core. To use this section effectively, it's essential to have a PSR-11 compatible container implementation installed, as the framework does not provide a default PSR-11 container.

For example, you can configure the `container` section to use the `\PhpDevCommunity\DependencyInjection\Container` provided by the DevCoder library for dependency injection. Here's an example of how you might set it up:

```php
'container' => static function (array $definitions, array $options): ContainerInterface {
    // Instantiate a PSR-11 compatible container using \PhpDevCommunity\DependencyInjection\Container or your preferred library.
    return new \PhpDevCommunity\DependencyInjection\Container($definitions, new \PhpDevCommunity\DependencyInjection\ReflectionResolver());
},
```

## Custom Environments

The `custom_environments` section is an array that allows you to define custom application environments beyond the standard 'development' and 'production' environments. You can use these custom environments to handle different aspects of your application based on specific requirements.

```php
'custom_environments' => [],
```
