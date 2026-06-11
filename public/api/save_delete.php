<?php
$_POST['action'] = 'delete';
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/app/Http/Api/SavesApi.php';
