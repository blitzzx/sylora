<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');



// TIER 1 — Hard ban: blocked entirely, not saved to DB
// Covers racial slurs, extreme hate speech, and severe harm incitement
// Uses regex patterns to catch leet-speak and common obfuscations
$HARD_BAN_PATTERNS = [
    // n-word variants (nigger, nigga, niggas, nigg, n1gg, n!gg, etc.)
    '/\bn[i1!|]([g9q][g9q]|[g9q]{1,2})[a@e3]?[rz]?s?\b/ui',
    '/\bn[i1!|][g9q]{1,3}\b/ui',
    // f-word slurs (faggot, fagg, fag, f4g, etc.)
    '/\bf[a@4][g9q]{1,3}([o0][t7])?s?\b/ui',
    // hard racial/ethnic slurs
    '/\bsp[i1!]c[ks]?\b/ui',
    '/\bch[i1!]nk[s]?\b/ui',
    '/\bk[i1!]k[e3]s?\b/ui',
    '/\bw[e3][t7]\s*b[a@4]ck[s]?\b/ui',
    '/\bgr[e3][a@4][s5][e3][r]?\b/ui',
    '/\bcr[a@4]ck[e3]r[s]?\b/ui',
    '/\bz[i1!][p]?h[e3][a@4]d[s]?\b/ui',
    // hard harm incitement
    '/\bkill\s+your\s*self\b/ui',
    '/\bkys\b/ui',
    '/\bg[o0]\s*die\b/ui',
    '/\bkill\s+(him|her|them|you)\b/ui',
    // Hitler/nazi celebration
    '/\bheil\s+h[i1]tler\b/ui',
    '/\bh[i1]tler\b/ui',
    '/\b(hail|heil)\s+n[a@4]z[i1]?\b/ui',
    // CSAM
    '/\bped[o0](fil[eo]|phile)\b/ui',
    '/\bped[oó]fil[eo]\b/ui',
    '/\bcp\s+(link|video|pic)\b/ui',
];

// TIER 2 — Soft censor: text replaced with ****, message still saved
$SOFT_CENSOR_WORDS = [
    // Portuguese
    'merda','puta','caralho','filho da puta','fdp','foda-se','fodasé','fodase',
    'corno','viado','bicha','porra','idiota','estupido','estúpido','burro',
    'imbecil','nojento','nojentos','odeio-te','merdas','raios','bosta','treta',
    'mato-me','vou-me matar','puto','cabrao','cabrão','bode','palhaço','palhaco',
    // English
    'fuck','fucking','fucked','fucker','shit','bitch','bitches','asshole',
    'ass','bastard','cunt','retard','retarded','moron','idiot','dumbass',
    'dickhead','dipshit','prick','twat','wanker','bullshit','crap',
    'screw you','shut up','stfu','wtf','fck',
    // Spanish
    'puta','hijo de puta','coño','joder','hostia','mierda','cabrón','cabron',
    'pendejo','gilipolla','gilipollas','imbecil',
];

function normalizeText(string $text): string {
    $map = [
        '@' => 'a', '4' => 'a', '3' => 'e', '1' => 'i', '!' => 'i',
        '|' => 'i', '0' => 'o', '$' => 's', '5' => 's', '7' => 't',
        '9' => 'g', '+' => 't',
    ];
    return strtr(mb_strtolower($text), $map);
}

function containsHardBan(string $text, array $patterns): bool {
    $lower = mb_strtolower($text);
    $normalized = normalizeText($text);
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $lower) || preg_match($pattern, $normalized)) {
            return true;
        }
    }
    return false;
}

function applySoftCensor(string $text, array $words): string {
    $lower = mb_strtolower($text);
    // Sort by length descending so multi-word phrases match before substrings
    usort($words, fn($a, $b) => strlen($b) - strlen($a));
    foreach ($words as $word) {
        $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
        if (preg_match($pattern, $lower)) {
            $text = preg_replace_callback($pattern, function($m) {
                return str_repeat('*', mb_strlen($m[0]));
            }, $text);
            $lower = mb_strtolower($text);
        }
    }
    return $text;
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

    global $HARD_BAN_PATTERNS, $SOFT_CENSOR_WORDS;
    if (containsHardBan($content, $HARD_BAN_PATTERNS)) {
        jsonErr(422, 'O teu comentário contém linguagem ofensiva grave. Mensagem não enviada.');
    }
    $content = applySoftCensor($content, $SOFT_CENSOR_WORDS);

    
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmtCheck->bind_param('i', $profileUserId);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows === 0) jsonErr(404, 'Utilizador não encontrado.');
    $stmtCheck->close();

    $authorId = (int) $_SESSION['user_id'];

    if ($authorId === $profileUserId) jsonErr(403, 'Não podes comentar no teu próprio perfil.');

    $stmtFriend = $conn->prepare("
        SELECT 1 FROM friendships
        WHERE ((requester_id = ? AND addressee_id = ?) OR (requester_id = ? AND addressee_id = ?))
          AND status = 'accepted' LIMIT 1
    ");
    $stmtFriend->bind_param('iiii', $authorId, $profileUserId, $profileUserId, $authorId);
    $stmtFriend->execute();
    $stmtFriend->store_result();
    $isFriend = $stmtFriend->num_rows > 0;
    $stmtFriend->close();
    if (!$isFriend) jsonErr(403, t('profile.comment_friends_only'));


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

    
    $canDelete = (int) $row['author_id'] === $userId
              || (int) $row['profile_user_id'] === $userId;
    if (!$canDelete) jsonErr(403, 'Sem permissão para apagar este comentário.');

    $stmtDel = $conn->prepare("UPDATE profile_comments SET is_hidden = 1 WHERE id = ?");
    $stmtDel->bind_param('i', $commentId);
    $stmtDel->execute();
    $stmtDel->close();

    echo json_encode(['success' => true]);
    exit;
}

jsonErr(405, 'Método não suportado.');
