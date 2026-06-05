<?php

require_once __DIR__ . '/config.php';

$isPjax = !empty($_SERVER['HTTP_X_PJAX']);

$_flashMsg = null; $_flashType = 'success';
if (isset($_SESSION['flash_key'])) {
    $_flashMsg  = t($_SESSION['flash_key'], $_SESSION['flash_vars'] ?? []);
    $_flashType = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_key'], $_SESSION['flash_vars'], $_SESSION['flash_type']);
}

$isLoggedIn  = isset($_SESSION['user_id']);
$username    = $isLoggedIn ? e($_SESSION['username'] ?? t('profile.role_user')) : null;
$userInitial = $isLoggedIn ? strtoupper(mb_substr($_SESSION['username'] ?? 'A', 0, 1)) : null;


$currentPage = basename($_SERVER['PHP_SELF']);

// ─── SEO: cada página pode sobrepor com $pageTitle / $pageDescription / $pageCanonical / $pageNoindex antes do include ───
$_seoTitle  = isset($pageTitle)       ? $pageTitle       : t('site.title');
$_seoDesc   = isset($pageDescription) ? $pageDescription : t('site.description');
$_seoPath   = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$_seoBase   = SITE_URL . $_seoPath;
$_seoLang   = getLang();
$_seoAlts   = ['en' => $_seoBase, 'pt' => $_seoBase . '?lang=pt', 'es' => $_seoBase . '?lang=es'];
// Emite hreflang só em páginas públicas sem canonical próprio (home, historia, sobre)
$_seoHasAlts = empty($pageNoindex) && !isset($pageCanonical);
$_seoCanon  = isset($pageCanonical) ? $pageCanonical : ($_seoAlts[$_seoLang] ?? $_seoBase);
$_seoImage  = SITE_URL . '/assets/img/Logo-Sylora.png';
$_seoRobots = !empty($pageNoindex) ? 'noindex, follow' : 'index, follow';
$_seoLocale = ['pt' => 'pt_PT', 'en' => 'en_US', 'es' => 'es_ES'][$_seoLang] ?? 'en_US';
?>
<?php if (!$isPjax): ?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>" data-theme="">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($_seoTitle) ?></title>
  <meta name="description" content="<?= e($_seoDesc) ?>">
  <meta name="robots" content="<?= $_seoRobots ?>">
  <meta name="author" content="Sylora">
  <meta name="keywords" content="Sylora, Sylora jogo, Sylora RPG, Sylora Ecos dos Deuses, jogo de mitologia grega, RPG português, aventura narrativa, jogo indie português, Sylora game">
  <link rel="canonical" href="<?= e($_seoCanon) ?>">
<?php if ($_seoHasAlts): ?>
  <link rel="alternate" hreflang="en" href="<?= e($_seoAlts['en']) ?>">
  <link rel="alternate" hreflang="pt" href="<?= e($_seoAlts['pt']) ?>">
  <link rel="alternate" hreflang="es" href="<?= e($_seoAlts['es']) ?>">
  <link rel="alternate" hreflang="x-default" href="<?= e($_seoBase) ?>">
