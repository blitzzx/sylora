<?php

function getLang(): string
{
    static $lang = null;
    if ($lang !== null) return $lang;
    $allowed = ['en', 'pt', 'es'];
    $c = $_COOKIE['sylora_lang'] ?? 'en';
    $lang = in_array($c, $allowed) ? $c : 'en';
    return $lang;
}

function t(string $key, array $vars = []): string
{
    static $strings = null;
    if ($strings === null) {
        $file = ROOT . '/resources/lang/' . getLang() . '.php';
        $strings = file_exists($file) ? require $file : [];
    }
    $val = $strings[$key] ?? $key;
    foreach ($vars as $k => $v) {
        $val = str_replace('{' . $k . '}', $v, $val);
    }
    return $val;
}
