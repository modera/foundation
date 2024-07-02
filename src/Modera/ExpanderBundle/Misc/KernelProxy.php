<?php

namespace Modera\ExpanderBundle\Misc;

use Modera\ExpanderBundle\DependencyInjection\ExtensionPointAwareCompilerPassInterface;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

if (\class_exists('App\Kernel')) {
    abstract class BaseKernel extends \App\Kernel
    {
    }
} elseif (\class_exists('AppKernel')) {
    abstract class BaseKernel extends \AppKernel
    {
    }
} else {
    abstract class BaseKernel extends Kernel
    {
        use MicroKernelTrait;
    }
}

/**
 * @internal This class is not a part of a public API.
 *
 * This kernel class caches used by container ContainerBuilder which allows later to introspect what
 * compiler passes have been used to build a container.
 */
class KernelProxy extends BaseKernel // nasty one
{
    private ?ContainerBuilder $containerBuilder = null;

    protected function buildContainer(): ContainerBuilder
    {
        // @phpstan-ignore-next-line
        $containerBuilder = parent::buildContainer();

        $this->containerBuilder = $containerBuilder;

        return $containerBuilder;
    }

    /**
     * @return ExtensionPointAwareCompilerPassInterface[]
     */
    public function getExtensionCompilerPasses(): array
    {
        if (!$this->containerBuilder) {
            throw new \RuntimeException("You haven't yet bootstrapped KernelProxy class!");
        }

        /** @var ExtensionPointAwareCompilerPassInterface[] $result */
        $result = [];

        foreach ($this->containerBuilder->getCompiler()->getPassConfig()->getPasses() as $pass) {
            if ($pass instanceof ExtensionPointAwareCompilerPassInterface) {
                $result[] = $pass;
            }
        }

        return $result;
    }

    public function getExtensionPoint(string $id): ?ExtensionPoint
    {
        foreach ($this->getExtensionCompilerPasses() as $pass) {
            $iteratedExtensionPoint = $pass->getExtensionPoint();
            if ($iteratedExtensionPoint && $iteratedExtensionPoint->getId() === $id) {
                return $iteratedExtensionPoint;
            }
        }

        return null;
    }

    public function getCacheDir(): string
    {
        return \sys_get_temp_dir().'/modera-kernel-proxy/cache';
    }

    public function getLogDir(): string
    {
        return \sys_get_temp_dir().'/modera-kernel-proxy/logs';
    }

    public function cleanUp(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->getCacheDir());
        $filesystem->remove($this->getLogDir());
    }
}
