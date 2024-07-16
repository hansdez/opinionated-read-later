<?php

namespace Hdz\ReadLater;

class Apicontroller
{

    public static function addLink()
    {
        // Check if the request is authenticated
        if (empty($_GET['key']) || $_GET['key'] != $_ENV['API_KEY']) { 
            // Not authenticated
            \Flight::json(array('error' => "The API key wasn't added or is incorrect"), 403);
        } else {
            // Authentication is fine, not check if there is a URL to add
            if (empty($_GET['url']) || filter_var(urldecode($_GET['url']), FILTER_VALIDATE_URL) == false || substr($_GET['url'], 0, 4) != 'http') {
                // No proper URL given
                \Flight::json(array('error' => "No correct URL was given to add"), 400);
            } else {
                // Add the URL and return the data for it
                $id = \Hdz\ReadLater\Link::addNewLink($_GET['url']);
                $link = new \Hdz\ReadLater\Link($id);
                \Flight::json($link);
            }
        }
    }

    public static function showReadLater()
    {
        // Check if the request is authenticated
        if (empty($_GET['key']) || $_GET['key'] != $_ENV['API_KEY']) { 
            // Not authenticated
            \Flight::json(array('error' => "The API key wasn't added or is incorrect"), 403);
        } else {
            $links = \Hdz\ReadLater\Link::getUnexpired();
            \Flight::json($links);
        }
    }

    public static function showToReadNow()
    {
        // Check if the request is authenticated
        if (empty($_GET['key']) || $_GET['key'] != $_ENV['API_KEY']) { 
            // Not authenticated
            \Flight::json(array('error' => "The API key wasn't added or is incorrect"), 403);
        } else {
            $links = \Hdz\ReadLater\Link::getUnexpired();
            $link = array_shift($links);
            \Flight::json($link);
        }
    }

    public static function showRead()
    {
        // Check if the request is authenticated
        if (empty($_GET['key']) || $_GET['key'] != $_ENV['API_KEY']) { 
            // Not authenticated
            \Flight::json(array('error' => "The API key wasn't added or is incorrect"), 403);
        } else {
            $links = \Hdz\ReadLater\Link::getRead();
            \Flight::json($links);
        }
    }

    public static function showExpired()
    {
        // Check if the request is authenticated
        if (empty($_GET['key']) || $_GET['key'] != $_ENV['API_KEY']) { 
            // Not authenticated
            \Flight::json(array('error' => "The API key wasn't added or is incorrect"), 403);
        } else {
            $links = \Hdz\ReadLater\Link::getExpired();
            \Flight::json($links);
        }
    }

}
