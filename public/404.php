<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/app/Core/config.php';
$errorCode = 404;
require ROOT . '/resources/views/pages/error.php';
