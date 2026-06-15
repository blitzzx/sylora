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
  <div class="error-card">
    <div class="error-code" aria-hidden="true"><?= $errorCode ?></div>
    <span class="error-tag"><?= t('error.code_label') ?> <?= $errorCode ?></span>
    <h1 class="error-title" data-i18n="<?= $keys[0] ?>"><?= e(t($keys[0])) ?></h1>
    <p class="error-msg" data-i18n="<?= $keys[1] ?>"><?= e(t($keys[1])) ?></p>
    <div class="error-actions">
      <a href="/" class="btn btn-primary" data-i18n="error.home_btn"><?= e(t('error.home_btn')) ?></a>
      <button type="button" class="btn btn-secondary" onclick="history.back()" data-i18n="error.back_btn"><?= e(t('error.back_btn')) ?></button>
    </div>
  </div>
</main>

<?php include ROOT . '/resources/views/partials/footer.php'; ?>
