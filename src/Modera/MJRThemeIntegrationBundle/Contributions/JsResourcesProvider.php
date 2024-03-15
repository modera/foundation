<?php

namespace Modera\MJRThemeIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MJRThemeIntegrationBundle\DependencyInjection\ModeraMJRThemeIntegrationExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class JsResourcesProvider implements ContributorInterface
{
    /**
     * @var array{'theme_path': string}
     */
    private array $themeIntegrationConfig;

    public function __construct(ContainerInterface $container)
    {
        /** @var array{'theme_path': string} $themeIntegrationConfig */
        $themeIntegrationConfig = $container->getParameter(ModeraMJRThemeIntegrationExtension::CONFIG_KEY);
        $this->themeIntegrationConfig = $themeIntegrationConfig;
    }

    public function getItems(): array
    {
        return [
            [
                'order' => PHP_INT_MIN + 10,
                'resource' => $this->themeIntegrationConfig['theme_path'].'/build/modera-theme.js',
            ],
        ];
    }
}
