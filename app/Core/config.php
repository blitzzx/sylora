<?php

ob_start();

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}

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

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Functions.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Lang.php';

$conn = Database::conn();

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'pt', 'es'], true)) {
    setcookie('sylora_lang', $_GET['lang'], [
        'expires'  => time() + 31536000,
        'path'     => '/',
        'samesite' => 'Lax',
        'secure'   => $_is_https,
    ]);
    $_COOKIE['sylora_lang'] = $_GET['lang'];
}

if (!isLoggedIn() && isset($_COOKIE['remember_selector'], $_COOKIE['remember_token'])) {
    tryRememberMeLogin();
}
