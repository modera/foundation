<?php

namespace Modera\MjrIntegrationBundle\Config;

/**
 * This implementation will read config from bundle's semantic config.
 *
 * @see \Modera\MjrIntegrationBundle\DependencyInjection\Configuration
 *
 * @copyright 2014 Modera Foundation
 */
class BundleSemanticMainConfig implements MainConfigInterface
{
    /**
     * @param array{'deployment_name'?: string, 'deployment_url'?: string, 'home_section': string} $config
     */
    public function __construct(
        private readonly array $config,
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->config['deployment_name'] ?? null;
    }

    public function getUrl(): ?string
    {
        return $this->config['deployment_url'] ?? null;
    }

    public function getHomeSection(): ?string
    {
        return $this->config['home_section'];
    }
}
