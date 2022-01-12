# ModeraBackendToolsSettingsBundle

Provides a unified way of  exposing sections that would allow to configure your modules. This bundle contributes
a section to "Backend / Tools" called "Settings".

See `Modera\BackendToolsSettingsBundle\ModeraBackendToolsSettingsBundle` for a list of exposed extension points.

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/backend-tools-settings-bundle:4.x-dev
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

### Step 2: Enable the Bundle

This bundle should be automatically enabled by [Flex](https://symfony.com/doc/current/setup/flex.html).
In case you don't use Flex, you'll need to manually enable the bundle by
adding the following line in the `config/bundles.php` file of your project:

``` php
<?php
// config/bundles.php

return [
    // ...
    Modera\BackendToolsSettingsBundle\ModeraBackendToolsSettingsBundle::class => ['all' => true],
];
```

## How to contribute your own settings section

In order to just contribute a section ( an activity ) to Settings section you need to create a provider class
which would return instances of `Modera\BackendToolsSettingsBundle\Section\SectionInterface`. This is an example
how to contributor class could look like:

``` php
<?php

namespace MyCompany\BlogBundle\Contributions;

use Modera\BackendToolsSettingsBundle\Section\StandardSection;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Sli\ExpanderBundle\Ext\ContributorInterface;

class SettingsSectionsProvider implements ContributorInterface
{
    /**
     * @inheritDoc
     */
    public function getItems()
    {
        return array(
            new StandardSection(
                'blog',
                'Blog',
                'Modera.backend.configutils.runtime.SettingsListActivity',
                FontAwesome::resolve('cog', 'fas'),
                array('category' => 'blog')
            )
        );
    }
}
```

Once you have created a class you need to register it in service container with tag `modera_backend_tools_settings.contributions.sections_provider`.

``` xml
<services>
    <service id="mycompany_blog.contributions.settings_sections_provider"
             class="MyCompany\BlogBundle\Contributions\SettingsSectionsProvider">

        <tag name="modera_backend_tools_settings.contributions.sections_provider" />
    </service>
</services>
```

Now if you go to "Backend / Tools / Settings" you should see a section there with name "Blog", it url it will be
named as "blog", icon will be "gear" ( see FontAwesome library ) and `Modera.backend.dcab.runtime.SettingsListActivity`
javascript activity will be used to create its UI.

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
