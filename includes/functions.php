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
