<?php
require_once 'includes/config.php';

$viewUsername = trim($_GET['u'] ?? '');
if ($viewUsername === '') {
    if (!isLoggedIn()) redirect('login.php');
    redirect('u.php?u=' . urlencode($_SESSION['username']));
}

// Buscar utilizador alvo
$stmt = $conn->prepare("
    SELECT id, username, email, role, bio, avatar, created_at, last_login_at
    FROM users WHERE username = ? AND is_active = 1 LIMIT 1
");
$stmt->bind_param('s', $viewUsername);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    http_response_code(404);
    include 'includes/header.php';
    echo '<main class="container" style="padding:80px 20px;text-align:center;">
        <h2 style="color:var(--muted)">Aventureiro não encontrado</h2>
        <a href="index.php" class="btn btn-primary" style="margin-top:24px">Voltar ao início</a>
    </main>';
    include 'includes/footer.php';
    exit;
}

$profileId   = (int) $profile['id'];
$isSelf      = isLoggedIn() && (int) $_SESSION['user_id'] === $profileId;
$isGuest     = !isLoggedIn();
$csrfToken   = isLoggedIn() ? generateCSRFToken() : '';
$memberSince = $profile['created_at'] ? date('d/m/Y', strtotime($profile['created_at'])) : '-';
$lastLogin   = $profile['last_login_at'] ? date('d/m/Y \à\s H:i', strtotime($profile['last_login_at'])) : '-';
$roleLabel   = $profile['role'] === 'admin' ? 'Admin' : 'Aventureiro';
$roleColor   = $profile['role'] === 'admin' ? 'role-admin' : 'role-user';
$hasAvatar   = !empty($profile['avatar']);

