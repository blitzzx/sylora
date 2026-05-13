<?php
require_once __DIR__ . '/includes/config.php';
require_once 'includes/header.php';

$isLoggedIn = isset($_SESSION['user_id']);
$username   = $isLoggedIn ? e($_SESSION['username'] ?? 'Aventureiro') : null;
?>

<main>

  <?php if ($isLoggedIn): ?>
  <section class="hero-logged">
    <div class="hero-logged-bg"></div>
    <div class="hero-logged-content container">
      <div class="hero-logged-text">
        <span class="overline-badge">✦ Bem-vindo de volta</span>
        <h1><?php echo $username; ?></h1>
        <p>A tua aventura continua. Sylora aguarda o teu regresso.</p>
        <div class="hero-actions">
          <a href="jogar.php"    class="btn btn-primary btn-lg">▶ Continuar a Jogar</a>
          <a href="historia.php" class="btn btn-secondary btn-lg">Ver História</a>
        </div>
      </div>
      <div class="hero-logged-clio">
        <div class="clio-orb">
          <div class="clio-orb-inner">
            <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="clio-svg">
              <circle cx="40" cy="40" r="32" stroke="rgba(201,153,58,0.4)" stroke-width="1"/>
              <circle cx="40" cy="40" r="22" stroke="rgba(201,153,58,0.6)" stroke-width="1.5"/>
              <path d="M40 8 L43 37 L40 40 L37 37 Z" fill="rgba(232,196,106,0.8)"/>
              <path d="M72 40 L43 43 L40 40 L43 37 Z" fill="rgba(201,153,58,0.6)"/>
              <path d="M40 72 L37 43 L40 40 L43 43 Z" fill="rgba(201,153,58,0.5)"/>
              <path d="M8 40 L37 37 L40 40 L37 43 Z" fill="rgba(232,196,106,0.6)"/>
              <circle cx="40" cy="40" r="5" fill="rgba(232,196,106,0.9)"/>
            </svg>
          </div>
        </div>
        <p class="clio-label">A Clio está à espera</p>
      </div>
    </div>
  </section>

  <?php else: ?>

  <!-- ===== HERO GUEST (NOVO) ===== -->
  <section class="site-hero-full" id="hero-full">
    <video class="hero-video" id="hero-video" autoplay muted loop playsinline preload="metadata" aria-hidden="true">
      <source src="assets/video/trailer.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay" aria-hidden="true"></div>
    <canvas class="hero-canvas" id="hero-canvas" aria-hidden="true"></canvas>

    <div class="site-hero-full-inner">
      <div class="hero-logo-wrap">
        <img src="assets/img/Logo-Sylora.png" alt="Sylora" class="hero-logo-img" loading="eager">
      </div>
    </div>

    <div class="hero-actions-row">
      <button class="hero-explore-btn" id="hero-explore" aria-label="Explorar mais">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <span>Explorar</span>
      </button>
      <button class="hero-download-btn" id="hero-download" aria-label="Download do jogo">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        <span>Download</span>
      </button>
    </div>

    <div class="hero-scroll-indicator" aria-hidden="true">
      <svg class="hero-mouse-svg" width="26" height="40" viewBox="0 0 26 40" fill="none" stroke="white" stroke-width="1.5">
        <rect x="1" y="1" width="24" height="38" rx="12"/>
        <rect class="hero-scroll-wheel" x="10" y="9" width="6" height="10" rx="3" fill="#c9993a" stroke="none"/>
      </svg>
      <svg class="hero-swipe-svg" width="28" height="28" viewBox="0 0 28 28" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 6 L14 22"/>
        <path d="M7 15 L14 22 L21 15"/>
      </svg>
    </div>
  </section>


  <script>
  /* ── Canvas partículas do hero ── */
  (function () {
    const canvas = document.getElementById('hero-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let W, H, particles;

    function resize() {
      W = canvas.width  = canvas.offsetWidth;
      H = canvas.height = canvas.offsetHeight;
    }
    window.addEventListener('resize', resize);
    resize();

    function rand(min, max) { return min + Math.random() * (max - min); }

    function initParticles() {
      const count = Math.min(20, Math.floor(W * H / 55000));
      particles = Array.from({ length: count }, () => ({
        x: rand(0, W), y: rand(0, H),
        r: rand(0.6, 2.2),
        vx: rand(-0.18, 0.18), vy: rand(-0.22, -0.06),
        opacity: rand(0.15, 0.65),
        flicker: rand(0, Math.PI * 2),
        flickerSpeed: rand(0.008, 0.022),
      }));
    }
    initParticles();
    window.addEventListener('resize', initParticles);

    function loop() {
      ctx.clearRect(0, 0, W, H);
      particles.forEach(p => {
        p.flicker += p.flickerSpeed;
        const alpha = p.opacity * (0.7 + 0.3 * Math.sin(p.flicker));
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(201,153,58,${alpha.toFixed(3)})`;
        ctx.fill();
        p.x += p.vx; p.y += p.vy;
        if (p.y < -4)  p.y = H + 4;
        if (p.x < -4)  p.x = W + 4;
        if (p.x > W+4) p.x = -4;
      });
      requestAnimationFrame(loop);
    }
    loop();

    document.getElementById('hero-explore')?.addEventListener('click', () => {
      window.scrollTo({ top: window.innerHeight, behavior: 'smooth' });
    });
    document.querySelector('.hero-scroll-indicator')?.addEventListener('click', () => {
      window.scrollTo({ top: window.innerHeight, behavior: 'smooth' });
    });

    document.getElementById('hero-download')?.addEventListener('click', () => {
      if (typeof showToast === 'function') showToast('Download brevemente disponível.', 'info');
    });
  })();
  </script>

  <?php endif; ?>

  <!-- ===== MAPA DE ILHAS ===== -->
  <section class="island-map-section container">
    <div class="ornament-divider"><span>As Ilhas de Sylora</span></div>

    <div class="island-map">
      <!-- Ilha 1: Thalassos -->
      <div class="island-card island-thalassos" data-act="I">
        <div class="island-card-bg">
          <div class="island-bg-ocean"></div>
        </div>
        <div class="island-card-content">
          <div class="island-act-badge">Ato I</div>
          <div class="island-sigil">
            <svg viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="8" stroke="currentColor" stroke-width="1.5" opacity="0.7"/><path d="M20 4v6M20 30v6M4 20h6M30 20h6" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><path d="M20 12 L24 20 L20 28 L16 20 Z" fill="currentColor" opacity="0.8"/></svg>
          </div>
          <h3>Ilha de Thalassos</h3>
          <p>O Despertar</p>
          <div class="island-meta">
            <span class="level-badge">Nív. 1–10</span>
            <span class="boss-badge">Boss: Pelágion</span>
          </div>
        </div>
        <?php if ($isLoggedIn): ?>
          <a href="jogar.php" class="island-cta">Explorar</a>
        <?php else: ?>
          <a href="register.php" class="island-cta island-cta-locked">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Criar conta
          </a>
        <?php endif; ?>
      </div>

      <!-- Ilha 2: Helion -->
      <div class="island-card island-helion" data-act="II">
        <div class="island-card-bg">
          <div class="island-bg-fire"></div>
        </div>
        <div class="island-card-content">
          <div class="island-act-badge">Ato II</div>
          <div class="island-sigil">
            <svg viewBox="0 0 40 40" fill="none"><path d="M20 4 C20 4 28 12 28 20 C28 28 20 34 20 34 C20 34 12 28 12 22 C12 16 16 12 20 14 C20 14 16 20 20 24 C24 20 24 14 20 4Z" fill="currentColor" opacity="0.75"/></svg>
          </div>
          <h3>Ilha de Helion</h3>
          <p>As Cinzas de Hyperion</p>
          <div class="island-meta">
            <span class="level-badge">Nív. 11–30</span>
            <span class="boss-badge">Boss: Photonar</span>
          </div>
        </div>
        <?php if ($isLoggedIn): ?>
          <a href="jogar.php" class="island-cta">Explorar</a>
        <?php else: ?>
          <a href="register.php" class="island-cta island-cta-locked">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Criar conta
          </a>
        <?php endif; ?>
      </div>

      <!-- Ilha 3: Zephyria -->
      <div class="island-card island-zephyria" data-act="III">
        <div class="island-card-bg">
          <div class="island-bg-wind"></div>
        </div>
        <div class="island-card-content">
          <div class="island-act-badge">Ato III</div>
          <div class="island-sigil">
            <svg viewBox="0 0 40 40" fill="none"><path d="M8 20 Q14 10 20 20 Q26 30 32 20" stroke="currentColor" stroke-width="2" fill="none" opacity="0.8"/><path d="M8 26 Q14 16 20 26 Q26 36 32 26" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.5"/><circle cx="20" cy="14" r="3" fill="currentColor" opacity="0.7"/></svg>
          </div>
          <h3>Zephyria</h3>
          <p>O Véu dos Ventos</p>
          <div class="island-meta">
            <span class="level-badge">Nív. 31–40</span>
            <span class="boss-badge">Boss: Astraeus</span>
          </div>
        </div>
        <span class="island-cta island-cta-soon">Em breve</span>
      </div>

      <!-- Ilha 4: Tártaro -->
      <div class="island-card island-tartaro" data-act="IV">
        <div class="island-card-bg">
          <div class="island-bg-abyss"></div>
        </div>
        <div class="island-card-content">
          <div class="island-act-badge">Ato IV</div>
          <div class="island-sigil">
            <svg viewBox="0 0 40 40" fill="none"><path d="M20 4 L36 34 H4 Z" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.6"/><path d="M20 14 L28 28 H12 Z" fill="currentColor" opacity="0.5"/><circle cx="20" cy="20" r="3" fill="currentColor" opacity="0.9"/></svg>
          </div>
          <h3>Tártaro Profundo</h3>
          <p>O Submundo pela Memória</p>
          <div class="island-meta">
            <span class="level-badge">Nív. 41–70</span>
            <span class="boss-badge">Boss: Cronos & Krios</span>
          </div>
        </div>
        <span class="island-cta island-cta-soon">Em breve</span>
      </div>

      <!-- Ilha 5: Olimpo -->
      <div class="island-card island-olimpo island-wide" data-act="V">
        <div class="island-card-bg">
          <div class="island-bg-olympus"></div>
        </div>
        <div class="island-card-content">
          <div class="island-act-badge">Ato V · Final</div>
          <div class="island-sigil">
            <svg viewBox="0 0 40 40" fill="none"><path d="M20 2 L22.5 15 L36 10 L27 22 L38 28 L24 26 L24 38 L20 28 L16 38 L16 26 L2 28 L13 22 L4 10 L17.5 15 Z" fill="currentColor" opacity="0.7"/></svg>
          </div>
          <h3>Templo Celestial de Themis</h3>
          <p>O Julgamento dos Deuses</p>
          <div class="island-meta">
            <span class="level-badge">Nív. 71+</span>
            <span class="boss-badge">Boss: Égide dos Doze</span>
          </div>
        </div>
        <span class="island-cta island-cta-soon">Em breve</span>
      </div>
    </div>
  </section>

  <!-- ===== FEATURES ===== -->
  <section class="features-section container">
    <div class="ornament-divider"><span>O que te espera</span></div>

    <div class="feature-grid">

      <a href="historia.php" class="feature-card">
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
        </div>
        <h3>História</h3>
        <p>Mergulha numa narrativa profunda da Grécia Antiga, onde cada escolha molda o destino de Sylora.</p>
        <span class="feature-card-arrow">→</span>
      </a>

      

      <?php if ($isLoggedIn): ?>
      <a href="jogar.php" class="feature-card">
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        </div>
        <h3>Jogar</h3>
        <p>Guarda o teu progresso na cloud e continua a aventura em qualquer dispositivo.</p>
        <span class="feature-card-arrow">→</span>
      </a>
      <a href="sobre.php" class="feature-card">
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <h3>Sobre a equipa</h3>
        <p>Descobre quem está por trás da criação do jogo Sylora.</p>
        <span class="feature-card-arrow">→</span>
      </a>
      <a href="u.php?u=<?php echo e($_SESSION['username'] ?? ''); ?>" class="feature-card">
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h3>Perfil Público</h3>
        <p>Personaliza o teu perfil com avatar e mostra-o à comunidade.</p>
        <span class="feature-card-arrow">→</span>
      </a>
      <?php else: ?>
      <div class="feature-card feature-card-locked">
        <div class="lock-badge">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          Requer conta
        </div>
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        </div>
        <h3>Jogar</h3>
        <p>Guarda o teu progresso na cloud e continua a aventura em qualquer dispositivo.</p>
      </div>  
      <div class="feature-card feature-card-locked">
        <div class="lock-badge">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          Requer conta
        </div>
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <h3>Sobre Nós</h3>
        <p>Descobre quem está por trás da criação do jogo Sylora.</p>
      </div>
      <div class="feature-card feature-card-locked">
        <div class="lock-badge">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          Requer conta
        </div>
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h3>Perfil Público</h3>
        <p>Personaliza o teu perfil com avatar e mostra-o à comunidade.</p>
      </div>
      <?php endif; ?>

      <?php if ($isLoggedIn): ?>
      <a href="u.php?u=<?php echo e($_SESSION['username'] ?? ''); ?>&tab=friends" class="feature-card">
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <h3>Amigos</h3>
        <p>Encontra outros aventureiros, adiciona amigos e partilha a tua jornada.</p>
        <span class="feature-card-arrow">→</span>
      </a>
      <?php else: ?>
      <div class="feature-card feature-card-locked">
        <div class="lock-badge">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          Requer conta
        </div>
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <h3>Amigos</h3>
        <p>Encontra outros aventureiros, adiciona amigos e partilha a tua jornada.</p>
      </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- ===== PERSONAGENS ===== -->
  <section class="chars-section container">
    <div class="ornament-divider"><span>Figuras da Lenda</span></div>
    <div class="chars-grid">

      <div class="char-card">
        <div class="char-icon char-sylora">
          <svg viewBox="0 0 60 60" fill="none"><circle cx="30" cy="30" r="20" stroke="rgba(232,196,106,0.5)" stroke-width="1.5"/><path d="M30 10 L33 28 L30 30 L27 28 Z" fill="rgba(232,196,106,0.9)"/><path d="M50 30 L32 33 L30 30 L32 27 Z" fill="rgba(201,153,58,0.7)"/><path d="M30 50 L27 32 L30 30 L33 32 Z" fill="rgba(201,153,58,0.6)"/><path d="M10 30 L28 27 L30 30 L28 33 Z" fill="rgba(232,196,106,0.7)"/><circle cx="30" cy="30" r="4" fill="rgba(232,196,106,1)"/></svg>
        </div>
        <h4>Sylora</h4>
        <p>Deusa em formação, aprendiz de Themis. Escolheu um campeão para agir em seu nome e salvar o equilíbrio mortal.</p>
        <span class="char-role">Deusa · Ordem</span>
      </div>

      <div class="char-card">
        <div class="char-icon char-clio">
          <svg viewBox="0 0 60 60" fill="none"><circle cx="30" cy="30" r="20" stroke="rgba(180,210,255,0.4)" stroke-width="1.5"/><path d="M44 10 C44 10 18 24 20 44 L24 49" stroke="rgba(180,210,255,0.85)" stroke-width="1.6" stroke-linecap="round" fill="none"/><path d="M24 49 C22 53 14 51 14 51 C14 51 21 47 20 44" stroke="rgba(180,210,255,0.65)" stroke-width="1.4" stroke-linecap="round" fill="none"/><path d="M36 18 L24 40" stroke="rgba(180,210,255,0.35)" stroke-width="1" stroke-linecap="round" stroke-dasharray="2 4"/><circle cx="30" cy="30" r="3" fill="rgba(180,210,255,0.8)"/></svg>
        </div>
        <h4>Clio</h4>
        <p>Inspirada na Musa da História. Guia do herói, ponte entre o mundo mortal e o divino.</p>
        <span class="char-role">Guia · Saber</span>
      </div>

      <div class="char-card">
        <div class="char-icon char-hero">
          <svg viewBox="0 0 60 60" fill="none"><circle cx="30" cy="30" r="20" stroke="rgba(232,196,106,0.4)" stroke-width="1.5"/><line x1="30" y1="11" x2="30" y2="47" stroke="rgba(232,196,106,0.85)" stroke-width="1.8" stroke-linecap="round"/><line x1="20" y1="30" x2="40" y2="30" stroke="rgba(232,196,106,0.75)" stroke-width="1.8" stroke-linecap="round"/><line x1="23" y1="26" x2="37" y2="26" stroke="rgba(232,196,106,0.45)" stroke-width="1.2" stroke-linecap="round"/><circle cx="30" cy="47" r="3" fill="rgba(201,153,58,0.5)" stroke="rgba(232,196,106,0.6)" stroke-width="1.2"/><circle cx="30" cy="11" r="1.8" fill="rgba(232,196,106,0.9)"/></svg>
        </div>
        <h4>O Herói</h4>
        <p>Sem memória, sem nome. Um guerreiro lendário escolhido e revivido. O teu destino está escrito nas estrelas.</p>
        <span class="char-role">Campeão · Incógnito</span>
      </div>

    </div>
  </section>

  <?php if (!$isLoggedIn): ?>
  <!-- ===== CTA FINAL ===== -->
  <section class="cta-section container">
    <div class="cta-box">
      <div class="cta-box-deco" aria-hidden="true">
        <svg viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0 30 Q50 0 100 30 Q150 60 200 30" stroke="rgba(201,153,58,0.25)" stroke-width="1" fill="none"/>
          <path d="M0 30 Q50 60 100 30 Q150 0 200 30" stroke="rgba(201,153,58,0.15)" stroke-width="1" fill="none"/>
        </svg>
      </div>
      <h2>Pronto para despertar?</h2>
      <p>A tua memória foi selada. A tua jornada começa agora.</p>
      <div class="cta-actions">
        <a href="register.php" class="btn btn-primary btn-lg">Criar conta gratuita</a>
        <a href="historia.php" class="btn btn-ghost btn-lg">Conhecer a história</a>
      </div>
    </div>
  </section>
  <?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>
