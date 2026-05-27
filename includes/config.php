<?php

ob_start();


$_is_production = (getenv('APP_ENV') === 'production');
if ($_is_production) {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}


$_is_https = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
          || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $_is_https,
    ]);
    session_start();
}


define('SITE_NAME', 'Sylora');
define('SITE_URL', rtrim(getenv('SITE_URL') ?: 'http://localhost:8080', '/'));

date_default_timezone_set('Europe/Lisbon');


require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';



if (!isLoggedIn() && isset($_COOKIE['remember_selector'], $_COOKIE['remember_token'])) {
    tryRememberMeLogin();
}
