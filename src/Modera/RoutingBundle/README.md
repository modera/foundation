# ModeraRoutingBundle

This bundle makes it possible for bundles to dynamically include routing files so you don't need to manually register
them in root `app/config/routing.yml` file.

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/routing-bundle:5.x-dev
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
    Modera\ExpanderBundle\ModeraExpanderBundle::class => ['all' => true], // if you still don't have it
    Modera\RoutingBundle\ModeraRoutingBundle::class => ['all' => true],
];
```

### Step 3: Add routing

``` yaml
// config/routes.yaml

_modera_routing:
    resource: "@ModeraRoutingBundle/Resources/config/routing.yml"
```

## Documentation

Internally `ModeraRoutingBundle` relies on `ModeraExpanderBundle` to leverage a consistent approach to creating extension
points. Shortly speaking, in order for a bundle to contribute routing resources it has to do two things:

 1. Create a contributor class which implements \Modera\ExpanderBundle\Ext\ContributorInterface
 2. Register it in a service container with tag `modera_routing.routing_resources_provider`.

This is how your contributor class may look like:

``` php
<?php

namespace Modera\ExampleBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

class RoutingResourcesProvider implements ContributorInterface
{
    public function getItems()
    {
        return array(
            '@ModeraExampleBundle/Resources/config/routing.yml'
        );
    }
}
```

And here we have its service container definition:

``` xml
<services>
    <service id="modera_example.contributions.routing_resources_provider"
             class="Modera\ExampleBundle\Contributions\RoutingResourcesProvider">

        <tag name="modera_routing.routing_resources_provider" />
    </service>
</services>
```

Since version v1.1 a simplified way of contributing new routing resources has been added (which
doesn't require adding intermediate files). Instead of having getItems() method return a path
to a routing file you can now return normalized file's content:

``` php
<?php

class RoutingResourcesProvider implements ContributorInterface
{
    public function getItems()
    {
        return array(
            array(
                'resource' => '@ModeraExampleBundle/Controller/DefaultController.php',
                'type' => 'annotation',
            ),
        );
    }
}
```

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
