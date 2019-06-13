<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===================================================
// Composer Autoloader
// ===================================================
require __DIR__ . '/vendor/autoload.php';

require 'request.php';
require 'requestError.php';


/**
 * [$token - Replace with an accounts JWT]
 * @var string
 */
$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJzaGEyNTYifQ.eyJpc3MiOiJodHRwOlwvXC9yZXNwb25zaWJsZS5pbyIsInN1YiI6MjkxNTk5ODcsImlhdCI6MTU2MDE0NTc4MCwibmJmIjoxNTYwMTQ1NzkwLCJleHAiOjE1NjAxNDU4NDB9.NENkZWsxbHJYc05YWXZYRzlwUDVDY1IwVlVQbkVQc0FjYnBrT3pzYlVMZw';


/**
 * [$clientID Replace with client account details]
 * @var string
 */
$clientID = '29159987';
$clientSecret = ')ikSv!aPVj1G$o98C^Dm@V1]NjpBN9Xr';

/**
 * [$URL Example endpoint]
 * @var string
 */
$url = '/blog/post/42154525';

/**
 * [$responsible Start the Responsible Client API]
 * @var [type]
 */
$responsible = responsiblAPIClient::API($clientID, $clientSecret);
$responsible::setAPIDomain('http://responsible.io/responsible', '8001');

/**
 * Store the access token as a cookie for demo purposes
 * This can be memory session or data base storage
 */
if (!isset($_COOKIE['responsible_token'])) {
    $responsible::store('responsible_token', $token);
} else {
    $token = $_COOKIE['responsible_token'];
}

/**
 * [$access Call the get method to access the endpoint]
 * @var [type]
 */
$access = $responsible::get($url, $token);
xdebug_var_dump($access);

echo '<pre>';
print_r(json_decode($access));
echo '</pre>';