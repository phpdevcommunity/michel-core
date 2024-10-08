<?php

namespace Test\PhpDevCommunity\Michel\Core\Kernel;

use PhpDevCommunity\Michel\Core\BaseKernel;

class  SampleKernelTest extends BaseKernel
{
    private string $envfile;

    public function __construct(string $envfile)
    {
        $this->envfile = $envfile;
        parent::__construct();
    }

    public function getProjectDir(): string
    {
       return dirname(__DIR__);
    }

    public function getCacheDir(): string
    {
        return '';
    }

    public function getLogDir(): string
    {
        return '';
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
        return filepath_join( $this->getProjectDir(), $this->envfile);
    }

    protected function afterBoot(): void
    {
        // TODO: Implement afterBoot() method.
    }
}
