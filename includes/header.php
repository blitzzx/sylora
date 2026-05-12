<?php

require_once __DIR__ . '/config.php';

$isPjax = !empty($_SERVER['HTTP_X_PJAX']);

$isLoggedIn  = isset($_SESSION['user_id']);
$username    = $isLoggedIn ? e($_SESSION['username'] ?? 'Aventureiro') : null;
$userInitial = $isLoggedIn ? strtoupper(mb_substr($_SESSION['username'] ?? 'A', 0, 1)) : null;

// Página atual para marcar nav-link como active
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<?php if (!$isPjax): ?>
<!DOCTYPE html>
<html lang="pt" data-theme="">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sylora: Ecos dos Deuses</title>
  <meta name="description" content="Sylora é um jogo de aventura narrativa na Grécia Antiga. Explora ilhas corrompidas, derrota titãs e descobre o teu passado esquecido.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&family=Cinzel:wght@400;600;700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="assets/img/FavIcon-Sylora.png">
  <link rel="apple-touch-icon" href="assets/img/FavIcon-Sylora.png">
  <link rel="stylesheet" href="css/style.css?v=<?php echo @filemtime('css/style.css') ?: '1'; ?>">

  <!-- Microsoft Clarity -->
  <script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "wpebubj10v");
  </script>

  <!-- Tema: aplica ANTES do render para evitar flash -->
  <script>
    (function(){
      var saved = localStorage.getItem('sylora-theme');
      var sys   = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      var theme = saved || sys;
      document.documentElement.setAttribute('data-theme', theme);
    })();
  </script>
</head>
<body>

<!-- ===== DRAWER OVERLAY ===== -->
<div class="drawer-overlay" id="drawer-overlay" aria-hidden="true"></div>

<!-- ===== USER DRAWER (só para utilizadores logados) ===== -->
<?php if ($isLoggedIn): ?>
<aside class="user-drawer" id="user-drawer" aria-hidden="true" role="dialog" aria-label="Menu do utilizador">

  <a href="/u?u=<?php echo urlencode($_SESSION['username'] ?? ''); ?>" class="drawer-header-link">
    <div class="drawer-avatar">
      <?php if (!empty($_SESSION['avatar'])): ?>
        <img
          src="avatar.php?id=<?php echo (int)$_SESSION['user_id']; ?>&t=<?php echo time(); ?>"
          alt="Avatar de <?php echo $username; ?>"
          width="52" height="52"
          style="width:52px;height:52px;border-radius:50%;object-fit:cover;display:block;"
          onerror="this.outerHTML='<?php echo htmlspecialchars($userInitial, ENT_QUOTES); ?>'">
      <?php else: ?>
        <?php echo $userInitial; ?>
      <?php endif; ?>
    </div>
    <div class="drawer-user-info">
      <strong><?php echo $username; ?></strong>
      <span><?php echo e($_SESSION['email'] ?? ''); ?></span>
      <span class="drawer-role">Aventureiro</span>
    </div>
    <svg class="drawer-header-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  </a>

  <button class="drawer-close" id="drawer-close" aria-label="Fechar menu">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </button>

  <div class="drawer-body">

    <!-- Navegação rápida -->
    <div class="drawer-section expanded" id="ds-nav">
      <button class="drawer-section-title" aria-controls="ds-nav-body">
        <span class="dst-left">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
          Navegação
        </span>
        <svg class="dst-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
      </button>
      <div class="drawer-section-body" id="ds-nav-body">
        <div class="drawer-subsection">
          <nav class="drawer-nav-links">
            <a href="/" class="drawer-nav-link <?php echo $currentPage==='index.php'?'active':''; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg> Início
            </a>
            <a href="/historia" class="drawer-nav-link <?php echo $currentPage==='historia.php'?'active':''; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg> História
            </a>
            <a href="/jogar" class="drawer-nav-link <?php echo $currentPage==='jogar.php'?'active':''; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg> Jogar
            </a>
            <a href="/sobrenos" class="drawer-nav-link <?php echo $currentPage==='sobrenos.php'?'active':''; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> Sobre Nós
            </a>
            <a href="/u?u=<?php echo urlencode($_SESSION['username'] ?? ''); ?>" class="drawer-nav-link <?php echo $currentPage==='u.php'?'active':''; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Perfil
            </a>
          </nav>
        </div>
      </div>
    </div>

    <!-- Avatar -->
    <div class="drawer-section" id="ds-avatar">
      <button class="drawer-section-title" aria-controls="ds-avatar-body">
        <span class="dst-left">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Mudar Avatar
        </span>
        <svg class="dst-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
      </button>
      <div class="drawer-section-body" id="ds-avatar-body">
        <div class="drawer-subsection">
          <form action="/profile" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_avatar">
            <input type="hidden" name="_csrf" value="<?php echo e(generateCSRFToken()); ?>">
            <div class="avatar-upload-wrap">
              <div class="avatar-preview" id="avatar-preview">
                <?php if (!empty($_SESSION['avatar'])): ?>
                  <img
                    src="avatar.php?id=<?php echo (int)$_SESSION['user_id']; ?>&t=<?php echo time(); ?>"
                    alt="Avatar atual"
                    width="52" height="52"
                    style="width:52px;height:52px;border-radius:50%;object-fit:cover;display:block;">
                <?php else: ?>
                  <?php echo $userInitial; ?>
                <?php endif; ?>
              </div>
              <div class="avatar-upload-info">
                <label for="avatar" class="btn btn-secondary btn-sm" style="cursor:pointer;">Escolher imagem</label>
                <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;">
                <p class="avatar-hint">JPG, PNG ou GIF · máx. 10MB</p>
              </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:10px;width:100%;">Guardar avatar</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Tema -->
    <div class="drawer-section" id="ds-tema">
      <button class="drawer-section-title" aria-controls="ds-tema-body">
        <span class="dst-left">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
          Tema
        </span>
        <svg class="dst-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
      </button>
      <div class="drawer-section-body" id="ds-tema-body">
        <div class="drawer-subsection">
          <div class="theme-toggle-row">
            <button class="theme-btn" data-theme-set="dark">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
              Escuro
            </button>
            <button class="theme-btn" data-theme-set="light">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
              Claro
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Logout -->
    <div class="drawer-danger-zone">
      <a href="/logout" class="drawer-danger-btn">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Terminar Sessão
      </a>
    </div>

  </div>
