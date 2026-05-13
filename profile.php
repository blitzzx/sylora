<?php
require_once __DIR__ . '/includes/config.php';

requireLogin();

$user   = getCurrentUser();
$action = $_POST['action'] ?? '';

$profileUrl = 'u.php?u=' . urlencode($user['username'] ?? '');

// Reserva de memória: libertada no shutdown para garantir que ele consegue correr mesmo em OOM
$GLOBALS['_mem_reserve'] = str_repeat(' ', 512 * 1024);

// Capturar erros fatais (ex: memória esgotada) e redirecionar com toast amigável
register_shutdown_function(function () use ($profileUrl) {
    $GLOBALS['_mem_reserve'] = null; // liberta os 512KB reservados
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level() > 0) ob_end_clean();
        if (!headers_sent()) {
            $_SESSION['flash_message'] = 'A imagem é demasiado grande para processar. Usa uma foto mais pequena (máx. 5MB).';
            $_SESSION['flash_type']    = 'error';
            header('Location: ' . $profileUrl);
            exit;
        }
    }
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($profileUrl, 'Ação inválida.', 'error');
}

// Se post_max_size foi excedido, $_POST fica vazio antes do script correr
if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
    redirect($profileUrl, 'A imagem excede o limite permitido (máx. 5MB).', 'error');
}

$csrf = $_POST['_csrf'] ?? '';
if (!verifyCSRFToken($csrf)) {
    redirect($profileUrl, 'Pedido inválido. Tenta novamente.', 'error');
}

// Responde em JSON se pedido via fetch (XHR), caso contrário redireciona
function avatarRespond(string $profileUrl, string $msg, string $type): void {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => $type === 'success', 'message' => $msg]);
        exit;
    }
    redirect($profileUrl, $msg, $type);
}

