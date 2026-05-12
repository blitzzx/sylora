<?php
require_once 'includes/config.php';
include 'includes/header.php';
?>

<div class="report-page">

  <!-- HERO -->
  <div class="story-hero">
    <div class="story-hero-runes" aria-hidden="true">⊕ ✦ ◈ ⟡ ✦</div>
    <p class="story-overline">Sylora</p>
    <h1>Sobre Nós</h1>
    <p class="story-lead">Dois estudantes da Escola Secundária de Mem Martins que decidiram criar um jogo completo como Prova de Aptidão Profissional e "sobreviveram" para contar a história.</p>
  </div>

  <!-- EQUIPA -->
  <div class="story-section">
    <div class="story-section-header">
      <div class="story-badge">Equipa</div>
      <h2>Quem Somos</h2>
    </div>
    <div class="story-content">
      <p>Somos uma equipa de dois alunos da <strong>Escola Secundária de Mem Martins</strong>. O projeto Sylora nasceu como a nossa <strong>Prova de Aptidão Profissional</strong>, um jogo de aventura narrativa ambientado na Grécia Antiga, desenvolvido de raiz, com história própria, mecânicas originais e um site completo de suporte.</p>

      <div class="about-team-grid">

        <!-- Márcio -->
        <div class="about-member-card">
          <div class="about-member-avatar">MS</div>
          <div class="about-member-info">
            <strong class="about-member-name">Márcio Sousa</strong>
            <span class="about-member-role">Desenvolvimento do Jogo &amp; Game Design</span>
          </div>
          <ul class="story-list about-member-tasks">
            <li>Programação das mecânicas de jogo em GameMaker (GML)</li>
            <li>Design de níveis e ilhas</li>
            <li>Criação de sprites e animações</li>
            <li>Implementação de sistemas de combate e inimigos</li>
          </ul>
        </div>

        <!-- Samuel -->
        <div class="about-member-card">
          <div class="about-member-avatar">SM</div>
          <div class="about-member-info">
            <strong class="about-member-name">Samuel Meixieira</strong>
            <span class="about-member-role">Desenvolvimento Web &amp; Design de Interface</span>
          </div>
          <ul class="story-list about-member-tasks">
            <li>Desenvolvimento do site completo (PHP, MySQL, HTML/CSS/JS)</li>
            <li>Sistema de autenticação, perfis e avatares</li>
            <li>Design de interface e experiência de utilizador</li>
            <li>Narrativa e world-building do universo Sylora</li>
          </ul>
        </div>

      </div>
    </div>
  </div>

  <!-- HISTÓRIA DO DESENVOLVIMENTO -->
  <div class="story-section">
    <div class="story-section-header">
      <div class="story-badge">Desenvolvimento</div>
      <h2>A Nossa Jornada</h2>
    </div>
    <div class="story-content">
      <p>Quando escolhemos desenvolver um jogo como PAP, sabíamos que seria ambicioso. O que não prevíamos era o nível de desafio que nos esperava, em especial com o motor que escolhemos: o <strong>GameMaker</strong>.</p>

      <div class="story-callout">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>O GameMaker foi escolhido pela sua curva de aprendizagem mais acessível para jogos 2D, mas rapidamente descobrimos que os seus recursos têm limites que nos obrigaram a repensar vários objetivos iniciais.</span>
      </div>

      <p>Funcionalidades que pareciam simples transformaram-se em verdadeiros pesadelos técnicos. Sistemas que imaginávamos implementar em dias levaram semanas. Aprendemos a adaptar, a simplificar sem perder a essência, e a encontrar soluções criativas dentro das limitações da engine.</p>

      <p>Paralelamente ao jogo, foi desenvolvido um site de suporte completo, com sistema de contas, perfis de utilizador, página de história e área de jogo, o que multiplicou o volume de trabalho mas também a nossa aprendizagem em desenvolvimento web full-stack.</p>

      <p>No final, o projeto Sylora representa mais do que uma PAP. É a prova de que dois estudantes conseguem, com determinação e muitas horas de trabalho, construir algo do zero: desde a narrativa até ao código, desde o pixel art até à base de dados.</p>
    </div>
  </div>

  <!-- FERRAMENTAS -->
  <div class="story-section">
    <div class="story-section-header">
      <div class="story-badge">Tecnologias</div>
      <h2>Ferramentas Utilizadas</h2>
    </div>
    <div class="story-content">
      <div class="about-tools-grid">
        <div class="about-tool-item">
          <span class="about-tool-name">GameMaker Studio 2</span>
          <span class="about-tool-desc">Motor do jogo, lógica, física e renderização 2D</span>
        </div>
        <div class="about-tool-item">
          <span class="about-tool-name">GML</span>
          <span class="about-tool-desc">Linguagem de programação nativa do GameMaker</span>
        </div>
        <div class="about-tool-item">
          <span class="about-tool-name">PHP &amp; MySQL</span>
          <span class="about-tool-desc">Backend do site, autenticação, base de dados, perfis</span>
        </div>
        <div class="about-tool-item">
          <span class="about-tool-name">HTML / CSS / JavaScript</span>
          <span class="about-tool-desc">Frontend do site, interface, animações, interatividade</span>
        </div>
        <div class="about-tool-item">
          <span class="about-tool-name">Aseprite</span>
          <span class="about-tool-desc">Criação de sprites e animações em pixel art</span>
        </div>
        <div class="about-tool-item">
          <span class="about-tool-name">Git &amp; GitHub</span>
          <span class="about-tool-desc">Controlo de versões e colaboração</span>
        </div>
      </div>
    </div>
  </div>

  <!-- REFLEXÃO -->
  <div class="story-section">
    <div class="story-section-header">
      <div class="story-badge">Reflexão</div>
      <h2>O Que Aprendemos</h2>
    </div>
    <div class="story-content">
      <p>Este projeto ensinou-nos que o desenvolvimento de software raramente corre como planeado, e que isso não é um fracasso, é parte do processo. Cada obstáculo técnico tornou-se uma oportunidade de aprender algo que os livros não ensinam.</p>
      <p>Aprendemos a trabalhar em equipa sob pressão, a dividir responsabilidades, a comunicar problemas e a tomar decisões difíceis sobre o que era viável dentro do tempo disponível. Acima de tudo, ficámos com a certeza de que escolhemos a área certa.</p>

      <div class="story-ending" style="margin-top: 4px;">
        <div class="story-ending-label">Escola Secundária de Mem Martins · PAP 2025/2026</div>
        <p>Sylora: Ecos dos Deuses foi desenvolvido como Prova de Aptidão Profissional no Curso Profissional de Gestão e Programação de Sistemas Informáticos. Todo o conteúdo, jogo, site, narrativa e arte, foi criado integralmente pelos elementos da equipa.</p>
      </div>
    </div>
  </div>

</div>

<?php include 'includes/footer.php'; ?>