</aside>
<?php endif; ?>

<!-- ===== NAVBAR ===== -->
<nav class="navbar" role="navigation" aria-label="Navegação principal">
  <div class="container">

    <a href="/" class="logo" aria-label="Sylora: Início">
      <img src="assets/img/Logo-Sylora.png" alt="Sylora" height="44" loading="eager">
    </a>

    <!-- Links desktop -->
    <ul class="nav-menu" id="nav-menu" role="list">
      <li><a href="/historia" class="<?php echo $currentPage==='historia.php'?'active':''; ?>">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
        História
      </a></li>
      <li><a href="/jogar" class="<?php echo $currentPage==='jogar.php'?'active':''; ?>">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        Jogar
      </a></li>
      <?php if ($isLoggedIn): ?>
      <li><a href="/sobrenos" class="<?php echo $currentPage==='sobrenos.php'?'active':''; ?>">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Sobre Nós
      </a></li>
      <?php endif; ?>
    </ul>

    <div class="nav-right">

      <!-- Widget de música: click=mute, hover=volume -->
      <div class="music-ctrl" id="music-ctrl">
        <button class="nav-icon-btn music-btn" id="music-toggle" aria-label="Ligar/desligar música">
          <svg id="music-icon-on" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
          <svg id="music-icon-muted" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2"/></svg>
          <svg id="music-icon-off" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/><line x1="2" y1="2" x2="22" y2="22" stroke="currentColor" stroke-width="2"/></svg>
        </button>
        <div class="music-vol-popup" id="music-vol-popup" role="tooltip">
          <div class="music-vol-row">
            <svg class="music-vol-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/></svg>
            <input type="range" class="music-vol-slider" id="music-vol-slider" min="0" max="100" step="1" value="50" aria-label="Volume da música">
            <svg class="music-vol-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 010 7.07"/><path d="M19.07 4.93a10 10 0 010 14.14"/></svg>
            <span class="music-vol-pct" id="music-vol-pct">50</span>
          </div>
        </div>
      </div>

      <!-- Botão tema -->
      <button class="nav-icon-btn" id="theme-toggle-nav" aria-label="Alternar tema">
        <svg id="theme-icon-dark" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg id="theme-icon-light" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
      </button>

      <?php if ($isLoggedIn): ?>
        <!-- Pill utilizador -->
        <button class="user-pill" id="drawer-trigger" aria-label="Abrir menu">
          <div class="user-avatar">
            <?php if (!empty($_SESSION['avatar'])): ?>
              <img
                src="avatar.php?id=<?php echo (int)$_SESSION['user_id']; ?>&t=<?php echo time(); ?>"
                alt=""
                width="28" height="28"
                style="width:28px;height:28px;border-radius:50%;object-fit:cover;display:block;"
                onerror="this.outerHTML='<?php echo e($userInitial); ?>'">
            <?php else: ?>
              <?php echo e($userInitial); ?>
            <?php endif; ?>
          </div>
          <span><?php echo $username; ?></span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>

      <?php else: ?>
        <!-- Guest -->
        <a href="/login"    class="btn btn-ghost btn-sm nav-guest-btn">Log in</a>
        <a href="/register" class="btn btn-primary btn-sm nav-guest-btn">Sign in</a>
      <?php endif; ?>

      <!-- Hamburger mobile -->
      <button class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="nav-mobile-menu" aria-label="Abrir menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>

  </div>

  <!-- Menu mobile -->
  <div class="nav-mobile-menu" id="nav-mobile-menu">
    <a href="/historia">História</a>
    <a href="/jogar">Jogar</a>
    <?php if ($isLoggedIn): ?>
      <a href="/sobrenos">Sobre Nós</a>
      <a href="/u?u=<?php echo urlencode($_SESSION['username'] ?? ''); ?>">Perfil</a>
      <a href="/logout" style="color:rgba(201,107,90,0.85);">Sair</a>
    <?php else: ?>
      <a href="/login">Log in</a>
      <a href="/register">Sign in</a>
    <?php endif; ?>
    <?php if ($isLoggedIn): ?>
    <div class="mobile-theme-row">
      <button class="theme-btn" data-theme-set="dark">Escuro</button>
      <button class="theme-btn" data-theme-set="light">Claro</button>
    </div>
    <?php endif; ?>
  </div>

