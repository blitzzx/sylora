<?php
/**
 * Endpoint: GET | POST /api/saves
 * Purpose:  Upload, download, and delete game save slots.
 * Auth:     Requires session
 * Input:    GET action=download, slot | POST action=upload (multipart) | POST action=delete, slot, _csrf
 * Output:   Binary .sav file (download) | JSON { success, message } | { error: string }
 */

require_once ROOT . '/app/Core/config.php';
require_once ROOT . '/app/Services/SaveService.php';

function jsonErr(int $code, string $msg): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $msg]);
    exit;
}

// Não usar requireLogin() aqui: ele faz redirect 302 para /login, e o
// download (via <a download>) gravaria o HTML do login dentro do .sav.
// Em vez disso devolvemos 401 limpo para o frontend tratar.
if (!isLoggedIn() && !tryRememberMeLogin()) {
    jsonErr(401, 'Sessão expirada. Faz login novamente.');
}

$method = $_SERVER['REQUEST_METHOD'];
$userId = (int) getCurrentUser()['id'];

// Qualquer exceção não prevista (ex.: mysqli) responde JSON em vez de
// 500 sem corpo — senão o frontend mostra "Erro de ligação" genérico.
try {

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'download';
    if ($action !== 'download') jsonErr(400, 'Ação inválida.');

    $slot = (int) ($_GET['slot'] ?? 0);
    if ($slot < 1 || $slot > 3) jsonErr(400, 'Slot inválido.');

    $saveData = SaveService::download($conn, $userId, $slot);
    if ($saveData === null) {
        http_response_code(404);
        echo 'Save não encontrado.';
        exit;
    }

    // Exporta no formato seguro "SYL2" (encriptado + assinado), tal como o
    // jogo grava localmente. O jogo aceita-o diretamente ao carregar.
    $output = SaveCrypto::encode($saveData);
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="syloradata.sav"');
    header('Content-Length: ' . strlen($output));
    header('Cache-Control: no-store');
    echo $output;
    exit;
}

if ($method === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? 'upload';

    if ($action === 'upload') {
        $csrf = $_POST['_csrf'] ?? '';
        if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');

        if (!checkActionRateLimit('save_upload', (string) $userId, 30, 60)) {
            jsonErr(429, 'Demasiados uploads. Aguarda uma hora.');
        }

        $slot = (int) ($_POST['slot'] ?? 0);

        $maxSize = 2 * 1024 * 1024;
        if (isset($_FILES['savefile']) && $_FILES['savefile']['size'] > $maxSize) {
            jsonErr(400, 'Ficheiro demasiado grande (máx. 2 MB).');
        }
        if (!isset($_FILES['savefile']) || $_FILES['savefile']['error'] !== UPLOAD_ERR_OK) {
            jsonErr(400, 'Nenhum ficheiro recebido.');
        }

        $raw    = file_get_contents($_FILES['savefile']['tmp_name']);
        $result = SaveService::validateAndUpload($conn, $userId, $slot, $raw);
        if (isset($result['error'])) jsonErr($result['code'], $result['error']);

        recordActionAttempt('save_upload', (string) $userId, 1);
        echo json_encode($result);
        exit;
    }

    if ($action === 'preview') {
        // Decifra o save no servidor (a chave nunca chega ao browser) e
        // devolve o JSON para o preview pré-upload. Não escreve nada na BD.
        if (!isset($_FILES['savefile']) || $_FILES['savefile']['error'] !== UPLOAD_ERR_OK) {
            jsonErr(400, 'Nenhum ficheiro recebido.');
        }
        if ($_FILES['savefile']['size'] > 2 * 1024 * 1024) {
            jsonErr(400, 'Ficheiro demasiado grande (máx. 2 MB).');
        }

        $raw = file_get_contents($_FILES['savefile']['tmp_name']);
        if (SaveCrypto::isEncrypted($raw)) {
            $decoded = SaveCrypto::decode($raw);
            if ($decoded === null) jsonErr(400, 'Save inválido ou adulterado.');
            $content = trim(str_replace("\x00", '', $decoded));
        } else {
            $content = trim(str_replace("\x00", '', $raw));
        }

        $data = json_decode($content, true);
        if (!$data || !isset($data['stats']) || !is_array($data['stats'])) {
            jsonErr(400, 'Ficheiro corrompido ou não é um save da Sylora.');
        }

        echo json_encode(['success' => true, 'save' => $data]);
        exit;
    }

    if ($action === 'delete') {
        $csrf = $_POST['_csrf'] ?? '';
        if (!verifyCSRFToken($csrf)) jsonErr(403, 'Token inválido.');

        if (!checkActionRateLimit('save_delete', (string) $userId, 30, 60)) {
            jsonErr(429, 'Demasiadas remoções. Aguarda uma hora.');
        }

        $slot   = (int) ($_POST['slot'] ?? 0);
        $result = SaveService::delete($conn, $userId, $slot);
        if (isset($result['error'])) jsonErr($result['code'], $result['error']);

        recordActionAttempt('save_delete', (string) $userId, 1);
        echo json_encode($result);
        exit;
    }

    jsonErr(400, 'Ação inválida.');
}

jsonErr(405, 'Método não suportado.');

} catch (Throwable $e) {
    error_log('saves api: erro inesperado: ' . $e->getMessage());
    jsonErr(500, 'Erro no servidor. Tenta novamente dentro de momentos.');
}
