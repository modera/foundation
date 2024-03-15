<?php

namespace Modera\ExpanderBundle;

use Modera\ExpanderBundle\Contributing\ExtensionPointsAwareBundlesCollectorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

class ModeraExpanderBundle extends Bundle
{
    private ?KernelInterface $kernel;

    public function __construct(?KernelInterface $kernel = null)
    {
        $this->kernel = $kernel;
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ExtensionPointsAwareBundlesCollectorCompilerPass($this->kernel));
    }
}
