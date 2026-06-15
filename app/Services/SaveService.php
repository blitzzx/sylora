<?php

require_once __DIR__ . '/../Repositories/SaveRepository.php';
require_once __DIR__ . '/../Core/SaveCrypto.php';

class SaveService
{
    private static array $chapterMap = [
        'Thalassos'      => 'Ato I: Ilha de Thalassos',
        'Thalassos_Cave' => 'Ato I: Gruta de Thalassos',
        'Thalassos_Boss' => 'Ato I: Templo de Pelágion',
        'Helion'         => 'Ato II: As Cinzas de Helion',
        'Zephyria'       => 'Ato III: O Véu dos Ventos',
    ];

    /**
     * Normaliza um valor numérico vindo de um save: rejeita não-numéricos
     * e NaN/INF (ex.: "hp": 1e500 num save editado à mão) e limita ao
     * intervalo das colunas MySQL — caso contrário o INSERT lança
     * mysqli_sql_exception (Out of range) e o upload rebenta com 500.
     */
    private static function num(mixed $v, float $min, float $max, float $default): float
    {
        if (!is_numeric($v)) return $default;
        $f = (float) $v;
        if (!is_finite($f)) return $default;
        return max($min, min($max, $f));
    }

    public static function validateAndUpload(mysqli $conn, int $userId, int $slot, string $rawData): array
    {
        if ($slot < 1 || $slot > 3) {
            return ['error' => 'Slot inválido (1-3).', 'code' => 400];
        }

        // Novo formato seguro "SYL2" → decifra + verifica a assinatura.
        // Formato antigo (texto puro) → aceite na mesma (migração suave).
        if (SaveCrypto::isEncrypted($rawData)) {
            $decoded = SaveCrypto::decode($rawData);
            if ($decoded === null) {
                return ['error' => 'Save inválido ou adulterado.', 'code' => 400];
            }
            $content = trim(str_replace("\x00", '', $decoded));
        } else {
            $content = trim(str_replace("\x00", '', $rawData));
        }

        $data = json_decode($content, true);

        if (!$data || !isset($data['stats']) || !is_array($data['stats'])) {
            return ['error' => 'Ficheiro corrompido ou não é um save da Sylora.', 'code' => 400];
        }

        $s = $data['stats'];
        $level          = (int) self::num($s['lvl']            ?? 1,   1, 9999,        1);
        $hp             =       self::num($s['hp']             ?? 100, 0, 999999999, 100);
        $hpTotal        =       self::num($s['hp_total']       ?? 100, 1, 999999999, 100);
        $xp             =       self::num($s['xp']             ?? 0,   0, 999999999,   0);
        $xpReq          =       self::num($s['xp_req']         ?? 100, 1, 999999999, 100);
        $damage         =       self::num($s['damage']         ?? 3,   0, 999999999,   3);
        $storyProgress  = (int) self::num($s['story_progress'] ?? 0,   0, 1000000,     0);
        $room           = preg_replace('/[^a-zA-Z0-9_]/', '', is_string($s['save_rm'] ?? null) ? $s['save_rm'] : 'Thalassos');
        $playerName     = trim((string) ($data['player_name'] ?? ''));

        if (mb_strlen($playerName) > 32) {
            $playerName = mb_substr($playerName, 0, 32);
        }

        $chapter = self::$chapterMap[$room] ?? 'Ato I: Ilha de Thalassos';

        try {
            SaveRepository::upsert(
                $conn, $userId, $slot, $playerName, $content,
                $level, $hp, $hpTotal, $xp, $xpReq, $damage,
                $chapter, $storyProgress
            );
        } catch (Throwable $e) {
            error_log('save upload: falha no upsert (user ' . $userId . '): ' . $e->getMessage());
            return ['error' => 'Não foi possível guardar o save. Verifica se o ficheiro é válido.', 'code' => 422];
        }

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
