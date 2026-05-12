<?php
require_once 'includes/config.php';
include 'includes/header.php';
$isLoggedIn = isset($_SESSION['user_id']);
?>

<?php if ($isLoggedIn): ?>


<div class="sx-outer">
<div data-no-footer aria-hidden="true" style="display:none"></div>
<div class="sx-wrap" id="sxWrap">

  <div class="sx-bg" aria-hidden="true">
    <div class="sx-orb sx-orb-1"></div>
    <div class="sx-orb sx-orb-2"></div>
    <div class="sx-orb sx-orb-3"></div>
    <canvas class="sx-particles" id="sxCanvas"></canvas>
  </div>

  <div class="sx-prog"><div class="sx-prog-fill" id="sxProg"></div></div>
  <div class="sx-counter" id="sxCounter">– / 8</div>

  <nav class="sx-dots" id="sxDots" aria-label="Navegação da história">
    <?php
    $dotLabels = ['Prólogo','Herói','Clio','Ato I','Ato II','Ato III','Ato IV','Ato V'];
    foreach ($dotLabels as $i => $label): ?>
      <button class="sx-dot" data-i="<?= $i ?>" type="button" aria-label="Ir para <?= htmlspecialchars($label) ?>">
        <span class="sx-dot-label"><?= htmlspecialchars($label) ?></span>
      </button>
    <?php endforeach; ?>
  </nav>

  <div class="sx-track" id="sxTrack">

    <!-- INTRO -->
    <div class="sx-panel sx-panel-intro">
      <div class="sx-intro-content">
        <div class="sx-hero-runes" aria-hidden="true">⊕ ✦ ◈ ⟡ ✦ ◈ ⊕</div>
        <p class="sx-overline">Sylora - Ecos dos Deuses</p>
        <h1 class="sx-hero-title">A História</h1>
        <p class="sx-hero-lead">Após a Titanomaquia, os deuses olímpicos assumiram o controlo do mundo.<br>Fragmentos dos Titãs permaneceram espalhados, corrompendo tudo o que tocavam.</p>
        <div class="sx-scroll-cta">
          <span class="sx-scroll-arrow" aria-hidden="true"></span>
          <span>Scroll ou seta → para avançar</span>
        </div>
      </div>
    </div>

    <!-- PRÓLOGO -->
    <div class="sx-panel" style="--hue:38">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <span class="sx-tag">Prólogo</span>
          <h2 class="sx-h2-final">O Pacto dos Olímpicos</h2>
          <div class="sx-rule"></div>
          <p>Para evitar um novo conflito entre deuses e mortais, os olímpicos impuseram uma regra absoluta: <strong>nenhuma divindade pode intervir diretamente nas ações humanas.</strong></p>
          <p>Sylora, deusa em formação e aprendiz de Themis, decide quebrar a inércia divina escolhendo um campeão humano para agir em seu nome.</p>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.42 .12 38/.35);--art-b:oklch(.14 .04 38/.6)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><line x1="27" y1="7" x2="27" y2="47"/><line x1="11" y1="15" x2="43" y2="15"/><path d="M9 25 Q9 33 18 33 Q27 33 27 25 L9 25Z"/><path d="M27 25 Q27 33 36 33 Q45 33 45 25 L27 25Z"/><line x1="17" y1="47" x2="37" y2="47"/></svg></span>
            <p>O Conselho dos Olímpicos decretou o silêncio divino.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- HERÓI -->
    <div class="sx-panel" style="--hue:195">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <span class="sx-tag">O Herói</span>
          <h2 class="sx-h2-final">O Protagonista</h2>
          <div class="sx-rule"></div>
          <p>Um jovem humano sem memória desperta numa pequena ilha isolada. Não sabe o seu passado, a sua origem nem como chegou ali.</p>
          <div class="sx-callout">
            <span class="sx-callout-ico">◈</span>
            <span>O esquecimento é intencional: a mente foi apagada para que os Titãs não percebessem a interferência de Sylora.</span>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.36 .12 195/.35);--art-b:oklch(.12 .05 195/.6)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12 L12 22 Q8 27 12 32 L24 20 Q29 15 34 20 L22 32"/><path d="M32 42 L40 32 Q44 27 40 22 L28 34 Q23 39 18 34 L30 22"/><line x1="25" y1="28" x2="29" y2="28" stroke-dasharray="3 3" opacity="0.6"/></svg></span>
            <p>Memória apagada. Força intacta.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- CLIO -->
    <div class="sx-panel" style="--hue:265">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <span class="sx-tag">A Guia</span>
          <h2 class="sx-h2-final">Clio</h2>
          <div class="sx-rule"></div>
          <p>Inspirada na musa da História, Clio manifesta-se no mundo mortal como ponte entre o jogador, a história e o divino.</p>
          <ul class="sx-list">
            <li>Explica mitos e eventos do passado</li>
            <li>Orienta o herói durante a viagem</li>
            <li>Comenta as consequências das ações do jogador</li>
          </ul>
          <p>Ela não luta, mas é indispensável.</p>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.38 .14 265/.36);--art-b:oklch(.12 .05 265/.62)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M42 8 C42 8 14 22 16 40 L20 45"/><path d="M20 45 C18 49 10 47 10 47 C10 47 17 43 16 40"/><path d="M21 36 L32 25"/><path d="M16 40 C20 32 32 18 42 8"/></svg></span>
            <p>A voz entre mundos.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ATO I -->
    <div class="sx-panel" style="--hue:184">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <div class="sx-act-num"><span>I</span><span class="sx-lvl">Nível 1–10</span></div>
          <h2 class="sx-h2-final">Ilha de Thalassos</h2>
          <div class="sx-rule"></div>
          <p class="sx-lore"><em>Thalassos - de Thálassa, "mar". O desconhecido e o nascimento da vida.</em></p>
          <p>O protagonista acorda na costa. Clio explica que foi escolhido. O herói explora ruínas dos Titãs e descobre um fragmento corrompido. Após a vitória, desbloqueia a navegação.</p>
          <div class="sx-boss">
            <div class="sx-boss-ico"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Boss</div>
            <div><strong>Pelágion</strong> - ser outrora humano, corrompido por Oceanus. <em>(de Pelagos, "alto-mar")</em></div>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.35 .12 184/.35);--art-b:oklch(.1 .05 184/.62)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><line x1="27" y1="14" x2="27" y2="46"/><line x1="13" y1="23" x2="13" y2="14"/><line x1="41" y1="23" x2="41" y2="14"/><path d="M13 14 Q20 23 27 20 Q34 23 41 14"/><line x1="20" y1="34" x2="34" y2="34"/></svg></span>
            <p>O mar guarda segredos antigos.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ATO II -->
    <div class="sx-panel" style="--hue:24">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <div class="sx-act-num"><span>II</span><span class="sx-lvl">Nível 11–30</span></div>
          <h2 class="sx-h2-final">As Cinzas de Helion</h2>
          <div class="sx-rule"></div>
          <p class="sx-lore"><em>Helion - de Hélios, luz que pode criar ou destruir.</em></p>
          <p>Mineiros descobriram a <strong>Brasa de Hyperion</strong> e enlouqueceram. A ilha ameaça uma erupção devastadora. Após o boss, o herói vê um flash da sua criação como campeão.</p>
          <div class="sx-boss">
            <div class="sx-boss-ico"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Boss</div>
            <div><strong>Photonar, o Olho Ardente</strong> - capataz com cristal incandescente no olho.</div>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.42 .15 28/.4);--art-b:oklch(.14 .06 22/.64)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><line x1="27" y1="38" x2="27" y2="46"/><rect x="22" y="30" width="10" height="10" rx="2"/><path d="M27 8 C27 8 19 18 22 26 Q22 18 27 16 Q32 18 32 26 C35 18 27 8 27 8Z"/><path d="M22 26 Q27 30 32 26"/></svg></span>
            <p>A luz corrompida queima por dentro.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ATO III -->
    <div class="sx-panel" style="--hue:208">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <div class="sx-act-num"><span>III</span><span class="sx-lvl">Nível 31–40</span></div>
          <h2 class="sx-h2-final">Zephyria - O Véu dos Ventos</h2>
          <div class="sx-rule"></div>
          <p class="sx-lore"><em>Zephyria - de Zéphyros, deus do vento de oeste, filho de Astraeus.</em></p>
          <p>Uma tempestade projeta o navio acima das nuvens. Nem Clio conhece este lugar. Um mecanismo celeste revela que o herói já estava escrito nas estrelas muito antes de nascer.</p>
          <div class="sx-boss">
            <div class="sx-boss-ico"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Boss</div>
            <div><strong>Ecos de Astraeus</strong> - formas etéreas que se fundem num titã de vento e relâmpago.</div>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.36 .13 208/.36);--art-b:oklch(.1 .05 216/.62)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round"><path d="M27 12 C38 12 44 19 42 27 C40 35 32 39 26 35 C20 31 22 23 28 21 C34 19 36 25 32 27"/><circle cx="27" cy="44" r="2" fill="white" stroke="none"/><circle cx="19" cy="42" r="1.5" fill="white" stroke="none"/><circle cx="35" cy="42" r="1.5" fill="white" stroke="none"/></svg></span>
            <p>As estrelas já sabiam o seu nome.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ATO IV -->
    <div class="sx-panel" style="--hue:258">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <div class="sx-act-num"><span>IV</span><span class="sx-lvl">Nível 41–70</span></div>
          <h2 class="sx-h2-final">O Submundo pela Memória</h2>
          <div class="sx-rule"></div>
          <p class="sx-lore"><em>"Tão abaixo do Hades quanto a terra é do céu." - Homero, Ilíada</em></p>
          <p>Krios reconhece o herói: era capitão dos <strong>Guerreiros do Luar Sangrento</strong>. Traído e morto por um semideus, Sylora reviveu a sua alma. Clio intervém com poder proibido para o salvar.</p>
          <div class="sx-boss sx-boss-purple">
            <div class="sx-boss-ico"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Boss<span class="sx-boss-phases">3 Fases</span></div>
            <div><strong>Cronos Manipulador &amp; Krios Manipulado</strong> - Titã do Tempo e Titã das Constelações.</div>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.3 .13 258/.38);--art-b:oklch(.09 .05 258/.65)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round"><path d="M36 10 C25 12 18 23 21 34 C24 45 35 49 44 45 C33 47 22 39 22 27 C22 15 33 9 36 10Z"/><circle cx="41" cy="18" r="1.8" fill="white" stroke="none"/><circle cx="45" cy="27" r="1.2" fill="white" stroke="none"/></svg></span>
            <p>A memória regressa como uma lâmina.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ATO V FINAL -->
    <div class="sx-panel sx-panel-final" style="--hue:46">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <div class="sx-act-num sx-act-num-final"><span>V</span><span class="sx-lvl">Nível 71–∞</span></div>
          <h2 class="sx-h2-final">O Julgamento dos Deuses</h2>
          <div class="sx-rule sx-rule-gold"></div>
          <p class="sx-lore"><em>Templo Celestial de Themis, acima das nuvens.</em></p>
          <p>Clio é presa. Sylora é acusada de violar o Pacto. O herói, com memória total, invade o templo carregando o manto rachado de Clio. Os deuses querem apagar tudo.</p>
          <div class="sx-boss sx-boss-final">
            <div class="sx-boss-ico"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Boss Final<span class="sx-boss-phases">3 Fases</span></div>
            <div><strong>Égide dos Doze</strong> - Fase 1: Ares/Zeus · Fase 2: Poseidon/Atena · Fase 3: Themis.</div>
          </div>
          <div class="sx-ending">
            <span class="sx-ending-label">✦ Final - A Liberdade</span>
            <p>Sylora é promovida. Clio torna-se mortal e continua com o Herói. O mundo vive sem intervenção divina. A saga torna-se mito, como hoje.</p>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.46 .16 50/.44);--art-b:oklch(.16 .08 42/.65)">
            <div class="sx-art-glow"></div><span class="sx-symbol sx-symbol-final"><svg width="68" height="68" viewBox="0 0 68 68" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M34 6 L37.5 27 L58 19 L44 35 L64 40 L44 45 L58 61 L37.5 53 L34 62 L30.5 53 L10 61 L24 45 L4 40 L24 35 L10 19 L30.5 27 Z"/></svg></span>
            <p>O fim que se tornou o início de tudo.</p>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /sx-track -->
