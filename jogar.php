<?php
require_once 'includes/config.php';
requireLogin();

$user       = getCurrentUser();
$user_id    = (int) $user['id'];
$csrfToken  = generateCSRFToken();

$saves = [];
$stmt  = $conn->prepare("SELECT * FROM saves WHERE user_id = ? ORDER BY slot ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $saves[$row['slot']] = $row;
}
$stmt->close();

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

    <div class="game-mobile-notice">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
      <span data-i18n-html="jogar.mobile_notice"><?= t('jogar.mobile_notice') ?></span>
    </div>

    <div class="page-header">
        <div>
            <h1 style="padding-bottom : 10px" data-i18n="jogar.title"><?= t('jogar.title') ?></h1>
            <p class="page-subtitle">
                <?= t('jogar.welcome', ['name' => '<strong>' . htmlspecialchars($user['username']) . '</strong>']) ?>
                <span data-i18n="jogar.subtitle"><?= t('jogar.subtitle') ?></span>
            </p>
        </div>
        <button class="btn btn-cta jogar-download-btn" id="jogar-download-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <span data-i18n="jogar.download_game"><?= t('jogar.download_game') ?></span>
        </button>
    </div>

    <section class="saves-section">

        <div class="section-header">
            <div class="section-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                </svg>
            </div>
            <h2 data-i18n="jogar.saves_title"><?= t('jogar.saves_title') ?></h2>
        </div>

        <aside class="save-helper" id="save-helper" aria-label="Onde está o meu save">
            <button type="button" class="save-helper-header" id="save-helper-toggle" aria-expanded="true" aria-controls="save-helper-content">
                <span class="save-helper-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                </span>
                <span class="save-helper-title" data-i18n="jogar.helper_title"><?= t('jogar.helper_title') ?></span>
                <svg class="save-helper-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
            <div class="save-helper-content" id="save-helper-content">
                <div class="save-helper-content-inner">
                    <p class="save-helper-text" data-i18n="jogar.helper_text"><?= t('jogar.helper_text') ?></p>
                    <div class="save-helper-path-row">
                        <code class="save-helper-path" id="save-helper-path">%LocalAppData%\Sylora</code>
                        <button type="button" class="btn btn-secondary btn-sm save-helper-copy" id="save-helper-copy" title="<?= t('jogar.copy') ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="9" y="9" width="13" height="13" rx="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                            <span data-i18n="jogar.copy"><?= t('jogar.copy') ?></span>
                        </button>
                    </div>
                    <ol class="save-helper-steps">
                        <li data-i18n-html="jogar.helper_step1"><?= t('jogar.helper_step1') ?></li>
                        <li data-i18n-html="jogar.helper_step2"><?= t('jogar.helper_step2') ?></li>
                        <li data-i18n-html="jogar.helper_step3"><?= t('jogar.helper_step3') ?></li>
                    </ol>
                </div>
            </div>
        </aside>

        <div class="saves-grid" id="saves-grid">
        <?php for ($slot = 1; $slot <= 3; $slot++):
            $save = $saves[$slot] ?? null; ?>

            <?php if ($save):
                $hp_pct  = $save['hp_total'] > 0 ? round(($save['hp'] / $save['hp_total']) * 100) : 100;
                $xp_pct  = $save['xp_req']   > 0 ? round(($save['xp'] / $save['xp_req'])   * 100) : 0;
                $icon    = actIcon($save['chapter']);
                $date    = date('d/m/Y \à\s H:i', strtotime($save['last_saved']));
            ?>

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
                        <div class="save-stat-label" data-i18n="jogar.level"><?= t('jogar.level') ?></div>
                        <div class="save-stat-value"><?= $save['level'] ?><small>lvl</small></div>
                    </div>
                    <div class="save-stat">
                        <div class="save-stat-label" data-i18n="jogar.damage"><?= t('jogar.damage') ?></div>
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
                    <button class="btn btn-primary btn-sm save-action-btn" onclick="downloadSave(<?= $slot ?>, this)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        <span data-i18n="jogar.download"><?= t('jogar.download') ?></span>
                    </button>
                    <label class="btn btn-secondary btn-sm save-action-btn">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <span data-i18n="jogar.replace"><?= t('jogar.replace') ?></span>
                        <input type="file" accept=".sav" style="display:none" onchange="uploadSave(this, <?= $slot ?>)">
                    </label>
                    <button class="btn btn-danger btn-sm save-action-btn" onclick="deleteSave(<?= $slot ?>, this)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                            <path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
                        </svg>
                        <span data-i18n="jogar.delete"><?= t('jogar.delete') ?></span>
                    </button>
                </div>

            </div>

            <?php else: ?>

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
                <p class="save-empty-label" data-i18n="jogar.slot_empty"><?= t('jogar.slot_empty') ?></p>
                <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <span data-i18n="jogar.upload_file"><?= t('jogar.upload_file') ?></span>
                    <input type="file" accept=".sav" style="display:none" onchange="uploadSave(this, <?= $slot ?>)">
                </label>
                <p class="save-drag-hint">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span data-i18n="jogar.drag_hint"><?= t('jogar.drag_hint') ?></span>
                </p>
            </div>
            <?php endif; ?>

        <?php endfor; ?>
        </div>

    </section>

    <div class="save-preview-overlay" id="save-preview-overlay" role="dialog" aria-modal="true" aria-labelledby="save-preview-title" aria-hidden="true">
        <div class="save-preview-box">
            <div class="save-preview-header">
                <h2 id="save-preview-title" data-i18n="jogar.preview_title"><?= t('jogar.preview_title') ?></h2>
                <button class="save-preview-close" id="save-preview-close" type="button" aria-label="Fechar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <p class="save-preview-subtitle">A guardar para o <strong>Slot <span id="save-preview-slot-num">?</span></strong></p>

            <div class="save-preview-warning" id="save-preview-warning" role="alert" style="display:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span>Este slot já tem uma save (<span id="save-preview-warning-detail"></span>). Será substituída.</span>
            </div>

            <div class="save-preview-body" id="save-preview-body"></div>

            <div class="save-preview-actions">
                <button class="btn btn-secondary btn-sm" type="button" id="save-preview-cancel" data-i18n="jogar.preview_cancel"><?= t('jogar.preview_cancel') ?></button>
                <button class="btn btn-primary btn-sm" type="button" id="save-preview-confirm" data-i18n="jogar.preview_confirm"><?= t('jogar.preview_confirm') ?></button>
            </div>
        </div>
    </div>

