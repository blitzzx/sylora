<?php
/**
 * Endpoint: POST /api/contact
 * Purpose:  Send a contact form message to the site owner via email.
 * Auth:     Open (CSRF required)
 * Input:    POST name, email, subject, message, _csrf, website (honeypot)
 * Output:   JSON { success, message } | { error: string }
 */

require_once ROOT . '/app/Core/config.php';
require_once ROOT . '/app/Core/Mailer.php';

header('Content-Type: application/json; charset=utf-8');

const CONTACT_RECIPIENT   = 'marciosousaa2007@gmail.com';
const CONTACT_RATE_WINDOW = 60;
const CONTACT_RATE_MAX    = 3;
const CONTACT_NAME_MIN    = 2;
const CONTACT_NAME_MAX    = 80;
const CONTACT_SUBJECT_MIN = 4;
const CONTACT_SUBJECT_MAX = 120;
const CONTACT_MESSAGE_MIN = 20;
const CONTACT_MESSAGE_MAX = 2000;

function jsonResponse(int $code, array $payload): never
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, ['error' => 'Método não permitido.']);
}

$csrf = $_POST['_csrf'] ?? '';
if (!verifyCSRFToken($csrf)) {
    jsonResponse(403, ['error' => 'Pedido inválido. Recarrega a página e tenta novamente.']);
}

if (!empty($_POST['website'] ?? '')) {
    jsonResponse(200, ['success' => true]);
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    jsonResponse(400, ['error' => 'Preenche todos os campos.']);
}

$nameLen = mb_strlen($name);
if ($nameLen < CONTACT_NAME_MIN || $nameLen > CONTACT_NAME_MAX) {
    jsonResponse(400, ['error' => 'Nome deve ter entre ' . CONTACT_NAME_MIN . ' e ' . CONTACT_NAME_MAX . ' caracteres.']);
}

if (!isValidEmail($email)) {
    jsonResponse(400, ['error' => 'Email inválido.']);
}

$subjLen = mb_strlen($subject);
if ($subjLen < CONTACT_SUBJECT_MIN || $subjLen > CONTACT_SUBJECT_MAX) {
    jsonResponse(400, ['error' => 'Assunto deve ter entre ' . CONTACT_SUBJECT_MIN . ' e ' . CONTACT_SUBJECT_MAX . ' caracteres.']);
}

$msgLen = mb_strlen($message);
if ($msgLen < CONTACT_MESSAGE_MIN || $msgLen > CONTACT_MESSAGE_MAX) {
    jsonResponse(400, ['error' => 'Mensagem deve ter entre ' . CONTACT_MESSAGE_MIN . ' e ' . CONTACT_MESSAGE_MAX . ' caracteres.']);
}

$stmt = $conn->prepare("
    SELECT COUNT(*) AS attempts
    FROM contact_attempts
    WHERE ip = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
");
$window = CONTACT_RATE_WINDOW;
$stmt->bind_param('si', $ip, $window);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (((int)$row['attempts']) >= CONTACT_RATE_MAX) {
    jsonResponse(429, ['error' => 'Demasiadas mensagens. Tenta novamente daqui a uma hora.']);
}

$stmt = $conn->prepare("INSERT INTO contact_attempts (ip, email) VALUES (?, ?)");
$stmt->bind_param('ss', $ip, $email);
$stmt->execute();
$stmt->close();

$sent = mailContactForm(CONTACT_RECIPIENT, $name, $email, $subject, $message, $ip);

if (!$sent) {
    jsonResponse(502, ['error' => 'Falha ao enviar a mensagem. Tenta novamente mais tarde.']);
}

jsonResponse(200, ['success' => true, 'message' => 'Mensagem enviada com sucesso. Obrigado pelo contacto!']);
