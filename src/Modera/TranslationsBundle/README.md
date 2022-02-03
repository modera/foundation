# ModeraTranslationsBundle 

Bundle defines architecture and provides utilities which make process of translating your web-site to many languages
an easier process.

Bundle's has been developed to simplify a process of localizing your bundles, to achieve that it does several things:

 * Defines an extensible architecture that can be used to describe what type of files must be scanned to extract
 translation tokens, at the moment we have support for these: twig templates, php files.
 * When tokens are extracted that can be published to a database. You can use your favorite toolkit to translate them
 ( some CRUD generator, for example )
 * When tokens have been translated you can use `modera:translations:compile` command to take the tokens from database
 and compile them back to physical files that Symfony can work with.

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/translations-bundle:4.x-dev
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
    Modera\LanguagesBundle\ModeraLanguagesBundle::class => ['all' => true], // if you still don't have it
    Modera\TranslationsBundle\ModeraTranslationsBundle::class => ['all' => true],
];
```

### Step3: Updated database

``` bash
bin/console doctrine:schema:update --force
```

## Documentation

Bundles ships two command line tasks:

### modera:translations:import

This task as you are already probably guessed is used to extract tokens from your files to database. In order
for this task to understand what files it should extract tokens from you need to register so called 'translations
handlers'. At the moment of writing we had support for two type of handlers: "twig templates" and "php files".

To inform the task that a bundle's twig templates ( Resources/views directory ) must be scanned you need to use a
service definition akin to the following:

``` xml
<!-- YourBundleName/Resources/config/services.xml -->
<services>
    <service parent="modera_translations.handling.template_translation_handler">

        <argument>YourBundleName</argument>

        <tag name="modera_translations.translation_handler" />
    </service>
</services>
```

If you also want to have your bundle's PHP files to be scanned then can use something similar to this:

``` xml
<!-- YourBundleName/Resources/config/services.xml -->
<services>
    <service parent="modera_translations.handling.php_classes_translation_handler">

        <argument>YourBundleName</argument>

        <tag name="modera_translations.translation_handler" />
    </service>
</services>
```

Now if you run `modera:translations:import` tasks then both twig templates and all *.php files located inside the
`YourBundleName` will be scanned, tokens extracted and finally added to database.

When it comes to working with localization in twig templates there's nothing new, you just use `trans` twig filters 
provided by Symfony, but situation gets more interesting when you need to localize your php code though.
Natively, you can use `translator` service, but before you can use it you need to inject it to your services and even
when you injected it Symfony still won't be able to detect and extract tokens from it, to solve this problem we have
created an implementation of a standard Symfony ExtractorInterface -
`Modera\TranslationsBundle\TokenExtraction\PhpClassTokenExtractor`. This class is able to statically analyze your PHP
files and extract tokens from it, to make code analysis more bullet-proof we decided to introduce a helper that
you should use to translate your messages and designate them as translations tokens - `Modera\FoundationBundle\Translation\T`.
This class provides static method - `trans`, its purpose and method signatures are mirrored
to standard Symfony's `Symfony\Contracts\Translation\TranslatorInterface`. You can use this method to translate
your messages without having to import translator service to your services beforehand. You may be wondering how
these methods work - essentially, when ModeraTranslationsBundle is bootstrapped by Symfony, the bundle will inject
translator service to `T` class and latter will use it to translate your messages. Probably you already have another
question - how do I test my services in unit tests if they rely on a service coming from dependency injection container ?
The answer is simple - you test your classes the same way as you always did, `T` is smart enough and when it is executed
without having access to automatically injected `translator` service it will just act as proxy class without actually
translating your messages. Here a few examples how you can use `T` service:

``` php
<?php

use Modera\TranslationsBundle\Helper\T;

T::trans('Hello');

T::trans('Hello, %name%', array('%name%' => $name'), 'greetings');

$domain = 'examples';
$longMessage = 'This way of defining long translation ';
$longMessage.= 'messages can be used.';
$longMessage.= 'For more details please see docblock for T class.';

T::trans($longMessage, array(), $domain);
```

Now when you run `modera:translations:import` command these tokens will be extracted:

 * Hello
 * Hello, %name%
 * This way of defining long translation messages can be used. For more details please see docblock for T class.

### modera:translations:compile

Once translation tokens have been extracted to database, translated you probably will want to compile them back
to physical files so Symfony translation mechanism could detect them and use when translating messages. For this
to happen you need to run `modera:translations:compile` tasks. During execution this task will compile all translation
tokens from database to root `Resources` directory of your application.

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
