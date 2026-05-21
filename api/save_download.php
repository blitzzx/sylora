<?php
require_once '../includes/config.php';
requireLogin();

$user    = getCurrentUser();
$user_id = (int) $user['id'];
$slot    = (int) ($_GET['slot'] ?? 0);

if ($slot < 1 || $slot > 3) {
    http_response_code(400);
    echo json_encode(['error' => 'Slot inválido.']);
    exit;
}

$stmt = $conn->prepare("SELECT save_data FROM saves WHERE user_id = ? AND slot = ?");
$stmt->bind_param("ii", $user_id, $slot);
$stmt->execute();
$result = $stmt->get_result();
$row    = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    http_response_code(404);
    echo "Save não encontrado.";
    exit;
}

$output = $row['save_data'] . "\x00";

// Descartar qualquer output acumulado (newlines de includes, BOMs) para o
// corpo ser exatamente o save e o Content-Length bater certo.
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="syloradata.sav"');
header('Content-Length: ' . strlen($output));
header('Cache-Control: no-store');
echo $output;