<?php endif; ?>


  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Sylora">
  <meta property="og:title" content="<?= e($_seoTitle) ?>">
  <meta property="og:description" content="<?= e($_seoDesc) ?>">
  <meta property="og:url" content="<?= e($_seoCanon) ?>">
  <meta property="og:image" content="<?= e($_seoImage) ?>">
  <meta property="og:image:width" content="512">
  <meta property="og:image:height" content="512">
  <meta property="og:image:alt" content="Sylora — Ecos dos Deuses">
  <meta property="og:locale" content="<?= $_seoLocale ?>">
  <meta property="og:locale:alternate" content="pt_PT">
  <meta property="og:locale:alternate" content="en_US">
  <meta property="og:locale:alternate" content="es_ES">


  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($_seoTitle) ?>">
  <meta name="twitter:description" content="<?= e($_seoDesc) ?>">
  <meta name="twitter:image" content="<?= e($_seoImage) ?>">


  <script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
      [
        '@type' => 'WebSite',
        '@id' => SITE_URL . '/#website',
        'name' => 'Sylora',
        'alternateName' => 'Sylora: Ecos dos Deuses',
        'url' => SITE_URL . '/',
        'inLanguage' => ['pt-PT', 'en', 'es'],
        'publisher' => ['@id' => SITE_URL . '/#organization'],
        'potentialAction' => [
          '@type' => 'SearchAction',
          'target' => ['@type' => 'EntryPoint', 'urlTemplate' => SITE_URL . '/search?q={search_term_string}'],
          'query-input' => 'required name=search_term_string',
        ],
      ],
      [
        '@type' => 'Organization',
        '@id' => SITE_URL . '/#organization',
        'name' => 'Sylora',
        'url' => SITE_URL . '/',
        'logo' => SITE_URL . '/assets/img/Logo-Sylora.png',
      ],
      [
        '@type' => 'VideoGame',
        '@id' => SITE_URL . '/#game',
        'name' => 'Sylora',
        'alternateName' => 'Sylora: Ecos dos Deuses',
        'url' => SITE_URL . '/',
        'description' => t('site.description'),
        'image' => SITE_URL . '/assets/img/Logo-Sylora.png',
        'inLanguage' => ['pt-PT', 'en', 'es'],
        'genre' => ['RPG', 'Adventure', 'Action'],
        'gamePlatform' => ['PC', 'Windows'],
        'operatingSystem' => 'Windows',
        'applicationCategory' => 'Game',
        'author' => ['@id' => SITE_URL . '/#organization'],
        'publisher' => ['@id' => SITE_URL . '/#organization'],
      ],
    ],
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&family=Cinzel:wght@400;600;700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="assets/img/FavIcon-Sylora.png">
  <link rel="apple-touch-icon" href="assets/img/FavIcon-Sylora.png">
  <link rel="stylesheet" href="css/style.css?v=<?php echo @filemtime('css/style.css') ?: '1'; ?>">

  
  <script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "wpebubj10v");
  </script>

  
  <script>
    (function(){
      var saved = localStorage.getItem('sylora-theme');
      var sys   = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      var theme = saved || sys;
      document.documentElement.setAttribute('data-theme', theme);
    })();
  </script>
  <script>window.SYLORA_I18N=<?= json_encode(['en'=>require __DIR__.'/../lang/en.php','pt'=>require __DIR__.'/../lang/pt.php','es'=>require __DIR__.'/../lang/es.php'],JSON_HEX_TAG|JSON_HEX_AMP) ?>;
  window.SYLORA_LANG=<?= json_encode(getLang()) ?>;
  window.SYLORA_T=function(key,vars){
    var dict=(window.SYLORA_I18N&&window.SYLORA_I18N[window.SYLORA_LANG])||{};
    var val=(dict[key]!==undefined)?dict[key]:key;
    if(vars){for(var k in vars){val=val.split('{'+k+'}').join(vars[k]);}}
    return val;
  };</script>
</head>
<body>


<div class="drawer-overlay" id="drawer-overlay" aria-hidden="true"></div>


