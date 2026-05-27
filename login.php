<?php
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
    redirect('/');
}

$errors     = [];
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['_csrf'] ?? '';
    if (!verifyCSRFToken($csrf)) {
        $errors[] = t('err.invalid_request');
    } else {
        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $ip       = $_SERVER['REMOTE_ADDR'];

        $emailValue = $email;

        if (empty($email) || empty($password)) {
            $errors[] = t('err.fill_all');
        } elseif (!isValidEmail($email)) {
            $errors[] = t('err.invalid_email');
        } elseif (!checkLoginRateLimit($ip)) {
            $errors[] = t('err.too_many');
        } else {
            $stmt = $conn->prepare('SELECT id, username, email, password, role, is_active, email_verified_at FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                if (empty($user['is_active'])) {
                    recordLoginAttempt($ip, $email, 0);
                    if (empty($user['email_verified_at'])) {
                        $errors[]      = t('err.verify_required');
                        $showVerifyLink = true;
                    } else {
                        $errors[] = t('err.account_disabled');
                    }
                } else {
                    recordLoginAttempt($ip, $email, 1);
                    loginUser($user['id'], $user['username'], $user['email'], $user['role']);

                    if ($remember) {
                        createRememberMeToken($user['id']);
                    }

                    redirect('/', t('flash.welcome_back', ['name' => e($user['username'])]), 'success');
                }
            } else {
                recordLoginAttempt($ip, $email, 0);
                $errors[] = t('err.bad_credentials');
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
  <title><?= t('login.title') ?> - Sylora</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=<?php echo @filemtime('css/style.css') ?: '1'; ?>">
  <link rel="icon" type="image/png" href="assets/img/FavIcon-Sylora.png">
  <link rel="apple-touch-icon" href="assets/img/FavIcon-Sylora.png">
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
        <p class="auth-deco-overline"><?= t('login.deco_over') ?></p>
        <h2><?= t('login.deco_h2') ?></h2>
        <p class="auth-deco-sub"><?= t('login.deco_sub') ?></p>
      </div>
      <div class="auth-deco-orbs" aria-hidden="true">
        <span class="auth-orb ao1"></span>
        <span class="auth-orb ao2"></span>
        <span class="auth-orb ao3"></span>
      </div>
      <div class="auth-deco-runes">
        <span>⊕</span><span>✦</span><span>◈</span><span>⟡</span><span>✦</span>
      </div>
    </div>
  </div>

  
  <div class="auth-form-panel">

    <div class="auth-form-top">
      <a href="/" class="auth-back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        <?= t('login.back_home') ?>
      </a>
      <button class="nav-icon-btn auth-theme-toggle" id="auth-theme-toggle" aria-label="Alternar tema">
        <svg id="ath-icon-dark" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg id="ath-icon-light" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
      </button>
    </div>

    <div class="auth-form-inner">

      <div class="auth-form-header">
        <h1><?= t('login.title') ?></h1>
        <p><?= t('login.no_account') ?></p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $err): ?>
            <p><?php echo e($err); ?></p>
          <?php endforeach; ?>
          <?php if (!empty($showVerifyLink)): ?>
            <p><a href="/verify"><?= t('login.resend_verify') ?></a></p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo e($_SESSION['flash_type'] ?? 'info'); ?>">
          <?php echo e($_SESSION['flash_message']); ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
      <?php endif; ?>

      <form method="POST" action="/login" class="auth-form" novalidate>
        <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">

        <div class="form-group">
          <label for="email"><?= t('login.email_label') ?></label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="<?= t('login.email_ph') ?>"
            value="<?php echo e($emailValue); ?>"
            autocomplete="email"
            required
          >
        </div>

        <div class="form-group">
          <label for="password"><?= t('login.password') ?></label>
          <div class="pw-wrap">
            <input
              type="password"
              id="password"
              name="password"
              placeholder="••••••••"
              autocomplete="current-password"
              required
            >
            <button type="button" class="pw-toggle" aria-label="Mostrar ou esconder password"><?= t('login.show') ?></button>
          </div>
        </div>

        <div class="auth-remember-row">
          <label class="auth-checkbox-label">
            <input type="checkbox" name="remember" id="remember">
            <span class="auth-checkbox-custom"></span>
            <?= t('login.remember') ?>
          </label>
          <a href="/forgot" class="auth-forgot-link"><?= t('login.forgot') ?></a>
        </div>

        <button type="submit" class="btn btn-primary btn-block auth-submit-btn">
          <?= t('login.submit') ?>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>

      </form>


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

  (function() {
    var SVG_EYE     = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    var SVG_EYE_OFF = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    var T = (window.SYLORA_T) || (function(k){return k;});
    document.querySelectorAll('.pw-toggle').forEach(function(btn) {
      btn.innerHTML = SVG_EYE;
      btn.setAttribute('aria-label', T('common.show_pw'));
      btn.addEventListener('click', function() {
        var input = btn.closest('.pw-wrap').querySelector('input');
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? SVG_EYE_OFF : SVG_EYE;
        btn.setAttribute('aria-label', show ? T('common.hide_pw') : T('common.show_pw'));
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
