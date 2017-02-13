<?php

$container = $app->getContainer();

/**
 * set timezone
 */
date_default_timezone_set($container->get('configs')['timezone']);

/**
 * @param Interop\Container\ContainerInterface $c
 * @return \app\helpers\handlers\Error
 */
$container['errorHandler'] = function ($c) {
    return new \app\helpers\handlers\Error($c);
};

/**
 * @param Interop\Container\ContainerInterface $c
 * @return \app\helpers\handlers\PhpError
 */
$container['phpErrorHandler'] = function ($c) {
    return new \app\helpers\handlers\PhpError($c);
};

/**
 * @param Interop\Container\ContainerInterface $c
 * @return \app\helpers\handlers\NotFound
 */
$container['notFoundHandler'] = function ($c) {
    return new \app\helpers\handlers\NotFound($c);
};

/**
 * @param Interop\Container\ContainerInterface $c
 * @return \app\helpers\handlers\NotAllowed
 */
$container['notAllowedHandler'] = function ($c) {
    return new \app\helpers\handlers\NotAllowed($c);
};

/**
 * add event
 * @return \app\helpers\Event
 */
$container['event'] = function () {
    return new \app\helpers\Event();
};

/**
 * add cache
 * @return \app\helpers\FileCache
 */
$container['cache'] = function () use ($container) {
    $cachePath = $container->get('configs')['cache.path'];
    $prefix = $container->get('configs')['name'];
    return new \app\helpers\FileCache($cachePath, $prefix);
};

/**
 * add logger
 * @return \app\helpers\Logger
 */
$container['logger'] = function () use ($container) {
    $logPath = $container->get('configs')['log.path'];
    return new \app\helpers\Logger($logPath);
};

/**
 * add db
 * @return \Medoo\Medoo
 */
$container['db'] = function () use ($container) {
    $dbConfig = $container->get('configs')['db'];
    return new \Medoo\Medoo($dbConfig);
};

/**
 * add view
 * @return \League\Plates\Engine
 */
$container['view'] = function () use ($container) {
    $view = new \League\Plates\Engine(__DIR__ . '/views');
    $theme = $container->get('configs')['theme'];
    $themePath = __DIR__ . '/../public/themes/' . $theme;
    $view->addFolder('theme', $themePath, true);
    $request = $container->get('request');
    $view->loadExtension(new League\Plates\Extension\URI($request->getRequestTarget()));
    return $view;
};

/**
 * add csrf
 * @return \Slim\Csrf\Guard
 */
$container['csrf'] = function () use ($container) {
    $prefix = $container->get('configs')['name'] . '_csrf';
    return new \Slim\Csrf\Guard($prefix);
};


if (! function_exists('app')) {
    /**
     * @param null $name
     * @return mixed
     */
    function app($name = null)
    {
        global $container;
        if (is_null($name)) {
            return $container;
        }
        return $container->get($name);
    }
}

if (! function_exists('pathFor')) {
    /**
     * @param $name
     * @param array $data
     * @param array $queryParams
     * @return mixed
     */
    function pathFor($name, array $data = [], array $queryParams = [])
    {
        return app('router')->pathFor($name, $data, $queryParams);
    }
}

if (! function_exists('asset')) {
    /**
     * @param $file
     * @param null $dirPath
     * @return string
     */
    function asset($file, $dirPath = null)
    {
        if (is_null($dirPath)) {
            $dirPath = __DIR__ . '/../public/assets';
        }

        $filePath = rtrim($dirPath, '/') . '/' . ltrim($file, '/');
        if (! file_exists($filePath)) {
            throw new \LogicException(
                'Unable to locate the asset "' . $file . '" in the "' . $dirPath . '" directory.'
            );
        }

        $lastUpdated = filemtime($filePath);
        $filePath = str_replace('\\', '/', $filePath);
        $rootPath = str_replace('\\', '/', __DIR__ . '/../public');
        $filePath = str_replace($rootPath, '', $filePath);

        return $filePath . '?v=' . $lastUpdated;
    }
}

if (! function_exists('themeAsset')) {
    /**
     * @param $file
     * @return string
     */
    function themeAsset($file)
    {
        $theme = app('configs')['theme'];
        $themePath = __DIR__ . '/../public/themes/' . $theme;
        return asset($file, $themePath);
    }
}

if (! function_exists('csrfField')) {
    /**
     * @param Psr\Http\Message\ServerRequestInterface $request
     */
    function csrfField($request)
    {
        $nameKey = app('csrf')->getTokenNameKey();
        $valueKey = app('csrf')->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);
        $csrfField = "<input type=\"hidden\" name=\"$nameKey\" value=\"$name\">";
        $csrfField .= "<input type=\"hidden\" name=\"$valueKey\" value=\"$value\">";
        app('view')->addData(['csrfField' => $csrfField]);
    }
}

if (! function_exists('getPostFields')) {
    /**
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    function getPostFields($request)
    {
        $nameKey = app('csrf')->getTokenNameKey();
        $valueKey = app('csrf')->getTokenValueKey();
        $posts = $request->getParsedBody();
        unset($posts[$nameKey], $posts[$valueKey]);
        return $posts;
    }
}
