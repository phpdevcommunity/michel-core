<?php

namespace PhpDevCommunity\Michel\Core;

use PhpDevCommunity\Michel\Core\Package\PackageInterface;
use Psr\Container\ContainerInterface;

final class Dependency
{
    const CACHE_KEY = '__michel_core_dependencies';

    private BaseKernel $baseKernel;

    public function __construct(BaseKernel $baseKernel)
    {
        $this->baseKernel = $baseKernel;
    }
    public function load(): array
    {
        $services = $this->loadConfigurationIfExists('services.php');
        $parameters = $this->loadParameters('parameters.php');
        $listeners = $this->loadConfigurationIfExists('listeners.php');
        $routes = $this->loadConfigurationIfExists('routes.php');
        $commands = $this->loadConfigurationIfExists('commands.php');
        $controllers = $this->loadConfigurationIfExists('controllers.php');
        $packages = $this->getPackages();
        foreach ($packages as $package) {
            $services = array_merge($package->getDefinitions(), $services);
            $parameters = array_merge($package->getParameters(), $parameters);
            $listeners = array_merge_recursive($package->getListeners(), $listeners);
            $routes = array_merge($package->getRoutes(), $routes);
            $commands = array_merge($package->getCommandSources(), $commands);
            $controllers = array_merge($package->getControllerSources(), $controllers);
        }

        return [$services, $parameters, $listeners, $routes, $commands, $packages, $controllers];
    }

    /**
     * @return array<PackageInterface>
     */
    private function getPackages(): array
    {
        $packagesName = $this->loadConfigurationIfExists('packages.php');
        $packages = [];
        foreach ($packagesName as $packageName => $envs) {
            if (!in_array($this->baseKernel->getEnv(), $envs)) {
                continue;
            }
            $packages[] = new $packageName();
        }
        return $packages;
    }

    private function loadConfigurationIfExists(string $fileName): array
    {
        return $this->baseKernel->loadConfigurationIfExists($fileName);
    }

    private function loadParameters(string $fileName): array
    {
        $parameters = $this->loadConfigurationIfExists($fileName);

        $parameters['michel.environment'] = $this->baseKernel->getEnv();
        $parameters['michel.debug'] = $this->baseKernel->isDebug();
        $parameters['michel.project_dir'] = $this->baseKernel->getProjectDir();
        $parameters['michel.cache_dir'] = $this->baseKernel->getCacheDir();
        $parameters['michel.logs_dir'] = $this->baseKernel->getLogDir();
        $parameters['michel.config_dir'] = $this->baseKernel->getConfigDir();
        $parameters['michel.public_dir'] = $this->baseKernel->getPublicDir();
        $parameters['michel.current_cache'] = $this->baseKernel->getEnv() === 'dev' ? null : $this->baseKernel->getCacheDir();

        return $parameters;
    }
}
