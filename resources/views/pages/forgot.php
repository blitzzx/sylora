<?php
if (isLoggedIn()) {
    redirect('/');
}

$state            = '';
$errors           = [];
$emailValue       = '';
$recaptchaSiteKey = getenv('RECAPTCHA_SITE_KEY') ?: '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf   = $_POST['_csrf'] ?? '';
    $email  = sanitize($_POST['email'] ?? '');
    $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $emailValue = $email;

    if (!verifyCSRFToken($csrf)) {
        $errors[] = t('err.invalid_request');
    } elseif (!empty($_POST['hp_website'])) {
        $state = 'sent';
    } elseif (!verifyRecaptchaV3($_POST['g_recaptcha_token'] ?? '', 'forgot')) {
        $errors[] = t('err.security_failed');
    } elseif (!checkEmailRateLimit($ip, 'forgot', 3)) {
        $errors[] = t('err.too_many_min');
    } elseif (!isValidEmail($email)) {
        $errors[] = t('err.invalid_email');
    } else {
        $allowed = checkActionRateLimit('forgot_ip', $ip, 5, 60)
                && checkActionRateLimit('forgot_email', strtolower($email), 3, 60);
        if (!$allowed) {
            $errors[] = t('err.too_many_min');
        } else {
            recordActionAttempt('forgot_ip', $ip, 1);
            recordActionAttempt('forgot_email', strtolower($email), 1);

            $stmt = $conn->prepare('SELECT id, username, is_active, email_verified_at FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && (int)$user['is_active'] === 1) {
                $token = createPasswordResetToken((int)$user['id']);
                mailPasswordReset($email, $user['username'], $token);
                $state = 'sent';
            } elseif ($user && empty($user['email_verified_at'])) {
                $state = 'unverified';
            } elseif ($user) {
                $state = 'disabled';
            } else {
                $stmt = $conn->prepare('SELECT 1 FROM pending_registrations WHERE email = ? AND expires_at > NOW() LIMIT 1');
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->store_result();
                $isPending = $stmt->num_rows > 0;
                $stmt->close();
                $state = $isPending ? 'unverified' : 'not_found';
            }
        }
    }

    if ($state === 'unverified') {
        $_SESSION['verify_for'] = $email;
    }
}

$redirectToVerify  = ($state === 'unverified');
$authToastMsg      = $redirectToVerify ? t('auth.verify_redirect') : '';
$authToastType     = 'info';
$authToastRedirect = $redirectToVerify ? '/verify' : '';

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>" data-theme="">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('forgot.title') ?> - Sylora</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
<?php foreach (['variables', 'base', 'animations', 'components', 'layout', 'pages'] as $cssFile): ?>
  <link rel="stylesheet" href="/css/<?= $cssFile ?>.css?v=<?= @filemtime(ROOT . '/public/css/' . $cssFile . '.css') ?: '1' ?>">
<?php endforeach; ?>
  <link rel="icon" type="image/png" href="/assets/img/FavIcon-Sylora.png">
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
  <script>window.SYLORA_I18N=<?= json_encode(['en'=>require ROOT.'/resources/lang/en.php','pt'=>require ROOT.'/resources/lang/pt.php','es'=>require ROOT.'/resources/lang/es.php'],JSON_HEX_TAG|JSON_HEX_AMP) ?>;
  window.SYLORA_LANG=<?= json_encode(getLang()) ?>;
  window.SYLORA_T=function(key,vars){var dict=(window.SYLORA_I18N&&window.SYLORA_I18N[window.SYLORA_LANG])||{};var val=(dict[key]!==undefined)?dict[key]:key;if(vars){for(var k in vars){val=val.split('{'+k+'}').join(vars[k]);}}return val;};</script>
</head>
<body class="auth-page">

