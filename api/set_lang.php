<?php
$allowed = ['en', 'pt', 'es'];
$lang = $_GET['lang'] ?? 'en';
if (!in_array($lang, $allowed)) $lang = 'en';

$secure = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
       || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

setcookie('sylora_lang', $lang, [
    'expires'  => time() + 60 * 60 * 24 * 365,
    'path'     => '/',
    'httponly' => false,
    'secure'   => $secure,
    'samesite' => 'Lax',
]);

$ref = $_SERVER['HTTP_REFERER'] ?? '/';
if (!preg_match('#^https?://#', $ref)) $ref = '/';
header('Location: ' . $ref);
exit;
