<?php
// Envia email via Resend HTTP API (porta 443, nunca bloqueada por firewalls).
// Fallback para SMTP genérico se RESEND_API_KEY não estiver definido.

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

function sendMail(string $to, string $toName, string $subject, string $htmlBody, ?string $replyTo = null, ?string $replyToName = null): bool {
    $resendKey = getenv('RESEND_API_KEY') ?: '';
    $smtpHost  = getenv('SMTP_HOST') ?: '';

    if ($resendKey) {
        return _sendMailResend($to, $toName, $subject, $htmlBody, $resendKey, $replyTo, $replyToName);
    }
    if ($smtpHost) {
        return _sendMailSmtp($to, $toName, $subject, $htmlBody, $replyTo, $replyToName);
    }
    return false;
}

function _sendMailResend(string $to, string $toName, string $subject, string $htmlBody, string $apiKey, ?string $replyTo = null, ?string $replyToName = null): bool {
    $fromName  = getenv('SMTP_FROM_NAME') ?: 'Sylora';
    $fromEmail = getenv('SMTP_FROM')      ?: 'noreply@sylora.lol';
    $altBody   = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

    $payloadArr = [
        'from'    => $fromName . ' <' . $fromEmail . '>',
        'to'      => [$toName !== '' ? $toName . ' <' . $to . '>' : $to],
        'subject' => $subject,
        'html'    => $htmlBody,
        'text'    => $altBody,
    ];
    if ($replyTo) {
        $payloadArr['reply_to'] = $replyToName ? $replyToName . ' <' . $replyTo . '>' : $replyTo;
    }
    $payload = json_encode($payloadArr);

    $ctx = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Authorization: Bearer $apiKey\r\nContent-Type: application/json\r\nContent-Length: " . strlen($payload),
            'content'       => $payload,
            'timeout'       => 15,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer'      => true,
            'verify_peer_name' => true,
        ],
    ]);

    $response = @file_get_contents('https://api.resend.com/emails', false, $ctx);

    if ($response === false) {
        error_log('[Sylora Mailer] Resend: sem resposta HTTP para ' . $to);
        return false;
    }

    $data = json_decode($response, true);
    if (!empty($data['id'])) {
        error_log('[Sylora Mailer] Resend OK: id=' . $data['id'] . ' to=' . $to);
        return true;
    }

    $errMsg = $data['message'] ?? ($data['name'] ?? $response);
    error_log('[Sylora Mailer] Resend erro para ' . $to . ': ' . $errMsg);
    return false;
}

function _sendMailSmtp(string $to, string $toName, string $subject, string $htmlBody, ?string $replyTo = null, ?string $replyToName = null): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER') ?: '';
        $mail->Password   = getenv('SMTP_PASS') ?: '';
        $enc = strtolower(getenv('SMTP_ENCRYPTION') ?: 'tls');
        $mail->SMTPSecure = ($enc === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)(getenv('SMTP_PORT') ?: 587);
        $mail->Timeout    = 10;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(getenv('SMTP_FROM') ?: 'noreply@sylora.lol', getenv('SMTP_FROM_NAME') ?: 'Sylora');
        $mail->addAddress($to, $toName);
        if ($replyTo) {
            $mail->addReplyTo($replyTo, $replyToName ?: '');
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
        $mail->send();
        return true;
    } catch (MailerException $e) {
        error_log('[Sylora Mailer] SMTP falhou para ' . $to . ': ' . $mail->ErrorInfo);
        return false;
    }
}

function mailVerification(string $email, string $username, string $code): bool {
    $u    = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $c    = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
    $html = <<<HTML
<div style="font-family:Georgia,serif;max-width:540px;margin:0 auto;padding:32px 28px;background:#0d0d14;color:#e8c46a;border:1px solid rgba(201,153,58,0.35);border-radius:10px;">
  <h1 style="margin:0 0 4px;font-size:22px;letter-spacing:2px;">SYLORA</h1>
  <p style="color:#7a6a4a;margin:0 0 28px;font-size:13px;letter-spacing:1px;">ECOS DOS DEUSES</p>
  <h2 style="font-size:17px;color:#f0d9a0;margin:0 0 14px;">O teu código de verificação</h2>
  <p style="color:#c8b890;line-height:1.7;margin:0 0 24px;">Olá <strong>{$u}</strong>,<br>
  Usa o código abaixo para confirmar o teu e-mail e ativar a conta Sylora.<br>
  <span style="color:#7a6a4a;font-size:13px;">O código é válido durante 1 hora.</span></p>
  <div style="text-align:center;margin:0 0 28px;">
    <div style="display:inline-block;background:#111118;border:1px solid rgba(201,153,58,0.45);border-radius:12px;padding:24px 40px;">
      <span style="font-family:Georgia,monospace;font-size:42px;letter-spacing:16px;color:#e8c46a;font-weight:bold;">{$c}</span>
    </div>
  </div>
  <p style="color:#4a3a2a;font-size:12px;margin:0;">Se não criaste esta conta, podes ignorar este e-mail. Nenhuma conta será criada sem o código.</p>
</div>
HTML;
    return sendMail($email, $username, 'O teu código de verificação: Sylora', $html);
}

