<?php

namespace Modera\FoundationBundle\Testing;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
abstract class AbstractFunctionalKernel extends Kernel
{
    /**
     * Extracts bundle name from a Kernel class name. For the method to work correctly it has to be formatted in the
     * following way: AcmeFooBundleAppKernel, in this case bundle's name is going to be AcmeFooBundle.
     *
     * @throws \LogicException  If bundle name cannot be extracted.
     *
     * @return string
     */
    protected function extractBundleName()
    {
        $className = explode('/', get_class($this));
        $className = $className[count($className)-1];

        $expectedSuffix = 'AppKernel';
        $actualSuffix = substr($className, -1 * strlen($expectedSuffix));

        if ($expectedSuffix != $actualSuffix) {
            throw new \LogicException('Kernel class is not formatted according to convention.');
        }

        return substr($className, 0, -1 * strlen($expectedSuffix));
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $reflClass = new \ReflectionClass($this);

        $loader->load(dirname($reflClass->getFileName()).'/config/config.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/'.$this->extractBundleName().'/cache';
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/'.$this->extractBundleName().'/logs';
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass().$this->extractBundleName();
    }
}
