<?php
/**
 *  middlewares
 */
$app->add(new \app\middlewares\UrlUnified());
$app->add($container->get('csrf'));

/**
 *  router
 */
$app->get('/', function() {
    echo $this->view->render('home');
})->setName('index');

$app->get('/admin', \app\controllers\Index::class . ':home')->setName('admin.index');