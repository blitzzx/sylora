<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/mailer.php';

if (isLoggedIn()) {
    redirect('/');
}

$errors   = [];
$formData = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['_csrf'] ?? '';
    if (!verifyCSRFToken($csrf)) {
        $errors[] = 'Pedido inválido. Tenta novamente.';
    } else {
        $username        = sanitize($_POST['username'] ?? '');
        $email           = sanitize($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $ip              = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $formData = ['username' => $username, 'email' => $email];

        $termsAccepted = !empty($_POST['terms']);

        
        if (!checkActionRateLimit('register', $ip, 5, 60)) {
            $errors[] = 'Demasiadas tentativas de registo. Aguarda uma hora.';
        } elseif (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $errors[] = 'Preenche todos os campos.';
        } elseif (!$termsAccepted) {
            $errors[] = 'Tens de aceitar os termos de utilização.';
        } elseif (!isValidUsername($username)) {
            $errors[] = 'Username deve ter entre 3 e 20 caracteres (letras, números e _).';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Email inválido.';
        } elseif (!isValidPassword($password)) {
            $errors[] = 'A password deve ter pelo menos 8 caracteres.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'As passwords não coincidem.';
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
                $errors[] = 'Este email ou username já está em uso.';
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
                    $errors[] = 'Este username já está em uso.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $emailConfigured = !empty(getenv('RESEND_API_KEY')) || !empty(getenv('SMTP_HOST'));
                    if (!$emailConfigured) {
                        $stmt = $conn->prepare('INSERT INTO users (username, email, password, role, is_active, email_verified_at, created_at) VALUES (?, ?, ?, "user", 1, NOW(), NOW())');
                        $stmt->bind_param('sss', $username, $email, $hash);
                        $stmt->execute();
                        $newId = $conn->insert_id;
                        $stmt->close();
                        loginUser($newId, $username, $email, 'user');
                        redirect('/', 'Conta criada! Bem-vindo, ' . e($username) . '!', 'success');
                    }

                    $code = createPendingRegistration($email, $username, $hash);
                    $emailSent = mailVerification($email, $username, $code);
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
<html lang="pt" data-theme="">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Conta - Sylora</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=<?php echo @filemtime('css/style.css') ?: '1'; ?>">
  <link rel="icon" type="image/png" href="assets/img/FavIcon-Sylora.png">
  <link rel="apple-touch-icon" href="assets/img/FavIcon-Sylora.png">
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
      margin-top: 6px;
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
</head>
<body class="auth-page">

<div class="auth-split auth-split-register">

  
  <div class="auth-deco" aria-hidden="true">
    <div class="auth-deco-bg auth-deco-bg-register"></div>
    <div class="auth-deco-content">
      <a href="/" class="auth-deco-logo">
        <img src="assets/img/Logo-Sylora.png" alt="Sylora" height="64">
      </a>
      <div class="auth-deco-text">
        <p class="auth-deco-overline">✦ Começa a tua jornada</p>
        <h2>O teu destino foi escrito nas estrelas.</h2>
        <p class="auth-deco-sub">Junta-te a outros aventureiros. Explora ilhas corrompidas e descobre a tua memória esquecida.</p>
      </div>
      <div class="auth-deco-stats">
        <div class="auth-stat">
          <span class="auth-stat-num">5</span>
          <span class="auth-stat-label">Ilhas</span>
        </div>
        <div class="auth-stat-divider"></div>
        <div class="auth-stat">
          <span class="auth-stat-num">∞</span>
          <span class="auth-stat-label">Escolhas</span>
        </div>
        <div class="auth-stat-divider"></div>
        <div class="auth-stat">
          <span class="auth-stat-num">1</span>
          <span class="auth-stat-label">Destino</span>
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
        Início
      </a>
      <button class="nav-icon-btn auth-theme-toggle" id="auth-theme-toggle" aria-label="Alternar tema">
        <svg id="ath-icon-dark" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg id="ath-icon-light" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
      </button>
    </div>

    <div class="auth-form-inner">

      <div class="auth-form-header">
        <h1>Criar Conta</h1>
        <p>Já tens conta? <a href="/login">Faz login aqui</a></p>
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

        <div class="form-row-two">
          <div class="form-group">
            <label for="username">Username</label>
            <input
              type="text"
              id="username"
              name="username"
              placeholder="aventureiro_123"
              value="<?php echo e($formData['username']); ?>"
              autocomplete="username"
              required
              minlength="3"
              maxlength="20"
            >
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="o-teu@email.com"
              value="<?php echo e($formData['email']); ?>"
              autocomplete="email"
              required
            >
          </div>
        </div>

        <div class="form-row-two">
          <div class="form-group">
            <label for="password">Password</label>
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
              <button type="button" class="pw-toggle" aria-label="Mostrar password">Mostrar</button>
            </div>
          </div>
          <div class="form-group">
            <label for="confirm_password">Confirmar Password</label>
            <div class="pw-wrap">
              <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                placeholder="••••••••"
                autocomplete="new-password"
                required
              >
              <button type="button" class="pw-toggle" aria-label="Mostrar password">Mostrar</button>
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
            Aceito os <a href="#" id="terms-open-link">termos de utilização</a>
          </label>
          <span class="terms-error-msg" id="terms-error">Tens de aceitar os termos para continuar.</span>
        </div>

        <button type="submit" class="btn btn-primary btn-block auth-submit-btn">
          Criar Conta
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
      <h2 class="terms-sheet-title" id="terms-title">Termos de Utilização</h2>
      <button class="terms-sheet-close" id="terms-close-btn" aria-label="Fechar">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="terms-sheet-body">
      <h3>1. Aceitação dos Termos</h3>
      <p>Ao criar uma conta e utilizar a plataforma Sylora, aceitas cumprir estes Termos de Utilização. Se não concordares com algum ponto, não deves criar uma conta nem utilizar os serviços.</p>

      <h3>2. Elegibilidade</h3>
      <p>A plataforma Sylora destina-se a utilizadores com 13 anos ou mais. Ao registares-te, confirmas que tens idade suficiente para aceitar estes termos ou que tens autorização de um responsável legal.</p>

      <h3>3. A Tua Conta</h3>
      <p>És responsável por:</p>
      <ul>
        <li>Manter a confidencialidade da tua password</li>
        <li>Toda a atividade que ocorra na tua conta</li>
        <li>Notificar-nos imediatamente de qualquer uso não autorizado</li>
      </ul>
      <p>É proibido partilhar, vender ou transferir o acesso à tua conta.</p>

      <h3>4. Conduta do Utilizador</h3>
      <p>Ao utilizar o Sylora comprometes-te a não:</p>
      <ul>
        <li>Publicar conteúdo ofensivo, discriminatório ou abusivo</li>
        <li>Fazer spam ou assediar outros utilizadores</li>
        <li>Tentar aceder a sistemas ou contas que não são tuas</li>
        <li>Usar scripts, bots ou automação não autorizada</li>
        <li>Criar múltiplas contas para contornar banimentos</li>
      </ul>

      <h3>5. Propriedade Intelectual</h3>
      <p>Todo o conteúdo do Sylora (arte, música, texto, código e marca) é propriedade exclusiva da equipa Sylora e está protegido por direitos de autor. Não podes reproduzir, distribuir ou criar trabalhos derivados sem autorização escrita prévia.</p>

      <h3>6. Privacidade</h3>
      <p>O teu endereço de email é utilizado apenas para autenticação e comunicações essenciais relacionadas com a tua conta. Não vendemos nem partilhamos os teus dados com terceiros para fins comerciais.</p>

      <h3>7. Suspensão e Encerramento</h3>
      <p>Reservamo-nos o direito de suspender ou encerrar contas que violem estes termos, sem aviso prévio. Em caso de encerramento, perdes o acesso a todos os dados e progresso associados à conta.</p>

      <h3>8. Limitação de Responsabilidade</h3>
      <p>O Sylora é fornecido "tal como está". Não garantimos disponibilidade ininterrupta nem a ausência de erros. Não somos responsáveis por perda de dados de jogo resultante de problemas técnicos.</p>

      <h3>9. Alterações aos Termos</h3>
      <p>Podemos atualizar estes termos. Quando o fizermos, notificaremos os utilizadores por email. A utilização continuada após as alterações constitui aceitação dos novos termos.</p>

      <h3>10. Contacto</h3>
      <p>Para questões sobre estes termos, contacta-nos através do email de suporte disponível no site.</p>
    </div>
    <div class="terms-sheet-footer">
      <button type="button" id="terms-accept-btn" class="btn btn-primary" style="flex:1;">
        Aceitar e Fechar
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      </button>
      <button type="button" id="terms-decline-btn" class="btn btn-ghost">Recusar</button>
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

      const levels = [
        { pct: '0%',   color: 'transparent', text: '' },
        { pct: '25%',  color: '#c96b5a',     text: 'Fraca' },
        { pct: '50%',  color: '#d4955a',     text: 'Razoável' },
        { pct: '75%',  color: '#c9993a',     text: 'Boa' },
        { pct: '90%',  color: '#7aad6e',     text: 'Forte' },
        { pct: '100%', color: '#4e8c3d',     text: 'Muito forte' },
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
    document.querySelectorAll('.pw-toggle').forEach(function(btn) {
      btn.innerHTML = SVG_EYE;
      btn.setAttribute('aria-label', 'Mostrar password');
      btn.addEventListener('click', function() {
        var input = btn.closest('.pw-wrap').querySelector('input');
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? SVG_EYE_OFF : SVG_EYE;
        btn.setAttribute('aria-label', show ? 'Esconder password' : 'Mostrar password');
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
        submitBtn.textContent = 'A criar conta…';
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
</script>

</body>
</html>
