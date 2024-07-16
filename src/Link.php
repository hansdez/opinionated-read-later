<?php

namespace Hdz\ReadLater;

class Link
{
    public int $id;
    public string $url;
    public string $title;
    public string $domain;
    public string $addedDate;
    public string $expiryTimestamp;
    public string $expiryString;
    public string $readLink;
    public int $snoozes;
    public string $read;

    public function __construct($id = null)
    {
        $now = time();
        if (isset($id)) {
            $database = \FLight::get('database');
            $result = $database->select('links', [
                'id',
                'url',
                'title',
                'domain',
                'addeddate',
                'expiry (expiryTimestamp)',
                'expiryString' => \Medoo\Medoo::raw(
                    'CASE
                        WHEN expiry-:now < 0 THEN "Expired"
                        WHEN expiry-:now < 600 THEN "minutes"
                        WHEN expiry-:now <= 3600 THEN CAST((expiry-:now)/60 AS INTEGER) || " minutes"
                        WHEN expiry-:now < 7200 THEN "1 hour"
                        WHEN expiry-:now <= 86400 THEN CAST((expiry-:now)/3600 AS INTEGER) || " hours"
                        WHEN expiry-:now < 172800 THEN "1 day"
                        ELSE CAST((expiry-:now)/86400 AS INTEGER) || " days"
                    END'
                ,[
                    ':now' => $now
                ]),
                'snoozes',
                'read'
            ],[
                'id' => $id
            ]);
            if (!empty($result)) {
                $result = $result[0];
                $this->id = $result['id'];
                $this->url = $result['url'];
                $this->title = $result['title'];
                $this->domain = $result['domain'];
                $this->addedDate = $result['addeddate'];
                $this->expiryTimestamp = $result['expiryTimestamp'];
                $this->expiryString = $result['expiryString'];
                $this->readLink = $_ENV['BASE_URL'] . '/link/' . $this->id . '/go';
                $this->snoozes = $result['snoozes'];
                $this->read = $result['read'];
            } else {
                die("This id doesn't seem to exist");
            }
        } else {
            die("The constructor function was called without an ID, that should not be possible");
        }
    }
    
    // Saves a link object to the database
    public function save(): bool
    {
        $database = \FLight::get('database');
        $result = $database->update("links", [
            "url" => $this->url,
            "title" => $this->title,
            "domain" => $this->domain,
            "addeddate" => $this->addedDate,
            "expiry" => $this->expiryTimestamp,
            "snoozes" => $this->snoozes,
            "read" => $this->read
        ],[
            "id" => $this->id
        ]);
        return true;
    }

    // Deletes a link in the database
    public function delete(): bool
    {
        $database = \FLight::get('database');
        $result = $database->delete("links", [
            "id" => $this->id
        ]);
        if ($result->rowCount()) {
            return true;
        } else {
            return false;
        }
    }

    // Inserts a link into the database on the basis of a URL and a title
    // This doesn't check whether the link already exists: no problem to have things twice
    public static function addNewLink(string $url, string $title = ''): int
    {
        // First sanitize the input
        $url = urldecode($url);
        $url = filter_var($url, FILTER_SANITIZE_URL);

        // Get the title and sanitize it
        if (!empty($title)) {
            $title = \Hdz\ReadLater\Link::cleanTitle($title);
        } else {
            $title = \Hdz\ReadLater\Link::getTitle($url);
            $title = \Hdz\ReadLater\Link::cleanTitle($title);
        }

        // Get the domain
        $domain = preg_replace('/^www\./', '', parse_url($url, PHP_URL_HOST));

        // Set the added date
        $addeddate = date(DATE_ISO8601);

        // Start setting the expiry
        $endOfSnooze = \Hdz\ReadLater\Link::getEndOfSnooze();
        if ($endOfSnooze < time()) {
            // No currently active snooze, add days as normal
            $expiry = time() + ($_ENV['DAYS_BEFORE_EXPIRY'] * 86400);
        } else {
            // There is an active snooze, add what is left of it
            $expiry = (time() + ($endOfSnooze - time()) + ($_ENV['DAYS_BEFORE_EXPIRY'] * 86400));
        }
        // Add half a day to align more with people's expectations
        $expiry = $expiry + 43200;
        // Now everything can be added to the database
        $database = \FLight::get('database');
        $result = $database->insert("links", [
            "url" => $url,
            "title" => $title,
            "domain" => $domain,
            "addeddate" => $addeddate,
            "expiry" => $expiry,
            "snoozes" => 0,
            "read" => 0
        ]);

        return $database->id('id');

    }

