<?php

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function sanitize(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Formata um valor numérico de stat para display. Acima de 1.000.000
 * usa notação científica (ex.: 1.5×10⁹) para não estourar a UI; abaixo
 * disso mostra o número normal arredondado a $dec casas.
 * Espelhado em JS (fmtStat) em jogar.php.
 */
function fmtStat(float|int|null $n, int $dec = 0): string
{
    $n = (float) ($n ?? 0);
    if (abs($n) > 1000000) {
        $exp  = (int) floor(log10(abs($n)));
        $mant = round($n / (10 ** $exp), 2);
        if (abs($mant) >= 10) { $mant /= 10; $exp++; }   // corrige bordas do log10
        $mantStr = rtrim(rtrim(number_format($mant, 2, '.', ''), '0'), '.');
        $sup = strtr((string) $exp, ['0'=>'⁰','1'=>'¹','2'=>'²','3'=>'³','4'=>'⁴','5'=>'⁵','6'=>'⁶','7'=>'⁷','8'=>'⁸','9'=>'⁹','-'=>'⁻']);
        return $mantStr . '×10' . $sup;
    }
    return $dec > 0 ? number_format($n, $dec, '.', '') : (string) round($n);
}

/**
 * IP real do cliente. Atrás do proxy do Railway, REMOTE_ADDR é o IP do
 * proxy — o que tornaria o rate limiting global (5 falhas de qualquer
 * pessoa bloqueariam toda a gente). O proxy confiável acrescenta o IP
 * real no FIM do X-Forwarded-For, por isso usamos o último elemento
 * válido; valores anteriores podem ser forjados pelo cliente.
 */
function getClientIp(): string
{
    $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($xff !== '') {
        $parts     = array_map('trim', explode(',', $xff));
        $candidate = end($parts);
        if (filter_var($candidate, FILTER_VALIDATE_IP)) {
            return $candidate;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidUsername(string $username): bool
{
    return (bool) preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

function isValidPassword(string $password): bool
{
    return strlen($password) >= 8;
}

function verifyRecaptchaV3(string $token, string $action = ''): bool
{
    $secret = getenv('RECAPTCHA_SECRET_KEY') ?: '';
    if (!$secret) return true;   // verificação desligada (sem chave configurada)
    if (!$token)  return false;  // chave configurada mas pedido sem token => bloquear

    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content'       => http_build_query(['secret' => $secret, 'response' => $token]),
        'timeout'       => 5,
        'ignore_errors' => true,
    ]]);
    $res  = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    $data = $res ? json_decode($res, true) : null;

    if (!$data || empty($data['success'])) return false;
    if ($action && ($data['action'] ?? '') !== $action) return false;
    return ($data['score'] ?? 0) >= 0.5;
}

function redirect(string $url, string $message = '', string $type = 'info'): never
{
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type']    = $type;
    }

    if (!preg_match('#^https?://#', $url) && !str_starts_with($url, 'api/')) {
        $url = preg_replace('#\.php(\?|$)#', '$1', $url);
        if ($url === '' || $url === 'index') {
            $url = '/';
        } elseif ($url[0] !== '/') {
            $url = '/' . $url;
        }
    }
    header("Location: $url");
    exit();
}
