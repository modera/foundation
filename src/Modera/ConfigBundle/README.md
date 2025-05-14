# ModeraConfigBundle

Bundles provides tools that allow to you to dynamically store and fetch your configuration properties in a flexible way.
You can store any type of configuration property - your configuration property can store both simple values (like string,
integers, arrays) or complex ones - like objects or references to entities, this is achieved by using so called
"Handlers" (implementations of `\Modera\ConfigBundle\Config\HandlerInterface`).

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/config-bundle:6.x-dev
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
    Modera\ConfigBundle\ModeraConfigBundle::class => ['all' => true],
];
```

## Publishing configuration properties

Before you can use your configuration properties you need to publish them. Publishing process consists of several steps:

1. Create a provider class.
2. Register your provides class in service container with "modera_config.config_entries_provider" tag.
3. Use `modera:config:install-config-entries` command to publish exposed configuration entries.

This is how a simple provider class could look like:

``` php
<?php

namespace MyCompany\SiteBundle\Contributions;

use Modera\ConfigBundle\Config\ConfigurationEntryDefinition as CED;
use Modera\ConfigBundle\Config\EntityRepositoryHandler;
use Modera\ExpanderBundle\Ext\ContributorInterface;

class ConfigEntriesProvider implements ContributorInterface
{
    public function __construct(
        private readony EntityManager $em,
    ) {
    }

    public function getItems(): array
    {
        $serverConfig = [
            'id' => EntityRepositoryHandler::class,
        ];

        $admin = $this->em->find('MyCompany\SecurityBundle\Entity\User', 1);

        return [
            new CED('admin_user', 'Site administrator', $admin),
        ];
    }
}
```

Once you have a class you need to register it in a service container:

``` xml
<services>
    <service id="MyCompany\SiteBundle\Contributions\ConfigEntriesProvider">
        <tag name="modera_config.config_entries_provider" />
    </service>
</services>
```

Now we can use `modera:config:install-config-entries` to publish our configuration property.

## Fetching configuration properties

In order to fetch a configuration property in your application code you need to use
`Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface` service.

``` php
<?php

/** @var \Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface $service */
$service = $container->get(\Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface);

/** @var \Modera\ConfigBundle\Config\ConfigurationEntryInterface $entry */
$entry = $service->findOneByNameOrDie('admin_user');

// will yield "MyCompany\SecurityBundle\Entity\User"
echo \get_class($property->getValue());
```

## Twig integration

The bundle also provides integration with Twig that allow you to fetch configuration properties' values from your
template. For this you will want to use `modera_config_value` function:

``` twig
{{ modera_config_value("my_property_name") }}
```

This will print value for "my_property_name" configuration property. By default if no given configuration property
is found then exception is thrown but you can change this behaviour by passing FALSE as second argument to the function
and in this case NULL be returned instead of throwing an exception.

As you will read later in this document the bundle also has support for associating configuration entries with users. To
fetch a user specific configuration property from a template use `modera_config_owner_value`, for example:

``` twig
{{ modera_config_value("my_property_name", app.user) }}
```

## Handlers

By default the bundle is capable of storing these types of values:

* string
* text
* float
* array
* boolean
* references to entities

If you need to store some more complex values then you need to implement `\Modera\ConfigBundle\Config\HandlerInterface`
interface. Please see already shipped implementations (`\Modera\ConfigBundle\Config\EntityRepositoryHandler`,
for example) to see how you can create your own handlers.

## Creating user related configuration entries

Sometimes you may want to store configuration entries which are not related to the system as a whole but instead
to one single user, for example - user's preferred admin panel language. To achieve this you need to use
`modera_config/owner_entity` semantic configuration key to specify a fully qualified name of user entity. For example:

``` yaml
modera_config:
    owner_entity: 'Modera\SecurityBundle\Entity\User'
```

Once `owner_entity` is configured don't forget to update your database schema by running `doctrine:schema:update --force`.

Now that we have proper configuration in place and database schema has been updated when creating new configuration
entries you can specify "owner", for example:

``` php
<?php

$bob = new \Modera\SecurityBundle\Entity\User();
// ... configure and persist $bob

$ce = new ConfigurationEntry();
$ce->setOwner($bob);

$manager->save($ce);
```

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
