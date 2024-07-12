<?php

namespace Modera\MjrIntegrationBundle\Contributions\Config;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\MainConfigInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Modera\MjrIntegrationBundle\Menu\MenuManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides standard configurators.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class StandardConfigMergersProvider implements ContributorInterface
{
    /**
     * @var ?ConfigMerger[]
     */
    private ?array $items = null;

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getItems(): array
    {
        if (!$this->items) {
            /** @var MenuManager $menuMgr */
            $menuMgr = $this->container->get('modera_mjr_integration.menu.menu_manager');

            /** @var ContributorInterface $sectionsProvider */
            $sectionsProvider = $this->container->get('modera_mjr_integration.sections_provider');

            /** @var ContributorInterface $loaderMappingsProvider */
            $loaderMappingsProvider = $this->container->get('modera_mjr_integration.class_loader_mappings_provider');

            /** @var array{'main_config_provider': string} $bundleConfig */
            $bundleConfig = $this->container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);

            /** @var MainConfigInterface $mainConfig */
            $mainConfig = $this->container->get($bundleConfig['main_config_provider']);

            $this->items = [
                new ConfigMerger($mainConfig, $menuMgr, $sectionsProvider, $loaderMappingsProvider),
            ];
        }

        return $this->items;
    }
}
