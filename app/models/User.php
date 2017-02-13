<?php

namespace app\models;

class User
{
    public static function get($uid)
    {
        return app('db')->get('user', '*', ['uid' => $uid]);
    }
}