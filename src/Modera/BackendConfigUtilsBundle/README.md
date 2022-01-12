# ModeraBackendConfigUtilsBundle

Bundle provides tools that simplify contributing your own configuration sections to "Backend/Tools/Settings" section.

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/backend-config-utils-bundle:4.x-dev
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
    Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle::class => ['all' => true],
];
```

## Documentation

For a example how to contribute a settings page using tools provided by this bundle please take a look at
[SettingsSectionsProvider](https://github.com/modera/ModeraBackendGoogleAnalyticsConfigBundle/blob/master/Contributions/SettingsSectionsProvider.php)
from ModeraBackendGoogleAnalyticsConfigBundle, this is how it is going to look in UI:

![Settings page from ModeraBackendGoogleAnalyticsConfigBundle](Resources/screenshots/ModeraBackendConfigUtilsBundle.png)

For more details regarding available configuration properties for in-place editor fields available for configuration
grid take a look at [PropertiesGrid.js](Resources/public/js/view/PropertiesGrid.js).

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
