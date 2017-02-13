<?php

namespace app\helpers;

class Logger
{

    private $path;

    public function __construct($path)
    {
        $this->path = rtrim($path, '/');
    }

    public function __call($name, array $args)
    {
        $message = $args[0];
        $log = date('c') . ' - ';
        if (is_array($message)) {
            foreach ($message as $key => $val) {
                $log .= ' [' . $key . ':' . $val . ']';
            }
        } else {
            $log .= ' ' . $message;
        }
        error_log($log . "\n", 3, $this->path . '/' . $name . '_' . gmdate('Y_m_d') . '.log');
    }
}