<?php if ($isLoggedIn): ?>
<aside class="user-drawer" id="user-drawer" aria-hidden="true" role="dialog" aria-label="Menu do utilizador">

  <a href="/u?u=<?php echo urlencode($_SESSION['username'] ?? ''); ?>" class="drawer-header-wrap">
    <div class="drawer-avatar">
      <?php if (!empty($_SESSION['avatar'])): ?>
        <img
          src="avatar.php?id=<?php echo (int)$_SESSION['user_id']; ?>&t=<?php echo time(); ?>"
          alt="Avatar de <?php echo $username; ?>"
          width="52" height="52"
          class="drawer-avatar-img"
          data-initial="<?php echo e($userInitial); ?>"
          style="width:52px;height:52px;border-radius:50%;object-fit:cover;display:block;">
      <?php else: ?>
        <?php echo $userInitial; ?>
      <?php endif; ?>
    </div>
    <div class="drawer-user-info">
      <strong><?php echo $username; ?></strong>
      <span><?php echo e($_SESSION['email'] ?? ''); ?></span>
      <span class="drawer-role" data-i18n="drawer.role"><?= t('drawer.role') ?></span>
    </div>
    <svg class="drawer-header-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  </a>

  <button class="drawer-close" id="drawer-close" aria-label="Fechar menu">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </button>

  <div class="drawer-body">

    
    <div class="drawer-section expanded" id="ds-nav">
      <button class="drawer-section-title" aria-controls="ds-nav-body">
        <span class="dst-left">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
          <span data-i18n="drawer.navigation"><?= t('drawer.navigation') ?></span>
        </span>
        <svg class="dst-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
      </button>
      <div class="drawer-section-body" id="ds-nav-body">
        <div class="drawer-subsection">
          <nav class="drawer-nav-links">
            <a href="/" class="drawer-nav-link <?php echo $currentPage==='index.php'?'active':''; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg> <span data-i18n="nav.home"><?= t('nav.home') ?></span>
            </a>
            <a href="/historia" class="drawer-nav-link <?php echo $currentPage==='historia.php'?'active':''; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg> <span data-i18n="nav.historia"><?= t('nav.historia') ?></span>
            </a>
            <a href="/jogar" class="drawer-nav-link <?php echo $currentPage==='jogar.php'?'active':''; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg> <span data-i18n="nav.play"><?= t('nav.play') ?></span>
            </a>
            <a href="/sobre" class="drawer-nav-link <?php echo $currentPage==='sobre.php'?'active':''; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> <span data-i18n="nav.about"><?= t('nav.about') ?></span>
            </a>
            <a href="/u?u=<?php echo urlencode($_SESSION['username'] ?? ''); ?>" class="drawer-nav-link <?php echo $currentPage==='u.php'?'active':''; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> <span data-i18n="nav.profile"><?= t('nav.profile') ?></span>
            </a>
          </nav>
        </div>
      </div>
    </div>

    
    <div class="drawer-section" id="ds-tema">
      <button class="drawer-section-title" aria-controls="ds-tema-body">
        <span class="dst-left">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
          <span data-i18n="drawer.theme"><?= t('drawer.theme') ?></span>
        </span>
        <svg class="dst-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
      </button>
      <div class="drawer-section-body" id="ds-tema-body">
        <div class="drawer-subsection">
          <div class="theme-toggle-row">
            <button class="theme-btn" data-theme-set="dark">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
              <span data-i18n="drawer.dark"><?= t('drawer.dark') ?></span>
            </button>
            <button class="theme-btn" data-theme-set="light">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
              <span data-i18n="drawer.light"><?= t('drawer.light') ?></span>
            </button>
          </div>
        </div>
      </div>
    </div>

    
    <div class="drawer-danger-zone">
      <a href="/logout" class="drawer-danger-btn">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span data-i18n="nav.logout"><?= t('nav.logout') ?></span>
      </a>
    </div>

  </div>
</aside>
<?php endif; ?>


<?php if ($isLoggedIn): ?>
<div class="avatar-crop-overlay" id="avatar-crop-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Recortar avatar">
  <div class="avatar-crop-box">
    <div class="avatar-crop-viewport">
      <canvas id="avatar-crop-canvas" width="280" height="280"></canvas>
    </div>
    <div class="avatar-crop-zoom-row">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="3 15 8 10 13 14 16 11 21 15"/><circle cx="8.5" cy="8.5" r="1.5"/></svg>
      <div class="crop-zoom-track">
        <div class="crop-zoom-fill" id="crop-zoom-fill"></div>
        <div class="crop-zoom-thumb" id="crop-zoom-thumb"></div>
        <input type="range" id="avatar-crop-zoom" min="0.1" max="4" step="0.01" value="1">
      </div>
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="3 15 8 10 13 14 16 11 21 15"/><circle cx="8.5" cy="8.5" r="1.5"/></svg>
    </div>
    <p class="avatar-crop-hint" data-i18n="avatar.crop_hint"><?= t('avatar.crop_hint') ?></p>
    <div class="avatar-crop-actions">
      <button class="btn btn-secondary btn-sm" id="avatar-crop-cancel" data-i18n="avatar.cancel"><?= t('avatar.cancel') ?></button>
      <button class="btn btn-primary btn-sm" id="avatar-crop-confirm">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
        <span data-i18n="avatar.save"><?= t('avatar.save') ?></span>
      </button>
    </div>
  </div>
