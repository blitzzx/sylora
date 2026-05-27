<?php
require_once 'includes/config.php';

$q       = trim($_GET['q'] ?? '');
$results = [];
$total   = 0;
$isJson  = !empty($_GET['json']);

if ($q !== '' && mb_strlen($q) >= 2) {
    $like = '%' . $q . '%';

    
    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM users WHERE username LIKE ? AND is_active = 1");
    $stmtCount->bind_param('s', $like);
    $stmtCount->execute();
    $total = (int) $stmtCount->get_result()->fetch_row()[0];
    $stmtCount->close();

    
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.role, u.created_at,
               (SELECT level FROM saves WHERE user_id = u.id ORDER BY level DESC LIMIT 1) AS best_level,
               (SELECT chapter FROM saves WHERE user_id = u.id ORDER BY level DESC LIMIT 1) AS best_chapter
        FROM users u
        WHERE u.username LIKE ? AND u.is_active = 1
        ORDER BY u.username ASC
        LIMIT 30
    ");
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}


if ($isJson) {
    header('Content-Type: application/json; charset=utf-8');
    $out = [];
    foreach ($results as $r) {
        $out[] = [
            'id'       => (int) $r['id'],
            'username' => $r['username'],
            'level'    => $r['best_level'] ? (int) $r['best_level'] : null,
        ];
    }
    echo json_encode($out);
    exit;
}

include 'includes/header.php';
?>

<main class="search-main">
  <div class="container">

    <div class="page-header" style="margin-bottom: 32px;">
      <h1 data-i18n="search.title"><?= t('search.title') ?></h1>
      <p class="page-subtitle" data-i18n="search.subtitle"><?= t('search.subtitle') ?></p>
    </div>


    <form class="search-form" method="GET" action="search.php" role="search">
      <div class="search-input-wrap">
        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        <input
          type="search"
          name="q"
          class="search-input"
          data-i18n-placeholder="search.input_ph"
          placeholder="<?= e(t('search.input_ph')) ?>"
          value="<?php echo e($q); ?>"
          autocomplete="off"
          autofocus
          minlength="2"
          maxlength="60"
        >
        <button type="submit" class="btn btn-primary" data-i18n="search.submit"><?= t('search.submit') ?></button>
      </div>
    </form>

    <?php if ($q !== ''): ?>


      <?php if ($total === 0): ?>
        <div class="search-empty">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--muted)">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
          </svg>
          <p><?php echo e(t('search.empty', ['q' => $q])); ?></p>
        </div>

      <?php else: ?>
        <p class="search-count">
          <?php
            $displayedCount = $total <= 30 ? (string)$total : '30+';
            $plural = $total !== 1 ? 's' : '';
            echo e(t('search.count', ['n' => $displayedCount, 'plural' => $plural, 'q' => $q]));
          ?>
        </p>

        <div class="search-results">
          <?php foreach ($results as $r): ?>
            <?php
              $level   = $r['best_level'] ? (int) $r['best_level'] : null;
              $chapter = $r['best_chapter'] ?? null;
              $roleLabel = $r['role'] === 'admin' ? t('profile.role_admin') : t('profile.role_user');
            ?>
            <a href="u.php?u=<?php echo urlencode($r['username']); ?>" class="search-card">
              <div class="search-avatar">
                <img
                  src="avatar.php?id=<?php echo (int)$r['id']; ?>"
                  alt=""
                  width="48" height="48"
                  onerror="this.outerHTML='<span class=\'search-initial\'><?php echo e(strtoupper(mb_substr($r['username'],0,1))); ?></span>'"
                >
              </div>
              <div class="search-info">
                <strong class="search-username"><?php echo e($r['username']); ?></strong>
                <span class="search-role"><?php echo e($roleLabel); ?></span>
                <?php if ($level): ?>
                  <span class="search-stats">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <?php echo e(t('search.level', ['n' => $level])); ?>
                    <?php if ($chapter): ?> · <?php echo e($chapter); ?><?php endif; ?>
                  </span>
                <?php else: ?>
                  <span class="search-stats" style="color:var(--faint)" data-i18n="search.no_saves"><?= t('search.no_saves') ?></span>
                <?php endif; ?>
              </div>
              <svg class="search-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
          <?php endforeach; ?>
        </div>

        <?php if ($total > 30): ?>
          <p class="search-more" data-i18n="search.more_results"><?= t('search.more_results') ?></p>
        <?php endif; ?>

      <?php endif; ?>

    <?php elseif ($q === '' && isset($_GET['q'])): ?>
      <p class="search-hint" data-i18n="search.too_short"><?= t('search.too_short') ?></p>
    <?php endif; ?>

  </div>
</main>

<?php include 'includes/footer.php'; ?>
