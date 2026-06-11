<?php
define('ROOT', dirname(__DIR__));
require_once ROOT . '/app/Core/config.php';

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) { http_response_code(404); exit; }

$stmt = $conn->prepare('SELECT avatar, avatar_mime FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || empty($row['avatar'])) { http_response_code(404); exit; }

$allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
$mime = in_array($row['avatar_mime'] ?? '', $allowedMime, true) ? $row['avatar_mime'] : 'image/jpeg';

header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo $row['avatar'];
exit;
