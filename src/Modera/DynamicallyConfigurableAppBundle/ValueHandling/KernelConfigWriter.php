<?php

namespace Modera\DynamicallyConfigurableAppBundle\ValueHandling;

use Modera\ConfigBundle\Config\ConfigurationEntryInterface;
use Modera\ConfigBundle\Config\ValueUpdatedHandlerInterface;
use Modera\DynamicallyConfigurableAppBundle\ModeraDynamicallyConfigurableAppBundle as Bundle;

/**
 * When "kernel_env", "kernel_debug" configuration entries are updated will synchronize its values with
 * kernel.json.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class KernelConfigWriter implements ValueUpdatedHandlerInterface
{
    /**
     * @var string
     */
    private $kernelClassName;

    /**
     * @param string $kernelClassName
     */
    public function __construct($kernelClassName = 'AppKernel')
    {
        $this->kernelClassName = $kernelClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function onUpdate(ConfigurationEntryInterface $entry)
    {
        if (!$this->canHandleEntry($entry)) {
            return;
        }

        $reflKernel = new \ReflectionClass($this->kernelClassName);

        $path = dirname($reflKernel->getFileName()).DIRECTORY_SEPARATOR.'kernel.json';

        $kernelJson = @file_get_contents($path);
        if (false === $kernelJson) {
            throw new \RuntimeException('Unable to find kernel.json, looked in '.$path);
        }
        $kernelJson = json_decode($kernelJson, true);

        $defaultValue = array(
            'debug' => false,
            'env' => 'prod',
        );
        $kernelJson = array_merge($defaultValue, $kernelJson);
        $kernelJson['_comment'] = 'This file is used by web/app.php to control with what configuration AppKernel should be created with.';

        if ($entry->getName() == Bundle::CONFIG_KERNEL_DEBUG) {
            $kernelJson['debug'] = $entry->getValue() == 'true';
        } elseif ($entry->getName() == Bundle::CONFIG_KERNEL_ENV) {
            $kernelJson['env'] = $entry->getValue();
        }

        file_put_contents($path, json_encode($kernelJson, \JSON_PRETTY_PRINT));
    }

    private function canHandleEntry(ConfigurationEntryInterface $entry)
    {
        return in_array($entry->getName(), array(Bundle::CONFIG_KERNEL_DEBUG, Bundle::CONFIG_KERNEL_ENV));
    }
}
