<?php

namespace Modera\FoundationBundle\Testing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Extending this class when writing functional tests makes it possible to run several test-suites that contain
 * several instances of AppKernel without having colliding namespaces/paths.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
abstract class AbstractFunctionalKernel extends Kernel
{
    /**
     * TODO: find a proper solution how to disable logging in console when running tests.
     */
    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        $this->debug = false;
    }

    /**
     * Extracts bundle name from a Kernel class name. For the method to work correctly it has to be formatted in the
     * following way: AcmeFooBundleAppKernel, in this case bundle's name is going to be AcmeFooBundle.
     *
     * @throws \LogicException If bundle name cannot be extracted
     */
    protected function extractBundleName(): string
    {
        $className = \explode('/', \get_class($this));
        $className = $className[\count($className) - 1];

        $expectedSuffix = 'AppKernel';
        $actualSuffix = \substr($className, -1 * \strlen($expectedSuffix));

        if ($expectedSuffix !== $actualSuffix) {
            throw new \LogicException('Kernel class is not formatted according to convention.');
        }

        return \substr($className, 0, -1 * \strlen($expectedSuffix));
    }

    /**
     * Conventionally assumes that entry and main configuration config.yml file lives in "config"
     * directory which is adjacent to you subclass of AbstractFunctionalKernel.
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $reflClass = new \ReflectionClass($this);
        /** @var string $path */
        $path = $reflClass->getFileName();
        $loader->load(\dirname($path).'/config/config.yml');
    }

    public function getProjectDir(): string
    {
        $r = new \ReflectionObject($this);
        /** @var string $path */
        $path = $r->getFileName();

        return \dirname($path, 2);
    }

    public function getCacheDir(): string
    {
        return \sys_get_temp_dir().'/'.$this->extractBundleName().'/cache';
    }

    public function getLogDir(): string
    {
        return \sys_get_temp_dir().'/'.$this->extractBundleName().'/logs';
    }

    protected function getContainerClass(): string
    {
        return parent::getContainerClass().$this->extractBundleName();
    }
}
