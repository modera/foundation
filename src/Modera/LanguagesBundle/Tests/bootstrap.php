<?php

if (!\is_file($loaderFile = __DIR__.'/../vendor/autoload.php')) {
    throw new LogicException('Could not find autoload.php in vendor/. Did you run "composer install --dev"?');
}

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require $loaderFile;

$loader->addPsr4('Modera\LanguagesBundle\Tests\Fixtures\Bundle\\', __DIR__.'/Fixtures/Bundle');
