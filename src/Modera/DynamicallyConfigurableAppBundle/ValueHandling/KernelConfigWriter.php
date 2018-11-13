<?php

namespace Modera\DynamicallyConfigurableAppBundle\ValueHandling;

use Modera\ConfigBundle\Config\ConfigurationEntryInterface;
use Modera\ConfigBundle\Config\ValueUpdatedHandlerInterface;
use Modera\DynamicallyConfigurableAppBundle\ModeraDynamicallyConfigurableAppBundle as Bundle;
use Modera\DynamicallyConfigurableAppBundle\KernelConfigInterface;
use Modera\DynamicallyConfigurableAppBundle\KernelConfig;

/**
 * When "kernel_env", "kernel_debug" configuration entries are updated
 * will synchronize its values with kernel.json.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class KernelConfigWriter implements ValueUpdatedHandlerInterface
{
    /**
     * @var string
     */
    private $kernelConfigFQCN;

    /**
     * @param null|string $kernelConfigFQCN
     */
    public function __construct($kernelConfigFQCN = null)
    {
        $this->kernelConfigFQCN = $kernelConfigFQCN ?: KernelConfig::class;

        if (!is_subclass_of($this->kernelConfigFQCN, KernelConfigInterface::class)) {
            throw new \RuntimeException(
                '\\' . $this->kernelConfigFQCN . ' must implement \\' . KernelConfigInterface::class
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onUpdate(ConfigurationEntryInterface $entry)
    {
        if (!$this->canHandleEntry($entry)) {
            return;
        }

        $mode = array();
        if ($entry->getName() == Bundle::CONFIG_KERNEL_DEBUG) {
            $mode['debug'] = $entry->getValue() == 'true';
        } elseif ($entry->getName() == Bundle::CONFIG_KERNEL_ENV) {
            $mode['env'] = $entry->getValue();
        }

        call_user_func(array($this->kernelConfigFQCN, 'write'), $mode);
    }

    /**
     * @param ConfigurationEntryInterface $entry
     * @return bool
     */
    private function canHandleEntry(ConfigurationEntryInterface $entry)
    {
        return in_array($entry->getName(), array(Bundle::CONFIG_KERNEL_DEBUG, Bundle::CONFIG_KERNEL_ENV));
    }
}
