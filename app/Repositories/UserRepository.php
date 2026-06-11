<?php

class UserRepository
{
    public static function findByUsername(mysqli $conn, string $username): ?array
    {
        $stmt = $conn->prepare("
            SELECT id, username, email, bio, avatar, created_at, last_login_at
            FROM users WHERE username = ? AND is_active = 1 LIMIT 1
        ");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public static function listForLeaderboard(mysqli $conn, int $limit, int $offset): array
    {
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.avatar,
                   s.level   AS best_level,
                   s.chapter AS best_chapter,
                   s.xp, s.xp_req
            FROM users u
            LEFT JOIN saves s ON s.id = (
                SELECT s2.id FROM saves s2
                WHERE s2.user_id = u.id
                ORDER BY s2.level DESC, s2.last_saved DESC
                LIMIT 1
            )
            WHERE u.is_active = 1
            ORDER BY COALESCE(s.level, -1) DESC, u.username ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function countActive(mysqli $conn): int
    {
        $result = $conn->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
        return (int) $result->fetch_row()[0];
    }

    public static function countWithSaves(mysqli $conn): int
    {
        $result = $conn->query("SELECT COUNT(DISTINCT user_id) FROM saves");
        return (int) $result->fetch_row()[0];
    }

    public static function search(mysqli $conn, string $query): array
    {
        $like = '%' . $query . '%';
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.created_at,
                   (SELECT level FROM saves WHERE user_id = u.id ORDER BY level DESC LIMIT 1) AS best_level,
                   (SELECT chapter FROM saves WHERE user_id = u.id ORDER BY level DESC LIMIT 1) AS best_chapter
            FROM users u
            WHERE u.username LIKE ? AND u.is_active = 1
            ORDER BY u.username ASC
            LIMIT 30
        ");
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function searchCount(mysqli $conn, string $query): int
    {
        $like = '%' . $query . '%';
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username LIKE ? AND is_active = 1");
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $count = (int) $stmt->get_result()->fetch_row()[0];
        $stmt->close();
        return $count;
    }

    public static function getMutualFriends(mysqli $conn, int $myId, int $profileId): array
    {
        $stmt = $conn->prepare("
            SELECT u.id, u.username FROM users u
            WHERE u.id IN (
                SELECT CASE WHEN f.requester_id = ? THEN f.addressee_id ELSE f.requester_id END
                FROM friendships f WHERE (f.requester_id = ? OR f.addressee_id = ?) AND f.status = 'accepted'
            )
            AND u.id IN (
                SELECT CASE WHEN f.requester_id = ? THEN f.addressee_id ELSE f.requester_id END
                FROM friendships f WHERE (f.requester_id = ? OR f.addressee_id = ?) AND f.status = 'accepted'
            ) LIMIT 5
        ");
        $stmt->bind_param('iiiiii', $myId, $myId, $myId, $profileId, $profileId, $profileId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}
