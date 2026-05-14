<?php
// FILE: /app/views/public/tour.php
?>

<?php
  // Detect & shape the 3D scene URL (if any)
  $splat = !empty($tour['splat_url']) ? splatEmbedInfo($tour['splat_url']) : null;
  $hasSplat = $splat && $splat['supports_iframe'];
?>

<!-- Viewer top bar -->
<nav class="viewer-nav">
  <div class="viewer-brand">360<span class="deg">°</span></div>

  <?php if ($hasSplat): ?>
  <!-- Mode toggle: 360° tour vs 3D scene -->
  <div class="view-mode-toggle">
    <button type="button" data-mode="pano" class="vmt-btn is-active">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
      360° tour
    </button>
    <button type="button" data-mode="splat" class="vmt-btn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
      3D scene
    </button>
  </div>
  <?php endif; ?>

  <div style="flex:1;"></div>
  <div style="display:flex;flex-direction:column;align-items:flex-end;gap:2px;">
    <div style="font-size:13px;font-weight:600;color:var(--ink);"><?php echo e($tour['property_title']); ?></div>
    <div style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:var(--ink-3);">
      <?php echo e($tour['title']); ?>
    </div>
  </div>
</nav>

<!-- Panorama viewer (Pannellum mounts here) -->
<div class="viewer-stage">
  <div id="panorama-viewer"
       data-tour='<?php echo htmlspecialchars(json_encode($tour), ENT_QUOTES, 'UTF-8'); ?>'
       class="viewer-pano">

    <?php if (empty($tour['scenes'])): ?>
    <div class="viewer-msg">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"
           style="margin:0 auto 12px;display:block;color:var(--ink-4);">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
      </svg>
      No scenes uploaded yet
    </div>
    <?php endif; ?>

  </div>

  <?php if ($hasSplat): ?>
  <!-- 3D scene iframe — hidden until user toggles to it -->
  <div id="splat-frame-wrap" style="position:absolute;inset:52px 0 0 0;background:#000;display:none;z-index:5;">
    <iframe id="splat-frame"
            src="about:blank"
            data-src="<?php echo e($splat['embed']); ?>"
            allow="fullscreen; xr-spatial-tracking; accelerometer; gyroscope"
            allowfullscreen
            referrerpolicy="no-referrer"
            style="width:100%;height:100%;border:0;background:#000;"></iframe>
    <div id="splat-loading" style="position:absolute;inset:0;display:grid;place-items:center;color:var(--ink-3);font-family:var(--font-mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;background:#000;">
      Loading 3D scene…
    </div>
  </div>
  <?php endif; ?>

  <!-- Custom controls -->
  <div id="pano-ctrls" style="position:fixed;bottom:<?php echo !empty($tour['scenes']) ? '82px' : '24px'; ?>;right:24px;z-index:60;display:flex;flex-direction:column;gap:8px;">
    <button id="zoom-in"  class="viewer-ctrl" title="Zoom in">+</button>
    <button id="zoom-out" class="viewer-ctrl" title="Zoom out">&minus;</button>
    <button id="fullscreen" class="viewer-ctrl" title="Fullscreen" style="font-size:12px;">&#x26F6;</button>
  </div>

  <?php if (count($tour['scenes'] ?? []) > 1): ?>
  <!-- Big always-visible scene navigation arrows -->
  <button id="nav-prev" class="scene-nav scene-nav--left" title="Previous scene" aria-label="Previous scene">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
  </button>
  <button id="nav-next" class="scene-nav scene-nav--right" title="Next scene" aria-label="Next scene">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
  </button>
  <!-- Scene counter pill -->
  <div id="scene-counter"><span id="scene-current">1</span> / <?php echo count($tour['scenes']); ?></div>
  <?php endif; ?>

  <!-- First-load hint: tells new users they can drag/swipe to look around.
       Auto-hides after 3.5s or on first interaction. -->
  <div id="first-hint" class="first-hint" aria-hidden="true">
    <svg width="42" height="42" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="32" cy="32" r="9"/>
      <path d="M32 14v6M32 44v6M14 32h6M44 32h6"/>
    </svg>
    <div class="first-hint__text">Drag to look around</div>
  </div>
