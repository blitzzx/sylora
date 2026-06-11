document.addEventListener("DOMContentLoaded", () => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  const navMenu = $(".nav-mobile-menu");
  const navBtn  = $(".nav-toggle");
  if (navMenu && navBtn) {
    navBtn.addEventListener("click", () => {
      const isOpen = navMenu.classList.toggle("open");
      navBtn.setAttribute("aria-expanded", String(isOpen));
    });
    document.addEventListener("click", (e) => {
      if (!navMenu.classList.contains("open")) return;
      if (!navMenu.contains(e.target) && !navBtn.contains(e.target)) {
        navMenu.classList.remove("open");
        navBtn.setAttribute("aria-expanded", "false");
      }
    });
    navMenu.addEventListener("click", (e) => {
      if (e.target.closest("a")) {
        navMenu.classList.remove("open");
        navBtn.setAttribute("aria-expanded", "false");
      }
    });
  }


  const pillBtn     = document.getElementById("drawer-trigger");
  const drawer      = document.getElementById("user-drawer");
  const overlay     = document.getElementById("drawer-overlay");
  const pillChevron = pillBtn ? pillBtn.querySelector("svg:last-child") : null;

  function openDrawer() {
    if (!drawer || !overlay) return;
    drawer.classList.add("open");
    overlay.classList.add("active");
    drawer.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    if (pillBtn) pillBtn.setAttribute("aria-expanded", "true");
    if (pillChevron) pillChevron.style.transform = "rotate(90deg)";
  }

  function closeDrawer() {
    if (!drawer || !overlay) return;
    drawer.classList.remove("open");
    overlay.classList.remove("active");
    drawer.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    if (pillBtn) {
      pillBtn.setAttribute("aria-expanded", "false");
      pillBtn.focus();
    }
    if (pillChevron) pillChevron.style.transform = "";
  }

  if (pillBtn && drawer && overlay) {
    pillBtn.addEventListener("click", () => {
      if (window.innerWidth <= 768) {
        const profileUrl = pillBtn.dataset.profileUrl;
        if (profileUrl) window.location.href = profileUrl;
        return;
      }
      drawer.classList.contains("open") ? closeDrawer() : openDrawer();
    });
    overlay.addEventListener("click", closeDrawer);
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && drawer && drawer.classList.contains("open")) closeDrawer();
    });
  }


  $$(".drawer-section-title").forEach((title) => {
    title.addEventListener("click", () => {
      title.closest(".drawer-section").classList.toggle("expanded");
    });
  });

  window.openDrawer = openDrawer;
  window.closeDrawer = closeDrawer;

  (function () {
    function installAvatarFallback(img) {
      img.addEventListener("error", function onErr() {
        img.removeEventListener("error", onErr);
        const initial  = img.dataset.initial || "?";
        const isSmall  = img.classList.contains("nav-avatar-img");
        const size     = isSmall ? 28 : 52;
        const fontSize = isSmall ? 12 : 18;
        const wrapper  = document.createElement("div");
        wrapper.textContent = initial;
        wrapper.style.cssText = [
          `width:${size}px`, `height:${size}px`, "border-radius:50%",
          "background:linear-gradient(135deg,var(--gold-dark),var(--gold))",
          "display:grid", "place-items:center",
          `font-size:${fontSize}px`, "font-weight:700",
          "font-family:var(--font-display)", "color:#0d0b08", "flex-shrink:0",
        ].join(";");
        img.replaceWith(wrapper);
      });
    }

    document.querySelectorAll(".nav-avatar-img, .drawer-avatar-img").forEach(installAvatarFallback);
  })();


  const html           = document.documentElement;
  const themeToggleNav = document.getElementById("theme-toggle-nav");
  const themeIconDark  = document.getElementById("theme-icon-dark");
  const themeIconLight = document.getElementById("theme-icon-light");

  function getPreferredTheme() {
    const saved = localStorage.getItem("sylora-theme");
    if (saved === "light" || saved === "dark") return saved;
    return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
  }

  function applyTheme(theme) {
    html.setAttribute("data-theme", theme);
    localStorage.setItem("sylora-theme", theme);
    if (themeIconDark && themeIconLight) {
      themeIconDark.style.display  = theme === "dark"  ? "none" : "";
      themeIconLight.style.display = theme === "light" ? "none" : "";
    }
    document.querySelectorAll(".theme-btn").forEach((b) => {
      b.classList.toggle("active", b.dataset.themeSet === theme);
    });
  }

  applyTheme(html.getAttribute("data-theme") || getPreferredTheme());

  if (themeToggleNav) {
    themeToggleNav.addEventListener("click", () => {
      applyTheme(html.getAttribute("data-theme") === "dark" ? "light" : "dark");
    });
  }

  document.querySelectorAll(".theme-btn").forEach((b) => {
    b.addEventListener("click", () => applyTheme(b.dataset.themeSet));
  });


  const audio        = document.getElementById("bg-music");
  const musicToggle  = document.getElementById("music-toggle");
  const iconOn       = document.getElementById("music-icon-on");
  const iconMuted    = document.getElementById("music-icon-muted");
  const iconOff      = document.getElementById("music-icon-off");
  const volSlider    = document.getElementById("music-vol-slider");
  const volPct       = document.getElementById("music-vol-pct");
  const MUSIC_KEY    = "sylora-music-on";
  const TIME_KEY     = "sylora-music-time";
  const VOL_KEY      = "sylora-music-volume";
  const MUTE_KEY     = "sylora-music-muted";
  let musicOn = false;
  let musicMuted = false;

  function updateMusicIcon() {
    if (!iconOn || !iconMuted || !iconOff) return;
    const vol0 = audio && audio.volume === 0;
    iconOn.style.display    = (musicOn && !musicMuted && !vol0) ? "" : "none";
    iconMuted.style.display = (musicOn && (musicMuted || vol0))  ? "" : "none";
    iconOff.style.display   = musicOn ? "none" : "";
  }

  function applyVolume(vol) {
    if (!audio) return;
    audio.volume = vol;
    if (volSlider) volSlider.value = Math.round(vol * 100);
    if (volPct) volPct.textContent = Math.round(vol * 100);
    updateVolSliderTrack(Math.round(vol * 100));
  }

  function updateVolSliderTrack(pct) {
    if (!volSlider) return;
    volSlider.style.background =
      `linear-gradient(to right, var(--gold) 0%, var(--gold) ${pct}%, var(--surface-offset) ${pct}%, var(--surface-offset) 100%)`;
  }

  function setMusicState(on) {
    musicOn = on;
    localStorage.setItem(MUSIC_KEY, on ? "true" : "false");
    if (on) {
      audio.play().catch(() => {
        musicOn = false;
        localStorage.setItem(MUSIC_KEY, "false");
        updateMusicIcon();
      });
    } else {
      audio.pause();
    }
    updateMusicIcon();
  }

  function toggleMute() {
    if (!musicOn) { setMusicState(true); return; }
    musicMuted = !musicMuted;
    audio.muted = musicMuted;
    if (!musicMuted && audio.volume === 0) {
      applyVolume(0.5);
      localStorage.setItem(VOL_KEY, "0.5");
    }
    localStorage.setItem(MUTE_KEY, musicMuted ? "true" : "false");
    updateMusicIcon();
  }

  if (audio) {
    const savedOn    = localStorage.getItem(MUSIC_KEY) === "true";
    const savedTime  = parseFloat(localStorage.getItem(TIME_KEY) || "0");
    const savedVol   = parseFloat(localStorage.getItem(VOL_KEY) || "0.5");
    const savedMuted = localStorage.getItem(MUTE_KEY) === "true";
    const rawVol     = isFinite(savedVol) ? Math.max(0, Math.min(1, savedVol)) : 0.5;
    const safeVol    = rawVol === 0 ? 0.5 : rawVol;

    musicMuted  = savedMuted;
    audio.muted = savedMuted;

    if (savedOn && savedTime > 0 && isFinite(savedTime)) {
      audio.addEventListener("canplay", function restorePos() {
        audio.currentTime = savedTime;
        audio.removeEventListener("canplay", restorePos);
      });
    }

    applyVolume(safeVol);

    setInterval(() => {
      if (!audio.paused) localStorage.setItem(TIME_KEY, String(audio.currentTime));
    }, 5000);

    window.addEventListener("pagehide", () => {
      localStorage.setItem(TIME_KEY, String(audio.currentTime));
    });
    document.addEventListener("visibilitychange", () => {
      if (document.visibilityState === "hidden") {
        localStorage.setItem(TIME_KEY, String(audio.currentTime));
      }
    });

    setMusicState(savedOn);

    if (musicToggle) {
      musicToggle.addEventListener("click", () => toggleMute());
    }

    if (volSlider) {
      volSlider.addEventListener("input", () => {
        const vol = parseInt(volSlider.value, 10) / 100;
        musicMuted  = vol === 0;
        audio.muted = musicMuted;
        applyVolume(vol);
        localStorage.setItem(VOL_KEY, String(vol));
        localStorage.setItem(MUTE_KEY, musicMuted ? "true" : "false");
        updateMusicIcon();
      });
    }
  }


  (function () {
    if (window.matchMedia("(hover: none)").matches) return;
    const el = document.createElement("div");
    el.id = "custom-cursor";
    document.body.appendChild(el);

    const INTERACTIVE = "a, button, [role='button'], input, textarea, select, label";
    let mouseX = 0, mouseY = 0, cursorRaf = false;
    document.addEventListener("mousemove", (e) => {
      mouseX = e.clientX;
      mouseY = e.clientY;
      if (!cursorRaf) {
        cursorRaf = true;
        requestAnimationFrame(() => {
          el.style.left = mouseX + "px";
          el.style.top  = mouseY + "px";
          cursorRaf = false;
        });
      }
    });
    document.addEventListener("mousedown", () => el.classList.add("clicking"));
    document.addEventListener("mouseup",   () => el.classList.remove("clicking"));
    document.addEventListener("mouseover", (e) => {
      if (e.target.closest(INTERACTIVE)) el.classList.add("hovering");
    });
    document.addEventListener("mouseout", (e) => {
      if (e.target.closest(INTERACTIVE) && !e.relatedTarget?.closest(INTERACTIVE)) {
        el.classList.remove("hovering");
      }
    });
    document.addEventListener("visibilitychange", () => {
      if (document.visibilityState === "hidden") el.classList.remove("hovering", "clicking");
    });
  })();

  (function () {
    const nav = document.querySelector(".navbar");
    if (!nav) return;

    let scrollRaf = false;
    function onScroll() {
      if (scrollRaf) return;
      scrollRaf = true;
      requestAnimationFrame(() => {
        scrollRaf = false;
        if (!nav.classList.contains("navbar-hero")) return;
        nav.classList.toggle("navbar-scrolled", window.scrollY > 150);
        const hero = document.getElementById("hero-full");
        if (hero) {
          const progress = Math.min(1, window.scrollY / (window.innerHeight * 0.45));
          const fadeStop = Math.round(100 - progress * 24);
          const mask = `linear-gradient(to bottom, black ${fadeStop}%, transparent 100%)`;
          hero.style.webkitMaskImage = mask;
          hero.style.maskImage       = mask;
        }
      });
    }

    function syncNavHero() {
      if (document.getElementById("hero-full")) {
        // Classe já pode vir do PHP — apenas garante o estado correto
        nav.classList.add("navbar-hero");
        // Ativa transições só após o primeiro frame para não animar no load inicial
        requestAnimationFrame(() => nav.classList.add("navbar-transitions-ready"));
        onScroll();
      } else {
        nav.classList.remove("navbar-hero", "navbar-scrolled", "navbar-transitions-ready");
      }
    }

    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("pjax:loaded", syncNavHero);
    syncNavHero();
  })();
});