<div class="auth-split">

  <div class="auth-deco" aria-hidden="true">
    <div class="auth-deco-bg"></div>
    <div class="auth-deco-content">
      <a href="/" class="auth-deco-logo">
        <img src="/assets/img/Logo-Sylora.png" alt="Sylora" height="64">
      </a>
      <div class="auth-deco-text">
        <p class="auth-deco-overline"><?= t('forgot.deco_over') ?></p>
        <h2><?= t('forgot.deco_h2') ?></h2>
        <p class="auth-deco-sub"><?= t('forgot.deco_sub') ?></p>
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
        <?= t('forgot.back_login') ?>
      </a>
    </div>

    <div class="auth-form-inner">

      <div class="auth-form-header">
        <h1><?= t('forgot.title') ?></h1>
        <p><?= t('forgot.subtitle') ?></p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $err): ?>
            <p><?php echo e($err); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($state === 'sent'): ?>
        <div class="alert alert-success">
          <p><?= t('forgot.sent_explicit', ['email' => e($emailValue)]) ?></p>
        </div>
        <a href="/login" class="btn btn-primary btn-block auth-submit-btn" style="text-align:center;">
          <?= t('forgot.go_login') ?>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>

      <?php elseif ($state === 'disabled'): ?>
        <div class="alert alert-error">
          <p><?= t('err.account_disabled') ?></p>
        </div>
        <a href="/login" class="btn btn-primary btn-block auth-submit-btn" style="text-align:center;">
          <?= t('forgot.go_login') ?>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>

      <?php elseif ($state === 'unverified'): ?>
        <div class="alert alert-info">
          <p><?= t('auth.verify_redirect') ?></p>
        </div>
        <a href="/verify" class="btn btn-primary btn-block auth-submit-btn" style="text-align:center;">
          <?= t('verify.title_form') ?>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>

      <?php else: ?>
        <?php if ($state === 'not_found'): ?>
          <div class="alert alert-error">
            <p><?= t('forgot.not_found') ?></p>
          </div>
        <?php endif; ?>
        <form method="POST" action="/forgot" class="auth-form" data-rc-action="forgot" novalidate>
          <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
          <input type="hidden" id="g-recaptcha-token" name="g_recaptcha_token">

          <div class="form-group">
            <label for="email"><?= t('forgot.email_label') ?></label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="<?= t('forgot.email_ph') ?>"
              value="<?php echo e($emailValue); ?>"
              autocomplete="email"
              required
            >
          </div>

          <input type="text" name="hp_website" id="hp_website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:1px;width:1px;overflow:hidden;">

          <button type="submit" class="btn btn-primary btn-block auth-submit-btn">
            <?= t('forgot.submit') ?>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </form>
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

  (function() {
    var siteKey = <?= json_encode($recaptchaSiteKey) ?>;
    <?php unset($_SESSION['_rc_debug']); ?>
    if (!siteKey) return;
    document.querySelectorAll('form.auth-form').forEach(function(form) {
      var tokenInput = form.querySelector('input[name=g_recaptcha_token]');
      var submitBtn  = form.querySelector('[type=submit]');
      var action     = form.getAttribute('data-rc-action') || 'forgot';
      if (!tokenInput) return;
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
            grecaptcha.execute(siteKey, {action: action})
              .then(function(t) { clearTimeout(timer); proceed(t); })
              .catch(function() { clearTimeout(timer); proceed(''); });
          });
        } catch(err) { clearTimeout(timer); proceed(''); }
      });
    });
  })();
</script>

<div id="sylora-toast" aria-live="polite" aria-atomic="true"></div>
<script>
function showToast(msg, type){
  var t = document.getElementById('sylora-toast');
  if (!t) return;
  t.textContent = msg;
  t.className = 'sylora-toast-show sylora-toast-' + (type || 'info');
  clearTimeout(t._timer);
  t._timer = setTimeout(function(){ t.className = ''; }, 3800);
}
<?php if (!empty($authToastMsg)): ?>
(function(){
  var msg      = <?= json_encode($authToastMsg) ?>;
  var type     = <?= json_encode($authToastType ?? 'info') ?>;
  var redirect = <?= json_encode($authToastRedirect ?? '') ?>;
  var delay    = <?= (int)($authToastDelay ?? 2500) ?>;
  setTimeout(function(){ showToast(msg, type); }, 120);
  if (redirect) { setTimeout(function(){ window.location.href = redirect; }, delay); }
})();
<?php endif; ?>
</script>

</body>
</html>