</div>

<!-- Scene strip (shown only when scenes exist) -->
<?php if (!empty($tour['scenes'])): ?>
<div id="scene-strip" style="background:rgba(10,9,8,0.92);backdrop-filter:blur(14px);border-top:1px solid var(--line);
     padding:10px 16px;display:flex;gap:8px;overflow-x:auto;position:fixed;bottom:0;left:0;right:0;z-index:50;
     scrollbar-width:thin;scrollbar-color:var(--line) transparent;">
  <?php foreach ($tour['scenes'] as $i => $scene): ?>
  <div class="scene-item" data-scene-id="<?php echo $scene['id']; ?>" data-scene-index="<?php echo $i; ?>"
       style="flex-shrink:0;cursor:pointer;border-radius:var(--r-sm);overflow:hidden;border:1px solid var(--line);
              transition:border-color 0.15s,opacity 0.15s;">
    <div style="width:76px;height:50px;background-image:url('<?php echo sceneImageUrl($scene['image_path']); ?>');
                background-size:cover;background-position:center;position:relative;">
      <div style="position:absolute;bottom:0;left:0;right:0;padding:3px 5px;background:rgba(0,0,0,0.65);
                  font-family:var(--font-mono);font-size:9px;letter-spacing:0.08em;text-transform:uppercase;
                  color:rgba(245,241,232,0.75);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        <?php echo e($scene['title'] ?? ''); ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Info panel (for info-type hotspots) -->
<div id="info-panel" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:200;
     background:var(--bg-elev);border:1px solid var(--line);border-radius:var(--r-lg);padding:28px 32px;
     min-width:300px;max-width:440px;box-shadow:0 24px 64px rgba(0,0,0,0.6);">
  <h3 id="info-title" style="font-family:var(--font-display);font-size:20px;color:var(--ink);margin-bottom:10px;"></h3>
  <p id="info-description" style="color:var(--ink-2);font-size:13px;line-height:1.65;"></p>
  <button id="close-info" class="btn" style="margin-top:16px;">Close</button>
</div>

<style>
/* Custom hotspot styles for Pannellum */
.pnlm-hotspot.hs-nav,
.pnlm-hotspot.hs-fwd,
.pnlm-hotspot.hs-back {
  background: rgba(201,169,97,0.18) !important;
  border: 1.5px solid rgba(201,169,97,0.6) !important;
  border-radius: 50% !important;
  width: 46px !important;
  height: 46px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  backdrop-filter: blur(8px) !important;
  cursor: pointer !important;
  transition: background 0.18s, transform 0.18s !important;
}
.pnlm-hotspot.hs-nav:hover,
.pnlm-hotspot.hs-fwd:hover,
.pnlm-hotspot.hs-back:hover {
  background: rgba(201,169,97,0.35) !important;
  transform: scale(1.12) !important;
}
/* Arrow icon via pseudo-element */
.pnlm-hotspot.hs-fwd::before,
.pnlm-hotspot.hs-nav::before {
  content: '' !important;
  display: block !important;
  width: 0 !important;
  height: 0 !important;
  border-left: 8px solid transparent !important;
  border-right: 8px solid transparent !important;
  border-bottom: 13px solid rgba(201,169,97,0.9) !important;
}
.pnlm-hotspot.hs-back::before {
  content: '' !important;
  display: block !important;
  width: 0 !important;
  height: 0 !important;
  border-left: 8px solid transparent !important;
  border-right: 8px solid transparent !important;
  border-top: 13px solid rgba(201,169,97,0.9) !important;
}
.pnlm-hotspot.hs-info {
  background: rgba(30,27,22,0.7) !important;
  border: 1.5px solid rgba(201,169,97,0.4) !important;
  border-radius: 50% !important;
  width: 32px !important;
  height: 32px !important;
  color: var(--gold) !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  backdrop-filter: blur(6px) !important;
}
.pnlm-hotspot.hs-link {
  background: rgba(30,27,22,0.7) !important;
  border: 1.5px solid rgba(201,169,97,0.4) !important;
  border-radius: 6px !important;
  padding: 4px 8px !important;
  color: var(--gold) !important;
  font-size: 11px !important;
  font-family: var(--font-mono) !important;
  backdrop-filter: blur(6px) !important;
}
/* Pannellum tooltip */
.pnlm-tooltip span {
  background: rgba(10,9,8,0.88) !important;
  border: 1px solid var(--line) !important;
  border-radius: var(--r-sm) !important;
  color: var(--ink) !important;
  font-family: var(--font-mono) !important;
  font-size: 11px !important;
  letter-spacing: 0.06em !important;
  padding: 4px 8px !important;
  backdrop-filter: blur(6px) !important;
}
/* Loading spinner overlay */
.pnlm-load-box { display:none !important; }
.pnlm-lbar { background: var(--gold) !important; }
.pnlm-lbar-fill { background: rgba(201,169,97,0.3) !important; }

