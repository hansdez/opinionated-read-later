<?php

# Set default for error reporting
error_reporting(E_ALL);

# Allowing Composer to do its magic
require_once '../vendor/autoload.php';

# Configuring Flight
\Flight::set('flight.views.path', '../src/templates');
\Flight::set('flight.handle_errors', false);

# Load the environment variables and show which ones are required
$dotenv = \Dotenv\Dotenv::createImmutable('../');
$dotenv->load();
$dotenv->required('BASE_URL')->notEmpty();
$dotenv->required('HOME_NAME')->notEmpty();
$dotenv->required('ERROR_LEVEL')->allowedValues(['-1', '0']);
$dotenv->required('PASSWORD')->notEmpty();
$dotenv->required('API_KEY')->notEmpty();
$dotenv->required('DAYS_BEFORE_EXPIRY')->isInteger();
$dotenv->required('DAYS_PER_SNOOZE')->isInteger();
$dotenv->required('MAXIMUM_ENTRIES_TO_SHOW')->isInteger();
$dotenv->required('MINIMUM_AGE_IN_DAYS_TO_SHOW')->isInteger();
$dotenv->required('FORCE_READ_FIRST')->isInteger();
$dotenv->required('SHOW_ADD_LINK_FORM')->isInteger();
$dotenv->required('SHOW_EXPIRY_TIME')->isInteger();
$dotenv->required('SHOW_BOOKMARKLET')->isInteger();
$dotenv->required('SHOW_CREATOR')->isInteger();
$dotenv->required('SHOW_SIGN_OUT')->isInteger();
$dotenv->required('SHOW_READ_LINK')->isInteger();
$dotenv->required('SHOW_EXPIRED_LINK')->isInteger();
$dotenv->required('SHOW_SNOOZE_ALL_EXPLANATION')->isInteger();

# Set the error level from the environment
error_reporting($_ENV['ERROR_LEVEL']);

# Check if the default passwords has been changed
if ($_ENV['PASSWORD'] == 'default' || $_ENV['API_KEY'] == 'default') {
    die("You have not changed the default password or the default API key in the '.env' file. The application will only work after you've changed the defaults.");
}

# Check if the default database has been copied
if (!file_exists('../database/links.db')) {
    // Copy the empty database to initialize the production database
    if (copy('../database/empty-database.db','../database/links.db')) {
        // Nothing to do, the database should exist
    } else {
        die("The database doesn't seem to exist or is not accessible. Work something out with the file permissions or find another way to make sure that 'empty-database.db' in the 'database' directory is copied to 'links.db' in the same directory.");
    }
}

# Check if the MINIMUM_AGE_IN_DAYS_TO_SHOW isn't bigger than DAYS_BEFORE_EXPIRY
if ($_ENV['MINIMUM_AGE_IN_DAYS_TO_SHOW'] != 0 && $_ENV['MINIMUM_AGE_IN_DAYS_TO_SHOW'] >= $_ENV['DAYS_BEFORE_EXPIRY']) {
    die("You will never see any unread links if your 'MINIMUM_AGE_IN_DAYS_TO_SHOW' value is higher than your 'DAYS_BEFORE_EXPIRY' value. Please adjust this in your '.env' file.");
}

# Open a database connection and store it globally
\Flight::set('database', new \Medoo\Medoo([
    'type' => 'sqlite',
    'database' => '../database/links.db'
    ])
);

# Define the routes
\Flight::route('/login', array('\Hdz\ReadLater\Controller', 'login'));
\Flight::route('/logout', array('\Hdz\ReadLater\Controller', 'logout'));
\Flight::route('/link/add', array('\Hdz\ReadLater\Controller', 'addLink'));
\Flight::route('/link/@id/snooze', array('\Hdz\ReadLater\Controller', 'snoozeLink'));
\Flight::route('/link/@id/expire', array('\Hdz\ReadLater\Controller', 'expireLink'));
\Flight::route('/link/@id/reread', array('\Hdz\ReadLater\Controller', 'rereadLink'));
\Flight::route('/link/@id/unexpire', array('\Hdz\ReadLater\Controller', 'unexpireLink'));
\Flight::route('/link/@id/go', array('\Hdz\ReadLater\Controller', 'redirectToLink'));
\Flight::route('/link/@id/delete/@type', array('\Hdz\ReadLater\Controller', 'deleteLink'));
\Flight::route('/added/@id', array('\Hdz\ReadLater\Controller', 'added'));
\Flight::route('/snooze/all', array('\Hdz\ReadLater\Controller', 'snoozeAll'));
\Flight::route('/read', array('\Hdz\ReadLater\Controller', 'read'));
\Flight::route('/expired', array('\Hdz\ReadLater\Controller', 'expired'));
\Flight::route('/api/v1/toreadnow', array('\Hdz\ReadLater\Apicontroller', 'showToReadNow'));
\Flight::route('/api/v1/readlaterlist', array('\Hdz\ReadLater\Apicontroller', 'showReadLater'));
\Flight::route('/api/v1/readlist', array('\Hdz\ReadLater\Apicontroller', 'showRead'));
\Flight::route('/api/v1/expiredlist', array('\Hdz\ReadLater\Apicontroller', 'showExpired'));
\Flight::route('/api/v1/add', array('\Hdz\ReadLater\Apicontroller', 'addLink'));
\Flight::route('/', array('\Hdz\ReadLater\Controller', 'home'));

# Start the app
\Flight::start();
