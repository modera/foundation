# ModeraDirectBundle

ModeraDirectBundle is an implementation of ExtDirect specification to Symfony framework.

## Installation

### Step 1: Download the Bundle

``` bash
composer require modera/direct-bundle:6.x-dev
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
    Modera\DirectBundle\ModeraDirectBundle::class => ['all' => true],
];
```

### Step 3: Add routing

``` yaml
// config/routes.yaml

direct:
    resource: '@ModeraDirectBundle/Resources/config/routing.yaml'
```

## How to use

### Add the ExtDirect API into your page

If you is using Twig engine, only add the follow line in your views page at the
script section:

``` html
<script type="text/javascript" src="{{ url('api') }}"></script>
```

Or if you are not using a template engine:

``` html
<script type="text/javascript" src="/api.js"></script>
```

### Expose your controller methods to ExtDirect Api

Mark the actions to expose with PHP attributes from `Modera\DirectBundle\Attribute`:

``` php
// .../Acme/DemoBundle/Controller/ExampleController.php

namespace Acme\DemoBundle\Controller;

use Modera\DirectBundle\Attribute\Form;
use Modera\DirectBundle\Attribute\Remote;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExampleController extends AbstractController
{
    /**
     * @param array<string, mixed> $params
     */
    #[Remote] // exposes the method to the API
    public function indexAction(array $params): string
    {
        return 'Hello '.$params['name'];
    }

    /**
     * @param array<string, mixed> $params Form submitted values
     * @param array<mixed>         $files  Uploaded files like $_FILES
     */
    #[Remote]
    #[Form] // exposes the method to the API with the formHandler option
    public function testFormAction(array $params, array $files): bool
    {
        // your processing
        return true;
    }
}
```

| Attribute | Docblock tag | Effect |
|---|---|---|
| `#[Remote]` | `@Remote` | Exposes the action in the `Actions.*` API |
| `#[Form]` | `@Form` | Marks the action as a form handler (form values and uploaded files) |
| `#[Secure]` | `@Secure` | Requires a session to call the action |
| `#[Anonymous]` | `@Anonymous` | Allows the call without a session, also when `direct.api.default_access` is `secure` |

The docblock tags still work, so existing controllers need no change; prefer the attributes in new code.
A method is marked when it has the attribute or the tag.

### Call the exposed methods from JavaScript

``` js
// 'AcmeDemo' is the Bundle name without 'Bundle'
// 'Example' is the Controller name without 'Controller'
// 'index' is the method name without 'Action'
Actions.AcmeDemo_Example.index({ name: 'ExtDirect' }, function(r) {
   alert(r);
});
```

## Licensing

This bundle is under the MIT license. See the complete license in the bundle:
Resources/meta/LICENSE
