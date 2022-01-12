# ModeraLanguagesBundle

Bundle provide set of basic utilities that allow you to define your site languages configuration in a config file and
then have it synchronized with database so you can establish database relations between languages and some other
entities that your project has.

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/languages-bundle:4.x-dev
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
    Modera\LanguagesBundle\ModeraLanguagesBundle::class => ['all' => true],
];
```

### Step3: Add config

This is a sample configuration:

``` yaml
// app/config/config.yml

modera_languages:
    - { locale: en }
    - { locale: ru, is_enabled: false }
    - { locale: et }
```

Later if you remove a language from `modera_languages` and run `modera:languages:config-sync` command then a database
record which corresponded to a deleted from a config file language will be marked as `isEnabled = false`.

### Step4: Create schema

``` bash
bin/console doctrine:schema:update --force
```

### Step5: Synchronize languages config with database.

``` bash
bin/console modera:languages:config-sync
```

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
