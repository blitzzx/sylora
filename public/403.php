<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/app/Core/config.php';
$errorCode = 403;
require ROOT . '/resources/views/pages/error.php';
