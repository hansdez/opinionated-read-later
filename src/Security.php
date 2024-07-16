<?php

namespace Hdz\ReadLater;

class Security
{
    public static function logIn($password)
    {
        if ($password == $_ENV['PASSWORD']) {
            $password = password_hash($_ENV['PASSWORD'], PASSWORD_DEFAULT);
            if (strstr(getenv('BASE_URL'), 'https')) {
                setcookie('id', $password, time()+60*60*24*10000, '/', $_SERVER['SERVER_NAME'], true, true);
                return true;
            } else {
                setcookie('id', $password, time()+60*60*24*10000, '/', $_SERVER['SERVER_NAME'], false, true);
                return true;
            }
        } else {
            return false;
        }
    }

    public static function logOut()
    {
        if (strstr(getenv('BASE_URL'), 'https')) {
            setcookie('id', '', time()-90000, '/', $_SERVER['SERVER_NAME'], true, true);
            return true;
        } else {
            setcookie('id', '', time()-90000, '/', $_SERVER['SERVER_NAME'], false, true);
            return true;
        }
    }

    public static function isLoggedIn()
    {
        $password = $_ENV['PASSWORD'];
        if (isset($_COOKIE['id']) && password_verify($password, $_COOKIE['id'])) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkLogin()
    {
        $password = $_ENV['PASSWORD'];
        if (isset($_COOKIE['id']) && password_verify($password, $_COOKIE['id'])) {
            return true;
        } else {
            \Flight::redirect('/login');
        }
    }
}