function mailPasswordReset(string $email, string $username, string $token): bool {
    $link = rtrim(SITE_URL, '/') . '/reset?t=' . urlencode($token);
    $u    = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $html = <<<HTML
<div style="font-family:Georgia,serif;max-width:540px;margin:0 auto;padding:32px 28px;background:#0d0d14;color:#e8c46a;border:1px solid rgba(201,153,58,0.35);border-radius:10px;">
  <h1 style="margin:0 0 4px;font-size:22px;letter-spacing:2px;">SYLORA</h1>
  <p style="color:#7a6a4a;margin:0 0 28px;font-size:13px;letter-spacing:1px;">ECOS DOS DEUSES</p>
  <h2 style="font-size:17px;color:#f0d9a0;margin:0 0 14px;">Repor password</h2>
  <p style="color:#c8b890;line-height:1.7;margin:0 0 24px;">Olá <strong>{$u}</strong>,<br>
  Recebemos um pedido para repor a password da tua conta.<br>
  <span style="color:#7a6a4a;font-size:13px;">O link é válido durante 1 hora.</span></p>
  <div style="text-align:center;margin:0 0 28px;">
    <a href="{$link}" style="display:inline-block;background:#c9993a;color:#0d0d14;padding:13px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;letter-spacing:0.5px;">Repor Password</a>
  </div>
  <p style="color:#4a3a2a;font-size:12px;margin:0;">Se não pediste a reposição da password, ignora este e-mail. A tua password não será alterada.</p>
</div>
HTML;
    return sendMail($email, $username, 'Repor password: Sylora', $html);
}

function mailContactForm(string $toEmail, string $fromName, string $fromEmail, string $subject, string $message, string $ip = ''): bool {
    $name    = htmlspecialchars($fromName, ENT_QUOTES, 'UTF-8');
    $email   = htmlspecialchars($fromEmail, ENT_QUOTES, 'UTF-8');
    $subj    = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $msg     = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    $ipSafe  = htmlspecialchars($ip, ENT_QUOTES, 'UTF-8');
    $sentAt  = date('Y-m-d H:i:s');

    $html = <<<HTML
<div style="font-family:Georgia,serif;max-width:600px;margin:0 auto;padding:32px 28px;background:#0d0d14;color:#e8c46a;border:1px solid rgba(201,153,58,0.35);border-radius:10px;">
  <h1 style="margin:0 0 4px;font-size:22px;letter-spacing:2px;">SYLORA</h1>
  <p style="color:#7a6a4a;margin:0 0 24px;font-size:13px;letter-spacing:1px;">NOVA MENSAGEM DE CONTACTO</p>

  <div style="background:#111118;border:1px solid rgba(201,153,58,0.25);border-radius:10px;padding:18px 20px;margin:0 0 18px;">
    <p style="margin:0 0 10px;color:#c8b890;line-height:1.7;"><strong style="color:#f0d9a0;">De:</strong> {$name} &lt;{$email}&gt;</p>
    <p style="margin:0 0 10px;color:#c8b890;line-height:1.7;"><strong style="color:#f0d9a0;">Assunto:</strong> {$subj}</p>
    <p style="margin:0;color:#7a6a4a;font-size:12px;">{$sentAt} · IP: {$ipSafe}</p>
  </div>

  <div style="background:#111118;border:1px solid rgba(201,153,58,0.25);border-radius:10px;padding:18px 20px;color:#e8d9b0;line-height:1.7;">
    {$msg}
  </div>

  <p style="color:#4a3a2a;font-size:12px;margin:24px 0 0;">Podes responder diretamente a esta mensagem; o Reply-To está configurado para {$email}.</p>
</div>
HTML;

    $finalSubject = '[Sylora Contacto] ' . $subject;
    return sendMail($toEmail, 'Sylora', $finalSubject, $html, $fromEmail, $fromName);
}
