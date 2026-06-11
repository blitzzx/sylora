<?php

class CommentRepository
{
    public static function listByProfile(mysqli $conn, int $profileUserId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
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
        return $rows;
    }

    public static function countByProfile(mysqli $conn, int $profileUserId): int
    {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM profile_comments WHERE profile_user_id = ? AND is_hidden = 0");
        $stmt->bind_param('i', $profileUserId);
        $stmt->execute();
        $count = (int) $stmt->get_result()->fetch_row()[0];
        $stmt->close();
        return $count;
    }

    public static function listRecentForProfile(mysqli $conn, int $profileUserId, int $limit = 10): array
    {
        $stmt = $conn->prepare("
            SELECT pc.id, pc.content, pc.created_at,
                   u.id AS author_id, u.username AS author_username
            FROM profile_comments pc
            INNER JOIN users u ON u.id = pc.author_id
            WHERE pc.profile_user_id = ? AND pc.is_hidden = 0
            ORDER BY pc.created_at DESC LIMIT ?
        ");
        $stmt->bind_param('ii', $profileUserId, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function create(mysqli $conn, int $authorId, int $profileUserId, string $content): int
    {
        $stmt = $conn->prepare("
            INSERT INTO profile_comments (author_id, profile_user_id, content)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('iis', $authorId, $profileUserId, $content);
        $stmt->execute();
        $newId = (int) $conn->insert_id;
        $stmt->close();
        return $newId;
    }

    public static function getById(mysqli $conn, int $commentId): ?array
    {
        $stmt = $conn->prepare("SELECT author_id, profile_user_id FROM profile_comments WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $commentId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public static function hide(mysqli $conn, int $commentId): void
    {
        $stmt = $conn->prepare("UPDATE profile_comments SET is_hidden = 1 WHERE id = ?");
        $stmt->bind_param('i', $commentId);
        $stmt->execute();
        $stmt->close();
    }

    public static function countRecentByAuthor(mysqli $conn, int $authorId): int
    {
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM profile_comments
            WHERE author_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->bind_param('i', $authorId);
        $stmt->execute();
        $count = (int) $stmt->get_result()->fetch_row()[0];
        $stmt->close();
        return $count;
    }
}
