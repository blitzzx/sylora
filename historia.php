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

  <nav class="sx-dots" id="sxDots" aria-label="<?= e(t('hist.nav')) ?>">
    <?php
    $dotKeys = ['hist.dot_prologue','hist.dot_hero','hist.dot_clio','hist.dot_act1','hist.dot_act2','hist.dot_act3','hist.dot_act4','hist.dot_act5'];
    foreach ($dotKeys as $i => $key): ?>
      <button class="sx-dot" data-i="<?= $i ?>" type="button" aria-label="<?= e(t($key)) ?>">
        <span class="sx-dot-label" data-i18n="<?= e($key) ?>"><?= e(t($key)) ?></span>
      </button>
    <?php endforeach; ?>
  </nav>

  <div class="sx-track" id="sxTrack">


    <div class="sx-panel sx-panel-intro">
      <div class="sx-intro-content">
        <div class="sx-hero-runes" aria-hidden="true">⊕ ✦ ◈ ⟡ ✦ ◈ ⊕</div>
        <p class="sx-overline" data-i18n="hist.overline"><?= t('hist.overline') ?></p>
        <h1 class="sx-hero-title" data-i18n="hist.title"><?= t('hist.title') ?></h1>
        <p class="sx-hero-lead" data-i18n-html="hist.lead_short"><?= t('hist.lead_short') ?></p>
        <div class="sx-scroll-cta">
          <span class="sx-scroll-arrow" aria-hidden="true"></span>
          <span data-i18n="hist.scroll_cta"><?= t('hist.scroll_cta') ?></span>
        </div>
      </div>
    </div>


    <div class="sx-panel" style="--hue:38">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <span class="sx-tag" data-i18n="hist.tag_prologue"><?= t('hist.tag_prologue') ?></span>
          <h2 class="sx-h2-final" data-i18n="hist.h_pact"><?= t('hist.h_pact') ?></h2>
          <div class="sx-rule"></div>
          <p data-i18n-html="hist.p_pact_1"><?= t('hist.p_pact_1') ?></p>
          <p data-i18n-html="hist.p_pact_2"><?= t('hist.p_pact_2') ?></p>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.42 .12 38/.35);--art-b:oklch(.14 .04 38/.6)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><line x1="27" y1="7" x2="27" y2="47"/><line x1="11" y1="15" x2="43" y2="15"/><path d="M9 25 Q9 33 18 33 Q27 33 27 25 L9 25Z"/><path d="M27 25 Q27 33 36 33 Q45 33 45 25 L27 25Z"/><line x1="17" y1="47" x2="37" y2="47"/></svg></span>
            <p data-i18n="hist.art_pact"><?= t('hist.art_pact') ?></p>
          </div>
        </div>
      </div>
    </div>


    <div class="sx-panel" style="--hue:195">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <span class="sx-tag" data-i18n="hist.tag_hero"><?= t('hist.tag_hero') ?></span>
          <h2 class="sx-h2-final" data-i18n="hist.h_hero"><?= t('hist.h_hero') ?></h2>
          <div class="sx-rule"></div>
          <p data-i18n="hist.p_hero_1"><?= t('hist.p_hero_1') ?></p>
          <div class="sx-callout">
            <span class="sx-callout-ico">◈</span>
            <span data-i18n="hist.callout_hero"><?= t('hist.callout_hero') ?></span>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.36 .12 195/.35);--art-b:oklch(.12 .05 195/.6)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12 L12 22 Q8 27 12 32 L24 20 Q29 15 34 20 L22 32"/><path d="M32 42 L40 32 Q44 27 40 22 L28 34 Q23 39 18 34 L30 22"/><line x1="25" y1="28" x2="29" y2="28" stroke-dasharray="3 3" opacity="0.6"/></svg></span>
            <p data-i18n="hist.art_hero"><?= t('hist.art_hero') ?></p>
          </div>
        </div>
      </div>
    </div>


    <div class="sx-panel" style="--hue:265">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <span class="sx-tag" data-i18n="hist.tag_guide"><?= t('hist.tag_guide') ?></span>
          <h2 class="sx-h2-final" data-i18n="hist.h_clio"><?= t('hist.h_clio') ?></h2>
          <div class="sx-rule"></div>
          <p data-i18n="hist.p_clio_1"><?= t('hist.p_clio_1') ?></p>
          <ul class="sx-list">
            <li data-i18n="hist.clio_li1"><?= t('hist.clio_li1') ?></li>
            <li data-i18n="hist.clio_li2"><?= t('hist.clio_li2') ?></li>
            <li data-i18n="hist.clio_li3"><?= t('hist.clio_li3') ?></li>
          </ul>
          <p data-i18n="hist.p_clio_2"><?= t('hist.p_clio_2') ?></p>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.38 .14 265/.36);--art-b:oklch(.12 .05 265/.62)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M42 8 C42 8 14 22 16 40 L20 45"/><path d="M20 45 C18 49 10 47 10 47 C10 47 17 43 16 40"/><path d="M21 36 L32 25"/><path d="M16 40 C20 32 32 18 42 8"/></svg></span>
            <p data-i18n="hist.art_clio"><?= t('hist.art_clio') ?></p>
          </div>
        </div>
      </div>
    </div>


    <div class="sx-panel" style="--hue:184">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <div class="sx-act-num"><span>I</span><span class="sx-lvl"><span data-i18n="hist.level"><?= t('hist.level') ?></span> 1–10</span></div>
          <h2 class="sx-h2-final" data-i18n="hist.a1_title"><?= t('hist.a1_title') ?></h2>
          <div class="sx-rule"></div>
          <p class="sx-lore"><em data-i18n="hist.a1_lore"><?= t('hist.a1_lore') ?></em></p>
          <p data-i18n-html="hist.a1_body"><?= t('hist.a1_body') ?></p>
          <div class="sx-boss">
            <div class="sx-boss-ico"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span data-i18n="hist.boss"><?= t('hist.boss') ?></span></div>
            <div data-i18n-html="hist.a1_boss"><?= t('hist.a1_boss') ?></div>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.35 .12 184/.35);--art-b:oklch(.1 .05 184/.62)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><line x1="27" y1="14" x2="27" y2="46"/><line x1="13" y1="23" x2="13" y2="14"/><line x1="41" y1="23" x2="41" y2="14"/><path d="M13 14 Q20 23 27 20 Q34 23 41 14"/><line x1="20" y1="34" x2="34" y2="34"/></svg></span>
            <p data-i18n="hist.a1_art"><?= t('hist.a1_art') ?></p>
          </div>
        </div>
      </div>
    </div>


    <div class="sx-panel" style="--hue:24">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <div class="sx-act-num"><span>II</span><span class="sx-lvl"><span data-i18n="hist.level"><?= t('hist.level') ?></span> 11–30</span></div>
          <h2 class="sx-h2-final" data-i18n="hist.a2_title"><?= t('hist.a2_title') ?></h2>
          <div class="sx-rule"></div>
          <p class="sx-lore"><em data-i18n="hist.a2_lore"><?= t('hist.a2_lore') ?></em></p>
          <p data-i18n-html="hist.a2_body"><?= t('hist.a2_body') ?></p>
          <div class="sx-boss">
            <div class="sx-boss-ico"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span data-i18n="hist.boss"><?= t('hist.boss') ?></span></div>
            <div data-i18n-html="hist.a2_boss"><?= t('hist.a2_boss') ?></div>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.42 .15 28/.4);--art-b:oklch(.14 .06 22/.64)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><line x1="27" y1="38" x2="27" y2="46"/><rect x="22" y="30" width="10" height="10" rx="2"/><path d="M27 8 C27 8 19 18 22 26 Q22 18 27 16 Q32 18 32 26 C35 18 27 8 27 8Z"/><path d="M22 26 Q27 30 32 26"/></svg></span>
            <p data-i18n="hist.a2_art"><?= t('hist.a2_art') ?></p>
          </div>
        </div>
      </div>
    </div>


    <div class="sx-panel" style="--hue:208">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <div class="sx-act-num"><span>III</span><span class="sx-lvl"><span data-i18n="hist.level"><?= t('hist.level') ?></span> 31–40</span></div>
          <h2 class="sx-h2-final" data-i18n="hist.a3_title"><?= t('hist.a3_title') ?></h2>
          <div class="sx-rule"></div>
          <p class="sx-lore"><em data-i18n="hist.a3_lore"><?= t('hist.a3_lore') ?></em></p>
          <p data-i18n-html="hist.a3_body"><?= t('hist.a3_body') ?></p>
          <div class="sx-boss">
            <div class="sx-boss-ico"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span data-i18n="hist.boss"><?= t('hist.boss') ?></span></div>
            <div data-i18n-html="hist.a3_boss"><?= t('hist.a3_boss') ?></div>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.36 .13 208/.36);--art-b:oklch(.1 .05 216/.62)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round"><path d="M27 12 C38 12 44 19 42 27 C40 35 32 39 26 35 C20 31 22 23 28 21 C34 19 36 25 32 27"/><circle cx="27" cy="44" r="2" fill="white" stroke="none"/><circle cx="19" cy="42" r="1.5" fill="white" stroke="none"/><circle cx="35" cy="42" r="1.5" fill="white" stroke="none"/></svg></span>
            <p data-i18n="hist.a3_art"><?= t('hist.a3_art') ?></p>
          </div>
        </div>
      </div>
    </div>


    <div class="sx-panel" style="--hue:258">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <div class="sx-act-num"><span>IV</span><span class="sx-lvl"><span data-i18n="hist.level"><?= t('hist.level') ?></span> 41–70</span></div>
          <h2 class="sx-h2-final" data-i18n="hist.a4_title"><?= t('hist.a4_title') ?></h2>
          <div class="sx-rule"></div>
          <p class="sx-lore"><em data-i18n="hist.a4_lore"><?= t('hist.a4_lore') ?></em></p>
          <p data-i18n-html="hist.a4_body"><?= t('hist.a4_body') ?></p>
          <div class="sx-boss sx-boss-purple">
            <div class="sx-boss-ico"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span data-i18n="hist.boss"><?= t('hist.boss') ?></span><span class="sx-boss-phases">3 <span data-i18n="hist.phases"><?= t('hist.phases') ?></span></span></div>
            <div data-i18n-html="hist.a4_boss"><?= t('hist.a4_boss') ?></div>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.3 .13 258/.38);--art-b:oklch(.09 .05 258/.65)">
            <div class="sx-art-glow"></div><span class="sx-symbol"><svg width="54" height="54" viewBox="0 0 54 54" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round"><path d="M36 10 C25 12 18 23 21 34 C24 45 35 49 44 45 C33 47 22 39 22 27 C22 15 33 9 36 10Z"/><circle cx="41" cy="18" r="1.8" fill="white" stroke="none"/><circle cx="45" cy="27" r="1.2" fill="white" stroke="none"/></svg></span>
            <p data-i18n="hist.a4_art"><?= t('hist.a4_art') ?></p>
          </div>
        </div>
      </div>
    </div>


    <div class="sx-panel sx-panel-final" style="--hue:46">
      <div class="sx-panel-glow"></div>
      <div class="sx-content">
        <div class="sx-left">
          <div class="sx-act-num sx-act-num-final"><span>V</span><span class="sx-lvl"><span data-i18n="hist.level"><?= t('hist.level') ?></span> 71–∞</span></div>
          <h2 class="sx-h2-final" data-i18n="hist.a5_title"><?= t('hist.a5_title') ?></h2>
          <div class="sx-rule sx-rule-gold"></div>
          <p class="sx-lore"><em data-i18n="hist.a5_lore"><?= t('hist.a5_lore') ?></em></p>
          <p data-i18n-html="hist.a5_body"><?= t('hist.a5_body') ?></p>
          <div class="sx-boss sx-boss-final">
            <div class="sx-boss-ico"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span data-i18n="hist.boss_final"><?= t('hist.boss_final') ?></span><span class="sx-boss-phases">3 <span data-i18n="hist.phases"><?= t('hist.phases') ?></span></span></div>
            <div data-i18n-html="hist.a5_boss"><?= t('hist.a5_boss') ?></div>
          </div>
          <div class="sx-ending">
            <span class="sx-ending-label" data-i18n="hist.a5_ending_label"><?= t('hist.a5_ending_label') ?></span>
            <p data-i18n="hist.a5_ending"><?= t('hist.a5_ending') ?></p>
          </div>
        </div>
        <div class="sx-right">
          <div class="sx-art" style="--art-a:oklch(.46 .16 50/.44);--art-b:oklch(.16 .08 42/.65)">
            <div class="sx-art-glow"></div><span class="sx-symbol sx-symbol-final"><svg width="68" height="68" viewBox="0 0 68 68" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M34 6 L37.5 27 L58 19 L44 35 L64 40 L44 45 L58 61 L37.5 53 L34 62 L30.5 53 L10 61 L24 45 L4 40 L24 35 L10 19 L30.5 27 Z"/></svg></span>
            <p data-i18n="hist.a5_art"><?= t('hist.a5_art') ?></p>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
