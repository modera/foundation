<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class ModeraExpanderAppKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Modera\ExpanderBundle\ModeraExpanderBundle(),
            new Modera\ExpanderBundle\Tests\Fixtures\Bundles\DummyBundle\ModeraExpanderDummyBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yaml');
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