</div>

<script>

window.SAVE_CSRF = <?= json_encode($csrfToken) ?>;
document.addEventListener('sylora:langchange', function(e) {
  var d = e.detail.dict;
  window.JOGAR_LANG = {
    confirm_delete: d['jogar.confirm_delete'] || window.JOGAR_LANG.confirm_delete,
    preview_slot:   d['jogar.preview_slot']   || window.JOGAR_LANG.preview_slot,
    preview_warn:   d['jogar.preview_warn']   || window.JOGAR_LANG.preview_warn,
  };
});
window.JOGAR_LANG = <?= json_encode([
    'confirm_delete' => t('jogar.confirm_delete'),
    'preview_slot'   => t('jogar.preview_slot'),
    'preview_warn'   => t('jogar.preview_warn'),
]) ?>;
window.SAVES_DATA = <?= json_encode(array_values(array_map(function ($s) {
    return [
        'slot'    => (int) $s['slot'],
        'level'   => (int) $s['level'],
        'chapter' => (string) $s['chapter'],
    ];
}, $saves)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

(function () {
    'use strict';

    const MAX_SIZE = 2 * 1024 * 1024;

    const CHAPTER_MAP = {
        'Thalassos':      'Ato I: Ilha de Thalassos',
        'Thalassos_Cave': 'Ato I: Gruta de Thalassos',
        'Thalassos_Boss': 'Ato I: Templo de Pelágion',
        'Helion':         'Ato II: As Cinzas de Helion',
        'Zephyria':       'Ato III: O Véu dos Ventos',
    };

    function actIconClient(chapter) {
        if (chapter.startsWith('Ato I:'))   return '🌊';
        if (chapter.startsWith('Ato II:'))  return '🌋';
        if (chapter.startsWith('Ato III:')) return '🌪️';
        if (chapter.startsWith('Ato IV:'))  return '⚔️';
        if (chapter.startsWith('Ato V:'))   return '⚡';
        return '📜';
    }

    function parseSafeSave(data) {
        if (!data || typeof data !== 'object') return null;
        const s = data.stats;
        if (!s || typeof s !== 'object') return null;

        const rmRaw   = String(s.save_rm || 'Thalassos').replace(/[^a-zA-Z0-9_]/g, '').slice(0, 32);
        const chapter = CHAPTER_MAP[rmRaw] || 'Ato I: Ilha de Thalassos';

        return {
            level:      Math.max(1, Math.floor(Number(s.lvl)      || 1)),
            hp:         Math.max(0, Number(s.hp)       || 0),
            hpTotal:    Math.max(1, Number(s.hp_total) || 100),
            xp:         Math.max(0, Number(s.xp)       || 0),
            xpReq:      Math.max(1, Number(s.xp_req)   || 100),
            damage:     Math.max(0, Number(s.damage)   || 0),
            chapter:    chapter,
            playerName: String(data.player_name || '').slice(0, 32),
        };
    }

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function buildStatBox(label, value, unit) {
        const box = el('div', 'save-stat');
        box.appendChild(el('div', 'save-stat-label', label));
        const v = el('div', 'save-stat-value', value);
        v.appendChild(el('small', '', unit));
        box.appendChild(v);
        return box;
    }

    function buildBar(label, cur, total, pct, fillClass) {
        const wrap = el('div', 'save-bar-wrap');
        const row  = el('div', 'save-bar-label');
        row.appendChild(el('span', '', label));
        row.appendChild(el('span', '', cur + ' / ' + total));
        wrap.appendChild(row);
        const bar  = el('div', 'save-bar');
        const fill = el('div', 'save-bar-fill ' + fillClass);
        fill.style.width = pct + '%';
        bar.appendChild(fill);
        wrap.appendChild(bar);
        return wrap;
    }

    function buildPreviewCard(parsed) {
        const hpPct = Math.min(100, Math.round((parsed.hp / parsed.hpTotal) * 100));
        const xpPct = Math.min(100, Math.round((parsed.xp / parsed.xpReq) * 100));

        const card = el('div', 'save-card save-card-active save-preview-card');

        const badge = el('div', 'save-slot-badge');
        badge.appendChild(el('span', '', 'Save'));
        badge.appendChild(el('span', 'save-slot-num', 'Novo'));
        card.appendChild(badge);

        const tag = el('div', 'save-chapter-tag');
        tag.appendChild(el('span', '', actIconClient(parsed.chapter)));
        tag.appendChild(el('span', '', parsed.chapter));
        card.appendChild(tag);

        const stats = el('div', 'save-stats');
        stats.appendChild(buildStatBox('Nível', String(parsed.level), 'lvl'));
        stats.appendChild(buildStatBox('Dano', String(Math.round(parsed.damage * 10) / 10), 'dmg'));
        card.appendChild(stats);

        card.appendChild(buildBar('HP', Math.round(parsed.hp), Math.round(parsed.hpTotal), hpPct, 'save-bar-fill-hp'));
        card.appendChild(buildBar('XP', Math.round(parsed.xp), Math.round(parsed.xpReq), xpPct, 'save-bar-fill-xp'));

        if (parsed.playerName) {
            const meta = el('div', 'save-meta');
            const svgNS = 'http://www.w3.org/2000/svg';
            const svg = document.createElementNS(svgNS, 'svg');
            svg.setAttribute('width', '13');
            svg.setAttribute('height', '13');
            svg.setAttribute('viewBox', '0 0 24 24');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
            svg.setAttribute('stroke-width', '2');
            const p = document.createElementNS(svgNS, 'path');
            p.setAttribute('d', 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2');
            const c = document.createElementNS(svgNS, 'circle');
            c.setAttribute('cx', '12'); c.setAttribute('cy', '7'); c.setAttribute('r', '4');
            svg.appendChild(p);
            svg.appendChild(c);
            meta.appendChild(svg);
            meta.appendChild(el('span', '', parsed.playerName));
            card.appendChild(meta);
        }

        return card;
    }

    let pendingFile = null;
    let pendingSlot = null;

    async function openPreview(file, slot) {
        if (!file) return;
        if (!file.name.toLowerCase().endsWith('.sav')) {
            showToast('Apenas ficheiros .sav são aceites.', 'error');
            return;
        }
        if (file.size > MAX_SIZE) {
            showToast('Ficheiro demasiado grande (máx. 2 MB).', 'error');
            return;
        }

        let raw;
        try { raw = await file.text(); }
        catch (e) { showToast('Não foi possível ler o ficheiro.', 'error'); return; }

        const clean = raw.replace(/\x00/g, '').trim();
        let data;
        try { data = JSON.parse(clean); }
        catch (e) {
            showToast('Ficheiro corrompido ou não é um save da Sylora.', 'error');
            return;
        }

        const parsed = parseSafeSave(data);
        if (!parsed) {
            showToast('Ficheiro corrompido ou não é um save da Sylora.', 'error');
            return;
        }

        pendingFile = file;
        pendingSlot = slot;
        showPreviewModal(parsed, slot);
    }

    function showPreviewModal(parsed, slot) {
        const overlay     = document.getElementById('save-preview-overlay');
        const body        = document.getElementById('save-preview-body');
        const slotNum     = document.getElementById('save-preview-slot-num');
        const confirmBtn  = document.getElementById('save-preview-confirm');
        const warning     = document.getElementById('save-preview-warning');
        const warnDetail  = document.getElementById('save-preview-warning-detail');
        if (!overlay) return;

        slotNum.textContent = String(slot);
        body.innerHTML = '';
        body.appendChild(buildPreviewCard(parsed));

        const arr = Array.isArray(window.SAVES_DATA) ? window.SAVES_DATA : [];
        const current = arr.find(s => Number(s.slot) === Number(slot));
        if (current) {
            warnDetail.textContent = 'Nível ' + current.level + ' · ' + current.chapter;
            warning.style.display = 'flex';
            confirmBtn.textContent = 'Substituir save';
        } else {
            warning.style.display = 'none';
            confirmBtn.textContent = 'Confirmar upload';
        }
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('btn-loading');

        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        confirmBtn.focus();
    }

    function closePreview() {
        const overlay = document.getElementById('save-preview-overlay');
        if (!overlay) return;
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        pendingFile = null;
        pendingSlot = null;
    }

    async function doUpload() {
        if (!pendingFile || pendingSlot == null) {
            closePreview();
            return;
        }
        const confirmBtn = document.getElementById('save-preview-confirm');
        confirmBtn.classList.add('btn-loading');
        confirmBtn.disabled = true;

        const form = new FormData();
        form.append('savefile', pendingFile);
        form.append('slot', String(pendingSlot));
        form.append('_csrf', window.SAVE_CSRF);

        try {
            const res  = await fetch('/api/save_upload', { method: 'POST', body: form, credentials: 'same-origin' });
            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Save guardado!', 'success');
                closePreview();
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(data.error || 'Erro ao guardar.', 'error');
                confirmBtn.classList.remove('btn-loading');
                confirmBtn.disabled = false;
            }
        } catch (e) {
            showToast('Erro de ligação.', 'error');
            confirmBtn.classList.remove('btn-loading');
            confirmBtn.disabled = false;
        }
    }

    window.uploadSave = function (input, slot) {
        const file = input && input.files && input.files[0];
        if (file) openPreview(file, parseInt(slot, 10));
        if (input && 'value' in input) input.value = '';
    };

    window.downloadSave = function (slot, btn) {
        btn.classList.add('btn-loading');
        setTimeout(() => btn.classList.remove('btn-loading'), 1200);
        const a = document.createElement('a');
        a.href = '/api/save_download?slot=' + encodeURIComponent(slot);
        a.download = 'syloradata.sav';
        document.body.appendChild(a);
        a.click();
        a.remove();
    };

    window.deleteSave = function (slot, btn) {
        var msg = window.JOGAR_LANG.confirm_delete.replace('{slot}', slot);
        showConfirm(msg, async () => {
            btn.classList.add('btn-loading');
            const form = new FormData();
            form.append('slot', String(slot));
            form.append('_csrf', window.SAVE_CSRF);
            try {
                const res  = await fetch('/api/save_delete', { method: 'POST', body: form, credentials: 'same-origin' });
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
    };

    document.getElementById('jogar-download-btn')?.addEventListener('click', () => {
        const a = document.createElement('a');
        a.href = '/assets/download/Sylora%20Demo.exe';
        a.download = 'Sylora Demo.exe';
        document.body.appendChild(a);
        a.click();
        a.remove();
        if (typeof showToast === 'function') showToast('Download iniciado!', 'success');
    });

    const helper = document.getElementById('save-helper');
    const helperToggle = document.getElementById('save-helper-toggle');
    if (helper && helperToggle) {
        const STORAGE_KEY = 'sylora-helper-collapsed';
        const startCollapsed = localStorage.getItem(STORAGE_KEY) === '1';
        if (startCollapsed) {
            helper.classList.add('collapsed');
            helperToggle.setAttribute('aria-expanded', 'false');
        }
        helperToggle.addEventListener('click', () => {
            const collapsed = helper.classList.toggle('collapsed');
            helperToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            try { localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0'); } catch (e) {}
        });
    }

    const copyBtn = document.getElementById('save-helper-copy');
    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            const path = document.getElementById('save-helper-path')?.textContent || '';
            const label = copyBtn.querySelector('span');
            const original = label ? label.textContent : '';
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(path);
                } else {

                    const ta = document.createElement('textarea');
                    ta.value = path;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }
                copyBtn.classList.add('copied');
                if (label) label.textContent = 'Copiado!';
                setTimeout(() => {
                    copyBtn.classList.remove('copied');
                    if (label) label.textContent = original;
                }, 1800);
            } catch (e) {
                showToast('Não foi possível copiar. Seleciona o caminho manualmente.', 'info');
            }
        });
    }

    const overlay = document.getElementById('save-preview-overlay');
    if (overlay) {
        document.getElementById('save-preview-cancel')?.addEventListener('click', closePreview);
        document.getElementById('save-preview-close') ?.addEventListener('click', closePreview);
        document.getElementById('save-preview-confirm')?.addEventListener('click', doUpload);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closePreview();
        });

        if (window.__sylora_jogar_abort) window.__sylora_jogar_abort.abort();
        window.__sylora_jogar_abort = new AbortController();
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && overlay.classList.contains('active')) closePreview();
        }, { signal: window.__sylora_jogar_abort.signal });
    }

    function wireDrop(card) {
        const slot = card.dataset?.slot || card.id?.replace('slot-card-', '');
        if (!slot) return;
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
            if (file) openPreview(file, parseInt(slot, 10));
        });
    }
    document.querySelectorAll('.save-card-empty').forEach(wireDrop);
    document.querySelectorAll('.save-card-active').forEach(wireDrop);
})();
</script>

<?php include 'includes/footer.php'; ?>