// Melhor save
$stmtSave = $conn->prepare("
    SELECT level, hp, hp_total, xp, xp_req, chapter, story_progress, damage, last_saved
    FROM saves WHERE user_id = ? ORDER BY level DESC, last_saved DESC LIMIT 1
");
$stmtSave->bind_param('i', $profileId);
$stmtSave->execute();
$bestSave = $stmtSave->get_result()->fetch_assoc();
$stmtSave->close();

// Flash message (só para o próprio)
$flash = null;
if ($isSelf && isset($_SESSION['flash_message'])) {
    $flash = ['msg' => $_SESSION['flash_message'], 'type' => $_SESSION['flash_type'] ?? 'info'];
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

// Estado de amizade
$friendStatus = 'none';
$iRequested   = false;
if (isLoggedIn() && !$isSelf) {
    $myId = (int) $_SESSION['user_id'];
    $stmtF = $conn->prepare("
        SELECT status, requester_id FROM friendships
        WHERE (requester_id = ? AND addressee_id = ?)
           OR (requester_id = ? AND addressee_id = ?) LIMIT 1
    ");
    $stmtF->bind_param('iiii', $myId, $profileId, $profileId, $myId);
    $stmtF->execute();
    $fRow = $stmtF->get_result()->fetch_assoc();
    $stmtF->close();
    if ($fRow) {
        $friendStatus = $fRow['status'];
        $iRequested   = (int) $fRow['requester_id'] === $myId;
    }
}

// Amigos
$friendsList   = [];
$pendingIn     = [];
$mutualFriends = [];

if ($isSelf) {
    $myId = (int) $_SESSION['user_id'];

    $stmtFriends = $conn->prepare("
        SELECT u.id, u.username, u.avatar,
               (SELECT s.level FROM saves s WHERE s.user_id = u.id ORDER BY s.level DESC LIMIT 1) AS best_level
        FROM users u
        INNER JOIN friendships f ON (
            (f.requester_id = ? AND f.addressee_id = u.id) OR
            (f.addressee_id = ? AND f.requester_id = u.id)
        )
        WHERE f.status = 'accepted' AND u.is_active = 1
        ORDER BY u.username ASC
    ");
    $stmtFriends->bind_param('ii', $myId, $myId);
    $stmtFriends->execute();
    $friendsList = $stmtFriends->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtFriends->close();

    $stmtPending = $conn->prepare("
        SELECT u.id, u.username FROM users u
        INNER JOIN friendships f ON f.requester_id = u.id
        WHERE f.addressee_id = ? AND f.status = 'pending' AND u.is_active = 1
        ORDER BY f.created_at DESC
    ");
    $stmtPending->bind_param('i', $myId);
    $stmtPending->execute();
    $pendingIn = $stmtPending->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtPending->close();

} else {
    if (isLoggedIn()) {
        $myId = (int) $_SESSION['user_id'];
        $stmtM = $conn->prepare("
            SELECT u.id, u.username FROM users u
            WHERE u.id IN (
                SELECT CASE WHEN f.requester_id = ? THEN f.addressee_id ELSE f.requester_id END
                FROM friendships f WHERE (f.requester_id = ? OR f.addressee_id = ?) AND f.status = 'accepted'
            )
            AND u.id IN (
                SELECT CASE WHEN f.requester_id = ? THEN f.addressee_id ELSE f.requester_id END
                FROM friendships f WHERE (f.requester_id = ? OR f.addressee_id = ?) AND f.status = 'accepted'
            ) LIMIT 5
        ");
        $stmtM->bind_param('iiiiii', $myId, $myId, $myId, $profileId, $profileId, $profileId);
        $stmtM->execute();
        $mutualFriends = $stmtM->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtM->close();
    }

    $stmtFriends = $conn->prepare("
        SELECT u.id, u.username, u.avatar,
               (SELECT s.level FROM saves s WHERE s.user_id = u.id ORDER BY s.level DESC LIMIT 1) AS best_level
        FROM users u
        INNER JOIN friendships f ON (
            (f.requester_id = ? AND f.addressee_id = u.id) OR
            (f.addressee_id = ? AND f.requester_id = u.id)
        )
        WHERE f.status = 'accepted' AND u.is_active = 1
        ORDER BY u.username ASC LIMIT 30
    ");
    $stmtFriends->bind_param('ii', $profileId, $profileId);
    $stmtFriends->execute();
    $friendsList = $stmtFriends->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtFriends->close();
}

// Comentários
$stmtComments = $conn->prepare("
    SELECT pc.id, pc.content, pc.created_at,
           u.id AS author_id, u.username AS author_username
    FROM profile_comments pc
    INNER JOIN users u ON u.id = pc.author_id
    WHERE pc.profile_user_id = ? AND pc.is_hidden = 0
    ORDER BY pc.created_at DESC LIMIT 10
");
$stmtComments->bind_param('i', $profileId);
$stmtComments->execute();
$comments = $stmtComments->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtComments->close();

$stmtComCount = $conn->prepare("SELECT COUNT(*) FROM profile_comments WHERE profile_user_id = ? AND is_hidden = 0");
$stmtComCount->bind_param('i', $profileId);
$stmtComCount->execute();
$totalComments = (int) $stmtComCount->get_result()->fetch_row()[0];
$stmtComCount->close();

include 'includes/header.php';
?>

<main class="user-profile-main">
  <div class="container">

    <?php if ($flash): ?>
      <div class="alert alert-<?php echo e($flash['type']); ?>" style="margin-bottom:16px"><?php echo e($flash['msg']); ?></div>
    <?php endif; ?>

    <!-- ══════════ HERO CARD ══════════ -->
    <div class="up-hero-card">
      <div class="up-hero-bg" aria-hidden="true">
        <span class="ph-orb ph-orb1"></span>
        <span class="ph-orb ph-orb2"></span>
      </div>

      <div class="up-hero-inner">
        <!-- Avatar -->
        <div class="up-avatar-wrap">
          <?php if ($hasAvatar): ?>
            <img
              src="avatar.php?id=<?php echo $profileId; ?>"
              alt="Avatar de <?php echo e($profile['username']); ?>"
              class="up-avatar-img" width="100" height="100" loading="lazy"
            >
          <?php else: ?>
            <div class="up-avatar-placeholder">
              <?php echo e(strtoupper(mb_substr($profile['username'], 0, 1))); ?>
            </div>
          <?php endif; ?>

          <?php if (!$isSelf && $bestSave): ?>
            <div class="up-level-badge">Lv.<?php echo (int)$bestSave['level']; ?></div>
          <?php endif; ?>

          <?php if ($isSelf): ?>
            <form id="avatar-form" method="POST" action="/profile" enctype="multipart/form-data" style="display:none">
              <input type="hidden" name="action" value="upload_avatar">
              <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
              <input type="file" id="avatar-upload-input" name="avatar" accept="image/jpeg,image/png,image/webp" style="display:none">
            </form>
            <label for="avatar-upload-input" class="up-avatar-edit-btn" title="Alterar foto">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </label>
          <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="up-info">
          <div class="up-name-row">
            <h1><?php echo e($profile['username']); ?></h1>
            <span class="profile-role-badge <?php echo $roleColor; ?>"><?php echo $roleLabel; ?></span>
          </div>
          <p class="up-since">
            Aventureiro desde <?php echo $memberSince; ?>
            <?php if ($isSelf): ?> &middot; Último login: <?php echo $lastLogin; ?><?php endif; ?>
          </p>
          <?php if (!empty($profile['bio'])): ?>
            <p class="up-bio"><?php echo nl2br(e($profile['bio'])); ?></p>
          <?php elseif ($isSelf): ?>
            <p class="up-bio up-bio-placeholder">Sem bio ainda. <button class="up-add-bio-btn" type="button">Adicionar bio</button></p>
          <?php endif; ?>
        </div>

        <!-- Ações -->
        <div class="up-actions">
          <?php if ($isSelf): ?>
            <button class="btn btn-secondary btn-sm" id="edit-profile-btn" type="button">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Editar Perfil
            </button>
          <?php elseif (!$isGuest): ?>
            <?php if ($friendStatus === 'none'): ?>
              <button class="btn btn-primary btn-sm" data-user-id="<?php echo $profileId; ?>" data-action="add">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                Adicionar Amigo
              </button>
            <?php elseif ($friendStatus === 'pending' && $iRequested): ?>
              <button class="btn btn-secondary btn-sm" data-user-id="<?php echo $profileId; ?>" data-action="cancel">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Pedido Enviado &middot; Cancelar
              </button>
            <?php elseif ($friendStatus === 'pending' && !$iRequested): ?>
              <div class="friend-pending-actions">
                <button class="btn btn-primary btn-sm" data-user-id="<?php echo $profileId; ?>" data-action="accept">Aceitar</button>
                <button class="btn btn-ghost btn-sm" data-user-id="<?php echo $profileId; ?>" data-action="decline">Recusar</button>
              </div>
            <?php elseif ($friendStatus === 'accepted'): ?>
              <button class="btn btn-secondary btn-sm" data-user-id="<?php echo $profileId; ?>" data-action="remove">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                Amigos
              </button>
            <?php endif; ?>
          <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-sm">Entrar para adicionar</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ══════════ PAINEL DE EDIÇÃO (só para o próprio) ══════════ -->
    <?php if ($isSelf): ?>
    <div class="up-edit-panel" id="edit-panel">
      <div class="up-edit-panel-inner">
        <div class="up-edit-panel-box">
          <div class="up-edit-tabs" role="tablist">
            <button class="up-edit-tab active" role="tab" data-tab="ep-username">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Username
            </button>
            <button class="up-edit-tab" role="tab" data-tab="ep-email">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
              Email
            </button>
            <button class="up-edit-tab" role="tab" data-tab="ep-bio">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              Bio
            </button>
            <button class="up-edit-tab" role="tab" data-tab="ep-password">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
              Password
            </button>
            <button class="up-edit-tab up-edit-tab-danger" role="tab" data-tab="ep-danger">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              Zona de Risco
            </button>
          </div>

          <div class="up-edit-panels">

            <!-- Username -->
            <div class="up-edit-panel-content active" id="ep-username" role="tabpanel">
              <h3>Alterar Username</h3>
              <p>O teu username é público e identificado por outros jogadores.</p>
              <form method="POST" action="/profile" class="profile-form">
                <input type="hidden" name="action" value="change_username">
                <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
                <div class="form-group">
                  <label for="ep_new_username">Novo Username</label>
                  <input type="text" id="ep_new_username" name="new_username" value="<?php echo e($profile['username']); ?>" minlength="3" maxlength="20" placeholder="aventureiro_123" required>
                  <span class="form-hint">3–20 caracteres. Letras, números e _</span>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Guardar Username</button>
              </form>
            </div>

            <!-- Email -->
            <div class="up-edit-panel-content" id="ep-email" role="tabpanel">
              <h3>Alterar Email</h3>
              <p>Usa um email válido para recuperação de conta.</p>
              <form method="POST" action="/profile" class="profile-form">
                <input type="hidden" name="action" value="change_email">
                <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
                <div class="form-group">
                  <label for="ep_new_email">Novo Email</label>
                  <input type="email" id="ep_new_email" name="new_email" value="<?php echo e($profile['email'] ?? ''); ?>" placeholder="novo@email.com" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Guardar Email</button>
              </form>
            </div>

            <!-- Bio -->
            <div class="up-edit-panel-content" id="ep-bio" role="tabpanel">
              <h3>Bio Pública</h3>
              <p>Apresenta-te à comunidade. Aparece no teu perfil.</p>
              <form method="POST" action="/profile" class="profile-form">
                <input type="hidden" name="action" value="change_bio">
                <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
                <div class="form-group">
                  <label for="ep_bio">Bio</label>
                  <textarea id="ep_bio" name="bio" rows="3" maxlength="300" placeholder="Conta-nos algo sobre ti como aventureiro..."><?php echo e($profile['bio'] ?? ''); ?></textarea>
                  <span class="form-hint">Máx. 300 caracteres · Sem linguagem ofensiva</span>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Guardar Bio</button>
              </form>
            </div>

            <!-- Password -->
            <div class="up-edit-panel-content" id="ep-password" role="tabpanel">
              <h3>Alterar Password</h3>
              <p>Escolhe uma password forte com pelo menos 6 caracteres.</p>
              <form method="POST" action="/profile" class="profile-form">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
                <div class="form-group">
                  <label for="ep_current_password">Password Atual</label>
                  <input type="password" id="ep_current_password" name="current_password" placeholder="••••••••" required>
                </div>
                <div class="form-row-two">
                  <div class="form-group">
                    <label for="ep_new_pw">Nova Password</label>
                    <input type="password" id="ep_new_pw" name="new_password" placeholder="••••••••" required minlength="6">
                  </div>
                  <div class="form-group">
                    <label for="ep_confirm_pw">Confirmar</label>
                    <input type="password" id="ep_confirm_pw" name="confirm_new_password" placeholder="••••••••" required>
                  </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Alterar Password</button>
              </form>
            </div>

            <!-- Danger zone -->
            <div class="up-edit-panel-content" id="ep-danger" role="tabpanel">
              <h3>Zona de Risco</h3>
              <p>Estas ações são irreversíveis. Procede com cuidado.</p>
              <div class="danger-card">
                <div class="danger-card-text">
                  <h3>Terminar todas as sessões</h3>
                  <p>Faz logout em todos os dispositivos onde tens sessão iniciada.</p>
                </div>
                <form method="POST" action="/profile">
                  <input type="hidden" name="action" value="revoke_sessions">
                  <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
                  <button type="submit" class="btn btn-danger-outline">Terminar sessões</button>
                </form>
              </div>
            </div>

            <button class="up-edit-close" id="edit-panel-close" type="button">↑ Fechar configurações</button>

          </div><!-- /up-edit-panels -->
        </div><!-- /up-edit-panel-box -->
      </div><!-- /up-edit-panel-inner -->
    </div><!-- /up-edit-panel -->
    <?php endif; ?>

    <!-- ══════════ TABS DE CONTEÚDO ══════════ -->
    <div class="up-content-tabs" role="tablist">
      <button class="up-content-tab active" role="tab" data-panel="panel-stats">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Estatísticas
      </button>
      <button class="up-content-tab" role="tab" data-panel="panel-friends">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        Amigos<?php if ($isSelf && !empty($pendingIn)): ?>
          <span class="up-tab-badge"><?php echo count($pendingIn); ?></span>
        <?php elseif (!$isSelf && !empty($friendsList)): ?>
          <span class="up-tab-badge-neutral up-tab-badge"><?php echo count($friendsList); ?></span>
        <?php endif; ?>
      </button>
    </div>

    <!-- ══ PAINEL: Estatísticas ══ -->
    <div class="up-content-panel active" id="panel-stats">
      <div class="up-card">
        <div class="up-card-title">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          Estatísticas do Jogo
        </div>

        <?php if ($bestSave):
          $hpPct = $bestSave['hp_total'] > 0 ? round(($bestSave['hp'] / $bestSave['hp_total']) * 100) : 100;
          $xpPct = $bestSave['xp_req'] > 0 ? round(($bestSave['xp'] / $bestSave['xp_req']) * 100) : 0;
        ?>
          <div class="up-stat-level">
            <span class="up-stat-number"><?php echo (int)$bestSave['level']; ?></span>
            <span class="up-stat-sub">Nível</span>
          </div>

          <div class="up-stat-row">
            <span class="up-stat-label">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#c96b5a" stroke-width="2.5"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
              HP
            </span>
            <span class="up-stat-val"><?php echo (int)$bestSave['hp']; ?> / <?php echo (int)$bestSave['hp_total']; ?></span>
          </div>
          <div class="up-bar"><div class="up-bar-fill up-bar-hp" style="width:<?php echo $hpPct; ?>%"></div></div>

          <div class="up-stat-row" style="margin-top:10px">
            <span class="up-stat-label">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#c9993a" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
              XP
            </span>
            <span class="up-stat-val"><?php echo (int)$bestSave['xp']; ?> / <?php echo (int)$bestSave['xp_req']; ?></span>
          </div>
          <div class="up-bar"><div class="up-bar-fill up-bar-xp" style="width:<?php echo $xpPct; ?>%"></div></div>

          <div class="up-stat-divider"></div>

          <div class="up-stat-row">
            <span class="up-stat-label">Capítulo</span>
            <span class="up-stat-val" style="font-size:11px;text-align:right"><?php echo e($bestSave['chapter']); ?></span>
          </div>
          <div class="up-stat-row">
            <span class="up-stat-label">Dano</span>
            <span class="up-stat-val"><?php echo number_format($bestSave['damage'], 1); ?></span>
          </div>
          <div class="up-stat-row">
            <span class="up-stat-label">Progresso</span>
            <span class="up-stat-val"><?php echo (int)$bestSave['story_progress']; ?>%</span>
          </div>
          <?php if ($bestSave['last_saved']): ?>
            <div class="up-stat-row">
              <span class="up-stat-label" style="color:var(--faint)">Último save</span>
              <span class="up-stat-val" style="color:var(--faint);font-size:11px"><?php echo date('d/m/Y', strtotime($bestSave['last_saved'])); ?></span>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <div class="up-card-empty">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--faint)"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            <p>Nenhum save carregado ainda</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ══ PAINEL: Amigos ══ -->
    <div class="up-content-panel" id="panel-friends">

      <?php if ($isSelf): ?>

        <!-- Pesquisa de aventureiros -->
        <div class="up-section">
          <div class="up-section-title">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            Encontrar Aventureiros
          </div>
          <div class="up-friend-search">
            <div class="up-friend-search-wrap">
              <svg class="up-friend-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" class="up-friend-search-input" id="friend-search-input" placeholder="Pesquisar por username..." autocomplete="off">
            </div>
            <div class="up-search-results" id="friend-search-results"></div>
          </div>
        </div>

        <!-- Pedidos pendentes -->
        <?php if (!empty($pendingIn)): ?>
          <div class="up-section">
            <div class="up-section-title">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              Pedidos Pendentes (<?php echo count($pendingIn); ?>)
            </div>
            <div class="up-pending-list">
              <?php foreach ($pendingIn as $req): ?>
                <div class="up-pending-item">
                  <div class="up-search-result-info">
                    <div class="up-mutual-avatar"><?php echo e(strtoupper(mb_substr($req['username'], 0, 1))); ?></div>
                    <a href="u.php?u=<?php echo urlencode($req['username']); ?>" style="font-weight:600;font-size:13px"><?php echo e($req['username']); ?></a>
                  </div>
                  <div class="up-pending-actions">
                    <button class="btn btn-primary btn-sm" data-action="accept" data-user-id="<?php echo (int)$req['id']; ?>">Aceitar</button>
                    <button class="btn btn-ghost btn-sm" data-action="decline" data-user-id="<?php echo (int)$req['id']; ?>">Recusar</button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Lista de amigos -->
        <div class="up-section">
          <div class="up-section-title">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            Os meus Amigos (<?php echo count($friendsList); ?>)
          </div>
          <?php if (!empty($friendsList)): ?>
            <div class="up-friends-grid">
              <?php foreach ($friendsList as $f): ?>
                <a href="u.php?u=<?php echo urlencode($f['username']); ?>" class="up-friend-card">
                  <div class="up-friend-avatar">
                    <?php if (!empty($f['avatar'])): ?>
                      <img src="avatar.php?id=<?php echo (int)$f['id']; ?>" alt="" loading="lazy">
                    <?php else: ?>
                      <?php echo e(strtoupper(mb_substr($f['username'], 0, 1))); ?>
                    <?php endif; ?>
                  </div>
                  <div class="up-friend-info">
                    <div class="up-friend-name"><?php echo e($f['username']); ?></div>
                    <div class="up-friend-level"><?php echo $f['best_level'] ? 'Nv. ' . (int)$f['best_level'] : 'Sem save'; ?></div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="up-empty-state">
              <p>Ainda não tens amigos. Pesquisa aventureiros acima!</p>
            </div>
          <?php endif; ?>
        </div>

      <?php else: ?>

        <!-- Amigos em comum -->
        <?php if (!empty($mutualFriends) && isLoggedIn()): ?>
          <div class="up-section">
            <div class="up-section-title">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
              Amigos em comum
            </div>
            <div class="up-friends-grid">
              <?php foreach ($mutualFriends as $mf): ?>
                <a href="u.php?u=<?php echo urlencode($mf['username']); ?>" class="up-friend-card">
                  <div class="up-friend-avatar"><?php echo e(strtoupper(mb_substr($mf['username'], 0, 1))); ?></div>
                  <div class="up-friend-info"><div class="up-friend-name"><?php echo e($mf['username']); ?></div></div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Amigos do perfil -->
        <div class="up-section">
          <div class="up-section-title">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            Amigos de <?php echo e($profile['username']); ?>
          </div>
          <?php if (!empty($friendsList)): ?>
            <div class="up-friends-grid">
              <?php foreach ($friendsList as $f): ?>
                <a href="u.php?u=<?php echo urlencode($f['username']); ?>" class="up-friend-card">
                  <div class="up-friend-avatar">
                    <?php if (!empty($f['avatar'])): ?>
                      <img src="avatar.php?id=<?php echo (int)$f['id']; ?>" alt="" loading="lazy">
                    <?php else: ?>
                      <?php echo e(strtoupper(mb_substr($f['username'], 0, 1))); ?>
                    <?php endif; ?>
                  </div>
                  <div class="up-friend-info">
                    <div class="up-friend-name"><?php echo e($f['username']); ?></div>
                    <div class="up-friend-level"><?php echo $f['best_level'] ? 'Nv. ' . (int)$f['best_level'] : 'Sem save'; ?></div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="up-empty-state"><p>Nenhum amigo ainda.</p></div>
          <?php endif; ?>
        </div>

      <?php endif; ?>
    </div>

    <!-- ══ PAINEL: Comentários ══ -->
    <div class="up-content-panel" id="panel-comments">
      <div class="up-card">
        <div class="up-card-title">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
          Comentários da Comunidade
          <span class="up-comment-count"><?php echo $totalComments; ?></span>
        </div>

        <?php if (isLoggedIn() && !$isSelf): ?>
          <form class="up-comment-form" id="comment-form" data-user-id="<?php echo $profileId; ?>">
            <input type="hidden" name="_csrf" value="<?php echo e($csrfToken); ?>">
            <textarea name="content" class="up-comment-textarea" placeholder="Deixa um comentário respeitoso..." maxlength="500" rows="3" required></textarea>
            <div class="up-comment-form-footer">
              <span class="up-comment-hint">Máx. 500 caracteres · Respeita a comunidade</span>
              <button type="submit" class="btn btn-primary btn-sm">Comentar</button>
            </div>
            <div class="up-comment-error" id="comment-error" style="display:none"></div>
          </form>
        <?php elseif ($isGuest): ?>
          <p class="up-comment-login-hint"><a href="login.php">Entra</a> para deixar um comentário.</p>
        <?php endif; ?>

        <div class="up-comments-list" id="comments-list">
          <?php foreach ($comments as $c): ?>
            <div class="up-comment" id="comment-<?php echo (int)$c['id']; ?>">
              <div class="up-comment-header">
                <a href="u.php?u=<?php echo urlencode($c['author_username']); ?>" class="up-comment-author"><?php echo e($c['author_username']); ?></a>
                <span class="up-comment-date"><?php echo date('d/m/Y H:i', strtotime($c['created_at'])); ?></span>
                <?php if (isLoggedIn() && ((int)$_SESSION['user_id'] === (int)$c['author_id'] || $isSelf || isAdmin())): ?>
                  <button class="up-comment-delete" data-comment-id="<?php echo (int)$c['id']; ?>" data-csrf="<?php echo e($csrfToken); ?>" title="Apagar" aria-label="Apagar">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                  </button>
                <?php endif; ?>
              </div>
              <p class="up-comment-body"><?php echo nl2br(e($c['content'])); ?></p>
            </div>
          <?php endforeach; ?>
          <?php if (empty($comments)): ?>
            <p class="up-no-comments">Ainda sem comentários. Sê o primeiro!</p>
          <?php endif; ?>
        </div>

        <?php if ($totalComments > 10): ?>
          <button class="btn btn-ghost btn-sm up-load-more" id="load-more-btn" data-page="2" data-user-id="<?php echo $profileId; ?>">
            Ver mais comentários (<?php echo $totalComments - 10; ?> restantes)
          </button>
        <?php endif; ?>
      </div>
    </div>

  </div>
</main>

<script>
(function () {
  const PROFILE_USER_ID = <?php echo $profileId; ?>;
  const CSRF_TOKEN = <?php echo json_encode($csrfToken); ?>;
  const IS_LOGGED  = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
  const IS_SELF    = <?php echo $isSelf ? 'true' : 'false'; ?>;
  const MY_ID      = <?php echo isLoggedIn() ? (int)$_SESSION['user_id'] : 0; ?>;

  function esc(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  // ── Edit panel toggle
  const editBtn   = document.getElementById('edit-profile-btn');
  const editPanel = document.getElementById('edit-panel');
  const closeBtn  = document.getElementById('edit-panel-close');

  function openEditPanel(tabId) {
    if (!editPanel) return;
    editPanel.classList.add('open');
    if (editBtn) editBtn.textContent = '↑ Fechar';
    if (tabId) {
      document.querySelectorAll('.up-edit-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.up-edit-panel-content').forEach(p => p.classList.remove('active'));
      const tab = document.querySelector('[data-tab="' + tabId + '"]');
      const panel = document.getElementById(tabId);
      if (tab) tab.classList.add('active');
      if (panel) panel.classList.add('active');
    }
    editPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  if (editBtn) {
    editBtn.addEventListener('click', () => {
      if (editPanel.classList.contains('open')) {
        editPanel.classList.remove('open');
        editBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Editar Perfil';
      } else {
        openEditPanel(null);
      }
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      if (editPanel) editPanel.classList.remove('open');
      if (editBtn) editBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Editar Perfil';
    });
  }

  // "Adicionar bio" shortcut
  const addBioBtn = document.querySelector('.up-add-bio-btn');
  if (addBioBtn) {
    addBioBtn.addEventListener('click', () => openEditPanel('ep-bio'));
  }

  // ── Edit panel tabs
  document.querySelectorAll('.up-edit-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.up-edit-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.up-edit-panel-content').forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      const target = document.getElementById(tab.dataset.tab);
      if (target) target.classList.add('active');
    });
  });

  // ── Merge comments into stats panel
  (function () {
    const statsPanel    = document.getElementById('panel-stats');
    const commentsPanel = document.getElementById('panel-comments');
    if (statsPanel && commentsPanel) {
      const card = commentsPanel.querySelector('.up-card');
      if (card) { card.style.marginTop = '20px'; statsPanel.appendChild(card); }
      commentsPanel.remove();
    }
  })();

  // ── Content tabs
  document.querySelectorAll('.up-content-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.up-content-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.up-content-panel').forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      const target = document.getElementById(tab.dataset.panel);
      if (target) target.classList.add('active');
    });
  });

  // ── Avatar upload
  const avatarInput = document.getElementById('avatar-upload-input');
  if (avatarInput) {
    avatarInput.addEventListener('change', function () {
      if (this.files && this.files[0]) {
        document.getElementById('avatar-form').submit();
      }
    });
  }

  // ── Friend actions
  function friendAction(action, userId) {
    const methods = { add: 'POST', cancel: 'DELETE', remove: 'DELETE', accept: 'PUT', decline: 'PUT' };
    const body    = JSON.stringify({ user_id: userId, _csrf: CSRF_TOKEN, action: (action === 'accept' || action === 'decline') ? action : undefined });
    fetch('/api/friends', {
      method: methods[action] || 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body,
    })
    .then(r => r.json())
    .then(d => { if (d.error) { showToast(d.error, 'error'); return; } location.reload(); })
    .catch(() => showToast('Erro de ligação.', 'error'));
  }

  document.querySelectorAll('[data-action]').forEach(btn => {
    btn.addEventListener('click', () => {
      const { action } = btn.dataset;
      const userId = parseInt(btn.dataset.userId, 10);
      if (action === 'remove') {
        showConfirm('Remover amigo?', () => friendAction(action, userId));
        return;
      }
      friendAction(action, userId);
    });
  });

  // ── Friend search (own profile only)
  if (IS_SELF) {
    const searchInput   = document.getElementById('friend-search-input');
    const searchResults = document.getElementById('friend-search-results');
    let searchTimer;

    if (searchInput && searchResults) {
      searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const q = searchInput.value.trim();
        if (!q || q.length < 2) { searchResults.innerHTML = ''; return; }

        searchTimer = setTimeout(() => {
          fetch('/search?json=1&q=' + encodeURIComponent(q), { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
              searchResults.innerHTML = '';
              if (!data.length) {
                searchResults.innerHTML = '<p style="color:var(--muted);font-size:13px;padding:8px 0">Nenhum aventureiro encontrado.</p>';
                return;
              }
              data.forEach(user => {
                const el = document.createElement('div');
                el.className = 'up-search-result-item';
                el.innerHTML = `
                  <div class="up-search-result-info">
                    <div class="up-mutual-avatar">${esc(user.username.charAt(0).toUpperCase())}</div>
                    <div>
                      <span style="font-weight:600;font-size:13px">${esc(user.username)}</span>
                      ${user.level ? `<span style="color:var(--muted);font-size:11px;display:block">Nv. ${esc(String(user.level))}</span>` : ''}
                    </div>
                  </div>
                  <a href="u.php?u=${encodeURIComponent(user.username)}" class="btn btn-secondary btn-sm">Ver Perfil</a>
                `;
                searchResults.appendChild(el);
              });
            })
            .catch(() => {});
        }, 300);
      });
    }
  }

  // ── Comentários: submeter
  const commentForm = document.getElementById('comment-form');
  if (commentForm) {
    commentForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const btn      = this.querySelector('button[type="submit"]');
      const textarea = this.querySelector('textarea');
      const errEl    = document.getElementById('comment-error');
      const content  = textarea.value.trim();
      if (!content) return;

      btn.disabled = true;
      errEl.style.display = 'none';

      const data = new FormData(this);
      data.set('user_id', PROFILE_USER_ID);

      fetch('/api/comments', { method: 'POST', body: data, credentials: 'same-origin' })
        .then(r => r.json())
        .then(d => {
          if (d.error) {
            errEl.textContent = d.error;
            errEl.style.display = 'block';
            btn.disabled = false;
            btn.textContent = btn.dataset.originalText || 'Comentar';
            return;
          }
          const list = document.getElementById('comments-list');
          const noMsg = list.querySelector('.up-no-comments');
          if (noMsg) noMsg.remove();

          const now  = new Date();
          const pad  = n => String(n).padStart(2, '0');
          const dateStr = pad(now.getDate()) + '/' + pad(now.getMonth()+1) + '/' + now.getFullYear()
                        + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
          const el = document.createElement('div');
          el.className = 'up-comment';
          el.id = 'comment-' + d.comment_id;
          el.innerHTML = `
            <div class="up-comment-header">
              <a href="u.php?u=${encodeURIComponent(d.author)}" class="up-comment-author">${esc(d.author)}</a>
              <span class="up-comment-date">${esc(dateStr)}</span>
              <button class="up-comment-delete" data-comment-id="${d.comment_id}" data-csrf="${esc(CSRF_TOKEN)}" title="Apagar" aria-label="Apagar">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
              </button>
            </div>
            <p class="up-comment-body">${esc(d.content).replace(/\n/g, '<br>')}</p>
          `;
          list.insertAdjacentElement('afterbegin', el);
          textarea.value = '';
          btn.disabled = false;
          btn.textContent = btn.dataset.originalText || 'Comentar';
          el.querySelector('.up-comment-delete')?.addEventListener('click', deleteComment);
        })
        .catch(() => {
          errEl.textContent = 'Erro de ligação. Tenta de novo.';
          errEl.style.display = 'block';
          btn.disabled = false;
          btn.textContent = btn.dataset.originalText || 'Comentar';
        });
    });
  }

  // ── Apagar comentário
  function deleteComment(e) {
    const btn = e.currentTarget;
    const commentId = parseInt(btn.dataset.commentId, 10);
    const csrf = btn.dataset.csrf;
    showConfirm('Apagar comentário?', () => {
      fetch('/api/comments', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: `comment_id=${commentId}&_csrf=${encodeURIComponent(csrf)}`,
      })
      .then(r => r.json())
      .then(d => {
        if (d.error) { showToast(d.error, 'error'); return; }
        document.getElementById('comment-' + commentId)?.remove();
      });
    });
  }

  document.querySelectorAll('.up-comment-delete').forEach(btn => btn.addEventListener('click', deleteComment));

  // ── Carregar mais comentários
  const loadMoreBtn = document.getElementById('load-more-btn');
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', function () {
      const page   = parseInt(this.dataset.page, 10);
      const userId = this.dataset.userId;
      this.disabled = true;
      this.textContent = 'A carregar...';

      fetch(`api/comments.php?user_id=${userId}&page=${page}`, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(d => {
          if (!d.comments) return;
          const list = document.getElementById('comments-list');
          d.comments.forEach(c => {
            const canDelete = IS_LOGGED && (parseInt(c.author_id) === MY_ID || IS_SELF);
            const el = document.createElement('div');
            el.className = 'up-comment';
            el.id = 'comment-' + c.id;
            el.innerHTML = `
              <div class="up-comment-header">
                <a href="u.php?u=${encodeURIComponent(c.author_username)}" class="up-comment-author">${esc(c.author_username)}</a>
                <span class="up-comment-date">${c.created_at.substring(0,10).split('-').reverse().join('/')}</span>
                ${canDelete ? `<button class="up-comment-delete" data-comment-id="${c.id}" data-csrf="${esc(CSRF_TOKEN)}" title="Apagar" aria-label="Apagar"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg></button>` : ''}
              </div>
              <p class="up-comment-body">${esc(c.content).replace(/\n/g,'<br>')}</p>
            `;
            if (canDelete) el.querySelector('.up-comment-delete')?.addEventListener('click', deleteComment);
            list.appendChild(el);
          });

          if (page < d.total_pages) {
            this.dataset.page = page + 1;
            const remaining = d.total - (page * 10);
            this.textContent = `Ver mais comentários (${Math.max(0, remaining)} restantes)`;
            this.disabled = false;
          } else {
            this.remove();
          }
        })
        .catch(() => { this.disabled = false; this.textContent = 'Tentar novamente'; });
    });
  }
})();
</script>

<?php include 'includes/footer.php'; ?>
