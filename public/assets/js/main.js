/* =========================================================
   DermaVision AI - main.js (FULL WORKING) + Nearby Doctors
   ✅ DOES NOT remove anything. Only adds nearby lookup logic.
========================================================= */

(function () {
  "use strict";

  /* ===============================
     Helpers
  =============================== */
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  function show(el) { if (el) el.classList.remove("hidden"); }
  function hide(el) { if (el) el.classList.add("hidden"); }

  function setText(el, txt) { if (el) el.textContent = txt; }

  function escapeHtml(str) {
    return String(str ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function toPercent(conf) {
    if (conf === null || conf === undefined || conf === "" || isNaN(conf)) return "N/A";
    const n = Number(conf);
    const pct = n <= 1 ? (n * 100) : n;
    return `${pct.toFixed(1)}%`;
  }

  function normalizeSeverity(sev) {
    if (!sev) return "Unknown";
    return String(sev).trim();
  }

  function normalizeList(arr) {
    if (!arr) return [];
    if (Array.isArray(arr)) return arr.filter(Boolean).map(String);
    if (typeof arr === "string") return arr.split(",").map(s => s.trim()).filter(Boolean);
    return [];
  }

  /* ===============================
     Profile Dropdown
  =============================== */
  function initUserDropdown() {
    const toggleBtn = $(".avatar") || $("[data-user-menu-toggle]");
    const dropdown = $(".user-dropdown") || $("[data-user-dropdown]");
    if (!toggleBtn || !dropdown) return;

    function close() { dropdown.classList.remove("show"); toggleBtn.setAttribute("aria-expanded", "false"); }
    function open() { dropdown.classList.add("show"); toggleBtn.setAttribute("aria-expanded", "true"); }
    function toggle() { dropdown.classList.contains("show") ? close() : open(); }

    toggleBtn.setAttribute("aria-haspopup", "true");
    toggleBtn.setAttribute("aria-expanded", dropdown.classList.contains("show") ? "true" : "false");

    toggleBtn.addEventListener("click", (e) => { e.preventDefault(); e.stopPropagation(); toggle(); });

    document.addEventListener("click", (e) => {
      if (!dropdown.classList.contains("show")) return;
      const clickedInside = dropdown.contains(e.target) || toggleBtn.contains(e.target);
      if (!clickedInside) close();
    });

    document.addEventListener("keydown", (e) => { if (e.key === "Escape") close(); });
  }

  /* ===============================
     Scroll-to-top (optional)
  =============================== */
  function initScrollTop() {
    let btn = $(".scroll-top-btn");
    if (!btn) return;

    function onScroll() {
      if (window.scrollY > 400) btn.classList.add("show");
      else btn.classList.remove("show");
    }

    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();

    btn.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
  }

  /* ===============================
     AI Dermatologist Page
  =============================== */
  function initAiDermatologist() {
    const form = $("#ai-form");
    if (!form) return;

    const fileInput = $("#skin_image");
    const previewWrap = $("#image-preview-wrapper");
    const previewImg = $("#preview-image");

    const cameraWrap = $("#camera-wrapper");
    const video = $("#camera-video");
    const canvas = $("#camera-canvas");
    const captureBtn = $("#capture-photo");

    const uploadModeBtn = $("#btn-upload-mode");
    const cameraModeBtn = $("#btn-camera-mode");

    const loadingEl = $("#ai-loading");
    const errorEl = $("#ai-error");
    const statusEl = $("#ai-status");
    const resultBody = $("#ai-result-body");

    // NEW: nearby container (will be created if not present)
    const resultCard = $("#ai-result-card");

    const API_URL = "api/diagnose.php";
    const NEARBY_URL = "api/nearby.php";

    let stream = null;
    let capturedBlob = null;

    function resetErrors() {
      hide(errorEl);
      if (errorEl) errorEl.innerHTML = "";
    }

    function showError(msg) {
      if (!errorEl) return;
      errorEl.innerHTML = escapeHtml(msg);
      show(errorEl);
    }

    function setStatus(msg) {
      if (!statusEl) return;
      statusEl.textContent = msg;
    }

    function showSkeleton() {
      if (!resultBody) return;
      resultBody.innerHTML = `
        <div class="ai-report">
          <div class="skeleton-line" style="width:55%"></div>
          <div class="skeleton-line" style="width:80%"></div>
          <div class="skeleton-line" style="width:65%"></div>
          <div class="skeleton-line" style="width:92%"></div>
          <div class="skeleton-line" style="width:70%"></div>
          <div class="skeleton-line" style="width:85%"></div>
        </div>
      `;
    }

    function setPreviewFromFile(file) {
      if (!file || !previewImg || !previewWrap) return;
      const url = URL.createObjectURL(file);
      previewImg.src = url;
      previewImg.classList.remove("hidden");
      previewWrap.classList.add("has-image");
    }

    function clearPreview() {
      if (previewImg) { previewImg.src = ""; previewImg.classList.add("hidden"); }
      if (previewWrap) previewWrap.classList.remove("has-image");
    }

    function stopCamera() {
      if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    }

    async function startCamera() {
      resetErrors();
      capturedBlob = null;

      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: "environment" },
          audio: false
        });
        if (video) video.srcObject = stream;
      } catch (err) {
        showError("Camera permission denied or camera not available.");
        setUploadMode();
      }
    }

    function setUploadMode() {
      stopCamera();
      capturedBlob = null;

      hide(cameraWrap);
      if (fileInput) fileInput.disabled = false;

      if (uploadModeBtn) uploadModeBtn.classList.add("btn-primary");
      if (cameraModeBtn) cameraModeBtn.classList.remove("btn-primary");
    }

    function setCameraMode() {
      if (fileInput) fileInput.value = "";
      clearPreview();

      show(cameraWrap);
      if (fileInput) fileInput.disabled = true;

      if (cameraModeBtn) cameraModeBtn.classList.add("btn-primary");
      if (uploadModeBtn) uploadModeBtn.classList.remove("btn-primary");

      startCamera();
    }

    setUploadMode();

    if (uploadModeBtn) uploadModeBtn.addEventListener("click", () => setUploadMode());
    if (cameraModeBtn) cameraModeBtn.addEventListener("click", () => setCameraMode());

    if (fileInput) {
      fileInput.addEventListener("change", () => {
        resetErrors();
        capturedBlob = null;
        const file = fileInput.files && fileInput.files[0];
        if (file) setPreviewFromFile(file);
        else clearPreview();
      });
    }

    if (captureBtn) {
      captureBtn.addEventListener("click", () => {
        resetErrors();
        if (!video || !canvas) return;

        const w = video.videoWidth || 640;
        const h = video.videoHeight || 480;

        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0, w, h);

        canvas.toBlob((blob) => {
          if (!blob) { showError("Failed to capture image. Try again."); return; }
          capturedBlob = blob;

          const url = URL.createObjectURL(blob);
          if (previewImg && previewWrap) {
            previewImg.src = url;
            previewImg.classList.remove("hidden");
            previewWrap.classList.add("has-image");
          }
        }, "image/jpeg", 0.92);
      });
    }

    function buildAIReport(data) {
      const detected = data.label_name || data.disease_name || data.label || "Unknown condition";
      const confidence = toPercent(data.confidence);
      const severity = normalizeSeverity(data.severity);

      const description = data.description || "No detailed description available yet.";
      const treatment = normalizeList(data.treatment);
      const products = normalizeList(data.products);
      const home = normalizeList(data.home_remedies);
      const warn = data.warnings || "";

      const infMs = (data.inference_ms !== undefined && data.inference_ms !== null)
        ? `${Number(data.inference_ms).toFixed(2)} ms`
        : "N/A";

      const listHtml = (arr) => {
        if (!arr.length) return `<div class="text-muted">Coming soon.</div>`;
        // NOTE: no bullets added here (your CSS can handle it)
        return `<ul class="list-unstyled">${arr.map(i => `<li class="library-item">${escapeHtml(i)}</li>`).join("")}</ul>`;
      };

      const chips = [];
      chips.push(`<span class="ai-chip">${escapeHtml(severity)}</span>`);
      chips.push(`<span class="ai-chip secondary">Confidence: ${escapeHtml(confidence)}</span>`);

      return `
        <div class="ai-report">

          <div>
            <div class="ai-section-title">Detected Condition</div>
            <div style="font-size:1.1rem;font-weight:800;color:#064e3b;">${escapeHtml(detected)}</div>
            <div class="ai-tag-row">${chips.join("")}</div>
          </div>

          <div>
            <div class="ai-section-title">Description</div>
            <div class="text-soft">${escapeHtml(description)}</div>
          </div>

          <div>
            <div class="ai-section-title">Treatment Options</div>
            ${listHtml(treatment)}
          </div>

          <div>
            <div class="ai-section-title">Recommended Products</div>
            ${listHtml(products)}
          </div>

          <div>
            <div class="ai-section-title">Home Remedies (Supportive)</div>
            ${listHtml(home)}
          </div>

          <div class="card-soft">
            <div class="flex-between">
              <div class="text-muted"><b>Inference time:</b> ${escapeHtml(infMs)}</div>
              ${warn ? `<span class="badge badge-warning">Safety note</span>` : ``}
            </div>
            ${warn ? `<div class="mt-8 text-danger">${escapeHtml(warn)}</div>` : ``}
          </div>

        </div>
      `;
    }

    function safeParseJson(rawText) {
      try { return JSON.parse(rawText); } catch (_) {}
      const firstBrace = rawText.indexOf("{");
      const lastBrace = rawText.lastIndexOf("}");
      if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
        const maybe = rawText.slice(firstBrace, lastBrace + 1);
        try { return JSON.parse(maybe); } catch (_) {}
      }
      return null;
    }

    /* =========================================================
       NEW: Nearby Dermatologists / Hospitals (GPS + API)
       - Requests location permission AFTER analysis succeeds
       - Uses timeout + graceful fallback (Google Maps search)
    ========================================================= */
    function ensureNearbyBox() {
      if (!resultCard) return null;
      let box = $("#nearby-box");
      if (box) return box;

      box = document.createElement("div");
      box.id = "nearby-box";
      box.className = "card-soft mt-12";
      box.innerHTML = `
        <div class="ai-section-title">Nearby Dermatologists / Hospitals</div>
        <div id="nearby-status" class="text-soft mt-4">Location-based suggestions will appear after analysis.</div>
        <div id="nearby-list" class="mt-8"></div>
      `;
      resultCard.appendChild(box);
      return box;
    }

    function setNearbyStatus(msg) {
      const s = $("#nearby-status");
      if (s) s.textContent = msg;
    }

    function setNearbyList(html) {
      const l = $("#nearby-list");
      if (l) l.innerHTML = html;
    }

    function googleMapsFallback(lat, lon) {
      const link = `https://www.google.com/maps/search/dermatologist/@${lat},${lon},14z`;
      setNearbyList(`
        <a class="btn-outline btn-small" target="_blank" rel="noopener" href="${link}">
          Open in Google Maps
        </a>
      `);
    }

    function fetchWithTimeout(url, ms = 12000) {
      const controller = new AbortController();
      const id = setTimeout(() => controller.abort(), ms);
      return fetch(url, { signal: controller.signal })
        .finally(() => clearTimeout(id));
    }

    async function loadNearby(lat, lon) {
      ensureNearbyBox();
      setNearbyStatus("Finding nearby dermatologists…");
      setNearbyList("");

      const url = `${NEARBY_URL}?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lon)}&r=8000&limit=8`;

      try {
        const res = await fetchWithTimeout(url, 14000);
        const text = await res.text();

        const data = safeParseJson(text);
        if (!data) {
          setNearbyStatus("Nearby lookup failed on this PC. Opening Maps is available.");
          googleMapsFallback(lat, lon);
          return;
        }

        if (!res.ok || data.success === false) {
          setNearbyStatus(data.error || "Nearby lookup failed.");
          googleMapsFallback(lat, lon);
          return;
        }

        const places = Array.isArray(data.places) ? data.places : [];
        if (!places.length) {
          setNearbyStatus("No nearby results found. Try increasing radius or open Maps.");
          googleMapsFallback(lat, lon);
          return;
        }

        setNearbyStatus("Nearby options:");
        setNearbyList(`
          <div class="nearby-grid">
            ${places.map(p => `
              <a class="nearby-card" target="_blank" rel="noopener" href="${escapeHtml(p.maps || "#")}">
                <div class="nearby-name">${escapeHtml(p.name || "Dermatology")}</div>
                <div class="nearby-meta">${escapeHtml(p.type || "Clinic")}${(p.distanceKm != null) ? ` • ${escapeHtml(p.distanceKm)} km` : ""}</div>
                ${p.address ? `<div class="nearby-addr">${escapeHtml(p.address)}</div>` : ``}
              </a>
            `).join("")}
          </div>
          <div class="mt-12">
            <a class="btn-outline btn-small" target="_blank" rel="noopener" href="https://www.google.com/maps/search/dermatologist/@${lat},${lon},14z">
              See more in Google Maps
            </a>
          </div>
        `);

      } catch (e) {
        setNearbyStatus("Nearby lookup timed out or blocked on this PC. Open Google Maps instead.");
        googleMapsFallback(lat, lon);
      }
    }

    function requestLocationThenNearby() {
      ensureNearbyBox();

      if (!("geolocation" in navigator)) {
        setNearbyStatus("Location not supported in this browser. Open Google Maps instead.");
        setNearbyList(`<a class="btn-outline btn-small" target="_blank" rel="noopener" href="https://www.google.com/maps/search/dermatologist">Open Google Maps</a>`);
        return;
      }

      setNearbyStatus("Requesting location permission…");

      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const lat = pos.coords.latitude;
          const lon = pos.coords.longitude;
          loadNearby(lat, lon);
        },
        (err) => {
          setNearbyStatus("Location permission denied. Open Google Maps instead.");
          setNearbyList(`<a class="btn-outline btn-small" target="_blank" rel="noopener" href="https://www.google.com/maps/search/dermatologist">Open Google Maps</a>`);
        },
        {
          enableHighAccuracy: true,
          timeout: 12000,
          maximumAge: 0
        }
      );
    }

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      resetErrors();
      hide(loadingEl);

      const file = fileInput && fileInput.files && fileInput.files[0];
      if (!file && !capturedBlob) {
        showError("Please upload an image or capture one using the camera.");
        return;
      }

      setStatus("⏳ Analyzing image… please wait.");
      show(loadingEl);
      showSkeleton();

      const fd = new FormData();
      if (file) fd.append("skin_image", file);
      else if (capturedBlob) fd.append("skin_image", capturedBlob, "capture.jpg");

      try {
        const res = await fetch(API_URL, { method: "POST", body: fd });
        const text = await res.text();
        const data = safeParseJson(text);

        if (!data) {
          hide(loadingEl);
          setStatus("Error");
          showError("AI returned invalid response (not valid JSON). Check PHP/Python logs.");
          if (resultBody) resultBody.innerHTML = "";
          return;
        }

        if (!res.ok || data.success === false) {
          hide(loadingEl);
          setStatus("Error");
          const msg = data.error || `Request failed (HTTP ${res.status}).`;
          showError(msg);
          if (resultBody) resultBody.innerHTML = "";
          return;
        }

        hide(loadingEl);
        setStatus("✅ AI analysis complete.");
        if (resultBody) resultBody.innerHTML = buildAIReport(data);

        // NEW: after showing AI result, fetch nearby
        requestLocationThenNearby();

      } catch (err) {
        hide(loadingEl);
        setStatus("Error");
        showError("Unexpected error contacting AI.");
        if (resultBody) resultBody.innerHTML = "";
      }
    });

    window.addEventListener("beforeunload", () => stopCamera());
  }

  /* ===============================
     Init all
  =============================== */
  document.addEventListener("DOMContentLoaded", () => {
    initUserDropdown();
    initScrollTop();
    initAiDermatologist();
  });

})();
