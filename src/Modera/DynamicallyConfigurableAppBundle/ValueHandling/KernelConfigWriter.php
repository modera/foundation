<?php

namespace Modera\DynamicallyConfigurableAppBundle\ValueHandling;

use Modera\ConfigBundle\Config\ConfigurationEntryInterface;
use Modera\ConfigBundle\Config\ValueUpdatedHandlerInterface;
use Modera\DynamicallyConfigurableAppBundle\KernelConfig;
use Modera\DynamicallyConfigurableAppBundle\KernelConfigInterface;
use Modera\DynamicallyConfigurableAppBundle\ModeraDynamicallyConfigurableAppBundle as Bundle;

/**
 * When "kernel_env", "kernel_debug" configuration entries are updated
 * will synchronize its values with kernel.json.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class KernelConfigWriter implements ValueUpdatedHandlerInterface
{
    private string $kernelConfigFQCN;

    public function __construct(?string $kernelConfigFQCN = null)
    {
        $this->kernelConfigFQCN = $kernelConfigFQCN ?: KernelConfig::class;

        if (!\is_subclass_of($this->kernelConfigFQCN, KernelConfigInterface::class)) {
            throw new \RuntimeException('\\'.$this->kernelConfigFQCN.' must implement \\'.KernelConfigInterface::class);
        }
    }

    public function onUpdate(ConfigurationEntryInterface $entry): void
    {
        if (!$this->canHandleEntry($entry)) {
            return;
        }

        $mode = [];
        if (Bundle::CONFIG_KERNEL_DEBUG === $entry->getName()) {
            $mode['debug'] = (bool) $entry->getValue();
        } elseif (Bundle::CONFIG_KERNEL_ENV === $entry->getName()) {
            $mode['env'] = $entry->getValue();
        }

        $callback = [$this->kernelConfigFQCN, 'write'];
        if (!\is_callable($callback)) {
            throw new \RuntimeException('Write method not found');
        }

        \call_user_func($callback, $mode);
    }

    private function canHandleEntry(ConfigurationEntryInterface $entry): bool
    {
        return \in_array($entry->getName(), [Bundle::CONFIG_KERNEL_DEBUG, Bundle::CONFIG_KERNEL_ENV]);
    }
}
