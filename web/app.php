<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

$loader = require __DIR__.'/../app/autoload.php';

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.

$apcLoader = new ApcClassLoader(sha1(__FILE__), $loader);
$loader->unregister();
$apcLoader->register(true);

function _mfKernelConfig() {
    $defaultMode = array(
        'env' => 'prod',
        'debug' => false
    );

    $mode = file_get_contents( './../app/kernel.json');

    if (false == $mode) {
        return $defaultMode;
    } else {
        $mode = json_decode($mode, true);
        if (is_array($mode) && isset($mode['env']) && isset($mode['debug'])) {
            return $mode;
        } else {
            return $defaultMode;
        }
    }
}

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$mode = _mfKernelConfig();

if ($mode['env'] == 'dev') {
    ini_set('display_errors', 1);
}


$kernel = new AppKernel($mode['env'], $mode['debug']);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
