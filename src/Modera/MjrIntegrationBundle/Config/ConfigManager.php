<?php

namespace Modera\MjrIntegrationBundle\Config;

use Modera\ExpanderBundle\Ext\ExtensionProvider;

/**
 * Service is responsible for providing configuration used by JavaScript runtime.
 *
 * @copyright 2013 Modera Foundation
 */
class ConfigManager
{
    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
    ) {
    }

    /**
     * Config which will be used by client-side js runtime to configure its state.
     *
     * @return array<mixed>
     */
    public function getConfig(): array
    {
        $result = [];
        foreach ($this->extensionProvider->get('modera_mjr_integration.config.config_mergers')->getItems() as $merger) {
            if (!($merger instanceof ConfigMergerInterface)) {
                throw new \RuntimeException();
            }

            $result = $merger->merge($result);
        }

        return $result;
    }
}
