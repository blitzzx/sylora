<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/mailer.php';

if (isLoggedIn()) {
    redirect('/');
}

$sent            = false;
$errors          = [];
$recaptchaSiteKey = getenv('RECAPTCHA_SITE_KEY') ?: '';
$rcDebug         = $_SESSION['_rc_debug'] ?? null;
unset($_SESSION['_rc_debug']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf  = $_POST['_csrf'] ?? '';
    $email = sanitize($_POST['email'] ?? '');
    $ip    = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if (!verifyCSRFToken($csrf)) {
        $errors[] = 'Pedido inválido. Tenta novamente.';
    } elseif (!empty($_POST['hp_website'])) {
        // Honeypot preenchido: bot detetado — fingir sucesso
        $sent = true;
    } elseif (!verifyRecaptchaV3($_POST['g_recaptcha_token'] ?? '', 'forgot')) {
        $errors[] = 'Verificação de segurança falhou. Tenta novamente.';
    } elseif (!checkEmailRateLimit($ip, 'forgot', 3)) {
        $errors[] = 'Demasiadas tentativas. Aguarda uns minutos.';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Endereço de e-mail inválido.';
    } else {
        
        
        
        $allowed = checkActionRateLimit('forgot_ip', $ip, 5, 60)
                && checkActionRateLimit('forgot_email', strtolower($email), 3, 60);
        if ($allowed) {
            recordActionAttempt('forgot_ip', $ip, 1);
            recordActionAttempt('forgot_email', strtolower($email), 1);
            $stmt = $conn->prepare('SELECT id, username FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user) {
                $token = createPasswordResetToken((int)$user['id']);
                mailPasswordReset($email, $user['username'], $token);
            }
        }
        $sent = true;
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt" data-theme="">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar Password - Sylora</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=<?php echo @filemtime('css/style.css') ?: '1'; ?>">
  <link rel="icon" type="image/png" href="assets/img/FavIcon-Sylora.png">
  <?php if ($recaptchaSiteKey): ?>
  <script src="https://www.google.com/recaptcha/api.js?render=<?= e($recaptchaSiteKey) ?>"></script>
  <?php endif; ?>
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
        <h2>A memória pode ser recuperada.</h2>
        <p class="auth-deco-sub">Insere o teu e-mail e enviamos um link para repores a tua password.</p>
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
      <a href="/login" class="auth-back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Login
      </a>
    </div>

    <div class="auth-form-inner">

      <div class="auth-form-header">
        <h1>Recuperar Password</h1>
        <?php if ($sent): ?>
          <p>Se esse e-mail estiver registado, receberás um link em breve. Verifica também a pasta de spam.</p>
        <?php else: ?>
          <p>Lembras-te da password? <a href="/login">Faz login aqui</a></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $err): ?>
            <p><?php echo e($err); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!$sent): ?>
        <form method="POST" action="/forgot" class="auth-form" novalidate>
          <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
          <input type="hidden" id="g-recaptcha-token" name="g_recaptcha_token">

          <div class="form-group">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="o-teu@email.com"
              autocomplete="email"
              required
            >
          </div>

          <input type="text" name="hp_website" id="hp_website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:1px;width:1px;overflow:hidden;">

          <button type="submit" class="btn btn-primary btn-block auth-submit-btn">
            Enviar link de recuperação
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </form>
      <?php else: ?>
        <a href="/login" class="btn btn-primary btn-block auth-submit-btn" style="text-align:center;">
          Ir para o Login
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      <?php endif; ?>

    </div>
  </div>

</div>

<script>
  (function(){
    if (window.matchMedia('(hover: none)').matches) return;
    const el = document.createElement('div');
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

  /* ── reCAPTCHA v3 debug + execução ── */
  (function() {
    var siteKey = <?= json_encode($recaptchaSiteKey) ?>;

    function rcToast(msg, ok) {
      var t = document.createElement('div');
      t.style.cssText = 'position:fixed;bottom:20px;left:20px;z-index:99999;padding:10px 14px;border-radius:8px;font-size:12px;font-family:monospace;max-width:420px;word-break:break-all;box-shadow:0 4px 16px rgba(0,0,0,0.5);pointer-events:none;' +
        (ok ? 'background:#0a2a0a;border:1px solid #3a7a3a;color:#7aad6e;' : 'background:#2a0a0a;border:1px solid #8a3a3a;color:#c96b5a;');
      t.innerHTML = '<strong>' + (ok ? '✓ reCAPTCHA' : '✗ reCAPTCHA') + '</strong><br>' + msg;
      document.body.appendChild(t);
      setTimeout(function() { t.style.transition='opacity .4s'; t.style.opacity='0'; setTimeout(function(){t.remove();},400); }, ok ? 6000 : 15000);
    }

    <?php if ($rcDebug): ?>
    (function() {
      var d = <?= json_encode($rcDebug) ?>;
      if (d.skipped) { rcToast('Saltado — ' + d.reason, false); return; }
      if (d.error)   { rcToast('Erro API: ' + d.error, false); return; }
      var ok = d.success && (d.score >= 0.5);
      rcToast(
        'success=' + d.success +
        ' | score=' + (d.score !== undefined ? d.score.toFixed(2) : '?') +
        ' | action=' + (d.action || '?') +
        (d['error-codes'] ? ' | erros=' + JSON.stringify(d['error-codes']) : ''),
        ok
      );
    })();
    <?php endif; ?>

    if (!siteKey) return;
    var form       = document.querySelector('form.auth-form');
    var tokenInput = document.getElementById('g-recaptcha-token');
    var submitBtn  = form ? form.querySelector('[type=submit]') : null;
    if (!form || !tokenInput) return;
    form.addEventListener('submit', function(e) {
      if (tokenInput.value) return;
      e.preventDefault();
      if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'A verificar…'; }
      var done = false;
      function proceed(token) {
        if (done) return; done = true;
        if (token) tokenInput.value = token;
        form.submit();
      }
      var timer = setTimeout(function() {
        rcToast('Timeout 4s — script não carregou ou domínio não registado', false);
        proceed('');
      }, 4000);
      try {
        rcToast('grecaptcha.ready() chamado…', true);
        grecaptcha.ready(function() {
          grecaptcha.execute(siteKey, {action: 'forgot'})
            .then(function(t) { clearTimeout(timer); rcToast('Token obtido: ' + t.substring(0,24) + '…', true); proceed(t); })
            .catch(function(err) { clearTimeout(timer); rcToast('execute() falhou: ' + (err && err.message ? err.message : String(err)), false); proceed(''); });
        });
      } catch(err) { clearTimeout(timer); rcToast('grecaptcha indefinido: ' + err.message, false); proceed(''); }
    });
  })();
</script>

</body>
</html>