.viewer-ctrl {
  width: 36px; height: 36px;
  border-radius: var(--r);
  background: rgba(10,9,8,0.75);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.08);
  color: var(--ink);
  font-size: 18px;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: background 0.15s;
}
.viewer-ctrl:hover { background: rgba(201,169,97,0.18); }

/* ===== Layout: viewer fills mobile-safe viewport, no scroll ===== */
.viewer-stage {
  flex: 1;
  position: relative;
  padding-top: 52px;
  /* Account for iOS home indicator / Android nav bar */
  padding-bottom: env(safe-area-inset-bottom, 0);
  min-height: 0;
}
.viewer-pano {
  width: 100%;
  height: calc(100dvh - 52px);
  background: #0a0908;
  position: relative;
  touch-action: none; /* Pannellum owns touches */
}
.viewer-nav {
  padding-left: max(16px, env(safe-area-inset-left));
  padding-right: max(16px, env(safe-area-inset-right));
}

/* Scene counter pill — fixed top-center, mobile-safe */
#scene-counter {
  position: fixed;
  top: calc(60px + env(safe-area-inset-top, 0px));
  left: 50%;
  transform: translateX(-50%);
  z-index: 55;
  background: rgba(10,9,8,0.78);
  backdrop-filter: blur(10px);
  border: 1px solid var(--line);
  padding: 6px 14px;
  border-radius: 999px;
  font-family: var(--font-mono);
  font-size: 11px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--ink-2);
  pointer-events: none;
}

/* ===== First-load drag/swipe hint ===== */
.first-hint {
  position: fixed;
  inset: 0;
  z-index: 70;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  background: rgba(10,9,8,0.55);
  backdrop-filter: blur(2px);
  color: rgba(245,241,232,0.95);
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.35s ease-out;
  text-align: center;
  padding: 0 32px;
}
.first-hint.is-visible {
  opacity: 1;
  animation: hint-pulse 1.6s ease-in-out infinite alternate;
}
.first-hint.is-hiding {
  opacity: 0;
}
.first-hint__text {
  font-family: var(--font-mono);
  font-size: 12px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}
@keyframes hint-pulse {
  from { transform: translateX(-12px); }
  to   { transform: translateX(12px); }
}
@media (hover: hover) {
  .first-hint__text::before { content: 'Click and '; }
}

