<?php
require_once 'includes/config.php';
requireLogin();

$user       = getCurrentUser();
$user_id    = (int) $user['id'];
$csrfToken  = generateCSRFToken();

/* ── Buscar saves do utilizador ── */
$saves = [];
$stmt  = $conn->prepare("SELECT * FROM saves WHERE user_id = ? ORDER BY slot ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $saves[$row['slot']] = $row;
}
$stmt->close();

/* ── Mapeamentos ── */
$act_icons = [
    'Ato I'   => '🌊',
    'Ato II'  => '🌋',
    'Ato III' => '🌪️',
    'Ato IV'  => '⚔️',
    'Ato V'   => '⚡',
];

function actIcon(string $chapter): string {
    global $act_icons;
    foreach ($act_icons as $prefix => $icon) {
        if (str_starts_with($chapter, $prefix)) return $icon;
    }
    return '📜';
}

include 'includes/header.php';
?>

<div class="game-page">

    <!-- Aviso mobile: jogo só disponível em PC -->
    <div class="game-mobile-notice">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
      <span>O jogo está disponível apenas em <strong>computador</strong>. Aqui podes gerir as tuas saves.</span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 style="padding-bottom : 10px">Jogar</h1>
            <p class="page-subtitle">
                Bem-vindo, <strong><?= htmlspecialchars($user['username']) ?></strong>.
                Continua a tua aventura ou começa uma nova.
            </p>
        </div>
    </div>

    <!-- Saves Section -->
    <section class="saves-section">

        <div class="section-header">
            <div class="section-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                </svg>
            </div>
            <h2>As tuas Saves</h2>
        </div>

        <div class="saves-grid" id="saves-grid">
        <?php for ($slot = 1; $slot <= 3; $slot++):
            $save = $saves[$slot] ?? null; ?>

            <?php if ($save):
                $hp_pct  = $save['hp_total'] > 0 ? round(($save['hp'] / $save['hp_total']) * 100) : 100;
                $xp_pct  = $save['xp_req']   > 0 ? round(($save['xp'] / $save['xp_req'])   * 100) : 0;
                $icon    = actIcon($save['chapter']);
                $date    = date('d/m/Y \à\s H:i', strtotime($save['last_saved']));
            ?>
            <!-- SAVE COM DADOS -->
            <div class="save-card save-card-active" data-slot="<?= $slot ?>">

                <div class="save-slot-badge">
                    <span>Save</span>
                    <span class="save-slot-num"><?= $slot ?></span>
                </div>

                <div class="save-chapter-tag">
                    <span><?= $icon ?></span>
                    <?= htmlspecialchars($save['chapter']) ?>
                </div>

                <div class="save-stats">
                    <div class="save-stat">
                        <div class="save-stat-label">Nível</div>
                        <div class="save-stat-value"><?= $save['level'] ?><small>lvl</small></div>
                    </div>
                    <div class="save-stat">
                        <div class="save-stat-label">Dano</div>
                        <div class="save-stat-value"><?= round($save['damage'], 1) ?><small>dmg</small></div>
                    </div>
                </div>

                <div class="save-bar-wrap">
                    <div class="save-bar-label">
                        <span>HP</span>
                        <span><?= round($save['hp']) ?> / <?= round($save['hp_total']) ?></span>
                    </div>
                    <div class="save-bar">
                        <div class="save-bar-fill save-bar-fill-hp" style="width:<?= $hp_pct ?>%"></div>
                    </div>
                </div>

                <div class="save-bar-wrap">
                    <div class="save-bar-label">
                        <span>XP</span>
                        <span><?= round($save['xp']) ?> / <?= round($save['xp_req']) ?></span>
                    </div>
                    <div class="save-bar">
                        <div class="save-bar-fill save-bar-fill-xp" style="width:<?= $xp_pct ?>%"></div>
                    </div>
                </div>

                <div class="save-meta">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <?= $date ?>
                </div>

                <div class="save-actions">
                    <button class="btn btn-download" onclick="downloadSave(<?= $slot ?>, this)">
                        <svg class="btn-download-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Descarregar
                    </button>
                    <label class="btn btn-secondary btn-sm" style="cursor:pointer;" title="Substituir save">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        Substituir
                        <input type="file" accept=".sav" style="display:none" onchange="uploadSave(this, <?= $slot ?>)">
                    </label>
                    <button class="btn btn-danger btn-sm" onclick="deleteSave(<?= $slot ?>, this)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                            <path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
                        </svg>
                        Apagar
                    </button>
                </div>

            </div>

            <?php else: ?>
            <!-- SLOT VAZIO -->
            <div class="save-card save-card-empty" id="slot-card-<?= $slot ?>">
                <div class="save-slot-badge" style="position:absolute;top:20px;left:24px;right:24px;">
                    <span>Save</span>
                    <span class="save-slot-num"><?= $slot ?></span>
                </div>
                <div class="save-empty-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                </div>
                <p class="save-empty-label">Slot vazio</p>
                <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Carregar ficheiro .sav
                    <input type="file" accept=".sav" style="display:none" onchange="uploadSave(this, <?= $slot ?>)">
                </label>
                <p class="save-drag-hint">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Ou arrasta o .sav aqui
                </p>
            </div>
            <?php endif; ?>

        <?php endfor; ?>
        </div><!-- /saves-grid -->

        <div class="saves-notice">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Carrega o ficheiro <strong>syloradata.sav</strong> para sincronizar com a cloud.
            Para continuar, descarrega e coloca em
            <code style="background:rgba(201,153,58,0.10);padding:1px 6px;border-radius:5px;font-size:12px;">%LocalAppData%\&lt;NomeDoProjeto&gt;\</code>
        </div>

    </section><!-- /saves-section -->

