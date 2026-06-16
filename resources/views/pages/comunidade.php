<?php
if (!isLoggedIn()) {
    redirect('/login');
}

$pageTitle = t('nav.community') . ' — Sylora: Ecos dos Deuses';
$pageDescription = t('community.description');
$pageNoindex = true;

$page    = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 50;
$offset  = ($page - 1) * $perPage;

$stmt = $conn->prepare("
    SELECT u.id, u.username, u.avatar,
           s.level   AS best_level,
           s.chapter AS best_chapter,
           s.xp, s.xp_req
    FROM users u
    LEFT JOIN saves s ON s.id = (
        SELECT s2.id FROM saves s2
        WHERE s2.user_id = u.id
        ORDER BY s2.level DESC, s2.last_saved DESC
        LIMIT 1
    )
    WHERE u.is_active = 1
    ORDER BY COALESCE(s.level, -1) DESC, u.username ASC
    LIMIT ? OFFSET ?
");
$stmt->bind_param('ii', $perPage, $offset);
$stmt->execute();
$players = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmtTotal = $conn->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
$totalPlayers = (int) $stmtTotal->fetch_row()[0];
$totalPages   = (int) ceil($totalPlayers / $perPage);

$stmtSaves = $conn->query("SELECT COUNT(DISTINCT user_id) FROM saves");
$playersWithSaves = (int) $stmtSaves->fetch_row()[0];

include ROOT . '/resources/views/partials/head.php';
include ROOT . '/resources/views/partials/navbar.php';
?>

<div class="cm-page">

  <div class="cm-hero">
    <div class="cm-runes" aria-hidden="true">⊕ ✦ ◈ ⟡ ✦</div>
    <p class="cm-overline" data-i18n="community.overline"><?= t('community.overline') ?></p>
    <h1 class="cm-title" data-i18n="community.title"><?= t('community.title') ?></h1>
    <div class="cm-stats-bar">
      <div class="cm-stat">
        <span class="cm-stat-num"><?= number_format($totalPlayers) ?></span>
        <span class="cm-stat-label" data-i18n="community.stat_adventurers"><?= t('community.stat_adventurers') ?></span>
      </div>
      <div class="cm-stat-sep"></div>
      <div class="cm-stat">
        <span class="cm-stat-num"><?= number_format($playersWithSaves) ?></span>
        <span class="cm-stat-label" data-i18n="community.stat_with_saves"><?= t('community.stat_with_saves') ?></span>
      </div>
    </div>
  </div>

  <div class="cm-search-wrap">
    <div class="cm-search-box">
      <svg class="cm-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input type="text" id="cm-search" class="cm-search-input"
        placeholder="<?= e(t('community.search_placeholder')) ?>"
        data-i18n-placeholder="community.search_placeholder"
        autocomplete="off" spellcheck="false">
      <button class="cm-search-clear" id="cm-search-clear" aria-label="Limpar">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="cm-results-count" id="cm-results-count"></div>
  </div>

  <?php if (!empty($players)): ?>

  <?php if ($page === 1 && count($players) >= 1):
    $top3 = array_slice($players, 0, min(3, count($players)));
    $podiumOrder = [];
    if (isset($top3[1])) $podiumOrder[] = ['p' => $top3[1], 'rank' => 2];
    $podiumOrder[] = ['p' => $top3[0], 'rank' => 1];
    if (isset($top3[2])) $podiumOrder[] = ['p' => $top3[2], 'rank' => 3];
  ?>
  <div class="cm-podium" id="cm-podium">
    <?php foreach ($podiumOrder as $item):
      $p    = $item['p'];
      $rank = $item['rank'];
      $ini  = strtoupper(mb_substr($p['username'], 0, 1));
      $xpPct = ($p['xp_req'] > 0 && $p['xp'] !== null)
               ? min(100, (int) round(($p['xp'] / $p['xp_req']) * 100))
               : 0;
    ?>
    <a href="/u?u=<?= urlencode($p['username']) ?>"
       class="cm-podium-card rank-<?= $rank ?>"
       data-username="<?= e(mb_strtolower($p['username'])) ?>">

      <div class="cm-pod-body">
        <div class="cm-pod-medal">
          <?php if ($rank === 1): ?>
            <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6l4.5 3.4L12 4l4.5 5.4L21 6l-1.7 11H4.7L3 6z"/></svg>
          <?php elseif ($rank === 2): ?>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
          <?php else: ?>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
          <?php endif; ?>
        </div>

        <div class="cm-pod-avatar">
          <?php if (!empty($p['avatar'])): ?>
            <img src="/avatar?id=<?= (int) $p['id'] ?>" alt="" loading="lazy">
          <?php else: ?>
            <?= e($ini) ?>
          <?php endif; ?>
        </div>

        <div class="cm-pod-username"><?= e($p['username']) ?></div>

        <?php if ($p['best_level'] !== null): ?>
          <div class="cm-pod-level">Lv.&nbsp;<?= (int) $p['best_level'] ?></div>
          <?php if ($p['xp_req'] > 0): ?>
          <div class="cm-pod-xp-wrap">
            <div class="cm-pod-xp-bar">
              <div class="cm-pod-xp-fill" style="width:<?= $xpPct ?>%"></div>
            </div>
            <span class="cm-pod-xp-label"><?= $xpPct ?>%&nbsp;XP</span>
          </div>
          <?php endif; ?>
          <?php if (!empty($p['best_chapter'])): ?>
            <div class="cm-pod-chapter"><?= e($p['best_chapter']) ?></div>
          <?php endif; ?>
        <?php else: ?>
          <div class="cm-pod-nosave" data-i18n="community.no_save"><?= t('community.no_save') ?></div>
        <?php endif; ?>
      </div>

      <div class="cm-pod-base">
        <span class="cm-pod-hash">#</span><span class="cm-pod-rank"><?= $rank ?></span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="cm-list" id="cm-list">
    <?php foreach ($players as $i => $p):
      $rank   = $offset + $i + 1;
      $ini    = strtoupper(mb_substr($p['username'], 0, 1));
      $xpPct  = ($p['xp_req'] > 0 && $p['xp'] !== null)
                ? min(100, (int) round(($p['xp'] / $p['xp_req']) * 100))
                : 0;
      if      ($rank === 1) $tier = 'tier-gold';
      elseif  ($rank === 2) $tier = 'tier-silver';
      elseif  ($rank === 3) $tier = 'tier-bronze';
      else                  $tier = '';
      $delay = number_format(min($i * 0.035, 0.8), 3);
    ?>
    <a href="/u?u=<?= urlencode($p['username']) ?>"
       class="cm-row <?= $tier ?>"
       data-username="<?= e(mb_strtolower($p['username'])) ?>"
       style="animation-delay:<?= $delay ?>s">

      <div class="cm-row-rank">
        <?php if ($rank === 1): ?>
          <span class="cm-medal cm-medal-1"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 6l4.5 3.4L12 4l4.5 5.4L21 6l-1.7 11H4.7L3 6z"/></svg></span>
        <?php elseif ($rank === 2): ?>
          <span class="cm-medal cm-medal-2">2</span>
        <?php elseif ($rank === 3): ?>
          <span class="cm-medal cm-medal-3">3</span>
        <?php else: ?>
          <span class="cm-rnum"><?= $rank ?></span>
        <?php endif; ?>
      </div>

      <div class="cm-row-av">
        <?php if (!empty($p['avatar'])): ?>
          <img src="/avatar?id=<?= (int) $p['id'] ?>" alt="" loading="lazy">
        <?php else: ?>
          <?= e($ini) ?>
        <?php endif; ?>
      </div>

      <div class="cm-row-info">
        <div class="cm-row-name"><?= e($p['username']) ?></div>
        <?php if (!empty($p['best_chapter'])): ?>
          <div class="cm-row-sub"><?= e($p['best_chapter']) ?></div>
        <?php endif; ?>
      </div>

      <div class="cm-row-right">
        <?php if ($p['best_level'] !== null): ?>
          <div class="cm-row-lv">Lv.&nbsp;<?= (int) $p['best_level'] ?></div>
          <?php if ($p['xp_req'] > 0): ?>
          <div class="cm-row-xpbar">
            <div class="cm-row-xpfill" style="width:<?= $xpPct ?>%"></div>
          </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="cm-row-nosave" data-i18n="community.no_save"><?= t('community.no_save') ?></div>
        <?php endif; ?>
      </div>

    </a>
    <?php endforeach; ?>
  </div>

  <div class="cm-no-results" id="cm-no-results" style="display:none">
    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
    <p data-i18n="community.search_empty"><?= t('community.search_empty') ?></p>
  </div>

  <?php if ($totalPages > 1): ?>
  <div class="cm-pagination">
    <?php if ($page > 1): ?>
      <a href="/comunidade?p=<?= $page - 1 ?>" class="btn btn-secondary btn-sm">← <?= t('common.prev') ?></a>
    <?php endif; ?>
    <span class="cm-page-info"><?= t('community.page_of', ['cur' => $page, 'total' => $totalPages]) ?></span>
    <?php if ($page < $totalPages): ?>
      <a href="/comunidade?p=<?= $page + 1 ?>" class="btn btn-secondary btn-sm"><?= t('common.next') ?> →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php else: ?>
  <div class="cm-empty">
    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="6" cy="9" r="3"/><path d="M1 21v-2a4 4 0 014-4h2"/><circle cx="18" cy="9" r="3"/><path d="M23 21v-2a4 4 0 00-4-4h-2"/><circle cx="12" cy="7" r="4"/><path d="M7 21a5 5 0 0110 0"/></svg>
    <p data-i18n="community.empty"><?= t('community.empty') ?></p>
  </div>
  <?php endif; ?>

</div>

<script>
(function () {
  var search   = document.getElementById('cm-search');
  var clearBtn = document.getElementById('cm-search-clear');
  var noRes    = document.getElementById('cm-no-results');
  var countEl  = document.getElementById('cm-results-count');
  var podium   = document.getElementById('cm-podium');
  var list     = document.getElementById('cm-list');
  if (!search || !list) return;

  function filter(q) {
    q = q.toLowerCase().trim();
    var rows  = list.querySelectorAll('.cm-row');
    var total = 0;
    rows.forEach(function (r) {
      var match = !q || (r.dataset.username || '').includes(q);
      r.style.display = match ? '' : 'none';
      if (match) total++;
    });
    if (podium) {
      var cards = podium.querySelectorAll('.cm-podium-card');
      var anyPod = false;
      cards.forEach(function (c) {
        var match = !q || (c.dataset.username || '').includes(q);
        c.style.display = match ? '' : 'none';
        if (match) { anyPod = true; total++; }
      });
      podium.style.display = (q && !anyPod) ? 'none' : '';
    }
    if (clearBtn) clearBtn.style.display = q ? 'flex' : 'none';
    if (noRes)    noRes.style.display    = (q && total === 0) ? 'flex' : 'none';
    if (countEl) {
      if (q && total > 0) {
        countEl.textContent = total + ' resultado' + (total !== 1 ? 's' : '');
        countEl.style.display = '';
      } else {
        countEl.style.display = 'none';
      }
    }
  }

  search.addEventListener('input', function () { filter(this.value); });
  if (clearBtn) {
    clearBtn.style.display = 'none';
    clearBtn.addEventListener('click', function () { search.value = ''; filter(''); search.focus(); });
  }

  document.addEventListener('sylora:langchange', function (e) {
    var d = e.detail && e.detail.dict;
    if (d && d['community.search_placeholder']) search.placeholder = d['community.search_placeholder'];
  });
})();
</script>

<?php include ROOT . '/resources/views/partials/footer.php'; ?>