switch ($action) {

    case 'upload_avatar':
    case 'change_avatar':
        $uploadErr = $_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($uploadErr !== UPLOAD_ERR_OK) {
            $msg = in_array($uploadErr, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE])
                ? 'A imagem excede o limite permitido (máx. 5MB).'
                : 'Erro ao carregar imagem. Tenta novamente.';
            avatarRespond($profileUrl, $msg, 'error');
        }

        $file    = $_FILES['avatar'];
        $maxSize = 5 * 1024 * 1024;
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if ($file['size'] > $maxSize) {
            avatarRespond($profileUrl, 'A imagem excede o limite permitido (máx. 5MB).', 'error');
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            avatarRespond($profileUrl, 'Formato inválido. Usa JPG, PNG ou WebP.', 'error');
        }

        // Verificar dimensões antes de carregar para memória
        $imgInfo = @getimagesize($file['tmp_name']);
        if (!$imgInfo) {
            avatarRespond($profileUrl, 'Ficheiro de imagem inválido.', 'error');
        }
        // Estimar memória necessária: largura × altura × 4 bytes × 2 (src + dst)
        $estimatedBytes = $imgInfo[0] * $imgInfo[1] * 4 * 2;
        if ($estimatedBytes > 200 * 1024 * 1024) {
            avatarRespond($profileUrl, 'Imagem demasiado grande. Usa uma foto com menos de ~6000×6000px.', 'error');
        }

        $src = match($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
            'image/png'  => imagecreatefrompng($file['tmp_name']),
            'image/webp' => imagecreatefromwebp($file['tmp_name']),
            default      => null,
        };

        if (!$src) {
            avatarRespond($profileUrl, 'Não foi possível processar a imagem.', 'error');
        }

        // Corrigir orientação EXIF (fotos de telemóvel giradas ou espelhadas)
        if (function_exists('exif_read_data') && in_array($mimeType, ['image/jpeg', 'image/png'])) {
            $exif        = @exif_read_data($file['tmp_name']);
            $orientation = $exif['Orientation'] ?? 1;
            if ($orientation !== 1) {
                $rotated = match($orientation) {
                    2 => imageflip($src, IMG_FLIP_HORIZONTAL) ? $src : null,
                    3 => imagerotate($src, 180, 0),
                    4 => imageflip($src, IMG_FLIP_VERTICAL) ? $src : null,
                    5 => (function() use ($src) {
                            $r = imagerotate($src, -90, 0);
                            if ($r) imageflip($r, IMG_FLIP_HORIZONTAL);
                            return $r;
                         })(),
                    6 => imagerotate($src, -90, 0),
                    7 => (function() use ($src) {
                            $r = imagerotate($src, 90, 0);
                            if ($r) imageflip($r, IMG_FLIP_HORIZONTAL);
                            return $r;
                         })(),
                    8 => imagerotate($src, 90, 0),
                    default => null,
                };
                if ($rotated && $rotated !== $src) {
                    imagedestroy($src);
                    $src = $rotated;
                }
            }
        }

        $origW = imagesx($src);
        $origH = imagesy($src);
        $maxDim = 400;

        if ($origW > $maxDim || $origH > $maxDim) {
            $ratio = min($maxDim / $origW, $maxDim / $origH);
            $newW  = (int) round($origW * $ratio);
            $newH  = (int) round($origH * $ratio);
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 92);
        imagedestroy($dst);
        $imageData = ob_get_clean();

        $null     = null;
        $saveMime = 'image/jpeg';

        $stmt = $conn->prepare("UPDATE users SET avatar = ?, avatar_mime = ? WHERE id = ?");
        $stmt->bind_param("bsi", $null, $saveMime, $user['id']);
        $stmt->send_long_data(0, $imageData);
        $stmt->execute();
        $stmt->close();

        $_SESSION['avatar'] = true;
        avatarRespond($profileUrl, 'Foto de perfil atualizada!', 'success');
        break;

    case 'change_username':
        $newUsername = sanitize($_POST['new_username'] ?? '');
        if (!isValidUsername($newUsername)) {
            redirect($profileUrl, 'Username inválido.', 'error');
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $newUsername, $user['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            redirect($profileUrl, 'Username já está em uso.', 'error');
        }
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $newUsername, $user['id']);
        $stmt->execute();
        $stmt->close();

        $_SESSION['username'] = $newUsername;
        redirect('u.php?u=' . urlencode($newUsername), 'Username alterado!', 'success');
        break;

    case 'change_email':
        $newEmail = sanitize($_POST['new_email'] ?? '');
        if (!isValidEmail($newEmail)) {
            redirect($profileUrl, 'Email inválido.', 'error');
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $newEmail, $user['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            redirect($profileUrl, 'Email já está em uso.', 'error');
        }
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $newEmail, $user['id']);
        $stmt->execute();
        $stmt->close();

        $_SESSION['email'] = $newEmail;
        redirect($profileUrl, 'Email alterado!', 'success');
        break;

    case 'change_password':
        $currentPassword    = $_POST['current_password'] ?? '';
        $newPassword        = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

        if (!isValidPassword($newPassword)) {
            redirect($profileUrl, 'Password deve ter no mínimo 6 caracteres.', 'error');
        }
        if ($newPassword !== $confirmNewPassword) {
            redirect($profileUrl, 'As passwords não coincidem.', 'error');
        }

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!password_verify($currentPassword, $row['password'])) {
            redirect($profileUrl, 'Password atual incorreta.', 'error');
        }

        $newHashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $newHashed, $user['id']);
        $stmt->execute();
        $stmt->close();

        redirect($profileUrl, 'Password alterada! Faz login novamente.', 'success');
        break;

    case 'change_bio':
        $newBio = trim($_POST['bio'] ?? '');
        if (mb_strlen($newBio) > 300) {
            redirect($profileUrl, 'Bio demasiado longa (máx. 300 caracteres).', 'error');
        }
        $stmtBio = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $stmtBio->bind_param("si", $newBio, $user['id']);
        $stmtBio->execute();
        $stmtBio->close();
        redirect($profileUrl, 'Bio atualizada!', 'success');
        break;

    case 'revoke_sessions':
        revokeAllUserSessions($user['id']);
        clearRememberMeCookies();
        redirect($profileUrl, 'Todas as sessões foram terminadas.', 'success');
        break;

    default:
        redirect($profileUrl, 'Ação desconhecida.', 'error');
}
?>