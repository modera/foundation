<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),

            new Modera\ExpanderBundle\ModeraExpanderBundle($this),
            new \Modera\ExpanderBundle\Tests\Fixtures\Bundles\DummyBundle\ModeraExpanderDummyBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yml');
    }

    public function getCacheDir(): string
    {
        return \sys_get_temp_dir().'/ModeraExpanderBundle/cache';
    }

    public function getLogDir(): string
    {
        return \sys_get_temp_dir().'/ModeraExpanderBundle/logs';
    }
}
