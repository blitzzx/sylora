<?php
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
    redirect('/');
}

$rawToken  = sanitize($_GET['t'] ?? '');
$errors    = [];
$resetData = $rawToken ? verifyPasswordResetToken($rawToken) : false;

if (!$rawToken || !$resetData) {
    redirect('/forgot', t('err.reset_link_bad'), 'error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf      = $_POST['_csrf'] ?? '';
    $postToken = sanitize($_POST['_reset_token'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';


    $expectedCsrf = hash_hmac('sha256', $rawToken, 'sylora-reset-csrf-v1');

    if (!hash_equals($expectedCsrf, $csrf)) {
        $errors[] = t('err.invalid_request');
    } elseif (!isValidPassword($password)) {
        $errors[] = t('err.pw_short');
    } elseif ($password !== $confirm) {
        $errors[] = t('err.pw_mismatch');
    } else {
        $rd = verifyPasswordResetToken($postToken);
        if (!$rd) {
            $errors[]  = t('err.reset_expired');
            $showLinkToForgot = true;
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->bind_param('si', $hash, $rd['user_id']);
            $stmt->execute();
            $stmt->close();

            consumePasswordResetToken($rd['reset_id']);
            revokeAllUserSessions($rd['user_id']);

            redirect('/login', t('flash.pw_reset_ok'), 'success');
        }
    }
}

$csrfToken = hash_hmac('sha256', $rawToken, 'sylora-reset-csrf-v1');
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>" data-theme="">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('reset.title') ?> - Sylora</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=<?php echo @filemtime('css/style.css') ?: '1'; ?>">
  <link rel="icon" type="image/png" href="assets/img/FavIcon-Sylora.png">
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
  <script>window.SYLORA_I18N=<?= json_encode(['en'=>require __DIR__.'/lang/en.php','pt'=>require __DIR__.'/lang/pt.php','es'=>require __DIR__.'/lang/es.php'],JSON_HEX_TAG|JSON_HEX_AMP) ?>;
  window.SYLORA_LANG=<?= json_encode(getLang()) ?>;
  window.SYLORA_T=function(key,vars){var dict=(window.SYLORA_I18N&&window.SYLORA_I18N[window.SYLORA_LANG])||{};var val=(dict[key]!==undefined)?dict[key]:key;if(vars){for(var k in vars){val=val.split('{'+k+'}').join(vars[k]);}}return val;};</script>
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
        <p class="auth-deco-overline"><?= t('reset.deco_over') ?></p>
        <h2><?= t('reset.deco_h2') ?></h2>
        <p class="auth-deco-sub"><?= t('reset.deco_sub') ?></p>
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
        <?= t('login.title') ?>
      </a>
    </div>

    <div class="auth-form-inner">

      <div class="auth-form-header">
        <h1><?= t('reset.title') ?></h1>
        <p><?= t('reset.subtitle') ?></p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $err): ?>
            <p><?php echo e($err); ?></p>
          <?php endforeach; ?>
          <?php if (!empty($showLinkToForgot)): ?>
            <p><a href="/forgot" data-i18n="common.new_link"><?= t('common.new_link') ?></a></p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/reset?t=<?php echo urlencode($rawToken); ?>" class="auth-form" novalidate>
        <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
        <input type="hidden" name="_reset_token" value="<?php echo e($rawToken); ?>">

        <div class="form-group">
          <label for="password"><?= t('reset.pw_label') ?></label>
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
            <button type="button" class="pw-toggle" aria-label="Mostrar password"><?= t('reset.show') ?></button>
          </div>
        </div>

        <div class="form-group">
          <label for="confirm_password"><?= t('reset.pw_confirm') ?></label>
          <div class="pw-wrap">
            <input
              type="password"
              id="confirm_password"
              name="confirm_password"
              placeholder="••••••••"
              autocomplete="new-password"
              required
            >
            <button type="button" class="pw-toggle" aria-label="Mostrar password"><?= t('reset.show') ?></button>
          </div>
        </div>

        <div class="password-strength" id="pw-strength" aria-live="polite">
          <div class="pw-strength-bar">
            <div class="pw-strength-fill" id="pw-strength-fill"></div>
          </div>
          <span class="pw-strength-label" id="pw-strength-label"></span>
        </div>

        <button type="submit" class="btn btn-primary btn-block auth-submit-btn">
          <?= t('reset.submit') ?>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </form>

    </div>
  </div>

</div>

<script>
  (function(){
    const pw    = document.getElementById('password');
    const fill  = document.getElementById('pw-strength-fill');
    const label = document.getElementById('pw-strength-label');
    if (!pw || !fill || !label) return;
    pw.addEventListener('input', () => {
      const val = pw.value;
      let score = 0;
      if (val.length >= 8)  score++;
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
    var T2 = (window.SYLORA_T) || (function(k){return k;});
    document.querySelectorAll('.pw-toggle').forEach(function(btn) {
      btn.innerHTML = SVG_EYE;
      btn.setAttribute('aria-label', T2('common.show_pw'));
      btn.addEventListener('click', function() {
        var input = btn.closest('.pw-wrap').querySelector('input');
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? SVG_EYE_OFF : SVG_EYE;
        btn.setAttribute('aria-label', show ? T2('common.hide_pw') : T2('common.show_pw'));
      });
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
</script>

</body>
</html>
