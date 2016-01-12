<?php
// Load environment if not found
if (!getenv('APP_ENV') && is_file('/var/www/app/app/config/env')) {
    $variables = [];
    $file = array_map('rtrim', file('/var/www/app/app/config/env'));
    foreach($file as $line) {
        list($key, $value) = explode('=', $line, 2);
        $variables[$key] = $value;
    }
} else {
    $variables = $_ENV;
}
// By default, symfony won't overwrite parameters from environment if they're already set in config. This always uses environment if found
foreach($variables as $key => $value) {
    if (0 !== strpos($key, 'SYMFONY__')) {
        continue;
    }

    // The conversion applied here is copied from Symfony\Component\HttpKernel::getEnvParameters
    $container->setParameter(strtolower(str_replace('__', '.', substr($key, 9))), $value);
}
