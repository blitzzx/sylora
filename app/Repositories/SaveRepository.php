<?php

class SaveRepository
{
    public static function getSlotsByUser(mysqli $conn, int $userId): array
    {
        $stmt = $conn->prepare("SELECT * FROM saves WHERE user_id = ? ORDER BY slot ASC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $saves  = [];
        while ($row = $result->fetch_assoc()) {
            $saves[$row['slot']] = $row;
        }
        $stmt->close();
        return $saves;
    }

    public static function getSlot(mysqli $conn, int $userId, int $slot): ?string
    {
        $stmt = $conn->prepare("SELECT save_data FROM saves WHERE user_id = ? AND slot = ?");
        $stmt->bind_param("ii", $userId, $slot);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? $row['save_data'] : null;
    }

    public static function getBestSave(mysqli $conn, int $userId): ?array
    {
        $stmt = $conn->prepare("
            SELECT level, hp, hp_total, xp, xp_req, chapter, story_progress, damage, last_saved
            FROM saves WHERE user_id = ? ORDER BY level DESC, last_saved DESC LIMIT 1
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public static function upsert(
        mysqli $conn,
        int $userId,
        int $slot,
        string $playerName,
        string $saveData,
        int $level,
        float $hp,
        float $hpTotal,
        float $xp,
        float $xpReq,
        float $damage,
        string $chapter,
        int $storyProgress
    ): void {
        $stmt = $conn->prepare("
            INSERT INTO saves
                (user_id, slot, player_name, save_data, level, hp, hp_total, xp, xp_req, damage, chapter, story_progress)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                player_name    = VALUES(player_name),
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
        $stmt->bind_param("iissidddddsi",
            $userId, $slot, $playerName, $saveData,
            $level, $hp, $hpTotal, $xp, $xpReq, $damage,
            $chapter, $storyProgress
        );
        $stmt->execute();
        $stmt->close();
    }

    public static function delete(mysqli $conn, int $userId, int $slot): void
    {
        $stmt = $conn->prepare("DELETE FROM saves WHERE user_id = ? AND slot = ?");
        $stmt->bind_param("ii", $userId, $slot);
        $stmt->execute();
        $stmt->close();
    }
}
