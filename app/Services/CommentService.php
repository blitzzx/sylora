<?php

require_once __DIR__ . '/../Repositories/CommentRepository.php';
require_once __DIR__ . '/../Repositories/FriendRepository.php';
require_once __DIR__ . '/ModerationService.php';

class CommentService
{
    public static function list(mysqli $conn, int $profileUserId, int $page = 1, int $perPage = 10): array
    {
        $comments = CommentRepository::listByProfile($conn, $profileUserId, $page, $perPage);
        $total    = CommentRepository::countByProfile($conn, $profileUserId);
        return [
            'comments'    => $comments,
            'total'       => $total,
            'page'        => $page,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public static function create(mysqli $conn, int $authorId, int $profileUserId, string $content): array
    {
        if (mb_strlen($content) < 3) {
            return ['error' => 'Comentário demasiado curto (mín. 3 caracteres).', 'code' => 400];
        }
        if (mb_strlen($content) > 500) {
            return ['error' => 'Comentário demasiado longo (máx. 500 caracteres).', 'code' => 400];
        }

        $result = ModerationService::check($content);
        if (!$result['passed']) {
            return ['error' => 'O teu comentário contém linguagem ofensiva grave. Mensagem não enviada.', 'code' => 422];
        }
        $content = $result['censored'];

        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param('i', $profileUserId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$exists) {
            return ['error' => 'Utilizador não encontrado.', 'code' => 404];
        }

        if ($authorId === $profileUserId) {
            return ['error' => 'Não podes comentar no teu próprio perfil.', 'code' => 403];
        }

        if (!FriendRepository::areFriends($conn, $authorId, $profileUserId)) {
            return ['error' => t('profile.comment_friends_only'), 'code' => 403];
        }

        if (CommentRepository::countRecentByAuthor($conn, $authorId) >= 5) {
            return ['error' => 'Estás a comentar demasiado rápido. Tenta mais tarde.', 'code' => 429];
        }

        $newId = CommentRepository::create($conn, $authorId, $profileUserId, $content);

        return [
            'success'    => true,
            'comment_id' => $newId,
            'content'    => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public static function hide(mysqli $conn, int $commentId, int $requesterId): array
    {
        $row = CommentRepository::getById($conn, $commentId);
        if (!$row) {
            return ['error' => 'Comentário não encontrado.', 'code' => 404];
        }

        $canDelete = (int) $row['author_id'] === $requesterId
                  || (int) $row['profile_user_id'] === $requesterId;
        if (!$canDelete) {
            return ['error' => 'Sem permissão para apagar este comentário.', 'code' => 403];
        }

        CommentRepository::hide($conn, $commentId);
        return ['success' => true];
    }
}
