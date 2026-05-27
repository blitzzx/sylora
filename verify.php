<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/mailer.php';

if (isLoggedIn()) redirect('/');

$pendingEmail    = $_SESSION['verify_for'] ?? '';
$codeError       = '';
$resent          = false;
$resendErrors    = [];
$recaptchaSiteKey = getenv('RECAPTCHA_SITE_KEY') ?: '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['_csrf'] ?? '';

    if (!verifyCSRFToken($csrf)) {
        $codeError = 'Pedido inválido. Tenta novamente.';
    } elseif (array_key_exists('code', $_POST)) {
        
        $code  = preg_replace('/\D/', '', $_POST['code'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $pendingEmail = $email;

        if (strlen($code) !== 6) {
            $codeError = 'Introduz os 6 dígitos do código.';
        } elseif (!checkActionRateLimit('verify_code', strtolower($email), 5, 15)) {
            
            
            
            $codeError = 'Demasiadas tentativas. Aguarda 15 minutos.';
        } else {
            $userId = verifyPendingCode($email, $code);
            if ($userId) {
                recordActionAttempt('verify_code', strtolower($email), 1);
                $stmt = $conn->prepare('SELECT username, email, role FROM users WHERE id = ? LIMIT 1');
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                loginUser($userId, $user['username'], $user['email'], $user['role']);
                unset($_SESSION['verify_for']);
                redirect('/', 'Conta criada! Bem-vindo ao Sylora, ' . e($user['username']) . '!', 'success');
            }
            recordActionAttempt('verify_code', strtolower($email), 0);
            $codeError = 'Código incorreto ou expirado. Tenta novamente.';
        }
    } else {
        
        $email = sanitize($_POST['email'] ?? '');
        $ip    = $_SERVER['REMOTE_ADDR'];
        if (!isValidEmail($email)) {
            $resendErrors[] = 'Email inválido.';
        } elseif (!empty($_POST['hp_website'])) {
            // Honeypot: bot detetado — fingir sucesso
            $resent = true;
        } elseif (!verifyRecaptchaV3($_POST['g_recaptcha_token'] ?? '', 'resend')) {
            $resendErrors[] = 'Verificação de segurança falhou. Tenta novamente.';
        } elseif (!checkEmailRateLimit($ip, 'verify-resend', 5) || !checkActionRateLimit('verify_resend', strtolower($email), 3, 60)) {
            $resendErrors[] = 'Demasiadas tentativas. Aguarda uns minutos.';
        } else {
            recordActionAttempt('verify_resend', strtolower($email), 1);
            $stmt = $conn->prepare('SELECT username, password_hash FROM pending_registrations WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $pending = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($pending) {
                $code = createPendingRegistration($email, $pending['username'], $pending['password_hash']);
                mailVerification($email, $pending['username'], $code);
                recordEmailAttempt($ip, 'verify-resend');
                $_SESSION['verify_for'] = $email;
                $pendingEmail = $email;
            }
            $resent = true;
        }
    }
}

$showCodeForm = !empty($pendingEmail);
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt" data-theme="">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verificar E-mail - Sylora</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=<?php echo @filemtime('css/style.css') ?: '1'; ?>">
  <link rel="icon" type="image/png" href="assets/img/FavIcon-Sylora.png">
  <?php if ($recaptchaSiteKey): ?>
  <script src="https://www.google.com/recaptcha/api.js?render=<?= e($recaptchaSiteKey) ?>"></script>
  <?php endif; ?>
  <style>
    @media (max-width: 767px) {
      .auth-split { flex-direction: column; min-height: 100dvh; }
      .auth-deco  { display: none; }
      .auth-form-panel { width: 100%; min-height: 100dvh; padding: 0; }
      .auth-form-inner { padding: 24px 20px 40px; }
    }
    @media (max-width: 480px) {
      .auth-form-inner { padding: 20px 16px 36px; }
      .auth-form-top   { padding: 14px 16px; }
    }
    
    .otp-wrap {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin: 28px 0 24px;
    }
    .otp-input {
      width: 48px;
      height: 60px;
      text-align: center;
      font-size: 26px;
      font-family: 'Cinzel', serif;
      font-weight: 600;
      color: var(--text-primary, #e8c46a);
      background: rgba(201,153,58,.06);
      border: 1.5px solid rgba(201,153,58,.28);
      border-radius: 10px;
      outline: none;
      caret-color: #e8c46a;
      transition: border-color .18s, background .18s, box-shadow .18s;
      -webkit-appearance: none;
    }
    .otp-input:focus {
      border-color: #c9993a;
      background: rgba(201,153,58,.10);
      box-shadow: 0 0 0 3px rgba(201,153,58,.12);
    }
    .otp-input.filled {
      border-color: rgba(201,153,58,.55);
    }
    .otp-input.error {
      border-color: #c96b5a;
      background: rgba(201,107,90,.07);
    }
    @media (max-width: 380px) {
      .otp-input { width: 40px; height: 52px; font-size: 22px; }
      .otp-wrap  { gap: 7px; }
    }
  </style>
  <script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "wpebubj10v");
  </script>
  <script>
    (function(){
      var s = localStorage.getItem('sylora-theme');
      var d = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      document.documentElement.setAttribute('data-theme', s || d);
    })();
  </script>
</head>
<body class="auth-page">

<div class="auth-split">

  <div class="auth-deco" aria-hidden="true">
    <div class="auth-deco-bg"></div>
    <div class="auth-deco-content">
      <a href="/" class="auth-deco-logo">
        <img src="assets/img/Logo-Sylora.png" alt="Sylora" height="64">
      </a>
      <div class="auth-deco-text">
        <p class="auth-deco-overline">✦ Ecos dos Deuses</p>
        <h2><?php echo $showCodeForm ? 'Quase lá.' : 'Confirma a tua identidade.'; ?></h2>
        <p class="auth-deco-sub"><?php echo $showCodeForm
          ? 'Introduz o código de 6 dígitos que enviámos para o teu e-mail para ativar a conta.'
          : 'Um passo antes de começares a tua aventura.'; ?></p>
      </div>
      <div class="auth-deco-orbs" aria-hidden="true">
        <span class="auth-orb ao1"></span>
        <span class="auth-orb ao2"></span>
        <span class="auth-orb ao3"></span>
      </div>
    </div>
  </div>

  <div class="auth-form-panel">

    <div class="auth-form-top">
      <a href="/" class="auth-back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Início
      </a>
    </div>

    <div class="auth-form-inner">

      <?php if ($showCodeForm): ?>

        
        <div class="auth-form-header">
          <h1>Introduz o código</h1>
          <p>Enviámos um código de 6 dígitos para<br><strong><?php echo e($pendingEmail); ?></strong></p>
        </div>

        <?php if ($resent): ?>
          <div class="alert alert-success" style="margin-bottom:16px;">
            <p>Novo código enviado! Verifica também a pasta de spam.</p>
          </div>
        <?php endif; ?>

        <?php if ($codeError): ?>
          <div class="alert alert-error" style="margin-bottom:16px;" id="code-error-banner">
            <p><?php echo e($codeError); ?></p>
          </div>
        <?php endif; ?>

        <form method="POST" action="/verify" class="auth-form" id="otp-form" novalidate>
          <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
          <input type="hidden" name="email"  value="<?php echo e($pendingEmail); ?>">
          <input type="hidden" name="code"   id="code-hidden">

          <div class="otp-wrap" id="otp-wrap">
            <input class="otp-input<?php echo $codeError ? ' error' : ''; ?>" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="Dígito 1" pattern="\d">
            <input class="otp-input<?php echo $codeError ? ' error' : ''; ?>" type="text" inputmode="numeric" maxlength="1" aria-label="Dígito 2" pattern="\d">
            <input class="otp-input<?php echo $codeError ? ' error' : ''; ?>" type="text" inputmode="numeric" maxlength="1" aria-label="Dígito 3" pattern="\d">
            <input class="otp-input<?php echo $codeError ? ' error' : ''; ?>" type="text" inputmode="numeric" maxlength="1" aria-label="Dígito 4" pattern="\d">
            <input class="otp-input<?php echo $codeError ? ' error' : ''; ?>" type="text" inputmode="numeric" maxlength="1" aria-label="Dígito 5" pattern="\d">
            <input class="otp-input<?php echo $codeError ? ' error' : ''; ?>" type="text" inputmode="numeric" maxlength="1" aria-label="Dígito 6" pattern="\d">
          </div>

          <button type="submit" class="btn btn-primary btn-block auth-submit-btn" id="otp-submit" disabled>
            Verificar Código
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </form>

        <p style="color:var(--text-muted,#7a6a4a);font-size:13px;text-align:center;margin-top:24px;margin-bottom:10px;">Não recebeste o código?</p>
        <form method="POST" action="/verify" style="text-align:center;" id="resend-form">
          <input type="hidden" name="_csrf"  value="<?php echo e($csrfToken); ?>">
          <input type="hidden" name="email"  value="<?php echo e($pendingEmail); ?>">
          <input type="hidden" id="g-recaptcha-resend" name="g_recaptcha_token">
          <input type="text" name="hp_website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:1px;width:1px;overflow:hidden;">
          <button type="submit" class="btn btn-ghost btn-block">Reenviar código</button>
        </form>
        <div style="text-align:center;margin-top:14px;">
          <a href="/register" class="auth-back-link">Usar outro e-mail</a>
        </div>

      <?php else: ?>

        
        <div class="auth-form-header">
          <h1>Verificar E-mail</h1>
          <p>Insere o teu e-mail para receber um novo código de verificação.</p>
        </div>

        <?php if (!empty($resendErrors)): ?>
          <div class="alert alert-error" style="margin-bottom:16px;">
            <?php foreach ($resendErrors as $err): ?>
              <p><?php echo e($err); ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="/verify" class="auth-form" novalidate id="verify-email-form">
          <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
          <input type="hidden" id="g-recaptcha-verify" name="g_recaptcha_token">
          <input type="text" name="hp_website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:1px;width:1px;overflow:hidden;">
          <div class="form-group">
            <label for="email">E-mail da conta</label>
            <input type="email" id="email" name="email" placeholder="o-teu@email.com" autocomplete="email" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block auth-submit-btn">
            Enviar código de verificação
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </form>

        <div style="text-align:center;margin-top:20px;">
          <a href="/login" class="auth-back-link">Voltar ao Login</a>
        </div>

      <?php endif; ?>

    </div>
  </div>

</div>

<script>
  
  (function() {
    var inputs  = document.querySelectorAll('.otp-input');
    var hidden  = document.getElementById('code-hidden');
    var submit  = document.getElementById('otp-submit');
    if (!inputs.length || !hidden) return;

    function updateHidden() {
      var val = Array.from(inputs).map(function(i) { return i.value; }).join('');
      hidden.value = val;
      if (submit) submit.disabled = val.length < 6;
      inputs.forEach(function(i) {
        i.classList.toggle('filled', i.value.length > 0);
      });
    }

    inputs.forEach(function(input, idx) {
      input.addEventListener('input', function(e) {
        var v = e.target.value.replace(/\D/g, '');
        e.target.value = v ? v[v.length - 1] : '';
        e.target.classList.remove('error');
        updateHidden();
        if (v && idx < inputs.length - 1) inputs[idx + 1].focus();
      });

      input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !input.value && idx > 0) {
          inputs[idx - 1].focus();
          inputs[idx - 1].value = '';
          updateHidden();
        }
        if (e.key === 'ArrowLeft' && idx > 0) inputs[idx - 1].focus();
        if (e.key === 'ArrowRight' && idx < inputs.length - 1) inputs[idx + 1].focus();
      });

      input.addEventListener('focus', function() { input.select(); });

      input.addEventListener('paste', function(e) {
        e.preventDefault();
        var paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
        inputs.forEach(function(inp, i) { inp.value = paste[i] || ''; inp.classList.remove('error'); });
        updateHidden();
        var focusIdx = Math.min(paste.length, inputs.length - 1);
        inputs[focusIdx].focus();
      });
    });


    if (inputs[0]) inputs[0].focus();


    var otpForm = document.getElementById('otp-form');
    if (otpForm) {
      otpForm.addEventListener('submit', function() {
        if (submit) { submit.disabled = true; submit.textContent = 'A verificar…'; }
      });
    }
  })();

  
  (function(){
    if (window.matchMedia('(hover: none)').matches) return;
    var el = document.createElement('div');
    el.id = 'custom-cursor';
    document.body.appendChild(el);
    document.addEventListener('mousemove', function(e) {
      el.style.left = e.clientX + 'px';
      el.style.top  = e.clientY + 'px';
    });
    document.addEventListener('mousedown', function() { el.classList.add('clicking'); });
    document.addEventListener('mouseup',   function() { el.classList.remove('clicking'); });
    document.querySelectorAll('a, button, input, textarea, select, label').forEach(function(n) {
      n.addEventListener('mouseenter', function() { el.classList.add('hovering'); });
      n.addEventListener('mouseleave', function() { el.classList.remove('hovering'); });
    });
  })();

  /* ── reCAPTCHA v3 ── */
  (function() {
    var siteKey = <?= json_encode($recaptchaSiteKey) ?>;
    if (!siteKey) return;

    function protect(formId, tokenInputId, action) {
      var form       = document.getElementById(formId);
      var tokenInput = document.getElementById(tokenInputId);
      if (!form || !tokenInput) return;
      form.addEventListener('submit', function(e) {
        if (tokenInput.value) return;
        e.preventDefault();
        var btn = form.querySelector('[type=submit]');
        if (btn) { btn.disabled = true; btn.textContent = 'A verificar…'; }
        var done = false;
        function proceed(token) {
          if (done) return; done = true;
          if (token) tokenInput.value = token;
          form.submit();
        }
        var timer = setTimeout(function() { proceed(''); }, 4000);
        try {
          grecaptcha.ready(function() {
            grecaptcha.execute(siteKey, {action: action})
              .then(function(t) { clearTimeout(timer); proceed(t); })
              .catch(function() { clearTimeout(timer); proceed(''); });
          });
        } catch(err) { clearTimeout(timer); proceed(''); }
      });
    }

    protect('resend-form',       'g-recaptcha-resend', 'resend');
    protect('verify-email-form', 'g-recaptcha-verify', 'resend');
  })();
</script>

</body>
</html>
