<?php

namespace app\helpers;

use Interop\Container\ContainerInterface as Container;

class Auth
{
    protected $container;

    protected $roles;

    protected $db;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->roles = $container->get('configs')['roles'];
        $this->db = $container->get('db');
    }

    public function login($name, $password, $expire = 0)
    {
        $selectField = filter_var($name, \FILTER_VALIDATE_EMAIL) === false ? 'username' : 'email';
        $user = $this->db->get('user', ['uid', 'username', 'email', 'nickname', 'role', 'password'], [$selectField=>$name]);
        if (false === $user) {
            return false;
        }
        $hashValidate = password_verify($password, $user['password']);
        if ($user && $hashValidate) {
            $authCode = function_exists('openssl_random_pseudo_bytes') ?
                bin2hex(openssl_random_pseudo_bytes(16)) : sha1(Helper::randString(20));
            response()->setCookie('__' . app('app.name') . '_uid', $user['uid'], $expire);
            $infoRandString = Helper::randString(10);
            $hash = base64_encode(Helper::hash($authCode) . '|' . $infoRandString . app('key'));
            response()->setCookie('__' . app('app.name') . '_authCode', $hash, $expire);
            unset($user['password']);
            $infoHash = Helper::encode(app('key') . '|' . implode('|', $user), app('key'), true);
            response()->setCookie('__' . app('app.name') . '_' . $infoRandString, $infoHash, $expire);
            db()->update('user', ['logintime' => time(), 'authcode' => $authCode], ['uid' => $user['uid']]);
            return $user;
        }
        return false;
    }
}