<?php

namespace app\controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use app\models\User;

class Index extends Base
{

    public function home(Request $request, Response $response, $args)
    {
        //$user = User::get(1);
        //echo $this->view->render('admin', ['user' => $user]);
        //return $response->write('hahah');
        echo 'sss';
    }

}