</div><!-- /game-page -->

<script>
const SAVE_CSRF = <?= json_encode($csrfToken) ?>;

async function uploadSave(input, slot) {
    const file = input.files[0];
    if (!file) return;

    const label = input.closest('label');
    if (label) { label.classList.add('btn-loading'); label.style.pointerEvents = 'none'; }

    const form = new FormData();
    form.append('savefile', file);
    form.append('slot', slot);
    form.append('_csrf', SAVE_CSRF);

    try {
        const res  = await fetch('/api/save_upload', { method: 'POST', body: form });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(data.error || 'Erro ao guardar.', 'error');
        }
    } catch (e) {
        showToast('Erro de ligação.', 'error');
    } finally {
        if (label) { label.classList.remove('btn-loading'); label.style.pointerEvents = ''; }
        input.value = '';
    }
}

function downloadSave(slot, btn) {
    btn.classList.add('btn-loading');
    setTimeout(() => btn.classList.remove('btn-loading'), 1200);
    window.location.href = 'api/save_download.php?slot=' + slot;
}

async function deleteSave(slot, btn) {
    showConfirm('Apagar Save ' + slot + '? Esta ação é irreversível.', async () => {
        btn.classList.add('btn-loading');
        const form = new FormData();
        form.append('slot', slot);
        form.append('_csrf', SAVE_CSRF);
        try {
            const res  = await fetch('/api/save_delete', { method: 'POST', body: form });
            const data = await res.json();
            if (data.success) {
                showToast('Save ' + slot + ' apagada.', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.error || 'Erro ao apagar.', 'error');
            }
        } catch (e) {
            showToast('Erro de ligação.', 'error');
        } finally {
            btn.classList.remove('btn-loading');
        }
    });
}

/* ══════════════════════════════════════════════
   DRAG & DROP em slots vazios
══════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.save-card-empty').forEach(card => {
        const slot = card.closest('[data-slot]')?.dataset?.slot
            || card.id?.replace('slot-card-', '');

        card.addEventListener('dragover', e => {
            e.preventDefault();
            card.classList.add('drag-over');
        });
        card.addEventListener('dragleave', e => {
            if (!card.contains(e.relatedTarget)) card.classList.remove('drag-over');
        });
        card.addEventListener('drop', e => {
            e.preventDefault();
            card.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (!file) return;
            if (!file.name.endsWith('.sav')) {
                showToast('Apenas ficheiros .sav são aceites.', 'error');
                return;
            }
            const fakeInput = { files: [file], closest: () => null, value: '' };
            uploadSave(fakeInput, parseInt(slot, 10));
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