    public static function getTitle(string $url): string
    {
        // Define an empty title to test against
        $title = '';
        // First try to get a title through downloading the page
        $page = file_get_contents($url);
        if ($page) {
            $matches = array();
            if (preg_match('/<title\b[^>]*>(.*?)<\/title>/i', $page, $matches)) {
                $title = $matches[1];
            }
        }
        if (empty($title)) {
            // Use the non-domain part of the URL as a title
            $title = parse_url($url, PHP_URL_PATH);
            $title = ltrim($title, '/');
            $title = str_replace('/', ' -> ', $title);
            $title = ucfirst($title);
        }
        if (empty($title)) {
            $title = "Untitled";
        }

        // Returns a title that still needs to be cleaned
        return $title;
    }

    // Cleans a title
    public static function cleanTitle(string $title): string
    {
       $title = trim($title);
       $title = html_entity_decode($title);
       require('../urlPatterns.php');
       foreach ($patterns as $pattern) {
	            $title = preg_replace($pattern, '', $title, 1);
	        }
       return htmlspecialchars($title); 
    }
    
    // Snoozes a link
    public function snooze(int $days = null): bool
    {
        if ($days === null) {
            $days = $_ENV['DAYS_PER_SNOOZE'];
        }
        $this->snoozes = $this->snoozes + 1;
        $this->expiryTimestamp = $this->expiryTimestamp + (86400 * $days);
        $this->save();
        return true;
    }

    // Expires a link
    public function expire(int $days = null): bool
    {
        $this->expiryTimestamp = time() - 1;
        $this->snoozes = 0;
        $this->save();
        return true;
    }

    // Marks a link as read
    public function markRead(): bool
    {
        $this->read = date(DATE_ISO8601);
        $this->snoozes= 0;
        $this->save();
        return true;
    }

    // Marks a link as unread
    public function markUnread(): bool
    {
        $this->read = 0;
        // Start setting the expiry
        $endOfSnooze = \Hdz\ReadLater\Link::getEndOfSnooze();
        if ($endOfSnooze < time()) {
            // No currently active snooze, add days as normal
            $this->expiryTimestamp = time() + ($_ENV['DAYS_BEFORE_EXPIRY'] * 86400) + 43200;
        } else {
            // There is an active snooze, add what is left of it
            $this->expiryTimestamp = (time() + ($endOfSnooze - time()) + ($_ENV['DAYS_BEFORE_EXPIRY'] * 86400)) + 43200;
        }
        $this->save();
        return true;
    }

    // Marks a link as unread
    public function unexpire(): bool
    {
        $this->read = 0;
        // Start setting the expiry
        $endOfSnooze = \Hdz\ReadLater\Link::getEndOfSnooze();
        if ($endOfSnooze < time()) {
            // No currently active snooze, add days as normal
            $this->expiryTimestamp = time() + ($_ENV['DAYS_BEFORE_EXPIRY'] * 86400) + 43200;
        } else {
            // There is an active snooze, add what is left of it
            $this->expiryTimestamp = (time() + ($endOfSnooze - time()) + ($_ENV['DAYS_BEFORE_EXPIRY'] * 86400)) + 43200;
        }
        $this->save();
        return true;
    }

