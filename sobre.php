<?php
require_once 'includes/config.php';

$csrfToken = generateCSRFToken();

$startDate    = new DateTime('2025-09-15');
$today        = new DateTime();
$diff         = $startDate->diff($today);
$monthsActive = ($diff->y * 12) + $diff->m + ($diff->d >= 15 ? 1 : 0);
if ($monthsActive < 1) $monthsActive = 1;

$pageTitle = t('nav.about') . ' — Sylora: Ecos dos Deuses';
include 'includes/header.php';
?>

<div class="about-page">

  <div class="about-hero">
    <div class="about-hero-runes" aria-hidden="true">⊕ ✦ ◈ ⟡ ✦</div>
    <p class="about-overline" data-i18n="sobre.overline"><?= t('sobre.overline') ?></p>
    <h1 data-i18n="sobre.title"><?= t('sobre.title') ?></h1>
    <p class="about-lead" data-i18n="sobre.lead"><?= t('sobre.lead') ?></p>

    <div class="about-hero-tags">
      <span class="about-hero-tag" data-i18n-html="sobre.tag_months" data-i18n-n="<?= $monthsActive ?>"><?= t('sobre.tag_months', ['n' => $monthsActive]) ?></span>
      <span class="about-hero-tag" data-i18n-html="sobre.tag_devs"><?= t('sobre.tag_devs') ?></span>
      <span class="about-hero-tag" data-i18n-html="sobre.tag_world"><?= t('sobre.tag_world') ?></span>
      <span class="about-hero-tag" data-i18n-html="sobre.tag_pap"><?= t('sobre.tag_pap') ?></span>
    </div>
  </div>

  <section class="about-section">
    <div class="about-section-header">
      <span class="about-badge" data-i18n="sobre.proj_badge"><?= t('sobre.proj_badge') ?></span>
      <h2 data-i18n="sobre.proj_title"><?= t('sobre.proj_title') ?></h2>
    </div>

    <div class="about-stats-grid">
      <div class="about-stat-card">
        <div class="about-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="about-stat-value"><?= $monthsActive ?></div>
        <div class="about-stat-label" data-i18n="sobre.stat_months"><?= t('sobre.stat_months') ?></div>
        <div class="about-stat-sub" data-i18n="sobre.stat_months_sub"><?= t('sobre.stat_months_sub') ?></div>
      </div>

      <div class="about-stat-card">
        <div class="about-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
        </div>
        <div class="about-stat-value">1</div>
        <div class="about-stat-label" data-i18n="sobre.stat_acts"><?= t('sobre.stat_acts') ?></div>
        <div class="about-stat-sub" data-i18n="sobre.stat_acts_sub"><?= t('sobre.stat_acts_sub') ?></div>
      </div>

      <div class="about-stat-card">
        <div class="about-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        </div>
        <div class="about-stat-value">~16k</div>
        <div class="about-stat-label" data-i18n="sobre.stat_code"><?= t('sobre.stat_code') ?></div>
        <div class="about-stat-sub" data-i18n="sobre.stat_code_sub"><?= t('sobre.stat_code_sub') ?></div>
      </div>

      <div class="about-stat-card">
        <div class="about-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <div class="about-stat-value">12+</div>
        <div class="about-stat-label" data-i18n="sobre.stat_systems"><?= t('sobre.stat_systems') ?></div>
        <div class="about-stat-sub" data-i18n="sobre.stat_systems_sub"><?= t('sobre.stat_systems_sub') ?></div>
      </div>
    </div>
  </section>

  <section class="about-section">
    <div class="about-section-header">
      <span class="about-badge" data-i18n="sobre.team_badge"><?= t('sobre.team_badge') ?></span>
      <h2 data-i18n="sobre.team_title"><?= t('sobre.team_title') ?></h2>
    </div>

    <div class="about-team-grid">

      <article class="about-member-card">
        <div class="about-member-stripe" aria-hidden="true"></div>
        <header class="about-member-head">
          <div class="about-member-avatar">
            <span>MS</span>
            <div class="about-member-avatar-glow" aria-hidden="true"></div>
          </div>
          <div class="about-member-id">
            <strong class="about-member-name">Márcio Sousa</strong>
            <span class="about-member-role">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              <span data-i18n-html="sobre.role_gd"><?= t('sobre.role_gd') ?></span>
            </span>
          </div>
        </header>

        <ul class="about-member-tasks">
          <li data-i18n="sobre.tasks_marcio_1"><?= t('sobre.tasks_marcio_1') ?></li>
          <li data-i18n="sobre.tasks_marcio_2"><?= t('sobre.tasks_marcio_2') ?></li>
          <li data-i18n="sobre.tasks_marcio_3"><?= t('sobre.tasks_marcio_3') ?></li>
          <li data-i18n="sobre.tasks_marcio_4"><?= t('sobre.tasks_marcio_4') ?></li>
        </ul>

        <div class="about-member-stack">
          <span class="about-stack-pill">GameMaker</span>
          <span class="about-stack-pill">GML</span>
          <span class="about-stack-pill">PHP</span>
          <span class="about-stack-pill">MySQL</span>
        </div>
      </article>

      <article class="about-member-card">
        <div class="about-member-stripe" aria-hidden="true"></div>
        <header class="about-member-head">
          <div class="about-member-avatar">
            <span>SM</span>
            <div class="about-member-avatar-glow" aria-hidden="true"></div>
          </div>
          <div class="about-member-id">
            <strong class="about-member-name">Samuel Meixieira</strong>
            <span class="about-member-role">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
              <span data-i18n-html="sobre.role_fs"><?= t('sobre.role_fs') ?></span>
            </span>
          </div>
        </header>

        <ul class="about-member-tasks">
          <li data-i18n="sobre.tasks_samuel_1"><?= t('sobre.tasks_samuel_1') ?></li>
          <li data-i18n="sobre.tasks_samuel_2"><?= t('sobre.tasks_samuel_2') ?></li>
          <li data-i18n="sobre.tasks_samuel_3"><?= t('sobre.tasks_samuel_3') ?></li>
          <li data-i18n="sobre.tasks_samuel_4"><?= t('sobre.tasks_samuel_4') ?></li>
        </ul>

        <div class="about-member-stack">
          <span class="about-stack-pill">JavaScript</span>
          <span class="about-stack-pill">CSS</span>
          <span class="about-stack-pill">Aseprite</span>
          <span class="about-stack-pill">Game Design</span>
        </div>
      </article>

    </div>
  </section>

  <section class="about-section">
    <div class="about-section-header">
      <span class="about-badge" data-i18n="sobre.dev_badge"><?= t('sobre.dev_badge') ?></span>
      <h2 data-i18n="sobre.dev_title"><?= t('sobre.dev_title') ?></h2>
    </div>

    <ol class="about-timeline" id="about-timeline">
      <span class="about-timeline-rail" aria-hidden="true"></span>
      <span class="about-timeline-progress" id="about-timeline-progress" aria-hidden="true"></span>

      <li class="about-timeline-item">
        <div class="about-timeline-marker"></div>
        <div class="about-timeline-body">
          <div class="about-timeline-meta" data-i18n="sobre.tl_sep"><?= t('sobre.tl_sep') ?></div>
          <h3 data-i18n="sobre.tl_sep_h"><?= t('sobre.tl_sep_h') ?></h3>
          <p data-i18n="sobre.tl_sep_p"><?= t('sobre.tl_sep_p') ?></p>
        </div>
      </li>

      <li class="about-timeline-item">
        <div class="about-timeline-marker"></div>
        <div class="about-timeline-body">
          <div class="about-timeline-meta" data-i18n="sobre.tl_oct"><?= t('sobre.tl_oct') ?></div>
          <h3 data-i18n="sobre.tl_oct_h"><?= t('sobre.tl_oct_h') ?></h3>
          <p data-i18n="sobre.tl_oct_p"><?= t('sobre.tl_oct_p') ?></p>
        </div>
      </li>

      <li class="about-timeline-item">
        <div class="about-timeline-marker"></div>
        <div class="about-timeline-body">
          <div class="about-timeline-meta" data-i18n="sobre.tl_dec"><?= t('sobre.tl_dec') ?></div>
          <h3 data-i18n="sobre.tl_dec_h"><?= t('sobre.tl_dec_h') ?></h3>
          <p data-i18n="sobre.tl_dec_p"><?= t('sobre.tl_dec_p') ?></p>
        </div>
      </li>

      <li class="about-timeline-item">
        <div class="about-timeline-marker"></div>
        <div class="about-timeline-body">
          <div class="about-timeline-meta" data-i18n="sobre.tl_mar"><?= t('sobre.tl_mar') ?></div>
          <h3 data-i18n="sobre.tl_mar_h"><?= t('sobre.tl_mar_h') ?></h3>
          <p data-i18n="sobre.tl_mar_p"><?= t('sobre.tl_mar_p') ?></p>
        </div>
      </li>

      <li class="about-timeline-item about-timeline-item-current">
        <div class="about-timeline-marker"></div>
        <div class="about-timeline-body">
          <div class="about-timeline-meta" data-i18n="sobre.tl_today"><?= t('sobre.tl_today') ?></div>
          <h3 data-i18n="sobre.tl_today_h"><?= t('sobre.tl_today_h') ?></h3>
          <p data-i18n="sobre.tl_today_p"><?= t('sobre.tl_today_p') ?></p>
        </div>
      </li>
    </ol>
  </section>

  <section class="about-section">
    <div class="about-section-header">
      <span class="about-badge" data-i18n="sobre.tech_badge"><?= t('sobre.tech_badge') ?></span>
      <h2 data-i18n="sobre.tech_title"><?= t('sobre.tech_title') ?></h2>
    </div>

    <div class="about-tools-grid">
      <?php
      $tools = [
          ['gm', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><circle cx="15" cy="11" r="1"/><circle cx="18" cy="13" r="1"/></svg>'],
          ['gml', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>'],
          ['php', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>'],
          ['web', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>'],
          ['aseprite', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h3v3H7zM14 14h3v3h-3zM7 14h3v3H7zM14 7h3v3h-3z"/></svg>'],
          ['git', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>'],
      ];
      foreach ($tools as [$slug, $svg]):
      ?>
      <div class="about-tool-item">
        <div class="about-tool-icon"><?= $svg ?></div>
        <div>
          <span class="about-tool-name" data-i18n-html="sobre.tool_<?= $slug ?>"><?= t('sobre.tool_' . $slug) ?></span>
          <span class="about-tool-desc" data-i18n="sobre.tool_<?= $slug ?>_d"><?= t('sobre.tool_' . $slug . '_d') ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="about-section">
    <div class="about-section-header">
      <span class="about-badge" data-i18n="sobre.ref_badge"><?= t('sobre.ref_badge') ?></span>
      <h2 data-i18n="sobre.ref_title"><?= t('sobre.ref_title') ?></h2>
    </div>
    <div class="about-text-block">
      <p data-i18n="sobre.ref_p1"><?= t('sobre.ref_p1') ?></p>
      <p data-i18n="sobre.ref_p2"><?= t('sobre.ref_p2') ?></p>
    </div>
  </section>

  <section class="about-section" id="contacto">
    <div class="about-section-header">
      <span class="about-badge" data-i18n="sobre.ct_badge"><?= t('sobre.ct_badge') ?></span>
      <h2 data-i18n="sobre.ct_title"><?= t('sobre.ct_title') ?></h2>
    </div>

    <p class="about-contact-intro" data-i18n="sobre.ct_intro"><?= t('sobre.ct_intro') ?></p>

    <form class="about-contact-form" id="contact-form" novalidate>
      <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
      <input type="text" name="website" tabindex="-1" autocomplete="off" class="about-contact-honeypot" aria-hidden="true">

      <div class="about-contact-row">
        <div class="form-group">
          <label for="contact-name" data-i18n="sobre.ct_name"><?= t('sobre.ct_name') ?></label>
          <input type="text" id="contact-name" name="name" data-i18n-placeholder="sobre.ct_name_ph" placeholder="<?= e(t('sobre.ct_name_ph')) ?>" required minlength="2" maxlength="80" autocomplete="name">
        </div>
        <div class="form-group">
          <label for="contact-email" data-i18n="sobre.ct_email"><?= t('sobre.ct_email') ?></label>
          <input type="email" id="contact-email" name="email" data-i18n-placeholder="sobre.ct_email_ph" placeholder="<?= e(t('sobre.ct_email_ph')) ?>" required autocomplete="email">
        </div>
      </div>

      <div class="form-group">
        <label for="contact-subject" data-i18n="sobre.ct_subject"><?= t('sobre.ct_subject') ?></label>
        <input type="text" id="contact-subject" name="subject" data-i18n-placeholder="sobre.ct_subject_ph" placeholder="<?= e(t('sobre.ct_subject_ph')) ?>" required minlength="4" maxlength="120">
      </div>

      <div class="form-group">
        <label for="contact-message" data-i18n="sobre.ct_message"><?= t('sobre.ct_message') ?></label>
        <textarea id="contact-message" name="message" rows="6" data-i18n-placeholder="sobre.ct_message_ph" placeholder="<?= e(t('sobre.ct_message_ph')) ?>" required minlength="20" maxlength="2000"></textarea>
        <div class="about-contact-counter"><span id="contact-msg-count">0</span> / 2000</div>
      </div>

      <button type="submit" class="btn btn-primary about-contact-submit" id="contact-submit-btn">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        <span data-i18n="sobre.ct_submit"><?= t('sobre.ct_submit') ?></span>
      </button>
    </form>
  </section>

  <div class="about-credential">
    <div class="about-credential-seal" aria-hidden="true">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><polyline points="8.21 13.89 7 22 12 19 17 22 15.79 13.88"/></svg>
    </div>
    <div class="about-credential-body">
      <div class="about-credential-label" data-i18n="sobre.credential_label"><?= t('sobre.credential_label') ?></div>
      <p data-i18n="sobre.credential_p"><?= t('sobre.credential_p') ?></p>
    </div>
  </div>

</div>

<script>
(function () {

  const timeline = document.getElementById('about-timeline');
  const progress = document.getElementById('about-timeline-progress');

  if (timeline && progress) {
    const items = timeline.querySelectorAll('.about-timeline-item');
    let rafPending = false;

    function updateTimeline() {
      rafPending = false;
      const rect       = timeline.getBoundingClientRect();
      const viewportH  = window.innerHeight || document.documentElement.clientHeight;
      const triggerY   = viewportH * 0.55;
      const railTop    = rect.top + 14;
      const railHeight = Math.max(0, rect.height - 28);

      let h = triggerY - railTop;
      if (h < 0) h = 0;
      if (h > railHeight) h = railHeight;
      progress.style.height = h + 'px';

      items.forEach(item => {
        const marker = item.querySelector('.about-timeline-marker');
        if (!marker) return;
        const mRect = marker.getBoundingClientRect();
        const mid   = mRect.top + (mRect.height / 2);
        item.classList.toggle('is-active', mid <= triggerY);
      });
    }

    function onScroll() {
      if (rafPending) return;
      rafPending = true;
      requestAnimationFrame(updateTimeline);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    updateTimeline();
  }

  const form    = document.getElementById('contact-form');
  const submit  = document.getElementById('contact-submit-btn');
  const msgEl   = document.getElementById('contact-message');
  const countEl = document.getElementById('contact-msg-count');

  if (!form) return;

  if (msgEl && countEl) {
    const updateCount = () => { countEl.textContent = msgEl.value.length; };
    msgEl.addEventListener('input', updateCount);
    updateCount();
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (submit.classList.contains('btn-loading')) return;

    submit.classList.add('btn-loading');
    submit.disabled = true;

    try {
      const fd  = new FormData(form);
      const res = await fetch('/api/contact', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        showToast(data.message || window.SYLORA_T('toast.contact_sent'), 'success');
        form.reset();
        if (countEl) countEl.textContent = '0';
      } else {
        showToast(data.error || window.SYLORA_T('toast.contact_error'), 'error');
      }
    } catch (err) {
      showToast(window.SYLORA_T('toast.connecting'), 'error');
    } finally {
      submit.classList.remove('btn-loading');
      submit.disabled = false;
    }
  });
})();
</script>

<?php include 'includes/footer.php'; ?>
