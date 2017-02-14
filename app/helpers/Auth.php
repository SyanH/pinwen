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
        $user = $this->db->get('user', ['uid', 'username', 'email', 'nickname', 'role', 'password'], [$selectField => $name]);

        if (false === $user) {
            return false;
        }

        $hashValidate = password_verify($password, $user['password']);
        if ($user && $hashValidate) {
            $authCode = function_exists('openssl_random_pseudo_bytes') ?
                bin2hex(openssl_random_pseudo_bytes(16)) : sha1(Helper::randString(20));

            $appName = $this->container->get('configs')['name'];
            $appKey = $this->container->get('configs')['key'];

            setcookie('__' . $appName . '_uid', $user['uid'], $expire, '/', '', false, true);

            $hash = base64_encode(Helper::hash($authCode) . '|' . $appKey);
            setcookie('__' . $appName . '_authCode', $hash, $expire, '/', '', false, true);

            unset($user['password']);

            $this->db->update('user', ['logintime' => time(), 'authcode' => $authCode], ['uid' => $user['uid']]);
            return $user;
        }

        return false;
    }

    public function logout()
    {
        $appName = $this->container->get('configs')['name'];
        setcookie('__' . $appName . '_uid', '', -1, '/', '', false, true);
        setcookie('__' . $appName . '_authCode', '', -1, '/', '', false, true);
    }

    public function hasLogin()
    {
        $appName = $this->container->get('configs')['name'];
        $appKey = $this->container->get('configs')['key'];

        $cookieUid = $_COOKIE['__' . $appName . '_uid'];
        $cookieAuthCode = $_COOKIE['__' . $appName . '_authCode'];

        if (null === $cookieUid || null === $cookieAuthCode) {
            return false;
        } else {
            $code = explode('|', base64_decode($cookieAuthCode), 2);
            if (count($code) !== 2 || $code[1] !== $appKey) {
                return false;
            }
            $user = $this->db->get('user', '*', ['uid' => intval($cookieUid)]);
            if ($user && Helper::hashValidate($user['authcode'], $code[0])) {
                unset($user['password']);
                return $user;
            }
            $this->logout();
        }
        return false;
    }

    public function getUser($key = null)
    {
        $user = $this->hasLogin();
        if (false !== $user) {
            if (null !== $key && isset($user[$key])) {
                return $user[$key];
            }
            return $user;
        }
        return null;
    }

    public function pass($role)
    {
        $user = $this->hasLogin();
        if (self::hasLogin()) {
            $userRole = self::getUser('role');
            if (array_key_exists($role, self::$role) && self::$role[$userRole] <= self::$role[$role]) {
                return true;
            }
        }
        return false;
    }
}