    // Changes the expiry date of all non-expired links with a number of days
    public static function snoozeAll(int $endOfSnooze): bool
    {
        if (\Hdz\ReadLater\Link::getEndofSnooze() > time()) {
            // There already is an active snooze, account for it
            $seconds = $endOfSnooze - \Hdz\ReadLater\Link::getEndOfSnooze();
        } else {
            // No active snooze, so add the time between now and the active snooze
            $seconds = $endOfSnooze - time();
        }
        // Update all the links that are not yet read or expired
        $database = \FLight::get('database');
        $database->update('links',[
            'expiry[+]' => $seconds
        ],[
            'read' => 0,
            'expiry[>]' => time()
        ]);
        // Update endOfSnooze
        \Hdz\ReadLater\Link::setEndOfSnooze($endOfSnooze);
        return true;
    }

    // Get all the links that aren't expired
    public static function getUnexpired() : array
    {
        $now = time();
        if ($_ENV['MINIMUM_AGE_IN_DAYS_TO_SHOW'] == 0) {
            // Set the from date to tomorrow, so all links are shown
            $fromDate = (new \DateTime())->modify('+1 days')->format(DATE_ISO8601);
        } else {
            $fromDate = (new \DateTime())->modify('-' . $_ENV['MINIMUM_AGE_IN_DAYS_TO_SHOW'] . ' days')->format(DATE_ISO8601);
        }
        if ($_ENV['MAXIMUM_ENTRIES_TO_SHOW'] == 0) {
            $maxNumberOfEntries = PHP_INT_MAX;
        } else {
            $maxNumberOfEntries = $_ENV['MAXIMUM_ENTRIES_TO_SHOW'];
        }
        $database = \FLight::get('database');
        $results = $database->select('links', [
            'id',
            'url',
            'title',
            'domain',
            'expiryString' => \Medoo\Medoo::raw(
                'CASE
                    WHEN expiry-:now < 0 THEN "Expired"
                    WHEN expiry-:now < 600 THEN "minutes"
                    WHEN expiry-:now <= 3600 THEN CAST((expiry-:now)/60 AS INTEGER) || " minutes"
                    WHEN expiry-:now < 7200 THEN "1 hour"
                    WHEN expiry-:now <= 86400 THEN CAST((expiry-:now)/3600 AS INTEGER) || " hours"
                    WHEN expiry-:now < 172800 THEN "1 day"
                    ELSE CAST((expiry-:now)/86400 AS INTEGER) || " days"
                END'
            ,[
                ':now' => $now
            ]),
            'readLink' => \Medoo\Medoo::raw(
                ':url || id || "/go"'
                
            ,[
                ':url' => $_ENV['BASE_URL'] . '/link/'
            ]),
            'snoozes'
        ],[
            'read' => 0,
            'expiry[>]' => $now,
            'addeddate[<]' => $fromDate,
            'LIMIT' => $maxNumberOfEntries,
            'ORDER' => [
                'expiry' => 'ASC'
            ]
        ]);
        return $results;
    }

    // Get all the links that have been read
    public static function getRead() : array
    {
        $database = \FLight::get('database');
        $results = $database->select('links', [
            'id',
            'url',
            'title',
            'domain'
        ],[
            'read[!]' => 0,
            'ORDER' => [
                'read' => 'DESC',
                'id' => 'DESC'
            ]
        ]);
        return $results;
    }

    // Get all the links that have expired
    public static function getExpired() : array
    {
        $database = \FLight::get('database');
        $results = $database->select('links', [
            'id',
            'url',
            'title',
            'domain'
        ],[
            "AND" => [
                'read' => 0,
                'expiry[<]' => time()
        ],
            'ORDER' => [
                'expiry' => 'DESC'
            ]
        ]);
        return $results;
    }

    public static function getEndOfSnooze() : int
    {
        $database = \Flight::get('database');
        $result = $database->get('snoozeall', 'endofsnooze');
        $endOfSnooze = (int)$result;
        return $endOfSnooze;
    }

    public static function setEndOfSnooze($timestamp) : bool
    {
        $database = \Flight::get('database');
        $result = $database->update('snoozeall', [
            "endofsnooze" => $timestamp
        ]);
        return true;
    }

}        
