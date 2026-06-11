document.addEventListener("DOMContentLoaded", () => {
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

    if (!fileInput || !modal || !canvas) return;

    const ctx = canvas.getContext("2d");
    let SIZE = 280;

    let img = null, zoom = 1, offsetX = 0, offsetY = 0;
    let dragStart = null, pinchStart = null, pinchZoomStart = 1;

    function computeSize() {
      SIZE = Math.min(380, Math.max(200, window.innerWidth - 76));
      canvas.width  = SIZE;
      canvas.height = SIZE;
    }

    if (trigger) trigger.addEventListener("click", () => fileInput.click());

    fileInput.addEventListener("change", () => {
      const file = fileInput.files[0];
      if (!file) return;
      if (file.size > 10 * 1024 * 1024) {
        showToast(window.SYLORA_T("toast.avatar_too_big"), "error");
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


      ctx.save();
      ctx.beginPath();
      ctx.rect(0, 0, SIZE, SIZE);
      ctx.arc(SIZE / 2, SIZE / 2, SIZE / 2 - 4, 0, Math.PI * 2, true);
      ctx.fillStyle = "rgba(0,0,0,0.55)";
      ctx.fill();
      ctx.restore();


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
        confirmBtn.textContent = window.SYLORA_T("common.saving");

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
              showToast(window.SYLORA_T("toast.avatar_saved"), "success");
              closeModal();
              setTimeout(() => location.reload(), 900);
            } else {
              showToast(data.message || window.SYLORA_T("toast.avatar_error"), "error");
            }
          } catch {
            showToast(window.SYLORA_T("toast.connection_error"), "error");
          } finally {
            confirmBtn.disabled  = false;
            confirmBtn.innerHTML = origLabel;
          }
        }, "image/jpeg", 0.92);
      });
    }
  })();
});