</div>
<input type="file" id="avatar-file-input" accept="image/jpeg,image/png,image/webp" style="display:none;">
<input type="hidden" id="avatar-csrf-token" value="<?php echo e(generateCSRFToken()); ?>">
<?php endif; ?>


<nav class="navbar<?= !empty($navbarHero) ? ' navbar-hero' : '' ?>" role="navigation" aria-label="Navegação principal">
  <div class="container">

    <a href="/" class="logo" aria-label="Sylora: Início">
      <img src="assets/img/Logo-Sylora.png" alt="Sylora" height="44" loading="eager">
    </a>

    
    <ul class="nav-menu" id="nav-menu" role="list">
      <li><a href="/historia" class="<?php echo $currentPage==='historia.php'?'active':''; ?>">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
        <span data-i18n="nav.historia"><?= t('nav.historia') ?></span>
      </a></li>
      <li><a href="/jogar" class="<?php echo $currentPage==='jogar.php'?'active':''; ?>">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        <span data-i18n="nav.play"><?= t('nav.play') ?></span>
      </a></li>
      <?php if ($isLoggedIn): ?>
      <li><a href="/sobre" class="<?php echo $currentPage==='sobre.php'?'active':''; ?>">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span data-i18n="nav.about"><?= t('nav.about') ?></span>
      </a></li>
      <?php endif; ?>
    </ul>

    <div class="nav-right">

      
      <div class="music-ctrl" id="music-ctrl">
        <button class="nav-icon-btn music-btn" id="music-toggle" aria-label="<?= e(t('common.toggle_music')) ?>" data-i18n-aria="common.toggle_music">
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

      
      <button class="nav-icon-btn" id="theme-toggle-nav" aria-label="<?= e(t('common.toggle_theme')) ?>" data-i18n-aria="common.toggle_theme">
        <svg id="theme-icon-dark" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg id="theme-icon-light" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
      </button>

      <?php
      $langNames = ['en' => 'English', 'pt' => 'Português', 'es' => 'Español'];
      $curLang = getLang();
      ?>
      <div class="lang-switcher" id="lang-switcher">
        <button type="button" class="nav-icon-btn lang-trigger" id="lang-trigger" aria-haspopup="listbox" aria-expanded="false" aria-label="<?= e(t('common.change_lang')) ?>" data-i18n-aria="common.change_lang">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
          </svg>
          <span class="lang-trigger-code" id="lang-trigger-code"><?= strtoupper($curLang) ?></span>
        </button>
        <ul class="lang-menu" id="lang-menu" role="listbox" aria-label="Idiomas disponíveis">
          <?php foreach($langNames as $code => $name): ?>
          <li role="presentation">
            <button type="button" class="lang-option<?= $curLang===$code?' active':'' ?>" data-lang="<?= $code ?>" role="option" aria-selected="<?= $curLang===$code?'true':'false' ?>">
              <span class="lang-option-code"><?= strtoupper($code) ?></span>
              <span class="lang-option-name"><?= $name ?></span>
              <svg class="lang-option-check" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <?php if ($isLoggedIn): ?>

        <button class="user-pill" id="drawer-trigger" aria-label="Abrir menu">
          <div class="user-avatar">
            <?php if (!empty($_SESSION['avatar'])): ?>
              <img
                src="avatar.php?id=<?php echo (int)$_SESSION['user_id']; ?>&t=<?php echo time(); ?>"
                alt=""
                width="28" height="28"
                class="nav-avatar-img"
                data-initial="<?php echo e($userInitial); ?>"
                style="width:28px;height:28px;border-radius:50%;object-fit:cover;display:block;">
            <?php else: ?>
              <?php echo e($userInitial); ?>
            <?php endif; ?>
          </div>
          <span><?php echo $username; ?></span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>

      <?php else: ?>
        
        <a href="/login"    class="btn btn-ghost btn-sm nav-guest-btn" data-i18n="nav.login"><?= t('nav.login') ?></a>
        <a href="/register" class="btn btn-primary btn-sm nav-guest-btn" data-i18n="nav.register"><?= t('nav.register') ?></a>
      <?php endif; ?>

      
      <button class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="nav-mobile-menu" aria-label="<?= e(t('common.open_menu')) ?>" data-i18n-aria="common.open_menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>

  </div>

  
  <div class="nav-mobile-menu" id="nav-mobile-menu">
    <a href="/historia" data-i18n="nav.historia"><?= t('nav.historia') ?></a>
    <a href="/jogar" data-i18n="nav.play"><?= t('nav.play') ?></a>
    <?php if ($isLoggedIn): ?>
      <a href="/sobre" data-i18n="nav.about"><?= t('nav.about') ?></a>
      <a href="/u?u=<?php echo urlencode($_SESSION['username'] ?? ''); ?>" data-i18n="nav.profile"><?= t('nav.profile') ?></a>
      <a href="/logout" style="color:rgba(201,107,90,0.85);" data-i18n="nav.logout"><?= t('nav.logout') ?></a>
    <?php else: ?>
      <a href="/login" data-i18n="nav.login"><?= t('nav.login') ?></a>
      <a href="/register" data-i18n="nav.register"><?= t('nav.register') ?></a>
    <?php endif; ?>
    <div class="mobile-lang-row">
      <?php foreach($langNames as $code => $name): ?>
      <button type="button" class="lang-btn-mobile<?= $curLang===$code?' active':'' ?>" data-lang="<?= $code ?>" aria-label="<?= $name ?>">
        <span class="lang-mobile-code"><?= strtoupper($code) ?></span>
        <span class="lang-mobile-name"><?= $name ?></span>
      </button>
      <?php endforeach; ?>
    </div>
    <?php if ($isLoggedIn): ?>
    <div class="mobile-theme-row">
      <button class="theme-btn" data-theme-set="dark" data-i18n="drawer.dark"><?= t('drawer.dark') ?></button>
      <button class="theme-btn" data-theme-set="light" data-i18n="drawer.light"><?= t('drawer.light') ?></button>
    </div>
    <?php endif; ?>
  </div>

