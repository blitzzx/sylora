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

$stmt = $conn->prepare("DELETE FROM saves WHERE user_id = ? AND slot = ?");
$stmt->bind_param("ii", $user_id, $slot);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
