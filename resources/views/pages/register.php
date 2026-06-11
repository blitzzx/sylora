<?php
if (isLoggedIn()) {
    redirect('/');
}

$errors          = [];
$formData        = ['username' => '', 'email' => ''];
$recaptchaSiteKey = getenv('RECAPTCHA_SITE_KEY') ?: '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['_csrf'] ?? '';
    $ip   = $_SERVER['REMOTE_ADDR'];

    if (!verifyCSRFToken($csrf)) {
        $errors[] = t('err.invalid_request');
    } elseif (!empty($_POST['hp_website'])) {
        $_SESSION['verify_for'] = sanitize($_POST['email'] ?? '') ?: 'bot@sylora.lol';
        redirect('/verify?sent=1');
    } elseif (!verifyRecaptchaV3($_POST['g_recaptcha_token'] ?? '', 'register')) {
        $errors[] = t('err.security_failed');
    } elseif (!checkEmailRateLimit($ip, 'register')) {
        $errors[] = t('err.too_many_min');
    } else {
        $username        = sanitize($_POST['username'] ?? '');
        $email           = sanitize($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $ip              = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $formData = ['username' => $username, 'email' => $email];

        $termsAccepted = !empty($_POST['terms']);

        if (!checkActionRateLimit('register', $ip, 5, 60)) {
            $errors[] = t('err.too_many_hour');
        } elseif (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $errors[] = t('err.fill_all');
        } elseif (!$termsAccepted) {
            $errors[] = t('err.terms_required');
        } elseif (!isValidUsername($username)) {
            $errors[] = t('err.username_format');
        } elseif (!isValidEmail($email)) {
            $errors[] = t('err.invalid_email');
        } elseif (!isValidPassword($password)) {
            $errors[] = t('err.pw_short');
        } elseif ($password !== $confirmPassword) {
            $errors[] = t('err.pw_mismatch');
        } else {
            $stmt = $conn->prepare('DELETE FROM users WHERE email = ? AND is_active = 0 AND email_verified_at IS NULL');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
            $stmt->bind_param('ss', $email, $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors[] = t('err.username_taken');
                $stmt->close();
            } else {
                $stmt->close();

                $stmt = $conn->prepare('SELECT id FROM pending_registrations WHERE username = ? AND email != ? AND expires_at > NOW() LIMIT 1');
                $stmt->bind_param('ss', $username, $email);
                $stmt->execute();
                $stmt->store_result();
                $usernamePending = $stmt->num_rows > 0;
                $stmt->close();

                if ($usernamePending) {
                    $errors[] = t('err.username_pending');
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $emailConfigured = !empty(getenv('RESEND_API_KEY')) || !empty(getenv('SMTP_HOST'));
                    if (!$emailConfigured) {
                        $stmt = $conn->prepare('INSERT INTO users (username, email, password, is_active, email_verified_at, created_at) VALUES (?, ?, ?, 1, NOW(), NOW())');
                        $stmt->bind_param('sss', $username, $email, $hash);
                        $stmt->execute();
                        $newId = $conn->insert_id;
                        $stmt->close();
                        loginUser($newId, $username, $email, 'user');
                        redirect('/', t('flash.account_created', ['name' => e($username)]), 'success');
                    }

                    $code = createPendingRegistration($email, $username, $hash);
                    $emailSent = mailVerification($email, $username, $code);
                    recordEmailAttempt($ip, 'register');
                    $_SESSION['verify_for'] = $email;
                    if (!$emailSent) {
                        error_log('[Sylora Register] Email falhou para ' . $email);
                    }
                    redirect('/verify?sent=1');
                }
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>" data-theme="">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('register.title') ?> - Sylora</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
<?php foreach (['variables', 'base', 'animations', 'components', 'layout', 'pages'] as $cssFile): ?>
  <link rel="stylesheet" href="/css/<?= $cssFile ?>.css?v=<?= @filemtime(ROOT . '/public/css/' . $cssFile . '.css') ?: '1' ?>">
<?php endforeach; ?>
  <link rel="icon" type="image/png" href="/assets/img/FavIcon-Sylora.png">
  <link rel="apple-touch-icon" href="/assets/img/FavIcon-Sylora.png">
  <?php if ($recaptchaSiteKey): ?>
  <script src="https://www.google.com/recaptcha/api.js?render=<?= e($recaptchaSiteKey) ?>"></script>
  <?php endif; ?>
  <style>
    @media (max-width: 767px) {
      .auth-split { flex-direction: column; min-height: 100dvh; }
      .auth-deco  { display: none; }
      .auth-form-panel { width: 100%; min-height: 100dvh; padding: 0; }
      .auth-form-inner { padding: 24px 20px 40px; }
      .form-row-two { grid-template-columns: 1fr !important; gap: 0; }
    }
    @media (max-width: 480px) {
      .auth-form-inner { padding: 20px 16px 36px; }
      .auth-form-top   { padding: 14px 16px; }
    }
    .terms-error-msg {
      display: none;
      color: #c96b5a;
      font-size: 12px;
      width: 100%;
    }
    .terms-overlay {
      position: fixed; inset: 0;
      background: rgba(0,0,0,0.72);
      backdrop-filter: blur(5px);
      z-index: 9000;
      display: flex;
      align-items: flex-end;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity .22s;
    }
    .terms-overlay.open { opacity: 1; pointer-events: all; }
    .terms-sheet {
      background: #0d0d14;
      border: 1px solid rgba(201,153,58,.28);
      border-radius: 18px 18px 0 0;
      width: 100%;
      max-width: 640px;
      max-height: 88dvh;
      display: flex;
      flex-direction: column;
      transform: translateY(110%);
      transition: transform .32s cubic-bezier(.4,0,.2,1);
    }
    .terms-overlay.open .terms-sheet { transform: translateY(0); }
    .terms-sheet-handle {
      width: 40px; height: 4px;
      background: rgba(201,153,58,.3);
      border-radius: 2px;
      margin: 12px auto 0;
      flex-shrink: 0;
    }
    .terms-sheet-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 22px 14px;
      border-bottom: 1px solid rgba(201,153,58,.12);
      flex-shrink: 0;
    }
    .terms-sheet-title {
      font-family: 'Cinzel', serif;
      font-size: 15px; color: #e8c46a; letter-spacing: 1.5px; margin: 0;
    }
    .terms-sheet-close {
      background: none; border: none; color: #5a4a32; cursor: pointer;
      padding: 4px; display: flex; align-items: center; border-radius: 6px;
      transition: color .18s, background .18s;
    }
    .terms-sheet-close:hover { color: #e8c46a; background: rgba(201,153,58,.08); }
    .terms-sheet-body {
      overflow-y: auto; padding: 20px 22px; flex: 1;
      color: #a89878; font-size: 14px; line-height: 1.8;
    }
    .terms-sheet-body::-webkit-scrollbar { width: 4px; }
    .terms-sheet-body::-webkit-scrollbar-track { background: transparent; }
    .terms-sheet-body::-webkit-scrollbar-thumb { background: rgba(201,153,58,.25); border-radius: 2px; }
    .terms-sheet-body h3 {
      font-family: 'Cinzel', serif;
      font-size: 12px; color: #e8c46a;
      letter-spacing: .8px; text-transform: uppercase;
      margin: 22px 0 8px; padding-bottom: 6px;
      border-bottom: 1px solid rgba(201,153,58,.1);
    }
    .terms-sheet-body h3:first-child { margin-top: 0; }
    .terms-sheet-body p { margin: 0 0 10px; }
    .terms-sheet-body ul { margin: 0 0 10px; padding-left: 18px; }
    .terms-sheet-body li { margin-bottom: 5px; }
    .terms-sheet-footer {
      padding: 14px 22px 20px;
      border-top: 1px solid rgba(201,153,58,.12);
      flex-shrink: 0;
      display: flex; gap: 10px;
    }
    @media (min-width: 640px) {
      .terms-overlay { align-items: center; }
      .terms-sheet { border-radius: 14px; transform: translateY(20px) scale(.97); max-height: 82dvh; }
      .terms-overlay.open .terms-sheet { transform: translateY(0) scale(1); }
      .terms-sheet-handle { display: none; }
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
  <script>window.SYLORA_I18N=<?= json_encode(['en'=>require ROOT.'/resources/lang/en.php','pt'=>require ROOT.'/resources/lang/pt.php','es'=>require ROOT.'/resources/lang/es.php'],JSON_HEX_TAG|JSON_HEX_AMP) ?>;
  window.SYLORA_LANG=<?= json_encode(getLang()) ?>;
  window.SYLORA_T=function(key,vars){var dict=(window.SYLORA_I18N&&window.SYLORA_I18N[window.SYLORA_LANG])||{};var val=(dict[key]!==undefined)?dict[key]:key;if(vars){for(var k in vars){val=val.split('{'+k+'}').join(vars[k]);}}return val;};</script>
</head>
<body class="auth-page">

<div class="auth-split auth-split-register">

  <div class="auth-deco" aria-hidden="true">
    <div class="auth-deco-bg auth-deco-bg-register"></div>
    <div class="auth-deco-content">
      <a href="/" class="auth-deco-logo">
        <img src="/assets/img/Logo-Sylora.png" alt="Sylora" height="64">
      </a>
      <div class="auth-deco-text">
        <p class="auth-deco-overline"><?= t('register.deco_over') ?></p>
        <h2><?= t('register.deco_h2') ?></h2>
        <p class="auth-deco-sub"><?= t('register.deco_sub') ?></p>
      </div>
      <div class="auth-deco-stats">
        <div class="auth-stat">
          <span class="auth-stat-num">5</span>
          <span class="auth-stat-label"><?= t('register.stat_islands') ?></span>
        </div>
        <div class="auth-stat-divider"></div>
        <div class="auth-stat">
          <span class="auth-stat-num">∞</span>
          <span class="auth-stat-label"><?= t('register.stat_choices') ?></span>
        </div>
        <div class="auth-stat-divider"></div>
        <div class="auth-stat">
          <span class="auth-stat-num">1</span>
          <span class="auth-stat-label"><?= t('register.stat_destiny') ?></span>
        </div>
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
        <?= t('nav.home') ?>
      </a>
      <button class="nav-icon-btn auth-theme-toggle" id="auth-theme-toggle" aria-label="Alternar tema">
        <svg id="ath-icon-dark" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg id="ath-icon-light" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
      </button>
    </div>

    <div class="auth-form-inner">

      <div class="auth-form-header">
        <h1><?= t('register.title') ?></h1>
        <p><?= t('register.has_account') ?></p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $err): ?>
            <p><?php echo e($err); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/register" class="auth-form" novalidate>
        <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
        <input type="hidden" id="g-recaptcha-token" name="g_recaptcha_token">
        <input type="text" name="hp_website" id="hp_website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:1px;width:1px;overflow:hidden;">

        <div class="form-row-two">
          <div class="form-group">
            <label for="username"><?= t('register.username') ?></label>
            <input
              type="text"
              id="username"
              name="username"
              placeholder="<?= t('register.username_ph') ?>"
              value="<?php echo e($formData['username']); ?>"
              autocomplete="off"
              required
              minlength="3"
              maxlength="20"
            >
          </div>
          <div class="form-group">
            <label for="email"><?= t('register.email') ?></label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="<?= t('register.email_ph') ?>"
              value="<?php echo e($formData['email']); ?>"
              autocomplete="email"
              required
            >
          </div>
        </div>

        <div class="form-row-two">
          <div class="form-group">
            <label for="password"><?= t('register.password') ?></label>
            <div class="pw-wrap">
              <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                autocomplete="new-password"
                required
                minlength="8"
              >
              <button type="button" class="pw-toggle" aria-label="Mostrar password"><?= t('register.show') ?></button>
            </div>
          </div>
          <div class="form-group">
            <label for="confirm_password"><?= t('register.confirm_pw') ?></label>
            <div class="pw-wrap">
              <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                placeholder="••••••••"
                autocomplete="new-password"
                required
              >
              <button type="button" class="pw-toggle" aria-label="Mostrar password"><?= t('register.show') ?></button>
            </div>
          </div>
        </div>

        <div class="password-strength" id="pw-strength" aria-live="polite">
          <div class="pw-strength-bar">
            <div class="pw-strength-fill" id="pw-strength-fill"></div>
          </div>
          <span class="pw-strength-label" id="pw-strength-label"></span>
        </div>

        <div class="auth-terms">
          <label class="auth-checkbox-label">
            <input type="checkbox" name="terms" id="terms" value="1">
            <span class="auth-checkbox-custom"></span>
            <?= t('register.terms') ?>
          </label>
          <span class="terms-error-msg" id="terms-error"><?= t('register.terms_err') ?></span>
        </div>

        <button type="submit" class="btn btn-primary btn-block auth-submit-btn">
          <?= t('register.submit') ?>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>

      </form>

    </div>
  </div>

</div>

<div class="terms-overlay" id="terms-overlay" role="dialog" aria-modal="true" aria-labelledby="terms-title">
  <div class="terms-sheet">
    <div class="terms-sheet-handle"></div>
    <div class="terms-sheet-header">
      <h2 class="terms-sheet-title" id="terms-title" data-i18n="terms.title"><?= t('terms.title') ?></h2>
      <button class="terms-sheet-close" id="terms-close-btn" aria-label="Fechar">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="terms-sheet-body">
      <h3 data-i18n="terms.s1_title"><?= t('terms.s1_title') ?></h3>
      <p data-i18n="terms.s1_body"><?= t('terms.s1_body') ?></p>

      <h3 data-i18n="terms.s2_title"><?= t('terms.s2_title') ?></h3>
      <p data-i18n="terms.s2_body"><?= t('terms.s2_body') ?></p>

      <h3 data-i18n="terms.s3_title"><?= t('terms.s3_title') ?></h3>
      <div data-i18n-html="terms.s3_body"><?= t('terms.s3_body') ?></div>

      <h3 data-i18n="terms.s4_title"><?= t('terms.s4_title') ?></h3>
      <div data-i18n-html="terms.s4_body"><?= t('terms.s4_body') ?></div>

      <h3 data-i18n="terms.s5_title"><?= t('terms.s5_title') ?></h3>
      <p data-i18n="terms.s5_body"><?= t('terms.s5_body') ?></p>

      <h3 data-i18n="terms.s6_title"><?= t('terms.s6_title') ?></h3>
      <p data-i18n="terms.s6_body"><?= t('terms.s6_body') ?></p>

      <h3 data-i18n="terms.s7_title"><?= t('terms.s7_title') ?></h3>
      <p data-i18n="terms.s7_body"><?= t('terms.s7_body') ?></p>

      <h3 data-i18n="terms.s8_title"><?= t('terms.s8_title') ?></h3>
      <p data-i18n="terms.s8_body"><?= t('terms.s8_body') ?></p>

      <h3 data-i18n="terms.s9_title"><?= t('terms.s9_title') ?></h3>
      <p data-i18n="terms.s9_body"><?= t('terms.s9_body') ?></p>

      <h3 data-i18n="terms.s10_title"><?= t('terms.s10_title') ?></h3>
      <p data-i18n="terms.s10_body"><?= t('terms.s10_body') ?></p>
    </div>
    <div class="terms-sheet-footer">
      <button type="button" id="terms-accept-btn" class="btn btn-primary" style="flex:1;">
        <?= t('terms.accept') ?>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      </button>
      <button type="button" id="terms-decline-btn" class="btn btn-ghost"><?= t('terms.decline') ?></button>
    </div>
  </div>
</div>

<script>
  (function(){
    const html  = document.documentElement;
    const btn   = document.getElementById('auth-theme-toggle');
    const dark  = document.getElementById('ath-icon-dark');
    const light = document.getElementById('ath-icon-light');

    function setTheme(t) {
      html.setAttribute('data-theme', t);
      localStorage.setItem('sylora-theme', t);
      dark.style.display  = t === 'dark'  ? '' : 'none';
      light.style.display = t === 'light' ? '' : 'none';
    }

    setTheme(html.getAttribute('data-theme') || 'dark');
    btn && btn.addEventListener('click', () => {
      setTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
  })();

  (function(){
    const pw    = document.getElementById('password');
    const fill  = document.getElementById('pw-strength-fill');
    const label = document.getElementById('pw-strength-label');
    if (!pw || !fill || !label) return;

    pw.addEventListener('input', () => {
      const val = pw.value;
      let score = 0;
      if (val.length >= 6)  score++;
      if (val.length >= 10) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^a-zA-Z0-9]/.test(val)) score++;

      var T = (window.SYLORA_T) || (function(k){return k;});
      const levels = [
        { pct: '0%',   color: 'transparent', text: '' },
        { pct: '25%',  color: '#c96b5a',     text: T('pw.weak') },
        { pct: '50%',  color: '#d4955a',     text: T('pw.fair') },
        { pct: '75%',  color: '#c9993a',     text: T('pw.good') },
        { pct: '90%',  color: '#7aad6e',     text: T('pw.strong') },
        { pct: '100%', color: '#4e8c3d',     text: T('pw.very_strong') },
      ];
      const lvl = levels[Math.min(score, 5)];
      fill.style.width      = val.length ? lvl.pct : '0%';
      fill.style.background = lvl.color;
      label.textContent     = val.length ? lvl.text : '';
    });
  })();

  (function() {
    var SVG_EYE     = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    var SVG_EYE_OFF = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    var T = (window.SYLORA_T) || (function(k){return k;});
    document.querySelectorAll('.pw-toggle').forEach(function(btn) {
      btn.innerHTML = SVG_EYE;
      btn.setAttribute('aria-label', T('common.show_pw'));
      btn.addEventListener('click', function() {
        var input = btn.closest('.pw-wrap').querySelector('input');
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? SVG_EYE_OFF : SVG_EYE;
        btn.setAttribute('aria-label', show ? T('common.hide_pw') : T('common.show_pw'));
      });
    });
  })();

  (function() {
    const overlay    = document.getElementById('terms-overlay');
    const openLink   = document.getElementById('terms-open-link');
    const closeBtn   = document.getElementById('terms-close-btn');
    const acceptBtn  = document.getElementById('terms-accept-btn');
    const declineBtn = document.getElementById('terms-decline-btn');
    const checkbox   = document.getElementById('terms');
    const errorMsg   = document.getElementById('terms-error');

    function open(e)  { if (e) e.preventDefault(); overlay.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function close()  { overlay.classList.remove('open'); document.body.style.overflow = ''; }

    if (openLink)   openLink.addEventListener('click', open);
    if (closeBtn)   closeBtn.addEventListener('click', close);
    if (declineBtn) declineBtn.addEventListener('click', function() { checkbox.checked = false; close(); });
    if (acceptBtn)  acceptBtn.addEventListener('click', function() {
      checkbox.checked = true;
      if (errorMsg) errorMsg.style.display = 'none';
      close();
    });
    if (overlay) overlay.addEventListener('click', function(e) { if (e.target === overlay) close(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && overlay.classList.contains('open')) close(); });
  })();

  (function() {
    const form     = document.querySelector('form.auth-form');
    const checkbox = document.getElementById('terms');
    const errorMsg = document.getElementById('terms-error');
    const submitBtn = form ? form.querySelector('[type=submit]') : null;

    if (!form || !checkbox) return;

    checkbox.addEventListener('change', function() {
      if (checkbox.checked && errorMsg) errorMsg.style.display = 'none';
    });

    form.addEventListener('submit', function(e) {
      if (!checkbox.checked) {
        e.preventDefault();
        if (errorMsg) { errorMsg.style.display = 'block'; }
        checkbox.closest('.auth-terms').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = (window.SYLORA_T ? window.SYLORA_T('common.creating') : 'A criar conta…');
      }
    });
  })();

  (function() {
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

  (function() {
    var siteKey = <?= json_encode($recaptchaSiteKey) ?>;
    <?php unset($_SESSION['_rc_debug']); ?>
    if (!siteKey) return;
    var form       = document.querySelector('form.auth-form');
    var tokenInput = document.getElementById('g-recaptcha-token');
    var submitBtn  = form ? form.querySelector('[type=submit]') : null;
    if (!form || !tokenInput) return;
    form.addEventListener('submit', function(e) {
      if (tokenInput.value) return;
      e.preventDefault();
      if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = (window.SYLORA_T ? window.SYLORA_T('common.verifying') : 'A verificar…'); }
      var done = false;
      function proceed(token) {
        if (done) return; done = true;
        if (token) tokenInput.value = token;
        form.submit();
      }
      var timer = setTimeout(function() { proceed(''); }, 4000);
      try {
        grecaptcha.ready(function() {
          grecaptcha.execute(siteKey, {action: 'register'})
            .then(function(t) { clearTimeout(timer); proceed(t); })
            .catch(function() { clearTimeout(timer); proceed(''); });
        });
      } catch(err) { clearTimeout(timer); proceed(''); }
    });
  })();
</script>

</body>
</html>
