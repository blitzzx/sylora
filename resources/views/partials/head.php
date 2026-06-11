<?php

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

$_seoTitle  = isset($pageTitle)       ? $pageTitle       : t('site.title');
$_seoDesc   = isset($pageDescription) ? $pageDescription : t('site.description');
$_seoPath   = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$_seoBase   = SITE_URL . $_seoPath;
$_seoLang   = getLang();
$_seoAlts   = ['en' => $_seoBase, 'pt' => $_seoBase . '?lang=pt', 'es' => $_seoBase . '?lang=es'];
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
  <link rel="icon" type="image/png" href="/assets/img/FavIcon-Sylora.png">
  <link rel="apple-touch-icon" href="/assets/img/FavIcon-Sylora.png">
<?php foreach (['variables', 'base', 'animations', 'components', 'layout', 'pages'] as $cssFile): ?>
  <link rel="stylesheet" href="/css/<?= $cssFile ?>.css?v=<?= @filemtime(ROOT . '/public/css/' . $cssFile . '.css') ?: '1' ?>">
<?php endforeach; ?>

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
  <script>window.SYLORA_I18N=<?= json_encode(['en'=>require ROOT.'/resources/lang/en.php','pt'=>require ROOT.'/resources/lang/pt.php','es'=>require ROOT.'/resources/lang/es.php'],JSON_HEX_TAG|JSON_HEX_AMP) ?>;
  window.SYLORA_LANG=<?= json_encode(getLang()) ?>;
  window.SYLORA_T=function(key,vars){
    var dict=(window.SYLORA_I18N&&window.SYLORA_I18N[window.SYLORA_LANG])||{};
    var val=(dict[key]!==undefined)?dict[key]:key;
    if(vars){for(var k in vars){val=val.split('{'+k+'}').join(vars[k]);}}
    return val;
  };</script>
</head>
<?php endif; ?>
