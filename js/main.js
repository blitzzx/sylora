document.addEventListener("DOMContentLoaded", () => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const prefersReducedMotion = () =>
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  // ===== initPageContent — executado em cada navegação PJAX
  function initPageContent(root) {
    // Auto-dismiss de alerts
    $$(".alert", root).forEach((a) => {
      window.setTimeout(() => {
        a.style.transition = prefersReducedMotion() ? "none" : "opacity 200ms ease";
        a.style.opacity = "0";
        window.setTimeout(() => a.remove(), prefersReducedMotion() ? 0 : 220);
      }, 5000);
    });

    // Evitar double submit
    $$("form", root).forEach((form) => {
      form.addEventListener("submit", () => {
        const submit = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submit) {
          submit.disabled = true;
          submit.dataset.originalText = submit.textContent || submit.value || "";
          if (submit.tagName.toLowerCase() === "button") submit.textContent = "A enviar...";
          else submit.value = "A enviar...";
        }
      });
    });

    // Password visibility toggle
    const SVG_EYE     = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    const SVG_EYE_OFF = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
    $$('input[type="password"]', root).forEach((input) => {
      if (input.closest(".pw-wrap")) return;
      const wrap = document.createElement("div");
      wrap.className = "pw-wrap";
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(input);
      const toggle = document.createElement("button");
      toggle.type = "button";
      toggle.className = "pw-toggle";
      toggle.innerHTML = SVG_EYE;
      toggle.setAttribute("aria-label", "Mostrar password");
      toggle.addEventListener("click", () => {
        const isPassword = input.type === "password";
        input.type = isPassword ? "text" : "password";
        toggle.innerHTML = isPassword ? SVG_EYE_OFF : SVG_EYE;
        toggle.setAttribute("aria-label", isPassword ? "Esconder password" : "Mostrar password");
        input.focus();
      });
      wrap.appendChild(toggle);
    });

    // Validação: confirmar password
    const pw        = $('[id="password"]', root);
    const confirmPw = $('[id="confirm_password"]', root);
    if (pw && confirmPw) {
      const validate = () => {
        confirmPw.setCustomValidity(
          confirmPw.value && pw.value !== confirmPw.value ? "As passwords não coincidem." : ""
        );
      };
      pw.addEventListener("input", validate);
      confirmPw.addEventListener("input", validate);
    }

  }

  // Primeira inicialização
  initPageContent(document);

  // Re-init depois de cada navegação PJAX
  window.addEventListener("pjax:loaded", () => {
    const pjaxRoot = document.getElementById("pjax-root");
    if (pjaxRoot) initPageContent(pjaxRoot);
  });

  // ===== 1) Mobile nav toggle
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
  }

  // ===== 2) User Drawer
  const pillBtn     = document.getElementById("drawer-trigger");
  const drawer      = document.getElementById("user-drawer");
  const overlay     = document.getElementById("drawer-overlay");
  const closeBtn    = document.getElementById("drawer-close");
  const pillChevron = pillBtn ? pillBtn.querySelector("svg:last-child") : null;

  function openDrawer() {
    if (!drawer || !overlay) return;
    drawer.classList.add("open");
    overlay.classList.add("active");
    drawer.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    if (pillBtn) pillBtn.setAttribute("aria-expanded", "true");
    if (pillChevron) pillChevron.style.transform = "rotate(90deg)";
    if (closeBtn) closeBtn.focus();
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

  if (pillBtn && drawer && overlay && closeBtn) {
    pillBtn.addEventListener("click", () => {
      drawer.classList.contains("open") ? closeDrawer() : openDrawer();
    });
    closeBtn.addEventListener("click", closeDrawer);
    overlay.addEventListener("click", closeDrawer);
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && drawer && drawer.classList.contains("open")) closeDrawer();
    });
  }

  // ===== 3) Drawer accordion
  $$(".drawer-section-title").forEach((title) => {
    title.addEventListener("click", () => {
      const section    = title.closest(".drawer-section");
      const isExpanded = section.classList.contains("expanded");
      $$(".drawer-section").forEach((s) => s.classList.remove("expanded"));
      if (!isExpanded) section.classList.add("expanded");
    });
  });

  // ===== 4) Avatar preview
  const avatarInput   = document.getElementById("avatar");
  const avatarPreview = document.getElementById("avatar-preview");
  if (avatarInput && avatarPreview) {
    avatarInput.addEventListener("change", () => {
      const file = avatarInput.files[0];
      if (!file) return;
      if (file.size > 10 * 1024 * 1024) {
        if (typeof showToast === "function") showToast("Imagem demasiado grande (máx. 10MB).", "error");
        avatarInput.value = "";
        return;
      }
      const reader = new FileReader();
      reader.onload = (e) => {
        avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width:52px;height:52px;border-radius:50%;object-fit:cover;display:block;">`;
      };
      reader.readAsDataURL(file);
    });
  }

  // ===== 5) Tema
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

  // ===== 6) Música ambiente — persiste sem pause via PJAX
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
    if (volPct) volPct.textContent = Math.round(vol * 100) + "%";
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
    localStorage.setItem(MUTE_KEY, musicMuted ? "true" : "false");
    updateMusicIcon();
  }

  if (audio) {
    const savedOn     = localStorage.getItem(MUSIC_KEY) === "true";
    const savedMuted  = localStorage.getItem(MUTE_KEY) === "true";
    const savedTime   = parseFloat(localStorage.getItem(TIME_KEY) || "0");
    const savedVol    = parseFloat(localStorage.getItem(VOL_KEY) || "0.7");
    const safeVol     = isFinite(savedVol) ? Math.max(0, Math.min(1, savedVol)) : 0.7;

    musicMuted = savedMuted;
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
      musicToggle.addEventListener("click", toggleMute);
    }

    if (volSlider) {
      volSlider.addEventListener("input", () => {
        const vol = parseInt(volSlider.value, 10) / 100;
        applyVolume(vol);
        localStorage.setItem(VOL_KEY, String(vol));
        if (vol === 0) {
          musicMuted = true;
          audio.muted = true;
          localStorage.setItem(MUTE_KEY, "true");
        } else if (musicMuted) {
          musicMuted = false;
          audio.muted = false;
          localStorage.setItem(MUTE_KEY, "false");
        }
        updateMusicIcon();
      });
    }
  }

  // ===== 7) Cursor circular com inversão de cores
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

  // ===== 8) PJAX — navegação sem recarregar a página (música contínua)
  (function () {
    const PJAX_SKIP = new Set(["logout.php", "profile.php"]);

    function reExecScripts(root) {
      root.querySelectorAll("script").forEach((oldScript) => {
        const newScript = document.createElement("script");
        Array.from(oldScript.attributes).forEach((attr) =>
          newScript.setAttribute(attr.name, attr.value)
        );
        newScript.textContent = oldScript.textContent;
        oldScript.parentNode.replaceChild(newScript, oldScript);
      });
    }

    function updateNavActive(url) {
      const page = url.split("/").pop().split("?")[0] || "index.php";
      document.querySelectorAll(".nav-menu a, .nav-mobile-menu a, .drawer-nav-link").forEach((a) => {
        const aPage = (a.getAttribute("href") || "").split("/").pop().split("?")[0];
        a.classList.toggle("active", aPage === page);
      });
    }

    function pjaxGo(url) {
      const root = document.getElementById("pjax-root");
      if (!root) { window.location.assign(url); return; }

      root.classList.add("pjax-loading");

      fetch(url, { headers: { "X-PJAX": "1" }, credentials: "same-origin" })
        .then((r) => r.text())
        .then((html) => {
          const doc     = new DOMParser().parseFromString(html, "text/html");
          const newRoot = doc.getElementById("pjax-root");
          if (!newRoot) { window.location.assign(url); return; }

          root.innerHTML = newRoot.innerHTML;
          root.classList.remove("pjax-loading");
          closeDrawer();

          history.pushState({ pjax: true, url }, "", url);
          window.scrollTo({ top: 0, behavior: "instant" });

          updateNavActive(url);
          reExecScripts(root);
          window.dispatchEvent(new Event("pjax:loaded"));
        })
        .catch(() => window.location.assign(url));
    }

    document.addEventListener("click", (e) => {
      const link = e.target.closest("a[href]");
      if (!link) return;
      const href = link.getAttribute("href");
      if (!href || href.startsWith("#") || href.startsWith("mailto:") || href.startsWith("tel:")) return;
      if (link.target && link.target !== "_self") return;
      if (link.hasAttribute("download")) return;

      let url;
      try { url = new URL(link.href, location.origin); } catch { return; }
      if (url.origin !== location.origin) return;

      const page = url.pathname.split("/").pop();
      if (PJAX_SKIP.has(page)) return;

      e.preventDefault();
      if (link.href === location.href) return;
      pjaxGo(link.href);
    });

    window.addEventListener("popstate", () => pjaxGo(location.href));
  })();

  // ===== Save helpers (jogar.php)
  function importSave(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      let text = e.target.result.replace(/\0/g, "").trim();
      try {
        displaySaveData(JSON.parse(text));
      } catch (err) {
        if (typeof showToast === "function") showToast("Ficheiro de save inválido ou corrompido.", "error");
        console.error(err);
      }
    };
    reader.readAsText(file, "utf-8");
  }

  function displaySaveData(data) {
    const s = data.stats;
    console.log("Nível:", s.lvl, "| HP:", s.hp, "/", s.hp_total,
                "| XP:", s.xp, "/", s.xp_req, "| Sala:", s.save_rm);
  }

  function exportSave(saveData) {
    const blob = new Blob([JSON.stringify(saveData) + "\0"], { type: "application/octet-stream" });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement("a");
    a.href = url; a.download = "syloradata.sav"; a.click();
    URL.revokeObjectURL(url);
  }

  // ===== 9) Navbar transparente no hero — scroll reveal
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
        nav.classList.add("navbar-hero");
        onScroll();
      } else {
        nav.classList.remove("navbar-hero", "navbar-scrolled");
      }
    }

    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("pjax:loaded", syncNavHero);
    syncNavHero();
  })();
});
