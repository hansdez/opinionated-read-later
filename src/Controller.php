<?php

namespace Hdz\ReadLater;

class Controller
{
    public static function home()
    {
        \Hdz\ReadLater\Security::checkLogin();
        $links = \Hdz\ReadLater\Link::getUnexpired();
        \Flight::render('unexpired', array('links' => $links), 'links_content');
        \Flight::render('home', array(), 'body_content');
        \Flight::render('layout', array('title' => $_ENV['HOME_NAME']));
    }

    public static function read()
    {
        \Hdz\ReadLater\Security::checkLogin();
        $links = \Hdz\ReadLater\Link::getRead();
        \Flight::render('read', array('links' => $links), 'links_content');
        \Flight::render('home', array(), 'body_content');
        \Flight::render('layout', array('title' => 'Read &bull; ' . $_ENV['HOME_NAME']));
    }

    public static function expired()
    {
        \Hdz\ReadLater\Security::checkLogin();
        $links = \Hdz\ReadLater\Link::getExpired();
        \Flight::render('expired', array('links' => $links), 'links_content');
        \Flight::render('home', array(), 'body_content');
        \Flight::render('layout', array('title' => 'Expired &bull; ' . $_ENV['HOME_NAME']));
    }

    public static function added($id)
    {
        \Hdz\ReadLater\Security::checkLogin();
        $link = new \Hdz\ReadLater\Link($id);
        \Flight::render('added', array('link' => $link), 'links_content');
        \Flight::render('home', array(), 'body_content');
        \Flight::render('layout', array('title' => 'Added: ' . $link->title  . ' &bull; ' . $_ENV['HOME_NAME']));
    }

    public static function login()
    {
        if (\Hdz\ReadLater\Security::isLoggedIn()) {
            // Already logged in so redirect to the front-page
            \Flight::redirect('/');
        }
        // Check if POST contains password
        if(!empty($_POST['password'])) {
            if(\Hdz\ReadLater\Security::logIn($_POST['password'])) {
                \Flight::redirect('/');
            }
        }
        // Still not logged in, so show the form
        \Flight::render('login', array(), 'body_content');
        \Flight::render('layout', array('title' => 'Log in'));
    }

    public static function logout()
    {
        \Hdz\ReadLater\Security::logOut();
        \Flight::redirect('/login');
    }

    public static function addLink()
    {
        \Hdz\ReadLater\Security::checkLogin();
        if (empty($_GET['url'])) {
            die("There should be a GET variable named 'url', it is missing.");
        } else {
            $url = $_GET['url'];
            if (!empty($_GET['title'])) {
                $title = $_GET['title'];
                $id = \Hdz\ReadLater\Link::addNewLink($url, $title);
            } else {
                $id = \Hdz\ReadLater\Link::addNewLink($url);
            }
        }
        \Flight::redirect('/added/' . $id);
    }

    public static function snoozeLink($id)
    {
        \Hdz\ReadLater\Security::checkLogin();
        $link = new \Hdz\ReadLater\Link($id);
        $link->snooze();
        \Flight::redirect('/');
    }

    public static function expireLink($id)
    {
        \Hdz\ReadLater\Security::checkLogin();
        $link = new \Hdz\ReadLater\Link($id);
        $link->expire();
        \Flight::redirect('/');
    }

    public static function rereadLink($id)
    {
        \Hdz\ReadLater\Security::checkLogin();
        $link = new \Hdz\ReadLater\Link($id);
        $link->markUnread();
        \Flight::redirect('/');
    }

    public static function unexpireLink($id)
    {
        \Hdz\ReadLater\Security::checkLogin();
        $link = new \Hdz\ReadLater\Link($id);
        $link->unexpire();
        \Flight::redirect('/');
    }

    public static function deleteLink($id, $type)
    {
        \Hdz\ReadLater\Security::checkLogin();
        $link = new \Hdz\ReadLater\Link($id);
        $link->delete();
        switch ($type) {
            case 'read':
                \Flight::redirect('/read');
                break;
            case 'expired':
                \Flight::redirect('/expired');
                break;
            default:
                \Flight::redirect('/');
                break;
        }
    }

    public static function redirectToLink($id)
    {
        \Hdz\ReadLater\Security::checkLogin();
        $link = new \Hdz\ReadLater\Link($id);
        $link->markRead();
        \Flight::redirect($link->url);
    }

    public static function snoozeAll()
    {
        \Hdz\ReadLater\Security::checkLogin();
        // Check if there is POST variable with days
        if (!empty($_POST['date'])) {
            // Make the day start at 9:00 AM
            $endOfSnooze = strtotime($_POST['date']) + 32400;
        }
        if (!empty($endOfSnooze) && $endOfSnooze > time()) {
            // It lies in the future, so do the mass snooze
            \Hdz\ReadLater\Link::snoozeAll($endOfSnooze);
            \Flight::redirect('/');
        } else {
            // Show the form
            \Flight::render('snoozeall', array(), 'body_content');
            \Flight::render('layout', array('title' => 'Mass snooze'));
        }
    }

}
