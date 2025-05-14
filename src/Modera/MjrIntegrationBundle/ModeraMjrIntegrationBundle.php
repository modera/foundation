<?php

namespace Modera\MjrIntegrationBundle;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\MjrIntegrationBundle\DependencyInjection\ConfigProviderAliasingCompilerPass;
use Modera\MjrIntegrationBundle\DependencyInjection\MenuItemContributorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle ships basic utilities which simplify integration of Modera JavaScript runtime ( MJR ).
 *
 * @copyright 2013 Modera Foundation
 */
class ModeraMjrIntegrationBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ConfigProviderAliasingCompilerPass());

        $configMergersProvider = new ExtensionPoint('modera_mjr_integration.config.config_mergers');
        $configMergersProvider->setDescription(
            'Lets to contribute implementations of ConfigMergerInterface that will be used to prepare runtime-config.'
        );
        $configMergersProviderDescription = <<<TEXT
You will need to use this extension point when you need to contribute some additional data to so called 'runtime-config'
(a config which is exposed to MJR when it is loaded). This is how a typical contribution could look like:

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\CallbackConfigMerger;

class ConfigMergersProvider implements ContributorInterface
{
    private \$items;

    public function __construct()
    {
        \$this->items = [
            new CallbackConfigMerger(function(array \$currentConfig)  {
                return \array_merge(\$currentConfig, [
                    'php_version' => phpversion(),
                ]);
            })
        ];
    }

    public function getItems(): array
    {
        return \$this->items;
    }
}
TEXT;
        $configMergersProvider->setDetailedDescription($configMergersProviderDescription);
        $container->addCompilerPass($configMergersProvider->createCompilerPass());

        $menuItemsProvider = new ExtensionPoint('modera_mjr_integration.menu.menu_items');
        $menuItemsProvider->setDescription('Allows to contribute sections that will be displayed in backend menu.');
        $menuItemsDescription = <<<TEXT
This extension point allows you to contribute new menu items to backend section. When you contribute a new section you
also have an option to define a namespace/path mapping that will be used to configure extjs class loader. Typical
menu item contribution could look like this:

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Menu\MenuItem;
use Modera\MjrIntegrationBundle\Menu\MenuItemInterface;
use Modera\MjrIntegrationBundle\Model\FontAwesome;

class MenuItemsProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            new MenuItem('Welcome', 'App.welcome.runtime.Section', 'welcome', [
                MenuItemInterface::META_NAMESPACE => 'App.welcome',
                MenuItemInterface::META_NAMESPACE_PATH => '/app/welcome/js',
            ], FontAwesome::resolve('symfony', 'fab')),
        ];
    }
}
TEXT;
        $menuItemsProvider->setDetailedDescription($menuItemsDescription);
        $container->addCompilerPass($menuItemsProvider->createCompilerPass());

        $serviceDefinitionsProvider = new ExtensionPoint('modera_mjr_integration.csdi.service_definitions');
        $serviceDefinitionsProvider->setDescription('Allows to contribute client side dependency injection container services.');
        $serviceDefinitionsProviderDescription = <<<TEXT
This extension point can be used when you need to expose services to client-side dependency injection container.
It is important to mention that services contributed using this extension point will be exposed to client-side
event before user is authenticated yet. If you need to expose services only when user has authenticated, then use
"modera_mjr_security_integration.client_di_service_defs" extension-point instead. A typical contribution could look like
this:

use Modera\ExpanderBundle\Ext\ContributorInterface;

class ClientDiServiceDefinitionsProvider implements ContributorInterface
{
    // override
    public function getItems(): array
    {
        return [
            'app.welcome.window_contributor' => [
                'className' => 'App.welcome.runtime.WindowContributor',
                'args' => ['@application'],
                'tags' => ['shared_activities_provider'],
            ],
        ];
    }
}
TEXT;
        $serviceDefinitionsProvider->setDetailedDescription($serviceDefinitionsProviderDescription);
        $container->addCompilerPass($serviceDefinitionsProvider->createCompilerPass());

        $sectionsProvider = new ExtensionPoint('modera_mjr_integration.sections');
        $sectionsProvider->setDescription('Contributes section which will be possible to activate in backend.');
        $sectionProviderDescription = <<<TEXT
