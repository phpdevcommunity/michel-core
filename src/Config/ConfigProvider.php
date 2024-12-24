<?php

namespace PhpDevCommunity\Michel\Core\Config;

use Psr\Container\ContainerInterface;

final class ConfigProvider
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface  $container)
    {

        $this->container = $container;
    }

    public function getTemplateDir(): string
    {
        $templateDir = $this->container->get('app.template_dir');
        if (!is_string($templateDir)) {
            throw new \LogicException('The "app.template_dir" should be a string');
        }

        if (!is_dir($templateDir)) {
            throw new \LogicException('The "app.template_dir" should be a valid directory');
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
        foreach ($allowedIps as $value) {
            if (!filter_var($value, FILTER_VALIDATE_IP)) {
                throw new \LogicException('The "app.allowed_ips" should be an array of IP addresses');
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

    public function getSessionConfig(): array
    {
        $pathSession = $this->container->get('session.save_path');
        if (!str_starts_with($pathSession, '/')) {
            $pathSession = filepath_join($this->container->get('michel.project_dir'), $pathSession);
        }

        return [
            'save_path' => $pathSession,
            'cookie_lifetime' => $this->container->get('session.cookie_lifetime'),
            'gc_maxlifetime' => $this->container->get('session.gc_maxlifetime'),
            'cookie_secure' => $this->container->get('session.cookie_secure'),
            'cookie_httponly' => $this->container->get('session.cookie_httponly'),
            'use_strict_mode' => $this->container->get('session.use_strict_mode'),
            'use_only_cookies' => $this->container->get('session.use_only_cookies'),
            'sid_length' => $this->container->get('session.sid_length'),
            'sid_bits_per_character' => $this->container->get('session.sid_bits_per_character'),
            'cookie_samesite' => $this->container->get('session.cookie_samesite'),
        ];

    }

}
