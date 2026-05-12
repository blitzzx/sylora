<?php
// Configuração da base de dados (lê de env vars, fallback para desenvolvimento local)
define('DB_HOST', getenv('DB_HOST') ?: 'sylora-db');
define('DB_USER', getenv('DB_USER') ?: 'sylora_user');
define('DB_PASS', getenv('DB_PASS') ?: 'sylora_pass');
define('DB_NAME', getenv('DB_NAME') ?: 'sylora');

// Criar ligação com mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar ligação
if ($conn->connect_error) {
    http_response_code(503);
    die('Serviço temporariamente indisponível.');
}

// Definir charset para UTF-8
$conn->set_charset("utf8mb4");
?>
