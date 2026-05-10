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

    // ── Build Pannellum config ──────────────────────────────────────────────
    var pannellumScenes = {};
    var firstSceneKey = 'scene_' + scenes[0].id;

    scenes.forEach(function (scene) {
      var sceneKey = 'scene_' + scene.id;
      var hotSpots = [];

      if (Array.isArray(scene.hotspots)) {
        scene.hotspots.forEach(function (h) {
          var hs = {
            pitch: parseFloat(h.pitch) || 0,
            yaw:   parseFloat(h.yaw)   || 0,
            text:  h.label || '',
          };

          if (h.type === 'navigation' && h.target_scene_id) {
            hs.type    = 'scene';
            hs.sceneId = 'scene_' + h.target_scene_id;
            hs.cssClass = 'hs-nav ' + (parseFloat(h.yaw) >= 90 || parseFloat(h.yaw) <= -90 ? 'hs-back' : 'hs-fwd');
          } else if (h.type === 'link' && h.external_url) {
            hs.type = 'url';
            hs.URL  = h.external_url;
            hs.cssClass = 'hs-link';
          } else {
            hs.type = 'info';
            hs.cssClass = 'hs-info';
          }

          hotSpots.push(hs);
        });
      }

      pannellumScenes[sceneKey] = {
        title:    scene.name,
        panorama: scene.image_url,
        yaw:      parseFloat(scene.initial_yaw)   || 0,
        pitch:    parseFloat(scene.initial_pitch) || 0,
        hotSpots: hotSpots,
      };
    });

    // ── Init Pannellum ──────────────────────────────────────────────────────
    var viewer = pannellum.viewer('panorama-viewer', {
      default: {
        firstScene:          firstSceneKey,
        sceneFadeDuration:   800,
        autoLoad:            true,
        showControls:        false,  // we provide our own
        showFullscreenCtrl:  false,
        showZoomCtrl:        false,
        keyboardZoom:        true,
        mouseZoom:           true,
        hfov:                90,
        minHfov:             40,
        maxHfov:             120,
        compass:             false,
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

    // Strip click → load scene
    sceneItems.forEach(function (item) {
      item.addEventListener('click', function () {
        var sceneId  = this.dataset.sceneId;
        var sceneKey = 'scene_' + sceneId;
        viewer.loadScene(sceneKey);
        setActiveStrip(sceneId);
      });
    });

    // When Pannellum transitions to a new scene, sync the strip
    viewer.on('scenechange', function (sceneKey) {
      var sceneId = sceneKey.replace('scene_', '');
      setActiveStrip(sceneId);
    });

    // Activate first scene in strip on load
    if (scenes.length > 0) {
      setActiveStrip(scenes[0].id);
    }

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
    viewer.on('click', function (e) {
      // Pannellum fires standard hotspot callbacks via cssClass links — info panel
      // is handled via the Pannellum hotspot clickHandlerFunc if needed.
    });

    var infoPanel   = document.getElementById('info-panel');
    var infoTitle   = document.getElementById('info-title');
    var infoDesc    = document.getElementById('info-description');
    var btnClose    = document.getElementById('close-info');

    if (btnClose && infoPanel) {
      btnClose.addEventListener('click', function () {
        infoPanel.style.display = 'none';
      });
    }

    // Expose viewer globally for hotspot callbacks
    window._wievViewer = viewer;
    window._wievShowInfo = function (title, desc) {
      if (!infoPanel) return;
      if (infoTitle) infoTitle.textContent = title || '';
      if (infoDesc)  infoDesc.textContent  = desc  || '';
      infoPanel.style.display = 'block';
    };
  });
})();
