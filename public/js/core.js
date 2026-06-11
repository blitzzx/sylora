document.addEventListener("DOMContentLoaded", () => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const prefersReducedMotion = () =>
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;


  function initPageContent(root) {

    $$(".alert", root).forEach((a) => {
      window.setTimeout(() => {
        a.style.transition = prefersReducedMotion() ? "none" : "opacity 200ms ease";
        a.style.opacity = "0";
        window.setTimeout(() => a.remove(), prefersReducedMotion() ? 0 : 220);
      }, 5000);
    });


    $$("form", root).forEach((form) => {
      form.addEventListener("submit", () => {
        const submit = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submit) {
          submit.disabled = true;
          submit.dataset.originalText = submit.textContent || submit.value || "";
          const txt = (window.SYLORA_T ? window.SYLORA_T("common.sending") : "A enviar...");
          if (submit.tagName.toLowerCase() === "button") submit.textContent = txt;
          else submit.value = txt;
        }
      });
    });


    const SVG_EYE     = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    const SVG_EYE_OFF = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
    const TPW = (k) => (window.SYLORA_T ? window.SYLORA_T(k) : k);
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
      toggle.setAttribute("aria-label", TPW("common.show_pw"));
      toggle.addEventListener("click", () => {
        const isPassword = input.type === "password";
        input.type = isPassword ? "text" : "password";
        toggle.innerHTML = isPassword ? SVG_EYE_OFF : SVG_EYE;
        toggle.setAttribute("aria-label", isPassword ? TPW("common.hide_pw") : TPW("common.show_pw"));
        input.focus();
      });
      wrap.appendChild(toggle);
    });


    const pw        = $('[id="password"]', root);
    const confirmPw = $('[id="confirm_password"]', root);
    if (pw && confirmPw) {
      const validate = () => {
        confirmPw.setCustomValidity(
          confirmPw.value && pw.value !== confirmPw.value ? TPW("err.pw_mismatch") : ""
        );
      };
      pw.addEventListener("input", validate);
      confirmPw.addEventListener("input", validate);
    }

  }


  initPageContent(document);


  window.addEventListener("pjax:loaded", () => {
    const pjaxRoot = document.getElementById("pjax-root");
    if (pjaxRoot) initPageContent(pjaxRoot);
  });

  // Refresh dynamic aria-labels on language change
  document.addEventListener("sylora:langchange", (e) => {
    const T = (k) => (e.detail.dict[k] !== undefined ? e.detail.dict[k] : k);
    $$(".pw-toggle").forEach((btn) => {
      const isShown = btn.closest(".pw-wrap")?.querySelector("input")?.type === "text";
      btn.setAttribute("aria-label", isShown ? T("common.hide_pw") : T("common.show_pw"));
    });
  });

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


      if (pjaxController) pjaxController.abort();
      pjaxController = new AbortController();

      root.classList.add("pjax-loading");


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
          if (typeof window.closeDrawer === "function") window.closeDrawer();


          if (url !== location.href) {
            history.pushState({ pjax: true, url }, "", url);
          }


          let targetHash = "";
          try { targetHash = new URL(url, location.origin).hash; } catch {  }
          if (targetHash) {
            const el = document.getElementById(targetHash.slice(1));
            if (el) {
              requestAnimationFrame(() => el.scrollIntoView({ behavior: "smooth", block: "start" }));
            } else {
              window.scrollTo({ top: 0, behavior: "instant" });
            }
          } else {
            window.scrollTo({ top: 0, behavior: "instant" });
          }

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


      const curr = new URL(location.href);
      if (url.pathname === curr.pathname && url.search === curr.search && url.hash) {
        const el = document.getElementById(url.hash.slice(1));
        if (el) {
          history.pushState({ pjax: true, url: url.href }, "", url.href);
          el.scrollIntoView({ behavior: "smooth", block: "start" });
          return;
        }
      }

      pjaxGo(link.href);
    });

    window.addEventListener("popstate", () => pjaxGo(location.href));
  })();
});