</div>

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
    <p class="story-overline" data-i18n="hist.overline"><?= t('hist.overline') ?></p>
    <h1 data-i18n="hist.title"><?= t('hist.title') ?></h1>
    <p class="story-lead" data-i18n="hist.lead_long"><?= t('hist.lead_long') ?></p>
  </div>
  <div class="story-section">
    <div class="story-section-header"><div class="story-badge" data-i18n="hist.tag_prologue"><?= t('hist.tag_prologue') ?></div><h2 data-i18n="hist.h_pact"><?= t('hist.h_pact') ?></h2></div>
    <div class="story-content">
      <p data-i18n-html="hist.p_pact_1"><?= t('hist.p_pact_1') ?></p>
      <p data-i18n-html="hist.p_pact_2_alt"><?= t('hist.p_pact_2_alt') ?></p>
    </div>
  </div>
  <div class="story-section">
    <div class="story-section-header"><div class="story-badge" data-i18n="hist.tag_hero"><?= t('hist.tag_hero') ?></div><h2 data-i18n="hist.h_hero"><?= t('hist.h_hero') ?></h2></div>
    <div class="story-content">
      <p data-i18n="hist.p_hero_short"><?= t('hist.p_hero_short') ?></p>
      <div class="story-callout"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span data-i18n="hist.callout_hero_long"><?= t('hist.callout_hero_long') ?></span></div>
    </div>
  </div>
  <div class="story-section">
    <div class="story-section-header"><div class="story-badge" data-i18n="hist.tag_guide"><?= t('hist.tag_guide') ?></div><h2 data-i18n="hist.h_clio"><?= t('hist.h_clio') ?></h2></div>
    <div class="story-content">
      <p data-i18n-html="hist.p_clio_short"><?= t('hist.p_clio_short') ?></p>
      <ul class="story-list"><li data-i18n="hist.clio_li1"><?= t('hist.clio_li1') ?></li><li data-i18n="hist.clio_li2"><?= t('hist.clio_li2') ?></li><li data-i18n="hist.clio_li3"><?= t('hist.clio_li3') ?></li></ul>
    </div>
  </div>
  <div class="story-acts">
    <h2 class="story-acts-title" data-i18n="hist.acts_title"><?= t('hist.acts_title') ?></h2>
    <div class="story-timeline">
      <?php
      $acts = [
          ['I',   '1-10',  'hist.a1_title', 'hist.a1_lore', 'hist.a1_body_short', 'hist.a1_boss_short', false],
          ['II',  '11-30', 'hist.a2_title', 'hist.a2_lore', 'hist.a2_body_short', 'hist.a2_boss_short', false],
          ['III', '31-40', 'hist.a3_title', 'hist.a3_lore', 'hist.a3_body_short', 'hist.a3_boss_short', false],
          ['IV',  '41-70', 'hist.a4_title', 'hist.a4_lore', 'hist.a4_body_short', 'hist.a4_boss_short', '3'],
          ['V',   '71-∞',  'hist.a5_title', 'hist.a5_lore', 'hist.a5_body_short', 'hist.a5_boss_short', '3'],
      ];
      foreach ($acts as $a):
          [$roman, $lvl, $titleK, $loreK, $bodyK, $bossK, $phases] = $a;
          $isFinal = $roman === 'V';
      ?>
      <div class="story-act<?= $isFinal ? ' story-act-final' : '' ?>"><div class="story-act-card"><div class="story-act-header"><div class="story-act-num"><span class="story-act-label" data-i18n="hist.act_label"><?= t('hist.act_label') ?></span><span class="story-act-roman"><?= $roman ?></span></div><div class="story-act-divider-v"></div><div class="story-act-title-wrap"><h3 data-i18n="<?= $titleK ?>"><?= t($titleK) ?></h3><span class="story-act-level-badge"><span data-i18n="hist.level"><?= t('hist.level') ?></span> <?= $lvl ?></span></div></div><div class="story-act-body"><p class="story-act-lore"><em data-i18n="<?= $loreK ?>"><?= t($loreK) ?></em></p><p data-i18n-html="<?= $bodyK ?>"><?= t($bodyK) ?></p><div class="story-boss"><div class="story-boss-icon"><svg class="story-boss-star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><span class="story-boss-label"><span data-i18n="<?= $isFinal ? 'hist.boss_final' : 'hist.boss' ?>"><?= t($isFinal ? 'hist.boss_final' : 'hist.boss') ?></span><?php if ($phases): ?><span class="sx-boss-phases"><?= $phases ?> <span data-i18n="hist.phases"><?= t('hist.phases') ?></span></span><?php endif; ?></span></div><div data-i18n-html="<?= $bossK ?>"><?= t($bossK) ?></div></div><?php if ($isFinal): ?><div class="story-ending"><div><span class="story-ending-label" data-i18n="hist.a5_ending_short"><?= t('hist.a5_ending_short') ?></span><p data-i18n="hist.a5_ending_short_body"><?= t('hist.a5_ending_short_body') ?></p></div></div><?php endif; ?></div></div></div>
      <?php endforeach; ?>
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

if ($isLoggedIn) { $noFooter = true; }
include 'includes/footer.php';
?>
