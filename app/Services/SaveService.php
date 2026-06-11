<?php

require_once __DIR__ . '/../Repositories/SaveRepository.php';

class SaveService
{
    private static array $chapterMap = [
        'Thalassos'      => 'Ato I: Ilha de Thalassos',
        'Thalassos_Cave' => 'Ato I: Gruta de Thalassos',
        'Thalassos_Boss' => 'Ato I: Templo de Pelágion',
        'Helion'         => 'Ato II: As Cinzas de Helion',
        'Zephyria'       => 'Ato III: O Véu dos Ventos',
    ];

    public static function validateAndUpload(mysqli $conn, int $userId, int $slot, string $rawData): array
    {
        if ($slot < 1 || $slot > 3) {
            return ['error' => 'Slot inválido (1-3).', 'code' => 400];
        }

        $content = trim(str_replace("\x00", '', $rawData));
        $data    = json_decode($content, true);

        if (!$data || !isset($data['stats'])) {
            return ['error' => 'Ficheiro corrompido ou não é um save da Sylora.', 'code' => 400];
        }

        $s = $data['stats'];
        $level          = (int)   ($s['lvl']            ?? 1);
        $hp             = (float) ($s['hp']             ?? 100);
        $hpTotal        = (float) ($s['hp_total']       ?? 100);
        $xp             = (float) ($s['xp']             ?? 0);
        $xpReq          = (float) ($s['xp_req']         ?? 100);
        $damage         = (float) ($s['damage']         ?? 3);
        $storyProgress  = (int)   ($s['story_progress'] ?? 0);
        $room           = preg_replace('/[^a-zA-Z0-9_]/', '', $s['save_rm'] ?? 'Thalassos');
        $playerName     = trim((string) ($data['player_name'] ?? ''));

        if (mb_strlen($playerName) > 32) {
            $playerName = mb_substr($playerName, 0, 32);
        }

        $chapter = self::$chapterMap[$room] ?? 'Ato I: Ilha de Thalassos';

        SaveRepository::upsert(
            $conn, $userId, $slot, $playerName, $content,
            $level, $hp, $hpTotal, $xp, $xpReq, $damage,
            $chapter, $storyProgress
        );

        return ['success' => true, 'message' => 'Save guardado na cloud!'];
    }

    public static function download(mysqli $conn, int $userId, int $slot): ?string
    {
        if ($slot < 1 || $slot > 3) {
            return null;
        }
        return SaveRepository::getSlot($conn, $userId, $slot);
    }

    public static function delete(mysqli $conn, int $userId, int $slot): array
    {
        if ($slot < 1 || $slot > 3) {
            return ['error' => 'Slot inválido.', 'code' => 400];
        }
        SaveRepository::delete($conn, $userId, $slot);
        return ['success' => true];
    }
}
