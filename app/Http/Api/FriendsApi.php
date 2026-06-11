<?php
/**
 * Endpoint: GET | POST | PUT | DELETE /api/friends
 * Purpose:  Manage friendship relations between users.
 * Auth:     Requires session
 * Input:    GET user_id | POST user_id, _csrf | PUT action, user_id, _csrf | DELETE user_id, _csrf
 * Output:   JSON { friends } | { status } | { success, status } | { error: string }
 */

require_once ROOT . '/app/Core/config.php';
require_once ROOT . '/app/Services/FriendService.php';
require_once ROOT . '/app/Repositories/FriendRepository.php';

header('Content-Type: application/json; charset=utf-8');

function jsonErr(int $code, string $msg): never
{
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

if (!isLoggedIn()) jsonErr(401, 'Precisas de estar autenticado.');

$method = $_SERVER['REQUEST_METHOD'];
$myId   = (int) $_SESSION['user_id'];

if ($method === 'GET') {
    if (isset($_GET['list'])) {
        echo json_encode(FriendService::listWithStatus($conn, $myId));
        exit;
    }
    $targetId = (int) ($_GET['user_id'] ?? 0);
    if ($targetId <= 0 || $targetId === $myId) {
        echo json_encode(['status' => 'none']);
        exit;
    }
    $row = FriendRepository::getRelation($conn, $myId, $targetId);
    if (!$row) {
        echo json_encode(['status' => 'none']);
    } else {
        echo json_encode([
            'status'      => $row['status'],
            'i_requested' => (int) $row['requester_id'] === $myId,
        ]);
    }
    exit;
}

if ($method === 'POST') {
    $body     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $targetId = (int) ($body['user_id'] ?? 0);
    $csrf     = $body['_csrf'] ?? '';
    if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');
    $result = FriendService::sendRequest($conn, $myId, $targetId);
    if (isset($result['error'])) jsonErr($result['code'], $result['error']);
    echo json_encode($result);
    exit;
}

if ($method === 'PUT') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $body['action']  ?? '';
    $fromId = (int) ($body['user_id'] ?? 0);
    $csrf   = $body['_csrf'] ?? '';
    if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');
    $result = FriendService::respond($conn, $myId, $fromId, $action);
    if (isset($result['error'])) jsonErr($result['code'], $result['error']);
    echo json_encode($result);
    exit;
}

if ($method === 'DELETE') {
    $body     = json_decode(file_get_contents('php://input'), true) ?? [];
    $targetId = (int) ($body['user_id'] ?? 0);
    $csrf     = $body['_csrf'] ?? '';
    if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');
    $result = FriendService::remove($conn, $myId, $targetId);
    if (isset($result['error'])) jsonErr($result['code'], $result['error']);
    echo json_encode($result);
    exit;
}

jsonErr(405, 'Método não suportado.');
