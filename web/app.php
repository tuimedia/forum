<?php
umask(0001);

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

// Use APC for autoloading to improve performance (if available)
// Change 'forum' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.
if (function_exists('apc_store')) {
    $apcLoader = new ApcClassLoader('forum', $loader);
    $loader->unregister();
    $apcLoader->register(true);
}

require_once __DIR__.'/../app/AppKernel.php';

if (getenv('APP_ENV') === 'dev') {
    Debug::enable();
    $kernel = new AppKernel('dev', true);
    $kernel->loadClassCache();
} else {
    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();
}

// When using the HttpCache', you need to call the method in your front controller instead of relying on the configuration parameter
Request::enableHttpMethodParameterOverride();
$stack = (new Stack\Builder())
    ->push('Asm89\Stack\Cors', [
        'allowedHeaders'      => ['*'],
        'allowedMethods'      => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
        'allowedOrigins'      => ['*'],
        'exposedHeaders'      => false,
        'maxAge'              => 3600,
        'supportsCredentials' => true,
    ])
    ->push('AppBundle\Http\JsonRequestKernel')
;

$kernel = $stack->resolve($kernel);

Stack\Run($kernel);
