<?php
/**
 * Página de erro genérica (404 / 403 / 500).
 * O bootstrap em public/<código>.php define $errorCode antes de incluir.
 */

$errorCode = isset($errorCode) ? (int) $errorCode : 404;

$map = [
    404 => ['error.404_title', 'error.404_msg'],
    403 => ['error.403_title', 'error.403_msg'],
    500 => ['error.500_title', 'error.500_msg'],
];
$keys = $map[$errorCode] ?? $map[404];

http_response_code($errorCode);

$pageTitle       = $errorCode . ' · ' . t($keys[0]) . ' — Sylora: Ecos dos Deuses';
$pageDescription = t($keys[1]);
$pageNoindex     = true;

include ROOT . '/resources/views/partials/head.php';
include ROOT . '/resources/views/partials/navbar.php';
?>

<main class="error-page">
  <div class="error-inner">
    <div class="error-code" aria-hidden="true"><?= $errorCode ?></div>
    <h1 class="error-title" data-i18n="<?= $keys[0] ?>"><?= e(t($keys[0])) ?></h1>
    <div class="error-actions">
      <a href="/" class="error-btn error-btn-gold">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>
        <span data-i18n="error.home_btn"><?= e(t('error.home_btn')) ?></span>
      </a>
      <button type="button" class="error-btn error-btn-ghost" onclick="history.back()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span data-i18n="error.back_btn"><?= e(t('error.back_btn')) ?></span>
      </button>
    </div>
  </div>
</main>

<?php include ROOT . '/resources/views/partials/footer.php'; ?>
