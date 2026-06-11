<?php
/**
 * Endpoint: GET | POST | DELETE /api/comments
 * Purpose:  List, create, and hide profile comments.
 * Auth:     GET — open; POST and DELETE — requires session
 * Input:    GET user_id, page | POST user_id, content, _csrf | DELETE comment_id, _csrf
 * Output:   JSON { comments, total, page, total_pages } | { success } | { error: string }
 */

require_once ROOT . '/app/Core/config.php';
require_once ROOT . '/app/Services/CommentService.php';

header('Content-Type: application/json; charset=utf-8');

function jsonErr(int $code, string $msg): never
{
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $profileUserId = (int) ($_GET['user_id'] ?? 0);
    if ($profileUserId <= 0) jsonErr(400, 'user_id inválido.');
    $page    = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 10;
    echo json_encode(CommentService::list($conn, $profileUserId, $page, $perPage));
    exit;
}

if ($method === 'POST') {
    if (!isLoggedIn()) jsonErr(401, 'Precisas de estar autenticado.');
    $profileUserId = (int) ($_POST['user_id'] ?? 0);
    $content       = trim($_POST['content'] ?? '');
    $csrf          = $_POST['_csrf'] ?? '';
    if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');
    if ($profileUserId <= 0) jsonErr(400, 'user_id inválido.');
    $authorId = (int) $_SESSION['user_id'];
    $result   = CommentService::create($conn, $authorId, $profileUserId, $content);
    if (isset($result['error'])) {
        jsonErr($result['code'], $result['error']);
    }
    $result['author'] = $_SESSION['username'];
    echo json_encode($result);
    exit;
}

if ($method === 'DELETE') {
    if (!isLoggedIn()) jsonErr(401, 'Precisas de estar autenticado.');
    parse_str(file_get_contents('php://input'), $body);
    $commentId = (int) ($body['comment_id'] ?? 0);
    $csrf      = $body['_csrf'] ?? '';
    if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');
    if ($commentId <= 0) jsonErr(400, 'comment_id inválido.');
    $result = CommentService::hide($conn, $commentId, (int) $_SESSION['user_id']);
    if (isset($result['error'])) {
        jsonErr($result['code'], $result['error']);
    }
    echo json_encode($result);
    exit;
}

jsonErr(405, 'Método não suportado.');
