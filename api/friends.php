<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

function jsonErr(int $code, string $msg): never {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

if (!isLoggedIn()) jsonErr(401, 'Precisas de estar autenticado.');

$method = $_SERVER['REQUEST_METHOD'];
$myId   = (int) $_SESSION['user_id'];




if ($method === 'GET') {
    if (isset($_GET['list'])) {
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.avatar_id,
                   (SELECT level FROM saves WHERE user_id = u.id ORDER BY level DESC LIMIT 1) AS best_level,
                   (SELECT chapter FROM saves WHERE user_id = u.id ORDER BY level DESC LIMIT 1) AS best_chapter
            FROM friendships f
            INNER JOIN users u ON (
                CASE WHEN f.requester_id = ? THEN u.id = f.addressee_id
                     ELSE u.id = f.requester_id END
            )
            WHERE (f.requester_id = ? OR f.addressee_id = ?)
              AND f.status = 'accepted'
            ORDER BY u.username ASC
        ");
        $stmt->bind_param('iii', $myId, $myId, $myId);
        $stmt->execute();
        $friends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode(['friends' => $friends]);
        exit;
    }

    $targetId = (int) ($_GET['user_id'] ?? 0);
    if ($targetId <= 0 || $targetId === $myId) {
        echo json_encode(['status' => 'none']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT status, requester_id
        FROM friendships
        WHERE (requester_id = ? AND addressee_id = ?)
           OR (requester_id = ? AND addressee_id = ?)
        LIMIT 1
    ");
    $stmt->bind_param('iiii', $myId, $targetId, $targetId, $myId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['status' => 'none']);
    } else {
        echo json_encode([
            'status'       => $row['status'],
            'i_requested'  => (int) $row['requester_id'] === $myId,
        ]);
    }
    exit;
}


if ($method === 'POST') {
    $body      = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $targetId  = (int) ($body['user_id'] ?? 0);
    $csrf      = $body['_csrf'] ?? '';

    if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');
    if ($targetId <= 0 || $targetId === $myId) jsonErr(400, 'Utilizador inválido.');

    
    $stmtCheck = $conn->prepare("
        SELECT id, status FROM friendships
        WHERE (requester_id = ? AND addressee_id = ?)
           OR (requester_id = ? AND addressee_id = ?)
        LIMIT 1
    ");
    $stmtCheck->bind_param('iiii', $myId, $targetId, $targetId, $myId);
    $stmtCheck->execute();
    $existing = $stmtCheck->get_result()->fetch_assoc();
    $stmtCheck->close();

    if ($existing) {
        if ($existing['status'] === 'accepted') jsonErr(409, 'Já são amigos.');
        if ($existing['status'] === 'pending')  jsonErr(409, 'Pedido já enviado.');
        if ($existing['status'] === 'blocked')  jsonErr(403, 'Não é possível enviar pedido.');
    }

    $stmtIns = $conn->prepare("INSERT INTO friendships (requester_id, addressee_id) VALUES (?, ?)");
    $stmtIns->bind_param('ii', $myId, $targetId);
    $stmtIns->execute();
    $stmtIns->close();

    echo json_encode(['success' => true, 'status' => 'pending']);
    exit;
}


if ($method === 'PUT') {
    $body     = json_decode(file_get_contents('php://input'), true) ?? [];
    $action   = $body['action']  ?? ''; 
    $fromId   = (int) ($body['user_id'] ?? 0);
    $csrf     = $body['_csrf'] ?? '';

    if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');
    if (!in_array($action, ['accept', 'decline'], true)) jsonErr(400, 'Ação inválida.');
    if ($fromId <= 0) jsonErr(400, 'user_id inválido.');

    $newStatus = $action === 'accept' ? 'accepted' : 'declined';

    $stmtUpd = $conn->prepare("
        UPDATE friendships SET status = ?
        WHERE requester_id = ? AND addressee_id = ? AND status = 'pending'
    ");
    $stmtUpd->bind_param('sii', $newStatus, $fromId, $myId);
    $stmtUpd->execute();
    $affected = $stmtUpd->affected_rows;
    $stmtUpd->close();

    if ($affected === 0) jsonErr(404, 'Pedido não encontrado.');
    echo json_encode(['success' => true, 'status' => $newStatus]);
    exit;
}


if ($method === 'DELETE') {
    $body     = json_decode(file_get_contents('php://input'), true) ?? [];
    $targetId = (int) ($body['user_id'] ?? 0);
    $csrf     = $body['_csrf'] ?? '';

    if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');
    if ($targetId <= 0 || $targetId === $myId) jsonErr(400, 'user_id inválido.');

    $stmtDel = $conn->prepare("
        DELETE FROM friendships
        WHERE (requester_id = ? AND addressee_id = ?)
           OR (requester_id = ? AND addressee_id = ?)
    ");
    $stmtDel->bind_param('iiii', $myId, $targetId, $targetId, $myId);
    $stmtDel->execute();
    $stmtDel->close();

    echo json_encode(['success' => true]);
    exit;
}

jsonErr(405, 'Método não suportado.');
