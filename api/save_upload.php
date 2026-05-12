<?php
require_once '../includes/config.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$user    = getCurrentUser();
$user_id = (int) $user['id'];

$csrf = $_POST['_csrf'] ?? '';
if (!verifyCSRFToken($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'Token inválido.']);
    exit;
}

$slot = (int) ($_POST['slot'] ?? 0);
if ($slot < 1 || $slot > 3) {
    http_response_code(400);
    echo json_encode(['error' => 'Slot inválido (1-3).']);
    exit;
}

$maxSize = 2 * 1024 * 1024; // 2 MB
if (isset($_FILES['savefile']) && $_FILES['savefile']['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'Ficheiro demasiado grande (máx. 2 MB).']);
    exit;
}

if (!isset($_FILES['savefile']) || $_FILES['savefile']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Nenhum ficheiro recebido.']);
    exit;
}

$raw     = file_get_contents($_FILES['savefile']['tmp_name']);
$content = trim(str_replace("\x00", '', $raw));
$data    = json_decode($content, true);

if (!$data || !isset($data['stats'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Ficheiro corrompido ou não é um save da Sylora.']);
    exit;
}

$s = $data['stats'];
$level          = (int)   ($s['lvl']            ?? 1);
$hp             = (float) ($s['hp']             ?? 100);
$hp_total       = (float) ($s['hp_total']       ?? 100);
$xp             = (float) ($s['xp']             ?? 0);
$xp_req         = (float) ($s['xp_req']         ?? 100);
$damage         = (float) ($s['damage']         ?? 3);
$story_progress = (int)   ($s['story_progress'] ?? 0);
$room           = preg_replace('/[^a-zA-Z0-9_]/', '', $s['save_rm'] ?? 'Thalassos');

$chapter_map = [
    'Thalassos'      => 'Ato I — Ilha de Thalassos',
    'Thalassos_Cave' => 'Ato I — Gruta de Thalassos',
    'Helion'         => 'Ato II — As Cinzas de Helion',
    'Zephyria'       => 'Ato III — O Véu dos Ventos',
];
$chapter = $chapter_map[$room] ?? 'Ato I — Ilha de Thalassos';

$stmt = $conn->prepare("
    INSERT INTO saves
        (user_id, slot, save_data, level, hp, hp_total, xp, xp_req, damage, chapter, story_progress)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        save_data      = VALUES(save_data),
        level          = VALUES(level),
        hp             = VALUES(hp),
        hp_total       = VALUES(hp_total),
        xp             = VALUES(xp),
        xp_req         = VALUES(xp_req),
        damage         = VALUES(damage),
        chapter        = VALUES(chapter),
        story_progress = VALUES(story_progress),
        last_saved     = CURRENT_TIMESTAMP
");
$stmt->bind_param("iisidddddsi",
    $user_id, $slot, $content,
    $level, $hp, $hp_total, $xp, $xp_req, $damage,
    $chapter, $story_progress
);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'message' => 'Save guardado na cloud!']);
