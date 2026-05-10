<?php
// FILE: /app/views/public/tour.php
?>

<!-- Viewer top bar -->
<nav class="viewer-nav">
  <div class="viewer-brand">360<span class="deg">°</span></div>
  <div style="flex:1;"></div>
  <div style="display:flex;flex-direction:column;align-items:flex-end;gap:2px;">
    <div style="font-size:13px;font-weight:600;color:var(--ink);"><?php echo e($tour['property_title']); ?></div>
    <div style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:var(--ink-3);">
      <?php echo e($tour['title']); ?>
    </div>
  </div>
</nav>

<!-- Panorama viewer -->
<div style="flex:1;position:relative;padding-top:52px;">
  <div id="panorama-viewer"
       data-tour='<?php echo htmlspecialchars(json_encode($tour), ENT_QUOTES, 'UTF-8'); ?>'
       style="width:100%;height:calc(100vh - 52px);background:#000;position:relative;">

    <?php if (empty($tour['scenes'])): ?>
    <div class="viewer-msg">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" style="margin:0 auto 12px;display:block;color:var(--ink-4);">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
      </svg>
      No scenes uploaded yet
    </div>
    <?php endif; ?>

    <!-- Controls -->
    <div style="position:absolute;bottom:24px;right:24px;z-index:10;display:flex;flex-direction:column;gap:8px;">
      <button id="zoom-in" style="width:36px;height:36px;border-radius:var(--r);background:rgba(10,9,8,0.7);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.08);color:var(--ink);font-size:18px;display:grid;place-items:center;">+</button>
      <button id="zoom-out" style="width:36px;height:36px;border-radius:var(--r);background:rgba(10,9,8,0.7);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.08);color:var(--ink);font-size:18px;display:grid;place-items:center;">−</button>
      <button id="fullscreen" style="width:36px;height:36px;border-radius:var(--r);background:rgba(10,9,8,0.7);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.08);color:var(--ink);font-size:14px;display:grid;place-items:center;" title="Fullscreen">⛶</button>
    </div>
  </div>
</div>

<!-- Scene strip -->
<?php if (!empty($tour['scenes'])): ?>
<div style="background:rgba(10,9,8,0.9);backdrop-filter:blur(12px);border-top:1px solid var(--line);padding:12px 20px;display:flex;gap:10px;overflow-x:auto;position:fixed;bottom:0;left:0;right:0;z-index:50;">
  <?php foreach ($tour['scenes'] as $i => $scene): ?>
  <div class="scene-item" data-scene-id="<?php echo $scene['id']; ?>" data-scene-index="<?php echo $i; ?>"
       style="flex-shrink:0;cursor:pointer;border-radius:var(--r-sm);overflow:hidden;border:1px solid var(--line);transition:border-color 0.14s;"
       onmouseenter="this.style.borderColor='var(--gold-line)'" onmouseleave="this.style.borderColor='var(--line)'">
    <div style="width:72px;height:48px;background-image:url('<?php echo uploadUrl('scenes/' . $scene['image_path']); ?>');background-size:cover;background-position:center;position:relative;">
      <div style="position:absolute;bottom:0;left:0;right:0;padding:3px 5px;background:rgba(0,0,0,0.6);font-family:var(--font-mono);font-size:9px;letter-spacing:0.1em;text-transform:uppercase;color:rgba(245,241,232,0.8);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
        <?php echo e($scene['name']); ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Info panel -->
<div id="info-panel" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:200;background:var(--bg-elev);border:1px solid var(--line);border-radius:var(--r-lg);padding:28px;min-width:300px;max-width:440px;">
  <h3 id="info-title" style="font-family:var(--font-display);font-size:20px;color:var(--ink);margin-bottom:10px;"></h3>
  <p id="info-description" style="color:var(--ink-2);font-size:13px;line-height:1.6;"></p>
  <button id="close-info" class="btn" style="margin-top:16px;">Close</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Splash360Viewer !== 'undefined') {
        const viewer = new Splash360Viewer('panorama-viewer');
        viewer.init();
    }
});
</script>