</div><!-- /sx-wrap -->
</div><!-- /sx-outer -->

<script>
(function(){
  const wrap    = document.getElementById('sxWrap');
  const track   = document.getElementById('sxTrack');
  const prog    = document.getElementById('sxProg');
  const counter = document.getElementById('sxCounter');
  const dots    = [...document.querySelectorAll('.sx-dot')];
  const TOTAL   = 9;
  let cur = 0, locked = false;

  wrap.addEventListener('wheel', function(e){
    e.preventDefault(); e.stopPropagation();
    if(locked) return;
    locked = true;
    if((e.deltaY||e.deltaX) > 0) go(cur+1); else go(cur-1);
    setTimeout(()=>locked=false, 750);
  }, {passive:false});

  const canvas = document.getElementById('sxCanvas');
  const ctx    = canvas ? canvas.getContext('2d') : null;
  if(ctx){
    const resize = ()=>{ canvas.width=innerWidth; canvas.height=innerHeight; };
    resize(); addEventListener('resize', resize);
    const pts = Array.from({length:48}, ()=>({
      x: Math.random()*innerWidth, y: Math.random()*innerHeight,
      r: Math.random()*1.3+.3,
      vx:(Math.random()-.5)*.16, vy:-(Math.random()*.3+.07),
      o: Math.random()*.5+.12
    }));
    let rafId;
    function tick(){
      ctx.clearRect(0,0,canvas.width,canvas.height);
      pts.forEach(p=>{
        ctx.beginPath(); ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
        ctx.fillStyle=`rgba(210,175,118,${p.o})`; ctx.fill();
        p.x+=p.vx; p.y+=p.vy;
        if(p.y<-4){p.y=canvas.height+4;p.x=Math.random()*canvas.width;}
        if(p.x<-4)p.x=canvas.width+4;
        if(p.x>canvas.width+4)p.x=-4;
      });
      rafId = requestAnimationFrame(tick);
    }
    tick();
    document.addEventListener('visibilitychange',()=>{
      if(document.visibilityState==='hidden') cancelAnimationFrame(rafId);
      else tick();
    });
  }

  function go(idx){
    idx = Math.max(0, Math.min(TOTAL-1, idx));
    cur = idx;
    track.style.transform = `translateX(calc(${-idx} * (100% / ${TOTAL})))`;
    const pct = idx===0 ? 0 : Math.round(idx/(TOTAL-1)*100);
    prog.style.width = pct+'%';
    counter.textContent = idx===0 ? '– / 8' : `${idx} / 8`;
    dots.forEach((d,i)=>d.classList.toggle('sx-dot-active', i===idx-1));
  }

  let tx=null;
  wrap.addEventListener('touchstart',e=>{tx=e.touches[0].clientX;},{passive:true});
  wrap.addEventListener('touchend',e=>{
    if(tx===null)return;
    const dx=e.changedTouches[0].clientX-tx;
    if(Math.abs(dx)>38) go(dx<0?cur+1:cur-1);
    tx=null;
  },{passive:true});

  document.addEventListener('keydown',e=>{
    const inWrap = wrap.contains(document.activeElement)||document.activeElement===document.body;
    if(!inWrap)return;
    if(e.key==='ArrowRight'||e.key==='ArrowDown'){e.preventDefault();go(cur+1);}
    if(e.key==='ArrowLeft' ||e.key==='ArrowUp')  {e.preventDefault();go(cur-1);}
  });

  dots.forEach((d,i)=>d.addEventListener('click',()=>go(i+1)));
  go(0);
})();
</script>