/* ===== Big scene-prev / scene-next arrows ===== */
.scene-nav {
  position: fixed;
  top: 50%;
  transform: translateY(-50%);
  z-index: 60;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(10,9,8,0.55);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(201,169,97,0.35);
  color: var(--gold, #c9a961);
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: background 0.18s, transform 0.18s, border-color 0.18s;
}
.scene-nav:hover {
  background: rgba(201,169,97,0.22);
  border-color: rgba(201,169,97,0.7);
  transform: translateY(-50%) scale(1.06);
}
.scene-nav--left  { left: 18px; }
.scene-nav--right { right: 18px; }
.scene-nav[disabled] {
  opacity: 0.28;
  cursor: not-allowed;
  pointer-events: none;
}
@media (max-width: 540px) {
  .scene-nav { width: 48px; height: 48px; }
  .scene-nav--left { left: 10px; }
  .scene-nav--right { right: 10px; }
  /* Push controls up so iOS home-indicator doesn't sit on top of them */
  #pano-ctrls {
    bottom: calc(86px + env(safe-area-inset-bottom, 0px)) !important;
    right: 12px !important;
  }
  .viewer-ctrl { width: 42px; height: 42px; }
  /* Bigger touch targets for the scene strip thumbnails */
  #scene-strip {
    padding: 10px 12px calc(10px + env(safe-area-inset-bottom, 0px)) 12px !important;
  }
  #scene-strip .scene-item > div {
    width: 84px !important;
    height: 56px !important;
  }
}

/* ===== Touch-friendly Pannellum hotspot sizes (floor arrows) ===== */
@media (pointer: coarse) {
  .pnlm-hotspot.hs-nav,
  .pnlm-hotspot.hs-fwd,
  .pnlm-hotspot.hs-back {
    width: 56px !important;
    height: 56px !important;
  }
  .pnlm-hotspot.hs-fwd::before,
  .pnlm-hotspot.hs-nav::before,
  .pnlm-hotspot.hs-back::before {
    border-left-width: 11px !important;
    border-right-width: 11px !important;
  }
  .pnlm-hotspot.hs-fwd::before,
  .pnlm-hotspot.hs-nav::before {
    border-bottom-width: 17px !important;
  }
  .pnlm-hotspot.hs-back::before {
    border-top-width: 17px !important;
  }
}

/* ===== 3D / 360° toggle ===== */
.view-mode-toggle {
  display: flex;
  gap: 4px;
  padding: 4px;
  background: rgba(10,9,8,0.65);
  border: 1px solid var(--line);
  border-radius: 999px;
  margin-left: 20px;
  backdrop-filter: blur(10px);
}
.vmt-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: transparent;
  border: none;
  border-radius: 999px;
  color: var(--ink-3);
  font-family: var(--font-mono);
  font-size: 11px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.vmt-btn:hover { color: var(--ink); }
.vmt-btn.is-active {
  background: rgba(201,169,97,0.18);
  color: var(--gold);
}
.vmt-btn svg { display: block; }

@media (max-width: 540px) {
  .view-mode-toggle { margin-left: 8px; }
  .vmt-btn { padding: 6px 10px; font-size: 10px; }
  .vmt-btn svg { width: 12px; height: 12px; }
}
</style>

<?php if ($hasSplat): ?>
<script>
(function() {
  const btns       = document.querySelectorAll('.vmt-btn');
  const panoEl     = document.getElementById('panorama-viewer');
  const splatWrap  = document.getElementById('splat-frame-wrap');
  const splatFrame = document.getElementById('splat-frame');
  const splatLoad  = document.getElementById('splat-loading');
  const panoCtrls  = document.getElementById('pano-ctrls');
  const sceneStrip = document.getElementById('scene-strip');

  let splatLoaded = false;

  function showMode(mode) {
    btns.forEach(b => b.classList.toggle('is-active', b.dataset.mode === mode));

    if (mode === 'splat') {
      // Lazy-load the iframe the first time
      if (!splatLoaded) {
        splatFrame.src = splatFrame.dataset.src;
        splatLoaded = true;
        splatFrame.addEventListener('load', () => {
          splatLoad.style.display = 'none';
        }, { once: true });
      }
      splatWrap.style.display  = '';
      panoEl.style.visibility  = 'hidden';
      if (panoCtrls)  panoCtrls.style.display  = 'none';
      if (sceneStrip) sceneStrip.style.display = 'none';
    } else {
      splatWrap.style.display  = 'none';
      panoEl.style.visibility  = '';
      if (panoCtrls)  panoCtrls.style.display  = '';
      if (sceneStrip) sceneStrip.style.display = '';
    }
  }

  btns.forEach(b => b.addEventListener('click', () => showMode(b.dataset.mode)));
})();
</script>
<?php endif; ?>
