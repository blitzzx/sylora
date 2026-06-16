<?php
$navbarHero = !isset($_SESSION['user_id']);
include ROOT . '/resources/views/partials/head.php';
include ROOT . '/resources/views/partials/navbar.php';
?>

<main>

  <?php if ($isLoggedIn): ?>
  <section class="hero-logged">
    <div class="hero-logged-bg"></div>

    <div class="hero-logged-content container">

      <div class="hero-runes" aria-hidden="true">⊕ ✦ ◈ ⟡ ✦</div>

      <span class="overline-badge" data-i18n="home.welcome_back"><?= t('home.welcome_back') ?></span>
      <h1 class="hero-logged-name"><?php echo $username; ?></h1>
      <p class="hero-logged-sub" data-i18n="home.adventure_sub"><?= t('home.adventure_sub') ?></p>

      <div class="hero-actions">
        <a href="/jogar"    class="btn btn-primary btn-lg"  data-i18n="home.continue"><?= t('home.continue') ?></a>
        <a href="/historia" class="btn btn-secondary btn-lg" data-i18n="home.view_story"><?= t('home.view_story') ?></a>
      </div>

    </div>
  </section>

  <?php else: ?>

  <section class="site-hero-full" id="hero-full">
    <?php if (file_exists(ROOT . '/public/assets/video/trailer.mp4')): ?>
    <video class="hero-video" id="hero-video" autoplay muted loop playsinline preload="metadata" aria-hidden="true">
      <source src="/assets/video/trailer.mp4" type="video/mp4">
    </video>
    <?php endif; ?>
    <div class="hero-overlay" aria-hidden="true"></div>
    <canvas class="hero-canvas" id="hero-canvas" aria-hidden="true"></canvas>

    <div class="site-hero-full-inner">
      <div class="hero-logo-wrap">
        <img src="/assets/img/Logo-Sylora.png" alt="Sylora" class="hero-logo-img" loading="eager">
      </div>
    </div>

    <div class="hero-actions-row">
      <button class="hero-explore-btn" id="hero-explore" aria-label="Explorar mais">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <span data-i18n="home.explore"><?= t('home.explore') ?></span>
      </button>
      <button class="hero-download-btn" id="hero-download" aria-label="Download do jogo">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        <span data-i18n="home.download"><?= t('home.download') ?></span>
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
      const a = document.createElement('a');
      a.href = '/assets/download/Sylora%20Demo.exe';
      a.download = 'Sylora Demo.exe';
      document.body.appendChild(a);
      a.click();
      a.remove();
      if (typeof showToast === 'function') showToast(window.SYLORA_T('toast.download_started'), 'success');
    });
  })();
  </script>

  <?php endif; ?>

  <section class="island-map-section container">
    <div class="ornament-divider"><span data-i18n="home.islands_title"><?= t('home.islands_title') ?></span></div>

    <div class="island-map">
      <div class="island-card island-thalassos" data-act="I">
        <div class="island-card-bg"><div class="island-bg-ocean"></div></div>
        <div class="island-card-content">
          <div class="island-act-badge" data-i18n="home.island_act1"><?= t('home.island_act1') ?></div>
          <div class="island-sigil"><svg viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="8" stroke="currentColor" stroke-width="1.5" opacity="0.7"/><path d="M20 4v6M20 30v6M4 20h6M30 20h6" stroke="currentColor" stroke-width="1.5" opacity="0.5"/><path d="M20 12 L24 20 L20 28 L16 20 Z" fill="currentColor" opacity="0.8"/></svg></div>
          <h3 data-i18n="home.island1_name"><?= t('home.island1_name') ?></h3>
          <p data-i18n="home.island1_sub"><?= t('home.island1_sub') ?></p>
          <div class="island-meta">
            <span class="level-badge" data-i18n="home.island1_level"><?= t('home.island1_level') ?></span>
            <span class="boss-badge" data-i18n="home.island1_boss"><?= t('home.island1_boss') ?></span>
          </div>
        </div>
        <?php if ($isLoggedIn): ?>
          <a href="/jogar" class="island-cta" data-i18n="home.island_explore"><?= t('home.island_explore') ?></a>
        <?php else: ?>
          <a href="/register" class="island-cta island-cta-locked">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            <span data-i18n="home.island_register"><?= t('home.island_register') ?></span>
          </a>
        <?php endif; ?>
      </div>

      <div class="island-card island-helion" data-act="II">
        <div class="island-card-bg"><div class="island-bg-fire"></div></div>
        <div class="island-card-content">
          <div class="island-act-badge" data-i18n="home.island_act2"><?= t('home.island_act2') ?></div>
          <div class="island-sigil"><svg viewBox="0 0 40 40" fill="none"><path d="M20 4 C20 4 28 12 28 20 C28 28 20 34 20 34 C20 34 12 28 12 22 C12 16 16 12 20 14 C20 14 16 20 20 24 C24 20 24 14 20 4Z" fill="currentColor" opacity="0.75"/></svg></div>
          <h3 data-i18n="home.island2_name"><?= t('home.island2_name') ?></h3>
          <p data-i18n="home.island2_sub"><?= t('home.island2_sub') ?></p>
          <div class="island-meta">
            <span class="level-badge" data-i18n="home.island2_level"><?= t('home.island2_level') ?></span>
            <span class="boss-badge" data-i18n="home.island2_boss"><?= t('home.island2_boss') ?></span>
          </div>
        </div>
        <?php if ($isLoggedIn): ?>
          <a href="/jogar" class="island-cta" data-i18n="home.island_explore"><?= t('home.island_explore') ?></a>
        <?php else: ?>
          <a href="/register" class="island-cta island-cta-locked">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            <span data-i18n="home.island_register"><?= t('home.island_register') ?></span>
          </a>
        <?php endif; ?>
      </div>

      <div class="island-card island-zephyria" data-act="III">
        <div class="island-card-bg"><div class="island-bg-wind"></div></div>
        <div class="island-card-content">
          <div class="island-act-badge" data-i18n="home.island_act3"><?= t('home.island_act3') ?></div>
          <div class="island-sigil"><svg viewBox="0 0 40 40" fill="none"><path d="M8 20 Q14 10 20 20 Q26 30 32 20" stroke="currentColor" stroke-width="2" fill="none" opacity="0.8"/><path d="M8 26 Q14 16 20 26 Q26 36 32 26" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.5"/><circle cx="20" cy="14" r="3" fill="currentColor" opacity="0.7"/></svg></div>
          <h3 data-i18n="home.island3_name"><?= t('home.island3_name') ?></h3>
          <p data-i18n="home.island3_sub"><?= t('home.island3_sub') ?></p>
          <div class="island-meta">
            <span class="level-badge" data-i18n="home.island3_level"><?= t('home.island3_level') ?></span>
            <span class="boss-badge" data-i18n="home.island3_boss"><?= t('home.island3_boss') ?></span>
          </div>
        </div>
        <span class="island-cta island-cta-soon" data-i18n="home.island_soon"><?= t('home.island_soon') ?></span>
      </div>

      <div class="island-card island-tartaro" data-act="IV">
        <div class="island-card-bg"><div class="island-bg-abyss"></div></div>
        <div class="island-card-content">
          <div class="island-act-badge" data-i18n="home.island_act4"><?= t('home.island_act4') ?></div>
          <div class="island-sigil"><svg viewBox="0 0 40 40" fill="none"><path d="M20 4 L36 34 H4 Z" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.6"/><path d="M20 14 L28 28 H12 Z" fill="currentColor" opacity="0.5"/><circle cx="20" cy="20" r="3" fill="currentColor" opacity="0.9"/></svg></div>
          <h3 data-i18n="home.island4_name"><?= t('home.island4_name') ?></h3>
          <p data-i18n="home.island4_sub"><?= t('home.island4_sub') ?></p>
          <div class="island-meta">
            <span class="level-badge" data-i18n="home.island4_level"><?= t('home.island4_level') ?></span>
            <span class="boss-badge" data-i18n="home.island4_boss"><?= t('home.island4_boss') ?></span>
          </div>
        </div>
        <span class="island-cta island-cta-soon" data-i18n="home.island_soon"><?= t('home.island_soon') ?></span>
      </div>

      <div class="island-card island-olimpo island-wide" data-act="V">
        <div class="island-card-bg"><div class="island-bg-olympus"></div></div>
        <div class="island-card-content">
          <div class="island-act-badge" data-i18n="home.island_act5"><?= t('home.island_act5') ?></div>
          <div class="island-sigil"><svg viewBox="0 0 40 40" fill="none"><path d="M20 2 L22.5 15 L36 10 L27 22 L38 28 L24 26 L24 38 L20 28 L16 38 L16 26 L2 28 L13 22 L4 10 L17.5 15 Z" fill="currentColor" opacity="0.7"/></svg></div>
          <h3 data-i18n="home.island5_name"><?= t('home.island5_name') ?></h3>
          <p data-i18n="home.island5_sub"><?= t('home.island5_sub') ?></p>
          <div class="island-meta">
            <span class="level-badge" data-i18n="home.island5_level"><?= t('home.island5_level') ?></span>
            <span class="boss-badge" data-i18n="home.island5_boss"><?= t('home.island5_boss') ?></span>
          </div>
        </div>
        <span class="island-cta island-cta-soon" data-i18n="home.island_soon"><?= t('home.island_soon') ?></span>
      </div>
    </div>
  </section>

  <section class="features-section container">
    <div class="ornament-divider"><span data-i18n="home.features_title"><?= t('home.features_title') ?></span></div>

    <div class="feature-grid">

      <a href="/historia" class="feature-card">
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
        </div>
        <h3 data-i18n="home.feat_story_title"><?= t('home.feat_story_title') ?></h3>
        <p data-i18n="home.feat_story_desc"><?= t('home.feat_story_desc') ?></p>
        <span class="feature-card-arrow">→</span>
      </a>

      <?php if ($isLoggedIn): ?>
      <a href="/jogar" class="feature-card">
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        </div>
        <h3 data-i18n="home.feat_play_title"><?= t('home.feat_play_title') ?></h3>
        <p data-i18n="home.feat_play_desc"><?= t('home.feat_play_desc') ?></p>
        <span class="feature-card-arrow">→</span>
      </a>
      <a href="/sobre" class="feature-card">
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <h3 data-i18n="home.feat_team_title"><?= t('home.feat_team_title') ?></h3>
        <p data-i18n="home.feat_team_desc"><?= t('home.feat_team_desc') ?></p>
        <span class="feature-card-arrow">→</span>
      </a>
      <a href="/u?u=<?php echo e($_SESSION['username'] ?? ''); ?>" class="feature-card">
        <div class="feature-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h3 data-i18n="home.feat_profile_title"><?= t('home.feat_profile_title') ?></h3>
        <p data-i18n="home.feat_profile_desc"><?= t('home.feat_profile_desc') ?></p>
        <span class="feature-card-arrow">→</span>
      </a>
      <?php else: ?>
      <div class="feature-card feature-card-locked">
        <div class="lock-badge"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg><span data-i18n="home.feat_locked"><?= t('home.feat_locked') ?></span></div>
        <div class="feature-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>
        <h3 data-i18n="home.feat_play_title"><?= t('home.feat_play_title') ?></h3>
        <p data-i18n="home.feat_play_desc"><?= t('home.feat_play_desc') ?></p>
      </div>
      <div class="feature-card feature-card-locked">
        <div class="lock-badge"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg><span data-i18n="home.feat_locked"><?= t('home.feat_locked') ?></span></div>
        <div class="feature-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <h3 data-i18n="home.feat_about_title"><?= t('home.feat_about_title') ?></h3>
        <p data-i18n="home.feat_team_desc"><?= t('home.feat_team_desc') ?></p>
      </div>
      <div class="feature-card feature-card-locked">
        <div class="lock-badge"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg><span data-i18n="home.feat_locked"><?= t('home.feat_locked') ?></span></div>
        <div class="feature-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
        <h3 data-i18n="home.feat_profile_title"><?= t('home.feat_profile_title') ?></h3>
        <p data-i18n="home.feat_profile_desc"><?= t('home.feat_profile_desc') ?></p>
      </div>
      <?php endif; ?>

      <?php if ($isLoggedIn): ?>
      <a href="/u?u=<?php echo e($_SESSION['username'] ?? ''); ?>&tab=friends" class="feature-card">
        <div class="feature-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
        <h3 data-i18n="home.feat_friends_title"><?= t('home.feat_friends_title') ?></h3>
        <p data-i18n="home.feat_friends_desc"><?= t('home.feat_friends_desc') ?></p>
        <span class="feature-card-arrow">→</span>
      </a>
      <a href="/comunidade" class="feature-card">
        <div class="feature-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="6" cy="9" r="3"/><path d="M1 21v-2a4 4 0 014-4h2"/><circle cx="18" cy="9" r="3"/><path d="M23 21v-2a4 4 0 00-4-4h-2"/><circle cx="12" cy="7" r="4"/><path d="M7 21a5 5 0 0110 0"/></svg></div>
        <h3 data-i18n="home.feat_community_title"><?= t('home.feat_community_title') ?></h3>
        <p data-i18n="home.feat_community_desc"><?= t('home.feat_community_desc') ?></p>
        <span class="feature-card-arrow">→</span>
      </a>
      <?php else: ?>
      <div class="feature-card feature-card-locked">
        <div class="lock-badge"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg><span data-i18n="home.feat_locked"><?= t('home.feat_locked') ?></span></div>
        <div class="feature-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
        <h3 data-i18n="home.feat_friends_title"><?= t('home.feat_friends_title') ?></h3>
        <p data-i18n="home.feat_friends_desc"><?= t('home.feat_friends_desc') ?></p>
      </div>
      <div class="feature-card feature-card-locked">
        <div class="lock-badge"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg><span data-i18n="home.feat_locked"><?= t('home.feat_locked') ?></span></div>
        <div class="feature-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="6" cy="9" r="3"/><path d="M1 21v-2a4 4 0 014-4h2"/><circle cx="18" cy="9" r="3"/><path d="M23 21v-2a4 4 0 00-4-4h-2"/><circle cx="12" cy="7" r="4"/><path d="M7 21a5 5 0 0110 0"/></svg></div>
        <h3 data-i18n="home.feat_community_title"><?= t('home.feat_community_title') ?></h3>
        <p data-i18n="home.feat_community_desc"><?= t('home.feat_community_desc') ?></p>
      </div>
      <?php endif; ?>

    </div>
  </section>

  <section class="chars-section container">
    <div class="ornament-divider"><span data-i18n="home.chars_title"><?= t('home.chars_title') ?></span></div>
    <div class="chars-grid">

      <div class="char-card">
        <div class="char-icon char-sylora">
          <svg viewBox="0 0 60 60" fill="none"><circle cx="30" cy="30" r="20" stroke="rgba(232,196,106,0.5)" stroke-width="1.5"/><path d="M30 10 L33 28 L30 30 L27 28 Z" fill="rgba(232,196,106,0.9)"/><path d="M50 30 L32 33 L30 30 L32 27 Z" fill="rgba(201,153,58,0.7)"/><path d="M30 50 L27 32 L30 30 L33 32 Z" fill="rgba(201,153,58,0.6)"/><path d="M10 30 L28 27 L30 30 L28 33 Z" fill="rgba(232,196,106,0.7)"/><circle cx="30" cy="30" r="4" fill="rgba(232,196,106,1)"/></svg>
        </div>
        <h4>Sylora</h4>
        <p data-i18n="home.char1_desc"><?= t('home.char1_desc') ?></p>
        <span class="char-role" data-i18n="home.char1_role"><?= t('home.char1_role') ?></span>
      </div>

      <div class="char-card">
        <div class="char-icon char-clio">
          <svg viewBox="0 0 60 60" fill="none"><circle cx="30" cy="30" r="20" stroke="rgba(180,210,255,0.4)" stroke-width="1.5"/><path d="M44 10 C44 10 18 24 20 44 L24 49" stroke="rgba(180,210,255,0.85)" stroke-width="1.6" stroke-linecap="round" fill="none"/><path d="M24 49 C22 53 14 51 14 51 C14 51 21 47 20 44" stroke="rgba(180,210,255,0.65)" stroke-width="1.4" stroke-linecap="round" fill="none"/><path d="M36 18 L24 40" stroke="rgba(180,210,255,0.35)" stroke-width="1" stroke-linecap="round" stroke-dasharray="2 4"/><circle cx="30" cy="30" r="3" fill="rgba(180,210,255,0.8)"/></svg>
        </div>
        <h4>Clio</h4>
        <p data-i18n="home.char2_desc"><?= t('home.char2_desc') ?></p>
        <span class="char-role" data-i18n="home.char2_role"><?= t('home.char2_role') ?></span>
      </div>

      <div class="char-card">
        <div class="char-icon char-hero">
          <svg viewBox="0 0 60 60" fill="none"><circle cx="30" cy="30" r="20" stroke="rgba(232,196,106,0.4)" stroke-width="1.5"/><line x1="30" y1="11" x2="30" y2="47" stroke="rgba(232,196,106,0.85)" stroke-width="1.8" stroke-linecap="round"/><line x1="20" y1="30" x2="40" y2="30" stroke="rgba(232,196,106,0.75)" stroke-width="1.8" stroke-linecap="round"/><line x1="23" y1="26" x2="37" y2="26" stroke="rgba(232,196,106,0.45)" stroke-width="1.2" stroke-linecap="round"/><circle cx="30" cy="47" r="3" fill="rgba(201,153,58,0.5)" stroke="rgba(232,196,106,0.6)" stroke-width="1.2"/><circle cx="30" cy="11" r="1.8" fill="rgba(232,196,106,0.9)"/></svg>
        </div>
        <h4 data-i18n="home.char3_name"><?= t('home.char3_name') ?></h4>
        <p data-i18n="home.char3_desc"><?= t('home.char3_desc') ?></p>
        <span class="char-role" data-i18n="home.char3_role"><?= t('home.char3_role') ?></span>
      </div>

    </div>
  </section>

  <?php if (!$isLoggedIn): ?>
  <section class="cta-section container">
    <div class="cta-box">
      <div class="cta-box-deco" aria-hidden="true">
        <svg viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0 30 Q50 0 100 30 Q150 60 200 30" stroke="rgba(201,153,58,0.25)" stroke-width="1" fill="none"/>
          <path d="M0 30 Q50 60 100 30 Q150 0 200 30" stroke="rgba(201,153,58,0.15)" stroke-width="1" fill="none"/>
        </svg>
      </div>
      <h2 data-i18n="home.cta_title"><?= t('home.cta_title') ?></h2>
      <p data-i18n="home.cta_sub"><?= t('home.cta_sub') ?></p>
      <div class="cta-actions">
        <a href="/register" class="btn btn-primary btn-lg" data-i18n="home.cta_register"><?= t('home.cta_register') ?></a>
        <a href="/historia" class="btn btn-ghost btn-lg" data-i18n="home.cta_story"><?= t('home.cta_story') ?></a>
      </div>
    </div>
  </section>
  <?php endif; ?>

</main>

<?php include ROOT . '/resources/views/partials/footer.php'; ?>
