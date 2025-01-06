<?php

namespace PhpDevCommunity\Michel\Core\Config;

use Psr\Container\ContainerInterface;

final class ConfigProvider
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getTemplateDir(): string
    {
        $templateDir = $this->container->get('app.template_dir');
        if (!is_string($templateDir)) {
            throw new \LogicException(sprintf('The "app.template_dir" configuration must be a string. Given: %s', gettype($templateDir)));
        }

        if (!str_starts_with($templateDir, '/')) {
            $templateDir = filepath_join($this->container->get('michel.project_dir'), $templateDir);
        }

        if (!is_dir($templateDir)) {
            throw new \LogicException(sprintf('The specified "app.template_dir" directory does not exist: "%s".', $templateDir));
        }

        return $templateDir;
    }

    public function getAllowedIps(): array
    {
        $allowedIps = $this->container->get('app.allowed_ips');

        if (is_string($allowedIps)) {
            $allowedIps = explode(',', $allowedIps);
        }

        if (!is_array($allowedIps)) {
            throw new \LogicException('The "app.allowed_ips" should be an array of IP addresses');
        }

        $allowedIps = array_filter($allowedIps);

        foreach ($allowedIps as $value) {
            if (!filter_var($value, FILTER_VALIDATE_IP)) {
                throw new \LogicException(sprintf('Invalid IP address detected: "%s". Ensure all values in allowed IPs are valid IP addresses.', $value));
            }
        }
        return $allowedIps;
    }

    public function isForceHttps(): bool
    {
        $forceHttps = $this->container->get('app.force_https');
        if (!is_bool($forceHttps)) {
            throw new \LogicException('The "app.force_https" should be a boolean value');
        }
        return $forceHttps;
    }

    public function isMaintenance(): bool
    {
        $maintenance = $this->container->get('app.maintenance');
        if (!is_bool($maintenance)) {
            throw new \LogicException('The "app.maintenance" should be a boolean value');
        }
        return $maintenance;
    }
}
