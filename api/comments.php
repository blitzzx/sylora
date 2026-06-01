<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');



$TOXICITY_LIST = [
    
    'merda','puta','caralho','filho da puta','fdp','corno','viado','bicha','porra','idiota',
    'estupido','estúpido','burro','imbecil','racista','nazista','pedofilo','pedófilo',
    'matar','suicidio','suicídio','mato-me','nojento','nojentos','odeio-te', 'gay',
    
    'fuck','shit','bitch','asshole','nigger','faggot','cunt','retard','kill yourself',
    'kys','rape','nazi','pedophile','whore','slut','moron','loser','idiot',
    'die','i hate you','scum','trash','worthless',
];

function containsToxic(string $text, array $list): bool {
    
    
    $lower = mb_strtolower($text);
    foreach ($list as $term) {
        $pattern = '/\b' . preg_quote($term, '/') . '\b/u';
        if (preg_match($pattern, $lower)) return true;
    }
    return false;
}

function jsonErr(int $code, string $msg): never {
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
    $offset  = ($page - 1) * $perPage;

    $stmt = $conn->prepare("
        SELECT pc.id, pc.content, pc.created_at,
               u.id AS author_id, u.username AS author_username
        FROM profile_comments pc
        INNER JOIN users u ON u.id = pc.author_id
        WHERE pc.profile_user_id = ? AND pc.is_hidden = 0
        ORDER BY pc.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param('iii', $profileUserId, $perPage, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM profile_comments WHERE profile_user_id = ? AND is_hidden = 0");
    $stmtCount->bind_param('i', $profileUserId);
    $stmtCount->execute();
    $total = (int) $stmtCount->get_result()->fetch_row()[0];
    $stmtCount->close();

    echo json_encode([
        'comments'   => $rows,
        'total'      => $total,
        'page'       => $page,
        'total_pages' => (int) ceil($total / $perPage),
    ]);
    exit;
}


if ($method === 'POST') {
    if (!isLoggedIn()) jsonErr(401, 'Precisas de estar autenticado.');

    $profileUserId = (int) ($_POST['user_id'] ?? 0);
    $content       = trim($_POST['content'] ?? '');
    $csrf          = $_POST['_csrf'] ?? '';

    if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');
    if ($profileUserId <= 0)     jsonErr(400, 'user_id inválido.');
    if (mb_strlen($content) < 3) jsonErr(400, 'Comentário demasiado curto (mín. 3 caracteres).');
    if (mb_strlen($content) > 500) jsonErr(400, 'Comentário demasiado longo (máx. 500 caracteres).');

    global $TOXICITY_LIST;
    if (containsToxic($content, $TOXICITY_LIST)) {
        jsonErr(422, 'O teu comentário contém linguagem inadequada. Por favor, mantém um ambiente respeitoso.');
    }

    
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmtCheck->bind_param('i', $profileUserId);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows === 0) jsonErr(404, 'Utilizador não encontrado.');
    $stmtCheck->close();

    $authorId = (int) $_SESSION['user_id'];

    
    $stmtRate = $conn->prepare("
        SELECT COUNT(*) FROM profile_comments
        WHERE author_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmtRate->bind_param('i', $authorId);
    $stmtRate->execute();
    $recentCount = (int) $stmtRate->get_result()->fetch_row()[0];
    $stmtRate->close();
    if ($recentCount >= 5) jsonErr(429, 'Estás a comentar demasiado rápido. Tenta mais tarde.');

    $stmtIns = $conn->prepare("
        INSERT INTO profile_comments (author_id, profile_user_id, content)
        VALUES (?, ?, ?)
    ");
    $stmtIns->bind_param('iis', $authorId, $profileUserId, $content);
    $stmtIns->execute();
    $newId = $conn->insert_id;
    $stmtIns->close();

    echo json_encode([
        'success'    => true,
        'comment_id' => $newId,
        'author'     => $_SESSION['username'],
        'content'    => $content,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    exit;
}


if ($method === 'DELETE') {
    if (!isLoggedIn()) jsonErr(401, 'Precisas de estar autenticado.');
    parse_str(file_get_contents('php://input'), $body);

    $commentId = (int) ($body['comment_id'] ?? 0);
    $csrf      = $body['_csrf'] ?? '';
    if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');
    if ($commentId <= 0)         jsonErr(400, 'comment_id inválido.');

    $userId = (int) $_SESSION['user_id'];

    $stmtGet = $conn->prepare("SELECT author_id, profile_user_id FROM profile_comments WHERE id = ? LIMIT 1");
    $stmtGet->bind_param('i', $commentId);
    $stmtGet->execute();
    $row = $stmtGet->get_result()->fetch_assoc();
    $stmtGet->close();

    if (!$row) jsonErr(404, 'Comentário não encontrado.');

    
    $canDelete = $row['author_id'] === $userId
              || $row['profile_user_id'] === $userId;
    if (!$canDelete) jsonErr(403, 'Sem permissão para apagar este comentário.');

    $stmtDel = $conn->prepare("UPDATE profile_comments SET is_hidden = 1 WHERE id = ?");
    $stmtDel->bind_param('i', $commentId);
    $stmtDel->execute();
    $stmtDel->close();

    echo json_encode(['success' => true]);
    exit;
}

jsonErr(405, 'Método não suportado.');