Allows to contribute new sections ( implementations of MJR's MF.runtime.Section ). You will want to use this extension-point
when you need a place where you can play with your activities but don't want to contribute a separate menu-item. Optionally
you can configure extjs-class loader. This is how contribution could look like:

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Sections\Section;

class SectionsProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            new Section('tools', 'App.tools.runtime.Section', [
                Section::META_NAMESPACE => 'App.tools',
                Section::META_NAMESPACE_PATH => '/app/tools/js',
            ])
        ];
    }
}
TEXT;
        $sectionsProvider->setDetailedDescription($sectionProviderDescription);
        $container->addCompilerPass($sectionsProvider->createCompilerPass());

        $cssResourcesProvider = new ExtensionPoint('modera_mjr_integration.css_resources');
        $cssResourcesProvider->setDescription('CSS files to include in a page where the runtime will be bootstrapped.');
        $cssResourcesProviderDescription = <<<TEXT
This extension point allow to contribute CSS resources that must be included to backend page when it is loaded. You
will need to use this extension point when you want to add some styling rules to you backend extjs components. This
is how a typical contribution could look like:

use Modera\ExpanderBundle\Ext\ContributorInterface;

class CssResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '/app/tools/css/styles.css',
        ];
    }
}
TEXT;
        $cssResourcesProvider->setDetailedDescription($cssResourcesProviderDescription);
        $container->addCompilerPass($cssResourcesProvider->createCompilerPass());

        $classLoaderMappingsProvider = new ExtensionPoint('modera_mjr_integration.class_loader_mappings');
        $classLoaderMappingsProvider->setDescription('Allows to add backend extjs class loader mappings. ');
        $classLoaderMappingsProviderDescription = <<<TEXT
You will want to use this extension-point when you need to teach backend extjs class loader where from to load
class which belong to a certain namespace. This is how contribution could look like:

use Modera\ExpanderBundle\Ext\ContributorInterface;

class ClassLoaderMappingsProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            'App.settings' => '/app/settings/js',
        ];
    }
}
TEXT;
        $classLoaderMappingsProvider->setDetailedDescription($classLoaderMappingsProviderDescription);
        $container->addCompilerPass($classLoaderMappingsProvider->createCompilerPass());

        $jsResourcesProvider = new ExtensionPoint('modera_mjr_integration.js_resources');
        $jsResourcesProvider->setDescription('Allows to contribute JS resources that will be used in Backend.');
        $jsResourcesProviderDescription = <<<TEXT
You can use this extension-point when you need to contribute javascript files to be loaded when a backend page
is loaded. This is how a typical contribution will look like:'

use Modera\ExpanderBundle\Ext\ContributorInterface;

class JsResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js',
        ];
    }
}
TEXT;
        $jsResourcesProvider->setDetailedDescription($jsResourcesProviderDescription);
        $container->addCompilerPass($jsResourcesProvider->createCompilerPass());

        // allows to contribute menu items by defining them in config file
        $container->addCompilerPass(new MenuItemContributorCompilerPass());

        $bootstrappingClassLoaderMappingsProvider = new ExtensionPoint(
            'modera_mjr_integration.bootstrapping_class_loader_mappings'
        );
        $bootstrappingClassLoaderMappingsProvider->setDescription(
            'Allows to contribute ExtJs classname:path mappings that will be configured before runtime is initialized.'
        );
        $container->addCompilerPass($bootstrappingClassLoaderMappingsProvider->createCompilerPass());

        $helpMenuItemsProvider = new ExtensionPoint('modera_mjr_integration.help_menu_items');
        $helpMenuItemsProvider->setDescription('Allows to contribute items to "Help" menu in backend.');
        $helpMenuItemDetailedDescription = <<<'TEXT'
You can use this extension point to contribute items to a Help menu (usually rendered in backend's header, next to where
username and exit button are located). This is how sample contribution can look like:

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Help\SimpleHelpMenuItem;

class HelpMenuItemsProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            SimpleHelpMenuItem::createUrlAware('Help / Support', 'https://confluence.modera.org'),
            SimpleHelpMenuItem::createActivityAware('About', 'product-about-info'),
        ];
    }
}
TEXT;
        $helpMenuItemsProvider->setDetailedDescription($helpMenuItemDetailedDescription);
        $container->addCompilerPass($helpMenuItemsProvider->createCompilerPass());
    }
}
