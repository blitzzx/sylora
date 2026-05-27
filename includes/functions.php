<?php
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function sanitize($input) {
    return trim(strip_tags($input));
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

function isValidPassword($password) {
    return strlen($password) >= 8;
}

function verifyRecaptchaV3(string $token, string $action = ''): bool {
    $secret = getenv('RECAPTCHA_SECRET_KEY') ?: '';
    if (!$secret || !$token) {
        $_SESSION['_rc_debug'] = ['skipped' => true, 'reason' => !$secret ? 'RECAPTCHA_SECRET_KEY não configurada' : 'token vazio (script não carregou?)'];
        return true;
    }

    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content'       => http_build_query(['secret' => $secret, 'response' => $token]),
        'timeout'       => 5,
        'ignore_errors' => true,
    ]]);
    $res  = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    $data = $res ? json_decode($res, true) : null;

    $_SESSION['_rc_debug'] = $data ?: ['error' => 'sem resposta da API Google'];

    if (!$data || empty($data['success'])) return false;
    if ($action && ($data['action'] ?? '') !== $action) return false;
    return ($data['score'] ?? 0) >= 0.5;
}

function redirect($url, $message = '', $type = 'info') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type']    = $type;
    }
    
    if (!preg_match('#^https?://#', $url) && !str_starts_with($url, 'api/')) {
        $url = preg_replace('#\.php(\?|$)#', '$1', $url);
        if ($url === '' || $url === 'index') {
            $url = '/';
        } elseif ($url[0] !== '/') {
            $url = '/' . $url;
        }
    }
    header("Location: $url");
    exit();
}