</nav>

<!-- Música ambiente -->
<audio id="bg-music" loop preload="none">
  <source src="assets/audio/She.mp3" type="audio/mpeg">
</audio>
<?php endif; ?>

<!-- ===== TOAST GLOBAL ===== -->
<div id="sylora-toast" aria-live="polite" aria-atomic="true"></div>

<!-- ===== CONFIRM MODAL GLOBAL ===== -->
<div class="sylora-confirm-overlay" id="sylora-confirm" role="dialog" aria-modal="true" aria-labelledby="sylora-confirm-msg">
  <div class="sylora-confirm-box">
    <div class="sylora-confirm-icon">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <p class="sylora-confirm-msg" id="sylora-confirm-msg"></p>
    <div class="sylora-confirm-actions">
      <button class="btn btn-secondary btn-sm" id="sylora-confirm-cancel">Cancelar</button>
      <button class="btn btn-danger btn-sm" id="sylora-confirm-ok">Confirmar</button>
    </div>
  </div>
</div>

<script>
/* ── Global toast ── */
function showToast(msg, type) {
  const t = document.getElementById('sylora-toast');
  if (!t) return;
  t.textContent = msg;
  t.className = 'sylora-toast-show sylora-toast-' + (type || 'info');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => { t.className = ''; }, 3800);
}

/* ── Global confirm ── */
function showConfirm(msg, onOk) {
  const overlay = document.getElementById('sylora-confirm');
  const msgEl   = document.getElementById('sylora-confirm-msg');
  const okBtn   = document.getElementById('sylora-confirm-ok');
  const cancelBtn = document.getElementById('sylora-confirm-cancel');
  if (!overlay) { if (window.confirm(msg)) onOk(); return; }

  msgEl.textContent = msg;
  overlay.classList.add('active');

  const close = () => { overlay.classList.remove('active'); okBtn.replaceWith(okBtn.cloneNode(true)); cancelBtn.replaceWith(cancelBtn.cloneNode(true)); };
  const freshOk     = document.getElementById('sylora-confirm-ok');
  const freshCancel = document.getElementById('sylora-confirm-cancel');
  freshOk.addEventListener('click', () => { close(); onOk(); }, { once: true });
  freshCancel.addEventListener('click', close, { once: true });
  overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); }, { once: true });
}
</script>

<div id="pjax-root">