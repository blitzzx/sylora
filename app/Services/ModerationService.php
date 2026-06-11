<?php

class ModerationService
{
    private static array $hardBanPatterns = [
        '/\bn[i1!|]([g9q][g9q]|[g9q]{1,2})[a@e3]?[rz]?s?\b/ui',
        '/\bn[i1!|][g9q]{1,3}\b/ui',
        '/\bf[a@4][g9q]{1,3}([o0][t7])?s?\b/ui',
        '/\bsp[i1!]c[ks]?\b/ui',
        '/\bch[i1!]nk[s]?\b/ui',
        '/\bk[i1!]k[e3]s?\b/ui',
        '/\bw[e3][t7]\s*b[a@4]ck[s]?\b/ui',
        '/\bgr[e3][a@4][s5][e3][r]?\b/ui',
        '/\bcr[a@4]ck[e3]r[s]?\b/ui',
        '/\bz[i1!][p]?h[e3][a@4]d[s]?\b/ui',
        '/\bkill\s+your\s*self\b/ui',
        '/\bkys\b/ui',
        '/\bg[o0]\s*die\b/ui',
        '/\bkill\s+(him|her|them|you)\b/ui',
        '/\bheil\s+h[i1]tler\b/ui',
        '/\bh[i1]tler\b/ui',
        '/\b(hail|heil)\s+n[a@4]z[i1]?\b/ui',
        '/\bped[o0](fil[eo]|phile)\b/ui',
        '/\bped[oó]fil[eo]\b/ui',
        '/\bcp\s+(link|video|pic)\b/ui',
    ];

    private static array $softCensorWords = [
        'merda','puta','caralho','filho da puta','fdp','foda-se','fodasé','fodase',
        'corno','viado','bicha','porra','idiota','estupido','estúpido','burro',
        'imbecil','nojento','nojentos','odeio-te','merdas','raios','bosta','treta',
        'mato-me','vou-me matar','puto','cabrao','cabrão','bode','palhaço','palhaco',
        'fuck','fucking','fucked','fucker','shit','bitch','bitches','asshole',
        'ass','bastard','cunt','retard','retarded','moron','idiot','dumbass',
        'dickhead','dipshit','prick','twat','wanker','bullshit','crap',
        'screw you','shut up','stfu','wtf','fck',
        'puta','hijo de puta','coño','joder','hostia','mierda','cabrón','cabron',
        'pendejo','gilipolla','gilipollas','imbecil',
    ];

    public static function check(string $text): array
    {
        if (self::containsHardBan($text)) {
            return ['passed' => false, 'censored' => $text];
        }
        return ['passed' => true, 'censored' => self::applySoftCensor($text)];
    }

    public static function containsHardBan(string $text): bool
    {
        $lower      = mb_strtolower($text);
        $normalized = self::normalizeText($text);
        foreach (self::$hardBanPatterns as $pattern) {
            if (preg_match($pattern, $lower) || preg_match($pattern, $normalized)) {
                return true;
            }
        }
        return false;
    }

    public static function applySoftCensor(string $text): string
    {
        $words = self::$softCensorWords;
        $lower = mb_strtolower($text);
        usort($words, fn($a, $b) => strlen($b) - strlen($a));
        foreach ($words as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
            if (preg_match($pattern, $lower)) {
                $text  = preg_replace_callback($pattern, fn($m) => str_repeat('*', mb_strlen($m[0])), $text);
                $lower = mb_strtolower($text);
            }
        }
        return $text;
    }

    private static function normalizeText(string $text): string
    {
        $map = [
            '@' => 'a', '4' => 'a', '3' => 'e', '1' => 'i', '!' => 'i',
            '|' => 'i', '0' => 'o', '$' => 's', '5' => 's', '7' => 't',
            '9' => 'g', '+' => 't',
        ];
        return strtr(mb_strtolower($text), $map);
    }
}
