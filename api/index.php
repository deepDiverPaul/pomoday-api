<?php

require_once('users.php');

function require_auth()
{
    if (isset($_SERVER["HTTP_AUTHORIZATION"]) && 0 === stripos($_SERVER["HTTP_AUTHORIZATION"], 'basic ')) {
        $exploded = explode(':', base64_decode(substr($_SERVER["HTTP_AUTHORIZATION"], 6)), 2);
        if (2 == count($exploded)) {
            list($un, $pw) = $exploded;
        }
    }
    global $users;
    $has_supplied_credentials = !(empty($un) && empty($pw));
    $is_not_authenticated = (
        !$has_supplied_credentials ||
        $users[$un] != sha1($pw)
    );
    if ($is_not_authenticated) {
        header('HTTP/1.1 403 Authorization Required');
        exit;
    }
    return $un;
}

if($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    // Handle CORS Preflight

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: PUT, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Accept, Authorization, Content-Type, Referer, Sec-Fetch-Mode, User-Agent');
        header('Access-Control-Max-Age: 1728000');
        header("Content-Length: 0");
        header("Content-Type: application/json");
        exit(0);
}

$user = require_auth();

$path = '../data/' . $user . '.txt';

$method = $_SERVER['REQUEST_METHOD'];
if ('PUT' === $method) {
    $f = file_get_contents('php://input');
    file_put_contents($path, $f);
}
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');

if (file_exists($path)) {
    print(file_get_contents($path));
} else {
    print('{}');
}