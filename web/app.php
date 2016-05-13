<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Modera\DynamicallyConfigurableAppBundle\KernelConfig;

$loader = require __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/bootstrap.php.cache';

$mode = KernelConfig::read();

if ($mode['debug']) {
    Debug::enable();
}

$kernel = new AppKernel($mode['env'], $mode['debug']);

// http://symfony.com/doc/current/cookbook/debugging.html
if (!$mode['debug']) {
    $kernel->loadClassCache();
}
// http://symfony.com/doc/current/book/http_cache.html
//$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
