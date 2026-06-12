<?php

class Database
{
    private static ?mysqli $conn = null;

    public static function conn(): mysqli
    {
        if (self::$conn !== null) {
            return self::$conn;
        }

        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $name = getenv('DB_NAME');

        // Em produção as credenciais TÊM de vir do ambiente — sem
        // fallback para valores previsíveis de desenvolvimento.
        if (getenv('APP_ENV') === 'production' && (!$host || !$user || !$pass || !$name)) {
            http_response_code(503);
            die('Serviço temporariamente indisponível.');
        }

        define('DB_HOST', $host ?: 'sylora-db');
        define('DB_USER', $user ?: 'sylora_user');
        define('DB_PASS', $pass ?: 'sylora_pass');
        define('DB_NAME', $name ?: 'sylora');

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            http_response_code(503);
            die('Serviço temporariamente indisponível.');
        }

        $conn->set_charset('utf8mb4');

        self::$conn = $conn;
        return self::$conn;
    }
}
