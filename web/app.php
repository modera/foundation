<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Modera\DynamicallyConfigurableAppBundle\KernelConfig;

$loader = require __DIR__.'/../app/autoload.php';

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.

$apcLoader = new ApcClassLoader(sha1(__FILE__), $loader);
$loader->unregister();
$apcLoader->register(true);

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$mode = KernelConfig::read();
if ($mode['debug']) {
    Debug::enable();
}

$kernel = new AppKernel($mode['env'], $mode['debug']);
//$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
