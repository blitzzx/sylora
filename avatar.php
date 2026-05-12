<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/db.php';

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    http_response_code(404); exit;
}

$stmt = $conn->prepare('SELECT avatar, avatar_mime FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || empty($row['avatar'])) {
    http_response_code(404); exit;
}

// Sem cache: força sempre imagem fresca após upload
header('Content-Type: ' . $row['avatar_mime']);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo $row['avatar'];
exit;