// FILE: /public/assets/js/viewer.js
// 360° WIEV — Pannellum-powered walkthrough viewer

(function () {
  'use strict';

  // ── Wait for DOM ────────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('panorama-viewer');
    if (!container) return;

    // Parse tour data embedded in the container element
    var tourData;
    try {
      tourData = JSON.parse(container.dataset.tour);
    } catch (e) {
      console.error('WIEV: failed to parse tour data', e);
      return;
    }

    var scenes = tourData.scenes || [];
    if (scenes.length === 0) {
      container.innerHTML = '<div class="viewer-msg">No scenes uploaded yet.</div>';
      return;
    }

    // ── Pre-load every image so we can detect each one's true aspect ratio.
    //    True 360° equirectangular images are 2:1 (aspect ~2.0). Anything
    //    else is a regular photo that should be projected onto a small
    //    portion of the sphere (otherwise it gets badly stretched).
    function detectAspects(scenes, done) {
      var remaining = scenes.length;
      var aspects = {};
      if (remaining === 0) return done(aspects);

      scenes.forEach(function (scene) {
        var img = new Image();
        // Don't set crossOrigin — Supabase doesn't always return CORS headers
        // and we only need natural dimensions, not pixel access.
        img.onload  = function () {
          aspects[scene.id] = (img.naturalWidth || 1) / (img.naturalHeight || 1);
          if (--remaining === 0) done(aspects);
        };
        img.onerror = function () {
          aspects[scene.id] = 2.0; // default: assume equirectangular
          if (--remaining === 0) done(aspects);
        };
        img.src = scene.image_url;
      });
    }

    detectAspects(scenes, function (aspects) {
      initViewer(aspects);
    });

    function initViewer(aspects) {
      var pannellumScenes = {};
      var firstSceneKey   = 'scene_' + scenes[0].id;

      scenes.forEach(function (scene) {
        var sceneKey = 'scene_' + scene.id;
        var hotSpots = [];

        if (Array.isArray(scene.hotspots)) {
          scene.hotspots.forEach(function (h) {
            var hs = {
              pitch: parseFloat(h.pitch) || 0,
              yaw:   parseFloat(h.yaw)   || 0,
              text:  h.title || h.label || '',
            };

            if (h.type === 'navigation' && h.target_scene_id) {
              hs.type    = 'scene';
              hs.sceneId = 'scene_' + h.target_scene_id;
              hs.cssClass = 'hs-nav ' + (parseFloat(h.yaw) >= 90 || parseFloat(h.yaw) <= -90 ? 'hs-back' : 'hs-fwd');
            } else if (h.type === 'link' && (h.url || h.external_url)) {
              hs.type = 'url';
              hs.URL  = h.url || h.external_url;
              hs.cssClass = 'hs-link';
            } else {
              hs.type = 'info';
              hs.cssClass = 'hs-info';
            }

            hotSpots.push(hs);
          });
        }

        // Decide whether this is a real 360° photo or a flat one.
        var aspect = aspects[scene.id] || 2.0;
        var isEqui = aspect > 1.85 && aspect < 2.15;

        var sceneCfg = {
          title:    scene.title || scene.name || '',
          panorama: scene.image_url,
          yaw:      parseFloat(scene.initial_yaw)   || 0,
          pitch:    parseFloat(scene.initial_pitch) || 0,
          hotSpots: hotSpots,
        };

        if (!isEqui) {
          // Partial panorama — show the photo undistorted on a small chunk
          // of the sphere instead of stretching it across the whole thing.
          // 130° horizontal feels natural; vertical is derived from aspect.
          sceneCfg.haov = 130;
          sceneCfg.vaov = Math.max(40, Math.min(120, 130 / aspect));
        }

        pannellumScenes[sceneKey] = sceneCfg;
      });

      // ── Init Pannellum ────────────────────────────────────────────────────
      var viewer = pannellum.viewer('panorama-viewer', {
        default: {
          firstScene:          firstSceneKey,
          sceneFadeDuration:   600,
          autoLoad:            true,
          showControls:        false,
          showFullscreenCtrl:  false,
          showZoomCtrl:        false,
          keyboardZoom:        true,
          mouseZoom:           true,
          // 120° hfov starts comfortably zoomed out so the whole image is visible
          hfov:                120,
          minHfov:             50,
          maxHfov:             140,
          compass:             false,
          backgroundColor:     [0.039, 0.035, 0.031],
        },
        scenes: pannellumScenes,
      });

      // ── Sync scene strip ────────────────────────────────────────────────────
      var sceneItems = document.querySelectorAll('.scene-item');

      function setActiveStrip(sceneId) {
        sceneItems.forEach(function (item) {
          var active = String(item.dataset.sceneId) === String(sceneId);
          item.style.borderColor = active ? 'var(--gold)' : '';
          item.style.opacity     = active ? '1' : '0.6';
        });
      }

      sceneItems.forEach(function (item) {
        item.addEventListener('click', function () {
          var sceneId  = this.dataset.sceneId;
          var sceneKey = 'scene_' + sceneId;
          viewer.loadScene(sceneKey);
          setActiveStrip(sceneId);
        });
      });

      if (scenes.length > 0) setActiveStrip(scenes[0].id);

      // ── Prev / Next scene buttons (big on-screen arrows) ────────────────────
      var btnPrev    = document.getElementById('nav-prev');
      var btnNext    = document.getElementById('nav-next');
      var lblCurrent = document.getElementById('scene-current');
      var currentIdx = 0;

      function goToIndex(i) {
        if (i < 0 || i >= scenes.length) return;
        currentIdx = i;
        viewer.loadScene('scene_' + scenes[i].id);
        setActiveStrip(scenes[i].id);
        syncNavButtons();
      }

      function syncNavButtons() {
        if (lblCurrent) lblCurrent.textContent = String(currentIdx + 1);
        if (btnPrev) btnPrev.toggleAttribute('disabled', currentIdx <= 0);
        if (btnNext) btnNext.toggleAttribute('disabled', currentIdx >= scenes.length - 1);
      }

      if (btnPrev) btnPrev.addEventListener('click', function () { goToIndex(currentIdx - 1); });
      if (btnNext) btnNext.addEventListener('click', function () { goToIndex(currentIdx + 1); });

      viewer.on('scenechange', function (sceneKey) {
        var id = sceneKey.replace('scene_', '');
        var idx = scenes.findIndex(function (s) { return String(s.id) === String(id); });
        if (idx >= 0) currentIdx = idx;
        setActiveStrip(id);
        syncNavButtons();
      });

      document.addEventListener('keydown', function (ev) {
        if (ev.key === 'ArrowRight') goToIndex(currentIdx + 1);
        else if (ev.key === 'ArrowLeft') goToIndex(currentIdx - 1);
      });

      syncNavButtons();

      // ── Custom controls ─────────────────────────────────────────────────────
      var btnZoomIn  = document.getElementById('zoom-in');
      var btnZoomOut = document.getElementById('zoom-out');
      var btnFs      = document.getElementById('fullscreen');

      if (btnZoomIn)  btnZoomIn.addEventListener('click',  function () { viewer.setHfov(viewer.getHfov() - 10, true); });
      if (btnZoomOut) btnZoomOut.addEventListener('click', function () { viewer.setHfov(viewer.getHfov() + 10, true); });
      if (btnFs) {
        btnFs.addEventListener('click', function () {
          if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen && document.documentElement.requestFullscreen();
          } else {
            document.exitFullscreen && document.exitFullscreen();
          }
        });
      }

      // ── Info panel (for info-type hotspots) ─────────────────────────────────
      var infoPanel = document.getElementById('info-panel');
      var infoTitle = document.getElementById('info-title');
      var infoDesc  = document.getElementById('info-description');
      var btnClose  = document.getElementById('close-info');

      if (btnClose && infoPanel) {
        btnClose.addEventListener('click', function () { infoPanel.style.display = 'none'; });
      }

      window._wievViewer = viewer;
      window._wievShowInfo = function (title, desc) {
        if (!infoPanel) return;
        if (infoTitle) infoTitle.textContent = title || '';
        if (infoDesc)  infoDesc.textContent  = desc  || '';
        infoPanel.style.display = 'block';
      };
    } // end initViewer
  });
})();
