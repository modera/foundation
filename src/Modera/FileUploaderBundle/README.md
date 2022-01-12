# ModeraFileUploaderBundle

The bundle simplifies and introduces a consistent approach to uploading and storing uploaded files.

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/file-uploader-bundle:4.x-dev
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
    Modera\FileRepositoryBundle\ModeraFileRepositoryBundle::class => ['all' => true], // if you still don't have it
    Modera\FileUploaderBundle\ModeraFileUploaderBundle::class => ['all' => true],
];
```

### Step 3: Add routing

``` yaml
// config/routes.yaml

file_uploader:
    resource: "@ModeraFileUploaderBundle/Resources/config/routing.yml"
```

### Step 4: Enable uploader

``` yaml
// config/packages/modera.yaml

modera_file_uploader:
    is_enabled: true
```

## Documentation

Before you can upload files you need to create a repository that will host them, for instructions please
see [ModeraFileRepositoryBundle](https://github.com/modera/ModeraFileRepositoryBundle).

Once you have a repository configured, from web you can send request with files to uploader gateway URL ( configured by
modera_file_uploader/url configuration property, default value is `uploader-gateway` ) and it will upload them and
put to a configured repository. For example, javascript pseudo code:

``` js
filesForm.submit({
    url: 'uploader-gateway',
    params: {
        _repository: 'my_files'
    }
});
```

Request parameter `_repository` will be used to determine what repository to use to store uploaded files. By default
all repositories are exposed to web and files can be uploaded to them, this feature is controller by `expose_all_repositories`
configuration property.

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
