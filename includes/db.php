<?php

define('DB_HOST', getenv('DB_HOST') ?: 'sylora-db');
define('DB_USER', getenv('DB_USER') ?: 'sylora_user');
define('DB_PASS', getenv('DB_PASS') ?: 'sylora_pass');
define('DB_NAME', getenv('DB_NAME') ?: 'sylora');


$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);


if ($conn->connect_error) {
    http_response_code(503);
    die('Serviço temporariamente indisponível.');
}


$conn->set_charset("utf8mb4");
