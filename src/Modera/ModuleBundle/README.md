# ModeraModuleBundle

Makes it possible to dynamically install and inject new bundles to your AppKernel class when a new bundle
is installed by composer. To make your bundle susceptible to automatic kernel installation please add similar lines
to your `composer.json` file:

``` json
{
    "extra": {
        "modera-module": {
            "register-bundle": "MyCompany\\HelloWorldBundle\\MyCompanyHelloWorldBundle"
        }
    }
}
```

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/module-bundle:4.x-dev
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
    Modera\ModuleBundle\ModeraModuleBundle::class => ['all' => true],
];
```

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
