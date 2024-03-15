<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\VersionResolving;

use Modera\MJRCacheAwareClassLoaderBundle\DependencyInjection\ModeraMJRCacheAwareClassLoaderExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Standard version resolver will try to do the following things in order to resolve currently installed MF version:.
 *
 *  * at first it will try use bundle semantic config's configuration property "version"
 *   ( see \Modera\MJRCacheAwareClassLoaderBundle\DependencyInjection\Configuration )
 *  * if no version is configured using bundle semantic configuration then it will try to locate "modera-version.txt" file
 *    which is located one level above where AppKernel class resides
 *  * if neither of the ways worked out then a default "1.0.0" version will be returned
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class StandardVersionResolver implements VersionResolverInterface
{
    private KernelInterface $kernel;

    /**
     * @var array{'version'?: string}
     */
    private array $semanticConfig;

    public function __construct(ContainerInterface $container)
    {
        /** @var KernelInterface $kernel */
        $kernel = $container->get('kernel');
        $this->kernel = $kernel;

        /** @var array{'version'?: string} $semanticConfig */
        $semanticConfig = $container->getParameter(ModeraMJRCacheAwareClassLoaderExtension::CONFIG_KEY);
        $this->semanticConfig = $semanticConfig;
    }

    public function resolve(): string
    {
        $configuredVersion = isset($this->semanticConfig['version']) ? $this->semanticConfig['version'] : '';
        $fileVersion = @\file_get_contents($this->kernel->getProjectDir().'/modera-version.txt');

        if ('' !== $configuredVersion) {
            return $configuredVersion;
        } elseif (false !== $fileVersion) {
            return $fileVersion;
        } else {
            return '1.0.0';
        }
    }
}
