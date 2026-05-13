<?php
require_once 'includes/config.php';

$csrfToken = generateCSRFToken();

// Cálculo dinâmico de meses desde 2025-09-01
$startDate    = new DateTime('2025-09-01');
$today        = new DateTime();
$diff         = $startDate->diff($today);
$monthsActive = ($diff->y * 12) + $diff->m + ($diff->d >= 15 ? 1 : 0);
if ($monthsActive < 1) $monthsActive = 1;

include 'includes/header.php';
?>

<div class="about-page">

  <!-- HERO -->
  <div class="about-hero">
    <div class="about-hero-runes" aria-hidden="true">⊕ ✦ ◈ ⟡ ✦</div>
    <p class="about-overline">Sylora · Bastidores</p>
    <h1>Quem está por trás do mundo</h1>
    <p class="about-lead">Dois alunos da Escola Secundária de Mem Martins que decidiram criar um jogo completo como Prova de Aptidão Profissional, com tudo o que isso implica: código, história, arte e infraestrutura web.</p>

    <div class="about-hero-tags">
      <span class="about-hero-tag"><strong><?= $monthsActive ?></strong> meses ativos</span>
      <span class="about-hero-tag"><strong>2</strong> developers</span>
      <span class="about-hero-tag"><strong>1</strong> mundo grego</span>
      <span class="about-hero-tag"><strong>PAP 2025/2026</strong></span>
    </div>
  </div>

  <!-- STATS DO PROJETO -->
  <section class="about-section">
    <div class="about-section-header">
      <span class="about-badge">Projeto</span>
      <h2>O projeto até hoje</h2>
    </div>

    <div class="about-stats-grid">
      <div class="about-stat-card">
        <div class="about-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="about-stat-value"><?= $monthsActive ?></div>
        <div class="about-stat-label">Meses de Desenvolvimento</div>
        <div class="about-stat-sub">Desde Setembro 2025</div>
      </div>

      <div class="about-stat-card">
        <div class="about-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
        </div>
        <div class="about-stat-value">0</div>
        <div class="about-stat-label">Atos Jogáveis</div>
        <div class="about-stat-sub">Jogo ainda em desenvolvimento</div>
      </div>

      <div class="about-stat-card">
        <div class="about-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        </div>
        <div class="about-stat-value">~16k</div>
        <div class="about-stat-label">Linhas de Código</div>
        <div class="about-stat-sub">Site + jogo combinados</div>
      </div>

      <div class="about-stat-card">
        <div class="about-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <div class="about-stat-value">12+</div>
        <div class="about-stat-label">Sistemas Implementados</div>
        <div class="about-stat-sub">Jogo e site</div>
      </div>
    </div>
  </section>

  <!-- EQUIPA -->
  <section class="about-section">
    <div class="about-section-header">
      <span class="about-badge">Equipa</span>
      <h2>Quem somos</h2>
    </div>

    <div class="about-team-grid">

      <!-- Márcio -->
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
              Game Developer
            </span>
          </div>
        </header>

        <ul class="about-member-tasks">
          <li>Programação das mecânicas de jogo em GameMaker (GML)</li>
          <li>Design de níveis e ilhas</li>
          <li>Criação de sprites e animações</li>
          <li>Implementação de sistemas de combate e inimigos</li>
        </ul>

        <div class="about-member-stack">
          <span class="about-stack-pill">GameMaker</span>
          <span class="about-stack-pill">GML</span>
          <span class="about-stack-pill">Aseprite</span>
          <span class="about-stack-pill">Game Design</span>
        </div>
      </article>

      <!-- Samuel -->
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
              Full-Stack &amp; UX
            </span>
          </div>
        </header>

        <ul class="about-member-tasks">
          <li>Desenvolvimento do site completo (PHP, MySQL, HTML/CSS/JS)</li>
          <li>Sistema de autenticação, perfis e avatares</li>
          <li>Design de interface e experiência de utilizador</li>
          <li>Narrativa e world-building do universo Sylora</li>
        </ul>

        <div class="about-member-stack">
          <span class="about-stack-pill">PHP</span>
          <span class="about-stack-pill">MySQL</span>
          <span class="about-stack-pill">JavaScript</span>
          <span class="about-stack-pill">CSS</span>
          <span class="about-stack-pill">UX</span>
        </div>
      </article>

    </div>
  </section>

  <!-- TIMELINE -->
  <section class="about-section">
    <div class="about-section-header">
      <span class="about-badge">Desenvolvimento</span>
      <h2>A nossa jornada</h2>
    </div>

    <ol class="about-timeline" id="about-timeline">
      <span class="about-timeline-rail" aria-hidden="true"></span>
      <span class="about-timeline-progress" id="about-timeline-progress" aria-hidden="true"></span>

      <li class="about-timeline-item">
        <div class="about-timeline-marker"></div>
        <div class="about-timeline-body">
          <div class="about-timeline-meta">Setembro 2025</div>
          <h3>Ideia inicial e arranque</h3>
          <p>Decidimos desenvolver um jogo como Prova de Aptidão Profissional. A ambição era clara: um RPG narrativo ambientado na Grécia Antiga, com história, combate e exploração próprios.</p>
        </div>
      </li>

      <li class="about-timeline-item">
        <div class="about-timeline-marker"></div>
        <div class="about-timeline-body">
          <div class="about-timeline-meta">Outono 2025</div>
          <h3>Escolha do GameMaker</h3>
          <p>O GameMaker foi escolhido pela sua curva de aprendizagem acessível para jogos 2D. Rapidamente descobrimos que os seus recursos têm limites que nos obrigaram a repensar vários objetivos iniciais.</p>
        </div>
      </li>

      <li class="about-timeline-item">
        <div class="about-timeline-marker"></div>
        <div class="about-timeline-body">
          <div class="about-timeline-meta">Inverno 2025/2026</div>
          <h3>Site de suporte</h3>
          <p>Em paralelo ao jogo, foi desenvolvido um site completo: sistema de contas, perfis, avatares com crop, página de história, área de jogo e saves na cloud. O volume de trabalho multiplicou, e a aprendizagem em desenvolvimento web full-stack também.</p>
        </div>
      </li>

      <li class="about-timeline-item">
        <div class="about-timeline-marker"></div>
        <div class="about-timeline-body">
          <div class="about-timeline-meta">Primavera 2026</div>
          <h3>Iteração e polish</h3>
          <p>Funcionalidades que pareciam simples transformaram-se em pesadelos técnicos. Sistemas que imaginávamos implementar em dias levaram semanas. Aprendemos a adaptar, a simplificar sem perder a essência, e a encontrar soluções criativas dentro das limitações da engine.</p>
        </div>
      </li>

      <li class="about-timeline-item about-timeline-item-current">
        <div class="about-timeline-marker"></div>
        <div class="about-timeline-body">
          <div class="about-timeline-meta">Hoje</div>
          <h3>A caminho da entrega</h3>
          <p>O projeto Sylora representa mais do que uma PAP. É a prova de que dois estudantes conseguem, com determinação e muitas horas de trabalho, construir algo do zero, desde a narrativa até ao código, desde o pixel art até à base de dados.</p>
        </div>
      </li>
    </ol>
  </section>

  <!-- FERRAMENTAS -->
  <section class="about-section">
    <div class="about-section-header">
      <span class="about-badge">Tecnologias</span>
      <h2>Ferramentas utilizadas</h2>
    </div>

    <div class="about-tools-grid">
      <div class="about-tool-item">
        <div class="about-tool-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><circle cx="15" cy="11" r="1"/><circle cx="18" cy="13" r="1"/></svg>
        </div>
        <div>
          <span class="about-tool-name">GameMaker Studio 2</span>
          <span class="about-tool-desc">Motor do jogo, lógica, física e renderização 2D</span>
        </div>
      </div>

      <div class="about-tool-item">
        <div class="about-tool-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        </div>
        <div>
          <span class="about-tool-name">GML</span>
          <span class="about-tool-desc">Linguagem de programação nativa do GameMaker</span>
        </div>
      </div>

      <div class="about-tool-item">
        <div class="about-tool-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
        </div>
        <div>
          <span class="about-tool-name">PHP &amp; MySQL</span>
          <span class="about-tool-desc">Backend do site, autenticação, base de dados, perfis</span>
        </div>
      </div>

      <div class="about-tool-item">
        <div class="about-tool-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
        </div>
        <div>
          <span class="about-tool-name">HTML / CSS / JavaScript</span>
          <span class="about-tool-desc">Frontend do site, interface, animações, interatividade</span>
        </div>
      </div>

      <div class="about-tool-item">
        <div class="about-tool-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h3v3H7zM14 14h3v3h-3zM7 14h3v3H7zM14 7h3v3h-3z"/></svg>
        </div>
        <div>
          <span class="about-tool-name">Aseprite</span>
          <span class="about-tool-desc">Criação de sprites e animações em pixel art</span>
        </div>
      </div>

      <div class="about-tool-item">
        <div class="about-tool-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        </div>
        <div>
          <span class="about-tool-name">Git &amp; GitHub</span>
          <span class="about-tool-desc">Controlo de versões e colaboração</span>
        </div>
      </div>
    </div>
  </section>

  <!-- REFLEXÃO -->
  <section class="about-section">
    <div class="about-section-header">
      <span class="about-badge">Reflexão</span>
      <h2>O que aprendemos</h2>
    </div>
    <div class="about-text-block">
      <p>Este projeto ensinou-nos que o desenvolvimento de software raramente corre como planeado, e que isso não é um fracasso, é parte do processo. Cada obstáculo técnico tornou-se uma oportunidade de aprender algo que os livros não ensinam.</p>
      <p>Aprendemos a trabalhar em equipa sob pressão, a dividir responsabilidades, a comunicar problemas e a tomar decisões difíceis sobre o que era viável dentro do tempo disponível. Acima de tudo, ficámos com a certeza de que escolhemos a área certa.</p>
    </div>
  </section>

  <!-- CONTACTO -->
  <section class="about-section" id="contacto">
    <div class="about-section-header">
      <span class="about-badge">Contacto</span>
      <h2>Fala connosco</h2>
    </div>

    <p class="about-contact-intro">Tens uma sugestão, encontraste um bug ou queres dizer olá? Envia uma mensagem e respondemos por email.</p>

    <form class="about-contact-form" id="contact-form" novalidate>
      <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
      <input type="text" name="website" tabindex="-1" autocomplete="off" class="about-contact-honeypot" aria-hidden="true">

      <div class="about-contact-row">
        <div class="form-group">
          <label for="contact-name">Nome</label>
          <input type="text" id="contact-name" name="name" placeholder="Como te chamas?" required minlength="2" maxlength="80" autocomplete="name">
        </div>
        <div class="form-group">
          <label for="contact-email">Email</label>
          <input type="email" id="contact-email" name="email" placeholder="o-teu@email.com" required autocomplete="email">
        </div>
      </div>

      <div class="form-group">
        <label for="contact-subject">Assunto</label>
        <input type="text" id="contact-subject" name="subject" placeholder="Sobre o que queres falar?" required minlength="4" maxlength="120">
      </div>

      <div class="form-group">
        <label for="contact-message">Mensagem</label>
        <textarea id="contact-message" name="message" rows="6" placeholder="Conta-nos." required minlength="20" maxlength="2000"></textarea>
        <div class="about-contact-counter"><span id="contact-msg-count">0</span> / 2000</div>
      </div>

      <button type="submit" class="btn btn-primary about-contact-submit" id="contact-submit-btn">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Enviar mensagem
      </button>
    </form>
  </section>

  <!-- CREDENCIAL PAP -->
  <div class="about-credential">
    <div class="about-credential-seal" aria-hidden="true">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><polyline points="8.21 13.89 7 22 12 19 17 22 15.79 13.88"/></svg>
    </div>
    <div class="about-credential-body">
      <div class="about-credential-label">Escola Secundária de Mem Martins · PAP 2025/2026</div>
      <p>Sylora: Ecos dos Deuses foi desenvolvido como Prova de Aptidão Profissional no Curso Profissional de Gestão e Programação de Sistemas Informáticos. O jogo, o site e a narrativa foram trabalhados pelos elementos da equipa ao longo do ano letivo.</p>
    </div>
  </div>

</div>

<script>
(function () {
  // ── Timeline animada com scroll ──
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

  // ── Formulário de contacto ──
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
        showToast(data.message || 'Mensagem enviada com sucesso.', 'success');
        form.reset();
        if (countEl) countEl.textContent = '0';
      } else {
        showToast(data.error || 'Erro ao enviar a mensagem.', 'error');
      }
    } catch (err) {
      showToast('Erro de ligação. Tenta novamente.', 'error');
    } finally {
      submit.classList.remove('btn-loading');
      submit.disabled = false;
    }
  });
})();
</script>

<?php include 'includes/footer.php'; ?>
