# ModeraDynamicallyConfigurableMJRBundle

Given that `ModeraBackendToolsSettingsConfigBundle` is installed this bundle provides integration tools that make it possible
to configure dynamically through UIs in "Backend / Tools / Settings / General" the following aspects of backend:

 * Site name
 * Site URL
 * Home section

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/dynamically-configurable-mjr-bundle:4.x-dev
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
    Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle::class => ['all' => true],
];
```

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
