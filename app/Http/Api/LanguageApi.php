<?php
/**
 * Endpoint: GET | POST /api/set_lang
 * Purpose:  Set the user's preferred language cookie.
 * Auth:     Open
 * Input:    GET/POST lang (en|pt|es)
 * Output:   JSON { ok, lang } (if XHR) | redirect to referer
 */

require_once ROOT . '/app/Core/config.php';

$allowed = ['en', 'pt', 'es'];
$lang    = $_GET['lang'] ?? $_POST['lang'] ?? '';
if (!in_array($lang, $allowed, true)) $lang = 'en';

$secure = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
       || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

setcookie('sylora_lang', $lang, [
    'expires'  => time() + 60 * 60 * 24 * 365,
    'path'     => '/',
    'httponly' => false,
    'secure'   => $secure,
    'samesite' => 'Lax',
]);

$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
$isJson = stripos($accept, 'application/json') !== false
       || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($isJson) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'lang' => $lang]);
    exit;
}

$ref      = $_SERVER['HTTP_REFERER'] ?? '/';
$refHost  = parse_url($ref, PHP_URL_HOST);
$selfHost = $_SERVER['HTTP_HOST'] ?? '';
if (!$refHost || $refHost !== $selfHost) $ref = '/';

header('Location: ' . $ref);
exit;