</nav>


<audio id="bg-music" loop preload="none">
  <source src="assets/audio/syloramusic.mp3" type="audio/mpeg">
</audio>
<?php endif; ?>


<div id="sylora-toast" aria-live="polite" aria-atomic="true"<?php if ($_flashMsg): ?> data-flash-msg="<?= e($_flashMsg) ?>" data-flash-type="<?= e($_flashType) ?>"<?php endif; ?>></div>


<div class="sylora-confirm-overlay" id="sylora-confirm" role="dialog" aria-modal="true" aria-labelledby="sylora-confirm-msg">
  <div class="sylora-confirm-box">
    <div class="sylora-confirm-icon">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <p class="sylora-confirm-msg" id="sylora-confirm-msg"></p>
    <div class="sylora-confirm-actions">
      <button class="btn btn-secondary btn-sm" id="sylora-confirm-cancel" data-i18n="confirm.cancel"><?= t('confirm.cancel') ?></button>
      <button class="btn btn-danger btn-sm" id="sylora-confirm-ok" data-i18n="confirm.confirm"><?= t('confirm.confirm') ?></button>
    </div>
  </div>
</div>

<script>

function setLang(lang) {
  var allowed = ['en','pt','es'];
  if (allowed.indexOf(lang) === -1) return;
  if (window.SYLORA_LANG === lang) return;
  var sec = location.protocol === 'https:' ? '; Secure' : '';
  document.cookie = 'sylora_lang=' + lang + '; path=/; max-age=31536000; SameSite=Lax' + sec;
  window.SYLORA_LANG = lang;
  var dict = window.SYLORA_I18N && window.SYLORA_I18N[lang];
  if (!dict) { location.reload(); return; }
  document.querySelectorAll('[data-i18n]').forEach(function(el) {
    var k = el.getAttribute('data-i18n');
    if (dict[k] !== undefined) el.textContent = dict[k];
  });
  document.querySelectorAll('[data-i18n-html]').forEach(function(el) {
    var k = el.getAttribute('data-i18n-html');
    if (dict[k] !== undefined) {
      var val = dict[k];
      var n = el.getAttribute('data-i18n-n');
      if (n !== null) val = val.split('{n}').join(n);
      el.innerHTML = val;
    }
  });
  document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el) {
    var k = el.getAttribute('data-i18n-placeholder');
    if (dict[k] !== undefined) el.placeholder = dict[k];
  });
  document.querySelectorAll('[data-i18n-title]').forEach(function(el) {
    var k = el.getAttribute('data-i18n-title');
    if (dict[k] !== undefined) el.title = dict[k];
  });
  document.querySelectorAll('[data-i18n-aria]').forEach(function(el) {
    var k = el.getAttribute('data-i18n-aria');
    if (dict[k] !== undefined) el.setAttribute('aria-label', dict[k]);
  });
  document.documentElement.lang = lang;
  if (dict['site.title']) document.title = dict['site.title'];
  var trigger = document.getElementById('lang-trigger-code');
  if (trigger) trigger.textContent = lang.toUpperCase();
  document.querySelectorAll('.lang-option').forEach(function(btn) {
    var on = btn.getAttribute('data-lang') === lang;
    btn.classList.toggle('active', on);
    btn.setAttribute('aria-selected', on ? 'true' : 'false');
  });
  document.querySelectorAll('.lang-btn-mobile').forEach(function(btn) {
    btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
  });
  document.dispatchEvent(new CustomEvent('sylora:langchange', { detail: { lang: lang, dict: dict } }));
}

