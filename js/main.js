document.addEventListener("DOMContentLoaded", () => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const prefersReducedMotion = () =>
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  // ===== initPageContent: executado em cada navegação PJAX
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
    navMenu.addEventListener("click", (e) => {
      if (e.target.closest("a")) {
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

  // ===== 4) Avatar crop modal
  (function () {
    const trigger    = document.getElementById("drawer-avatar-trigger");
    const fileInput  = document.getElementById("avatar-file-input");
    const modal      = document.getElementById("avatar-crop-modal");
    const canvas     = document.getElementById("avatar-crop-canvas");
    const zoomSlider = document.getElementById("avatar-crop-zoom");
    const cancelBtn  = document.getElementById("avatar-crop-cancel");
    const confirmBtn = document.getElementById("avatar-crop-confirm");
    const csrfInput  = document.getElementById("avatar-csrf-token");
    const zoomFill   = document.getElementById("crop-zoom-fill");
    const zoomThumb  = document.getElementById("crop-zoom-thumb");

    if (!trigger || !fileInput || !modal || !canvas) return;

    const ctx = canvas.getContext("2d");
    let SIZE = 280;

    let img = null, zoom = 1, offsetX = 0, offsetY = 0;
    let dragStart = null, pinchStart = null, pinchZoomStart = 1;

    function computeSize() {
      SIZE = Math.min(380, Math.max(200, window.innerWidth - 76));
      canvas.width  = SIZE;
      canvas.height = SIZE;
    }

    trigger.addEventListener("click", () => fileInput.click());

    fileInput.addEventListener("change", () => {
      const file = fileInput.files[0];
      if (!file) return;
      if (file.size > 10 * 1024 * 1024) {
        showToast("Imagem demasiado grande (máx. 10MB).", "error");
        fileInput.value = "";
        return;
      }
      const reader = new FileReader();
      reader.onload = (ev) => {
        const image = new Image();
        image.onload = () => {
          img = image;
          computeSize();
          resetView();
          modal.setAttribute("aria-hidden", "false");
          modal.classList.add("open");
        };
        image.src = ev.target.result;
      };
      reader.readAsDataURL(file);
      fileInput.value = "";
    });

    function updateZoomTrack() {
      if (!zoomSlider) return;
      const min = parseFloat(zoomSlider.min);
      const max = parseFloat(zoomSlider.max);
      const val = parseFloat(zoomSlider.value);
      const pct = ((val - min) / (max - min)) * 100;
      if (zoomFill)  zoomFill.style.width = pct + "%";
      if (zoomThumb) zoomThumb.style.left  = pct + "%";
    }

    function resetView() {
      zoom = Math.max(SIZE / img.width, SIZE / img.height);
      if (zoomSlider) {
        zoomSlider.min   = zoom;
        zoomSlider.max   = zoom * 4;
        zoomSlider.step  = zoom / 100;
        zoomSlider.value = zoom;
        updateZoomTrack();
      }
      offsetX = (SIZE - img.width  * zoom) / 2;
      offsetY = (SIZE - img.height * zoom) / 2;
      drawCrop();
    }

    function clampOffset() {
      if (!img) return;
      offsetX = Math.min(0, Math.max(SIZE - img.width  * zoom, offsetX));
      offsetY = Math.min(0, Math.max(SIZE - img.height * zoom, offsetY));
    }

    function drawCrop() {
      if (!img) return;
      ctx.clearRect(0, 0, SIZE, SIZE);
      ctx.drawImage(img, offsetX, offsetY, img.width * zoom, img.height * zoom);

      // Dark overlay only outside the preview circle (nonzero winding: CW rect + CCW arc)
      ctx.save();
      ctx.beginPath();
      ctx.rect(0, 0, SIZE, SIZE);
      ctx.arc(SIZE / 2, SIZE / 2, SIZE / 2 - 4, 0, Math.PI * 2, true);
      ctx.fillStyle = "rgba(0,0,0,0.55)";
      ctx.fill();
      ctx.restore();

      // Gold ring
      ctx.save();
      ctx.strokeStyle = "rgba(201,153,58,0.85)";
      ctx.lineWidth   = 2;
      ctx.beginPath();
      ctx.arc(SIZE / 2, SIZE / 2, SIZE / 2 - 4, 0, Math.PI * 2);
      ctx.stroke();
      ctx.restore();
    }

    function applyZoom(newZoom, pivotCanvasX, pivotCanvasY) {
      const min = zoomSlider ? parseFloat(zoomSlider.min) : zoom;
      const max = zoomSlider ? parseFloat(zoomSlider.max) : zoom * 4;
      newZoom = Math.min(max, Math.max(min, newZoom));
      const ratio = newZoom / zoom;
      offsetX = pivotCanvasX - (pivotCanvasX - offsetX) * ratio;
      offsetY = pivotCanvasY - (pivotCanvasY - offsetY) * ratio;
      zoom = newZoom;
      if (zoomSlider) { zoomSlider.value = zoom; updateZoomTrack(); }
      clampOffset();
      drawCrop();
    }

    if (zoomSlider) {
      zoomSlider.addEventListener("input", () => {
        const newZoom = parseFloat(zoomSlider.value);
        applyZoom(newZoom, SIZE / 2, SIZE / 2);
      });
    }

    // Mouse drag
    canvas.addEventListener("mousedown", (e) => {
      dragStart = { x: e.clientX - offsetX, y: e.clientY - offsetY };
      canvas.style.cursor = "grabbing";
    });
    window.addEventListener("mousemove", (e) => {
      if (!dragStart) return;
      offsetX = e.clientX - dragStart.x;
      offsetY = e.clientY - dragStart.y;
      clampOffset();
      drawCrop();
    });
    window.addEventListener("mouseup", () => {
      dragStart = null;
      canvas.style.cursor = "grab";
    });

    // Mouse wheel zoom
    canvas.addEventListener("wheel", (e) => {
      e.preventDefault();
      const rect      = canvas.getBoundingClientRect();
      const scaleX    = SIZE / rect.width;
      const scaleY    = SIZE / rect.height;
      const pivotX    = (e.clientX - rect.left) * scaleX;
      const pivotY    = (e.clientY - rect.top)  * scaleY;
      const delta     = e.deltaY > 0 ? -0.08 : 0.08;
      applyZoom(zoom * (1 + delta), pivotX, pivotY);
    }, { passive: false });

    // Touch: single-finger drag + two-finger pinch
    canvas.addEventListener("touchstart", (e) => {
      if (e.touches.length === 2) {
        const t1 = e.touches[0], t2 = e.touches[1];
        pinchStart     = Math.hypot(t2.clientX - t1.clientX, t2.clientY - t1.clientY);
        pinchZoomStart = zoom;
        dragStart      = null;
      } else {
        const t = e.touches[0];
        dragStart  = { x: t.clientX - offsetX, y: t.clientY - offsetY };
        pinchStart = null;
      }
    }, { passive: true });

    canvas.addEventListener("touchmove", (e) => {
      if (e.touches.length === 2 && pinchStart !== null) {
        const t1   = e.touches[0], t2 = e.touches[1];
        const dist = Math.hypot(t2.clientX - t1.clientX, t2.clientY - t1.clientY);
        const rect = canvas.getBoundingClientRect();
        const scaleX = SIZE / rect.width;
        const scaleY = SIZE / rect.height;
        const midX   = ((t1.clientX + t2.clientX) / 2 - rect.left) * scaleX;
        const midY   = ((t1.clientY + t2.clientY) / 2 - rect.top)  * scaleY;
        applyZoom(pinchZoomStart * (dist / pinchStart), midX, midY);
        e.preventDefault();
      } else if (e.touches.length === 1 && dragStart !== null) {
        const t = e.touches[0];
        offsetX = t.clientX - dragStart.x;
        offsetY = t.clientY - dragStart.y;
        clampOffset();
        drawCrop();
        e.preventDefault();
      }
    }, { passive: false });

    canvas.addEventListener("touchend", (e) => {
      if (e.touches.length < 2) pinchStart = null;
      if (e.touches.length === 1) {
        const t = e.touches[0];
        dragStart = { x: t.clientX - offsetX, y: t.clientY - offsetY };
      }
      if (e.touches.length === 0) dragStart = null;
    });

    function closeModal() {
      modal.setAttribute("aria-hidden", "true");
      modal.classList.remove("open");
      img = null;
    }

    if (cancelBtn) cancelBtn.addEventListener("click", closeModal);
    modal.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });

    if (confirmBtn) {
      confirmBtn.addEventListener("click", async () => {
        if (!img) return;
        const out    = document.createElement("canvas");
        out.width    = SIZE;
        out.height   = SIZE;
        const outCtx = out.getContext("2d");
        outCtx.beginPath();
        outCtx.arc(SIZE / 2, SIZE / 2, SIZE / 2, 0, Math.PI * 2);
        outCtx.clip();
        outCtx.drawImage(img, offsetX, offsetY, img.width * zoom, img.height * zoom);

        const origLabel = confirmBtn.innerHTML;
        confirmBtn.disabled    = true;
        confirmBtn.textContent = "A guardar…";

        out.toBlob(async (blob) => {
          const form = new FormData();
          form.append("action", "upload_avatar");
          form.append("_csrf",  csrfInput ? csrfInput.value : "");
          form.append("avatar", blob, "avatar.jpg");
          try {
            const res  = await fetch("/profile", {
              method: "POST",
              headers: { "X-Requested-With": "XMLHttpRequest" },
              body: form,
            });
            const data = await res.json();
            if (data.success) {
              showToast("Avatar atualizado!", "success");
              closeModal();
              setTimeout(() => location.reload(), 900);
            } else {
              showToast(data.message || "Erro ao guardar avatar.", "error");
            }
          } catch {
            showToast("Erro de ligação.", "error");
          } finally {
            confirmBtn.disabled  = false;
            confirmBtn.innerHTML = origLabel;
          }
        }, "image/jpeg", 0.92);
      });
    }
  })();

  // ===== 4b) Avatar fallback — imagens quebradas mostram a inicial com estilos corretos
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

  // ===== 6) Música ambiente: persiste sem pause via PJAX
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
    localStorage.setItem(MUTE_KEY, musicMuted ? "true" : "false");
    updateMusicIcon();
  }

  if (audio) {
    const savedOn     = localStorage.getItem(MUSIC_KEY) === "true";
    const savedMuted  = localStorage.getItem(MUTE_KEY) === "true";
    const savedTime   = parseFloat(localStorage.getItem(TIME_KEY) || "0");
    const savedVol    = parseFloat(localStorage.getItem(VOL_KEY) || "0.5");
    const safeVol     = isFinite(savedVol) ? Math.max(0, Math.min(1, savedVol)) : 0.5;

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

    const musicCtrl   = document.getElementById("music-ctrl");
    const volPopup    = document.getElementById("music-vol-popup");
    const isTouchOnly = window.matchMedia("(hover: none)").matches;
    let volPopupOpen  = false;

    function closeVolPopup() {
      volPopupOpen = false;
      if (volPopup) volPopup.classList.remove("music-vol-open");
    }

    if (musicToggle) {
      musicToggle.addEventListener("click", (e) => {
        if (isTouchOnly) {
          if (!musicOn) {
            setMusicState(true);
            return;
          }
          e.stopPropagation();
          volPopupOpen = !volPopupOpen;
          if (volPopup) volPopup.classList.toggle("music-vol-open", volPopupOpen);
        } else {
          toggleMute();
        }
      });
    }

    if (isTouchOnly) {
      document.addEventListener("click", (e) => {
        if (volPopupOpen && musicCtrl && !musicCtrl.contains(e.target)) {
          closeVolPopup();
        }
      });
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

  // ===== 8) PJAX: navegação sem recarregar a página (música contínua)
  (function () {
    const PJAX_SKIP = new Set(["logout.php", "logout", "profile.php"]);

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

    let pjaxController = null;

    function pjaxGo(url) {
      const root = document.getElementById("pjax-root");
      if (!root) { window.location.assign(url); return; }

      // Abort any in-flight request
      if (pjaxController) pjaxController.abort();
      pjaxController = new AbortController();

      root.classList.add("pjax-loading");

      // Safety net: remove loading state if navigation gets stuck
      const safetyTimer = setTimeout(() => root.classList.remove("pjax-loading"), 8000);

      fetch(url, {
        headers: { "X-PJAX": "1" },
        credentials: "same-origin",
        signal: pjaxController.signal,
      })
        .then((r) => r.text())
        .then((html) => {
          clearTimeout(safetyTimer);
          pjaxController = null;

          const doc     = new DOMParser().parseFromString(html, "text/html");
          const newRoot = doc.getElementById("pjax-root");
          if (!newRoot) {
            root.classList.remove("pjax-loading");
            window.location.assign(url);
            return;
          }

          if (root.dataset.auth !== newRoot.dataset.auth) {
            root.classList.remove("pjax-loading");
            window.location.assign(url);
            return;
          }

          root.innerHTML = newRoot.innerHTML;
          root.classList.remove("pjax-loading");
          const siteFooter = document.querySelector(".site-footer");
          if (siteFooter) siteFooter.style.display = root.querySelector("[data-no-footer]") ? "none" : "";
          closeDrawer();

          // Only push state when navigating to a new URL (not on popstate)
          if (url !== location.href) {
            history.pushState({ pjax: true, url }, "", url);
          }
          window.scrollTo({ top: 0, behavior: "instant" });

          updateNavActive(url);
          reExecScripts(root);
          window.dispatchEvent(new Event("pjax:loaded"));
        })
        .catch((err) => {
          clearTimeout(safetyTimer);
          pjaxController = null;
          if (err.name === "AbortError") return;
          root.classList.remove("pjax-loading");
          window.location.assign(url);
        });
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

  // ===== 9) Navbar transparente no hero: scroll reveal
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
