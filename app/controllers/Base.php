<?php

namespace app\controllers;

use Interop\Container\ContainerInterface as Container;

class Base
{
    protected $container;

    /**
     * Base constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->container->get($name);
    }

}