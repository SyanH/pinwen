<?php

error_reporting(E_ALL | E_STRICT);

ini_set('display_errors', true);

require __DIR__ . '/../vendor/autoload.php';

session_start();

// framework settings
$settings = [
    'settings' => [
//        'httpVersion' => '1.1',
//        'responseChunkSize' => 4096,
//        'outputBuffering' => 'append',
        'displayErrorDetails' => true,
//        'addContentLengthHeader' => true,
        'determineRouteBeforeAppMiddleware' => false,
//        'routerCacheFile' => false,
    ],
];

$configs = require  __DIR__ . '/../app/configs.php';

$app = new \Slim\App($settings + $configs);

require __DIR__ . '/../app/bootstrap.php';

require __DIR__ . '/../app/routes.php';

$app->run();