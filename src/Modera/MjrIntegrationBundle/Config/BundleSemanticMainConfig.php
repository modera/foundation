<?php

namespace Modera\MjrIntegrationBundle\Config;

use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This implementation will read config from bundle's semantic config.
 *
 * @see \Modera\MjrIntegrationBundle\DependencyInjection\Configuration
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class BundleSemanticMainConfig implements MainConfigInterface
{
    /**
     * @var array{'deployment_name'?: string, 'deployment_url'?: string, 'home_section': string}
     */
    private array $config;

    public function __construct(ContainerInterface $container)
    {
        /** @var array{'deployment_name'?: string, 'deployment_url'?: string, 'home_section': string} $config */
        $config = $container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);
        $this->config = $config;
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
