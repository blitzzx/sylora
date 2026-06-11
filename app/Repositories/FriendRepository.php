<?php

class FriendRepository
{
    public static function getRelation(mysqli $conn, int $myId, int $targetId): ?array
    {
        $stmt = $conn->prepare("
            SELECT status, requester_id
            FROM friendships
            WHERE (requester_id = ? AND addressee_id = ?)
               OR (requester_id = ? AND addressee_id = ?)
            LIMIT 1
        ");
        $stmt->bind_param('iiii', $myId, $targetId, $targetId, $myId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public static function listAccepted(mysqli $conn, int $userId): array
    {
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.avatar,
                   (SELECT level FROM saves WHERE user_id = u.id ORDER BY level DESC LIMIT 1) AS best_level,
                   (SELECT chapter FROM saves WHERE user_id = u.id ORDER BY level DESC LIMIT 1) AS best_chapter
            FROM friendships f
            INNER JOIN users u ON (
                CASE WHEN f.requester_id = ? THEN u.id = f.addressee_id
                     ELSE u.id = f.requester_id END
            )
            WHERE (f.requester_id = ? OR f.addressee_id = ?)
              AND f.status = 'accepted'
            ORDER BY u.username ASC
        ");
        $stmt->bind_param('iii', $userId, $userId, $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function listAcceptedForProfile(mysqli $conn, int $profileId, int $limit = 30): array
    {
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.avatar,
                   (SELECT s.level FROM saves s WHERE s.user_id = u.id ORDER BY s.level DESC LIMIT 1) AS best_level
            FROM users u
            INNER JOIN friendships f ON (
                (f.requester_id = ? AND f.addressee_id = u.id) OR
                (f.addressee_id = ? AND f.requester_id = u.id)
            )
            WHERE f.status = 'accepted' AND u.is_active = 1
            ORDER BY u.username ASC LIMIT ?
        ");
        $stmt->bind_param('iii', $profileId, $profileId, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function listAcceptedForSelf(mysqli $conn, int $userId): array
    {
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.avatar,
                   (SELECT s.level FROM saves s WHERE s.user_id = u.id ORDER BY s.level DESC LIMIT 1) AS best_level
            FROM users u
            INNER JOIN friendships f ON (
                (f.requester_id = ? AND f.addressee_id = u.id) OR
                (f.addressee_id = ? AND f.requester_id = u.id)
            )
            WHERE f.status = 'accepted' AND u.is_active = 1
            ORDER BY u.username ASC
        ");
        $stmt->bind_param('ii', $userId, $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function listPendingReceived(mysqli $conn, int $userId): array
    {
        $stmt = $conn->prepare("
            SELECT u.id, u.username FROM users u
            INNER JOIN friendships f ON f.requester_id = u.id
            WHERE f.addressee_id = ? AND f.status = 'pending' AND u.is_active = 1
            ORDER BY f.created_at DESC
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function insert(mysqli $conn, int $requesterId, int $addresseeId): void
    {
        $stmt = $conn->prepare("INSERT INTO friendships (requester_id, addressee_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $requesterId, $addresseeId);
        $stmt->execute();
        $stmt->close();
    }

    public static function updateStatus(mysqli $conn, int $requesterId, int $addresseeId, string $newStatus): int
    {
        $stmt = $conn->prepare("
            UPDATE friendships SET status = ?
            WHERE requester_id = ? AND addressee_id = ? AND status = 'pending'
        ");
        $stmt->bind_param('sii', $newStatus, $requesterId, $addresseeId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public static function delete(mysqli $conn, int $userA, int $userB): void
    {
        $stmt = $conn->prepare("
            DELETE FROM friendships
            WHERE (requester_id = ? AND addressee_id = ?)
               OR (requester_id = ? AND addressee_id = ?)
        ");
        $stmt->bind_param('iiii', $userA, $userB, $userB, $userA);
        $stmt->execute();
        $stmt->close();
    }

    public static function areFriends(mysqli $conn, int $userA, int $userB): bool
    {
        $stmt = $conn->prepare("
            SELECT 1 FROM friendships
            WHERE ((requester_id = ? AND addressee_id = ?) OR (requester_id = ? AND addressee_id = ?))
              AND status = 'accepted' LIMIT 1
        ");
        $stmt->bind_param('iiii', $userA, $userB, $userB, $userA);
        $stmt->execute();
        $stmt->store_result();
        $found = $stmt->num_rows > 0;
        $stmt->close();
        return $found;
    }
}
