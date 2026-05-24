<?php
require_once '../includes/config.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$csrf = $_POST['_csrf'] ?? '';
if (!verifyCSRFToken($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'Token inválido.']);
    exit;
}

$user    = getCurrentUser();
$user_id = (int) $user['id'];
$slot    = (int) ($_POST['slot'] ?? 0);

if ($slot < 1 || $slot > 3) {
    http_response_code(400);
    echo json_encode(['error' => 'Slot inválido.']);
    exit;
}



if (!checkActionRateLimit('save_delete', (string) $user_id, 30, 60)) {
    http_response_code(429);
    echo json_encode(['error' => 'Demasiadas remoções. Aguarda uma hora.']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM saves WHERE user_id = ? AND slot = ?");
$stmt->bind_param("ii", $user_id, $slot);
$stmt->execute();
$stmt->close();

recordActionAttempt('save_delete', (string) $user_id, 1);
echo json_encode(['success' => true]);
