<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        // Tentar auto-login via cookie "remember me"
        if (!tryRememberMeLogin()) {
            header('Location: login.php');
            exit();
        }
    }
}

function loginUser($userId, $username, $email, $role) {
    global $conn;

    $csrfToken = $_SESSION['csrf_token'] ?? null;

    session_regenerate_id(true); // apaga sessão antiga

    // Restaura o token após regenerar
    if ($csrfToken) {
        $_SESSION['csrf_token'] = $csrfToken;
    }

    $_SESSION['user_id']  = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email']    = $email;
    $_SESSION['role']     = $role;

    // Flag de avatar — usada pelo header/drawer para mostrar a foto de perfil.
    // Definida aqui para cobrir todos os caminhos de login (password,
    // remember-me, verificação de email e registo).
    $stmt = $conn->prepare("SELECT LENGTH(avatar) > 0 FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($hasAvatar);
    $stmt->fetch();
    $stmt->close();
    $_SESSION['avatar'] = (bool) $hasAvatar;

    updateLastLogin($userId);
}

function createRememberMeToken($userId) {
    global $conn;

    // Gerar selector e token aleatórios
    $selector  = bin2hex(random_bytes(12)); // 24 chars
    $token     = bin2hex(random_bytes(32)); // 64 chars
    $tokenHash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 dias
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip        = $_SERVER['REMOTE_ADDR'];

    // Guardar na BD
    $stmt = $conn->prepare("
        INSERT INTO user_sessions (user_id, selector, token_hash, user_agent, ip, expires_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssss", $userId, $selector, $tokenHash, $userAgent, $ip, $expiresAt);
    $stmt->execute();
    $stmt->close();

    // Definir cookies (30 dias, HttpOnly, SameSite)
    $cookieOptions = [
        'expires'  => time() + (30 * 24 * 60 * 60),
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    setcookie('remember_selector', $selector, $cookieOptions);
    setcookie('remember_token',    $token,    $cookieOptions);
}

function tryRememberMeLogin() {
    global $conn;

    if (!isset($_COOKIE['remember_selector']) || !isset($_COOKIE['remember_token'])) {
        return false;
    }

    $selector  = $_COOKIE['remember_selector'];
    $token     = $_COOKIE['remember_token'];
    $tokenHash = hash('sha256', $token);

    // Buscar sessão válida (não expirada, não revogada)
    $stmt = $conn->prepare("
        SELECT us.id, us.user_id, us.token_hash,
               u.username, u.email, u.role, u.is_active
        FROM user_sessions us
        INNER JOIN users u ON u.id = us.user_id
        WHERE us.selector = ?
          AND us.expires_at > NOW()
          AND us.revoked_at IS NULL
    ");
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $stmt->close();
        clearRememberMeCookies();
        return false;
    }

    $session = $result->fetch_assoc();
    $stmt->close();

    // Verificar conta ativa
    if (!$session['is_active']) {
        clearRememberMeCookies();
        return false;
    }

    // Comparar token com hash (timing-safe)
    if (!hash_equals($session['token_hash'], $tokenHash)) {
        // Token inválido - possível roubo - revogar TODAS as sessões deste utilizador
        clearRememberMeCookies();
        return false;
    }

    // Tudo OK - fazer login automático
    loginUser($session['user_id'], $session['username'], $session['email'], $session['role']);

    // Renovar token (rotation de token para mais segurança)
    createRememberMeToken($session['user_id']);

    return true;
}


function clearRememberMeCookies() {
    setcookie('remember_selector', '', time() - 3600, '/');
    setcookie('remember_token',    '', time() - 3600, '/');
}

function logoutUser() {
    global $conn;

    // Revogar sessão persistente atual
    if (isset($_COOKIE['remember_selector'])) {
        $selector = $_COOKIE['remember_selector'];
        $stmt = $conn->prepare("UPDATE user_sessions SET revoked_at = NOW() WHERE selector = ? AND revoked_at IS NULL");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }

    clearRememberMeCookies();

    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email'    => $_SESSION['email'],
        'role'     => $_SESSION['role'] ?? 'user',
    ];
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function revokeAllUserSessions($userId) {
    global $conn;
    $stmt = $conn->prepare("UPDATE user_sessions SET revoked_at = NOW() WHERE user_id = ? AND revoked_at IS NULL");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

function updateLastLogin($userId) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

function checkLoginRateLimit($ip) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempts 
        FROM login_attempts 
        WHERE ip = ? AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['attempts'] < 5;
}

function recordLoginAttempt($ip, $username, $success) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip, username, success) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $ip, $username, $success);
    $stmt->execute();
    $stmt->close();
}

function createPasswordResetToken(int $userId): string {
    global $conn;

    $stmt = $conn->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    $selector  = bin2hex(random_bytes(12));
    $token     = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1h

    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $selector, $tokenHash, $expiresAt);
    $stmt->execute();
    $stmt->close();

    return $selector . ':' . $token;
}

function verifyPasswordResetToken(string $rawToken) {
    global $conn;

    $parts = explode(':', $rawToken, 2);
    if (count($parts) !== 2) return false;
    [$selector, $token] = $parts;

    $stmt = $conn->prepare("SELECT id, user_id, token_hash FROM password_resets WHERE selector = ? AND expires_at > NOW() AND used_at IS NULL");
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !hash_equals($row['token_hash'], hash('sha256', $token))) return false;

    return ['reset_id' => (int)$row['id'], 'user_id' => (int)$row['user_id']];
}

function consumePasswordResetToken(int $resetId): void {
    global $conn;
    $stmt = $conn->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $resetId);
    $stmt->execute();
    $stmt->close();
}

function createPendingRegistration(string $email, string $username, string $passwordHash): string {
    global $conn;

    $code     = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $codeHash = hash('sha256', $code);
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1h

    $stmt = $conn->prepare("DELETE FROM pending_registrations WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO pending_registrations (email, username, password_hash, code_hash, expires_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $username, $passwordHash, $codeHash, $expiresAt);
    $stmt->execute();
    $stmt->close();

    return $code;
}

function verifyPendingCode(string $email, string $code) {
    global $conn;

    $codeHash = hash('sha256', $code);

    $stmt = $conn->prepare("SELECT username, password_hash FROM pending_registrations WHERE email = ? AND code_hash = ? AND expires_at > NOW()");
    $stmt->bind_param("ss", $email, $codeHash);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) return false;

    // Race-condition guard: check users table before inserting
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
    $stmt->bind_param("ss", $email, $row['username']);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    if ($exists) {
        $stmt = $conn->prepare("DELETE FROM pending_registrations WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, is_active, email_verified_at, created_at) VALUES (?, ?, ?, 'user', 1, NOW(), NOW())");
    $stmt->bind_param("sss", $row['username'], $email, $row['password_hash']);
    $stmt->execute();
    $newId = (int)$conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM pending_registrations WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();

    return $newId;
}
?>