<?php else: ?>

<div class="story-page">
  <div class="story-hero">
    <div class="story-hero-runes" aria-hidden="true">⊕ ✦ ◈ ⟡ ✦</div>
    <p class="story-overline">Sylora - Ecos dos Deuses</p>
    <h1>A História</h1>
    <p class="story-lead">Após a Titanomaquia, os deuses olímpicos assumiram o controlo do mundo. Mas a derrota dos Titãs não apagou a sua essência - fragmentos do seu poder permaneceram espalhados, corrompendo ilhas, templos e criaturas mortais.</p>
  </div>
  <div class="story-section">
    <div class="story-section-header"><div class="story-badge">Prólogo</div><h2>O Pacto dos Olímpicos</h2></div>
    <div class="story-content">
      <p>Para evitar um novo conflito entre deuses e mortais, os olímpicos impuseram uma regra absoluta: <strong>nenhuma divindade pode intervir diretamente nas ações humanas.</strong></p>
      <p>Entre os deuses mais jovens encontra-se <strong>Sylora</strong>, encarregada de observar o equilíbrio entre ordem e caos. Impossibilitada de agir diretamente, decide escolher um campeão humano para agir em seu nome.</p>
    </div>
  </div>
  <div class="story-section">
    <div class="story-section-header"><div class="story-badge">O Herói</div><h2>O Protagonista</h2></div>
    <div class="story-content">
      <p>O jogador controla um jovem humano sem memória, que desperta numa pequena ilha isolada.</p>
      <div class="story-callout"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>O esquecimento não é acidental: a mente foi apagada para evitar que os Titãs percebessem a interferência de Sylora.</span></div>
    </div>
  </div>
  <div class="story-section">
    <div class="story-section-header"><div class="story-badge">A Guia</div><h2>Clio</h2></div>
    <div class="story-content">
      <p>Ao lado do herói surge <strong>Clio</strong>, inspirada na musa da História.</p>
      <ul class="story-list"><li>Explica mitos e eventos do passado</li><li>Orienta o herói durante a viagem</li><li>Comenta as consequências das ações do jogador</li></ul>
    </div>
  </div>
  <div class="story-acts">
    <h2 class="story-acts-title">Atos da História</h2>
    <div class="story-timeline">
      <div class="story-act"><div class="story-act-card"><div class="story-act-header"><div class="story-act-num"><span class="story-act-label">Ato</span><span class="story-act-roman">I</span></div><div class="story-act-divider-v"></div><div class="story-act-title-wrap"><h3>Ilha de Thalassos</h3><span class="story-act-level-badge">Nível 1-10</span></div></div><div class="story-act-body"><p class="story-act-lore"><em>Thalassos vem de Thálassa, "mar".</em></p><p>O protagonista acorda na costa. Clio explica que foi escolhido por Sylora.</p><div class="story-boss"><div class="story-boss-icon"><svg class="story-boss-star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span class="story-boss-label">Boss</span></div><div><strong>Pelágion</strong> - ser corrompido por Oceanus.</div></div></div></div></div>
      <div class="story-act"><div class="story-act-card"><div class="story-act-header"><div class="story-act-num"><span class="story-act-label">Ato</span><span class="story-act-roman">II</span></div><div class="story-act-divider-v"></div><div class="story-act-title-wrap"><h3>As Cinzas de Helion</h3><span class="story-act-level-badge">Nível 11-30</span></div></div><div class="story-act-body"><p class="story-act-lore"><em>Helion deriva de Hélios.</em></p><p>Mineiros descobriram a <strong>Brasa de Hyperion</strong> e enlouqueceram.</p><div class="story-boss"><div class="story-boss-icon"><svg class="story-boss-star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span class="story-boss-label">Boss</span></div><div><strong>Photonar, o Olho Ardente</strong></div></div></div></div></div>
      <div class="story-act"><div class="story-act-card"><div class="story-act-header"><div class="story-act-num"><span class="story-act-label">Ato</span><span class="story-act-roman">III</span></div><div class="story-act-divider-v"></div><div class="story-act-title-wrap"><h3>Zephyria - O Véu dos Ventos</h3><span class="story-act-level-badge">Nível 31-40</span></div></div><div class="story-act-body"><p class="story-act-lore"><em>Zephyria deriva de Zéphyros.</em></p><p>Uma tempestade projeta o navio acima das nuvens.</p><div class="story-boss"><div class="story-boss-icon"><svg class="story-boss-star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span class="story-boss-label">Boss</span></div><div><strong>Ecos de Astraeus</strong></div></div></div></div></div>
      <div class="story-act"><div class="story-act-card"><div class="story-act-header"><div class="story-act-num"><span class="story-act-label">Ato</span><span class="story-act-roman">IV</span></div><div class="story-act-divider-v"></div><div class="story-act-title-wrap"><h3>O Submundo pela Memória</h3><span class="story-act-level-badge">Nível 41-70</span></div></div><div class="story-act-body"><p class="story-act-lore"><em>"Tão abaixo do Hades quanto a terra é do céu." - Homero</em></p><p>Krios reconhece o herói: era capitão dos <strong>Guerreiros do Luar Sangrento</strong>.</p><div class="story-boss"><div class="story-boss-icon"><svg class="story-boss-star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span class="story-boss-label">Boss<span class="sx-boss-phases">3 Fases</span></span></div><div><strong>Cronos &amp; Krios</strong></div></div></div></div></div>
      <div class="story-act story-act-final"><div class="story-act-card"><div class="story-act-header"><div class="story-act-num"><span class="story-act-label">Ato</span><span class="story-act-roman">V</span></div><div class="story-act-divider-v"></div><div class="story-act-title-wrap"><h3>O Julgamento dos Deuses</h3><span class="story-act-level-badge">Nível 71-∞</span></div></div><div class="story-act-body"><p class="story-act-lore"><em>Templo Celestial de Themis, acima das nuvens.</em></p><p>O herói invade o templo com a memória total restaurada.</p><div class="story-boss"><div class="story-boss-icon"><svg class="story-boss-star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span class="story-boss-label">Boss Final<span class="sx-boss-phases">3 Fases</span></span></div><div><strong>Égide dos Doze</strong></div></div><div class="story-ending"><div><span class="story-ending-label">Final - A Liberdade</span><p>Sylora é promovida. Clio torna-se mortal. O mundo vive sem intervenção divina.</p></div></div></div></div></div>
    </div>
  </div>
</div>
<script>
(function(){
  const acts=document.querySelectorAll('.story-act');
  if(!acts.length)return;
  const obs=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);}});},{threshold:.12,rootMargin:'0px 0px -40px 0px'});
  acts.forEach((a,i)=>{a.style.transitionDelay=(i*60)+'ms';obs.observe(a);});
})();
</script>

<?php endif; ?>

<?php
// Suprimir o footer na experiência de história logada (ocupa espaço desnecessário no fullscreen)
if ($isLoggedIn) { $noFooter = true; }
include 'includes/footer.php';
?>
