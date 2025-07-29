<?php

namespace Test\PhpDevCommunity\Michel\Core\Kernel;

use PhpDevCommunity\Michel\Core\BaseKernel;

class  SampleKernelTest extends BaseKernel
{
    private string $envFile;

    public function __construct(string $envFile)
    {
        $this->envFile = $envFile;
        parent::__construct();
    }

    public function getProjectDir(): string
    {
       return dirname(__DIR__);
    }

    public function getCacheDir(): string
    {
        return filepath_join($this->getProjectDir(),'cache');
    }

    public function getLogDir(): string
    {
        return filepath_join($this->getProjectDir(),'log');
    }

    public function getConfigDir(): string
    {
        return filepath_join($this->getProjectDir(),'config');
    }

    public function getPublicDir(): string
    {
        return '';
    }

    public function getEnvFile(): string
    {
        return filepath_join( $this->getProjectDir(), $this->envFile);
    }

    protected function afterBoot(): void
    {
        // TODO: Implement afterBoot() method.
    }
}
