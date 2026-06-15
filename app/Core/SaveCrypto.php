<?php

/**
 * SaveCrypto — formato de save seguro "SYL2" (encrypt-then-MAC).
 *
 * Espelho EXATO de scripts/SaveCrypto/SaveCrypto.gml no jogo GameMaker.
 * Mesma construção, mesmos bytes — o que o jogo cifra, o site decifra, e
 * vice-versa.
 *
 * Container binário:
 *   "SYL2" (4B) | versão (1B) | nonce (8B) | tag SHA1 (20B) | ciphertext (N B)
 *
 *   - ciphertext = json  XOR  keystream
 *   - keystream  = blocos de sha1(SECRET . nonce_hex . i) concatenados
 *   - tag        = sha1(SECRET . ":" . nonce_hex . ":" . ct_hex . ":" . SECRET)
 *
 * O SECRET vive APENAS no servidor (env SYLORA_SAVE_SECRET) — nunca chega ao
 * browser. Qualquer edição do ficheiro sem o SECRET falha a verificação da tag.
 */
class SaveCrypto
{
    private static function secret(): string
    {
        $s = getenv('SYLORA_SAVE_SECRET');
        if ($s === false || $s === '') {
            $s = $_ENV['SYLORA_SAVE_SECRET'] ?? $_SERVER['SYLORA_SAVE_SECRET'] ?? '';
        }
        return (string) $s;
    }

    public static function isEncrypted(string $bytes): bool
    {
        return strlen($bytes) >= 33 && substr($bytes, 0, 4) === 'SYL2';
    }

    private static function keystream(string $nonceHex, int $n): string
    {
        $secret = self::secret();
        $ks  = '';
        $blk = 0;
        while (strlen($ks) < $n) {
            $ks .= sha1($secret . $nonceHex . $blk, true); // 20 bytes raw por bloco
            $blk++;
        }
        return substr($ks, 0, $n);
    }

    /** JSON em texto → container binário "SYL2". */
    public static function encode(string $json): string
    {
        $secret   = self::secret();
        $nonce    = random_bytes(8);
        $nonceHex = bin2hex($nonce);
        $n        = strlen($json);

        $ks    = self::keystream($nonceHex, $n);
        $ct    = $json ^ $ks;            // XOR de strings de igual comprimento
        $ctHex = bin2hex($ct);
        $tag   = sha1($secret . ':' . $nonceHex . ':' . $ctHex . ':' . $secret, true);

        return 'SYL2' . chr(1) . $nonce . $tag . $ct;
    }

    /** Container "SYL2" → JSON em texto, ou null se inválido/adulterado. */
    public static function decode(string $bytes): ?string
    {
        if (!self::isEncrypted($bytes)) return null;

        $secret    = self::secret();
        $nonce     = substr($bytes, 5, 8);
        $nonceHex  = bin2hex($nonce);
        $tagStored = substr($bytes, 13, 20);
        $ct        = substr($bytes, 33);

        if ($ct === '') return null;

        $ctHex   = bin2hex($ct);
        $tagCalc = sha1($secret . ':' . $nonceHex . ':' . $ctHex . ':' . $secret, true);
        if (!hash_equals($tagCalc, $tagStored)) return null; // adulterado

        $ks = self::keystream($nonceHex, strlen($ct));
        return $ct ^ $ks;
    }
}