(function(){
  var trigger = document.getElementById('lang-trigger');
  var menu    = document.getElementById('lang-menu');
  var wrap    = document.getElementById('lang-switcher');
  if (!trigger || !menu || !wrap) return;

  function close() {
    wrap.classList.remove('open');
    trigger.setAttribute('aria-expanded', 'false');
  }
  function open() {
    wrap.classList.add('open');
    trigger.setAttribute('aria-expanded', 'true');
  }
  trigger.addEventListener('click', function(e){
    e.stopPropagation();
    wrap.classList.contains('open') ? close() : open();
  });
  document.addEventListener('click', function(e){
    if (!wrap.contains(e.target)) close();
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && wrap.classList.contains('open')) {
      close();
      trigger.focus();
    }
  });
  menu.querySelectorAll('.lang-option').forEach(function(btn){
    btn.addEventListener('click', function(){
      var l = btn.getAttribute('data-lang');
      close();
      setLang(l);
    });
  });
  document.querySelectorAll('.lang-btn-mobile').forEach(function(btn){
    btn.addEventListener('click', function(){
      setLang(btn.getAttribute('data-lang'));
    });
  });
})();

(function(){
  var t = document.getElementById('sylora-toast');
  if (t && t.dataset.flashMsg) {
    setTimeout(function(){ showToast(t.dataset.flashMsg, t.dataset.flashType || 'success'); }, 120);
  }
})();

function showToast(msg, type) {
  const t = document.getElementById('sylora-toast');
  if (!t) return;
  t.textContent = msg;
  t.className = 'sylora-toast-show sylora-toast-' + (type || 'info');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => { t.className = ''; }, 3800);
}


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

<div id="pjax-root" data-auth="<?= $isLoggedIn ? '1' : '0' ?>">