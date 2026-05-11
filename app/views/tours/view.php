<?php // FILE: /app/views/tours/view.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours'); ?>" style="color:var(--ink-3);">Walkthroughs</a>
    <span class="sep">/</span>
    <span class="here"><?php echo e($tour['title']); ?></span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <?php if ($tour['status'] === 'published' && $tour['is_public']): ?>
    <a href="<?php echo url('/tour/' . $tour['slug']); ?>" target="_blank" class="btn btn-outline-gold btn-sm">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Preview live
    </a>
    <?php endif; ?>
    <a href="<?php echo url('/tours/' . $tour['id'] . '/edit'); ?>" class="btn btn-sm">Edit</a>
  </div>
</header>

<div class="page-body fade-up">

  <?php
    $publicUrl  = rtrim(config('app_url'), '/') . '/tour/' . $tour['slug'];
    $isShareable = $tour['is_public'] && $tour['status'] === 'published';
  ?>

  <!-- ============ SHARE / DOWNLOAD STRIP ============ -->
  <?php if ($isShareable): ?>
  <div class="share-box">
    <div class="share-box__lead">
      <div class="share-box__label">Public walkthrough — anyone with the link can view</div>
      <div class="share-box__url-row">
        <input type="text" class="share-box__url" id="share-url" readonly value="<?php echo e($publicUrl); ?>">
        <button type="button" class="btn btn-primary btn-sm" id="copy-link-btn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
          <span id="copy-label">Copy link</span>
        </button>
      </div>
    </div>
    <div class="share-box__actions">
      <a href="<?php echo url('/tour/' . $tour['slug']); ?>" target="_blank" class="btn btn-outline-gold">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          Open viewer
        </a>
        <a href="mailto:?subject=<?php echo rawurlencode('360° Walkthrough — ' . $tour['title']); ?>&body=<?php echo rawurlencode("Take a look at this 360° tour:\n\n" . $publicUrl); ?>" class="btn btn-ghost">Email</a>
        <a href="https://wa.me/?text=<?php echo rawurlencode($tour['title'] . ' — ' . $publicUrl); ?>" target="_blank" class="btn btn-ghost">WhatsApp</a>
    </div>
  </div>
  <?php else: ?>
  <div class="share-box share-box--draft">
    <div>
      <div class="share-box__label" style="color:var(--gold);">This walkthrough is <?php echo $tour['status']; ?> — not yet shareable</div>
      <div style="font-size:12px;color:var(--ink-3);margin-top:4px;">Set status to "Published" and toggle "Public" to get a share link</div>
    </div>
    <a href="<?php echo url('/tours/' . $tour['id'] . '/edit'); ?>" class="btn btn-primary btn-sm">Publish</a>
  </div>
  <?php endif; ?>

  <div class="detail-grid">

    <!-- Left: main content -->
    <div>

      <!-- Scene grid -->
      <div class="section-header">
        <h2 class="section-title">Scenes <em>(<?php echo count($tour['scenes']); ?>)</em></h2>
        <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/create'); ?>" class="btn btn-primary btn-sm">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add scene
        </a>
      </div>

      <?php if (!empty($tour['scenes'])): ?>
      <div class="scene-grid">
        <?php foreach ($tour['scenes'] as $scene): ?>
        <div class="scene-card">
          <div class="scene-card__thumb">
            <?php if (!empty($scene['image_path'])): ?>
            <img src="<?php echo sceneImageUrl($scene['image_path']); ?>" alt="<?php echo e($scene['name']); ?>">
            <?php endif; ?>
          </div>
          <div class="scene-card__body">
            <div class="scene-card__title"><?php echo e($scene['name']); ?></div>
            <div class="scene-card__sub"><?php echo count($scene['hotspots']); ?> hotspots</div>
            <div class="scene-card__actions">
              <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/edit'); ?>" class="btn btn-sm">Edit</a>
              <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/create'); ?>" class="btn btn-sm btn-ghost">+ Hotspot</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="tape">
        <div class="empty">
          <div class="empty__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
          </div>
          <div class="empty__title">No scenes yet</div>
          <div class="empty__sub">Upload your first 360° panorama image</div>
          <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/create'); ?>" class="btn btn-primary" style="margin-top:8px;">Add first scene</a>
        </div>
      </div>
      <?php endif; ?>

      <!-- Delete zone -->
      <div class="hairline"></div>
      <div style="display:flex;gap:10px;align-items:center;">
        <form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/delete'); ?>">
          <?php echo CSRF::field(); ?>
          <button type="submit" class="btn btn-danger btn-sm"
                  onclick="return confirm('Permanently delete this tour and all its scenes?')">
            Delete tour
          </button>
        </form>
        <span style="font-size:12px;color:var(--ink-4);">This action cannot be undone</span>
      </div>

    </div>

    <!-- Right: metadata panel -->
    <div>
      <div class="card">
        <div class="card-header">
          <span class="card-title" style="font-size:15px;">Tour details</span>
          <?php
            $sc = match($tour['status']) { 'published' => 'chip-good', 'draft' => '', default => 'chip-danger' };
          ?>
          <span class="chip <?php echo $sc; ?>"><?php echo ucfirst($tour['status']); ?></span>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Property</div>
            <div class="detail-stat__val">
              <a href="<?php echo url('/properties/' . $property['id']); ?>" style="color:var(--ink);">
                <?php echo e($property['name'] ?? $property['title'] ?? '—'); ?>
              </a>
            </div>
          </div>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Total views</div>
            <div class="detail-stat__val" style="font-family:var(--font-mono);font-size:20px;"><?php echo number_format($view_count); ?></div>
          </div>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Scenes</div>
            <div class="detail-stat__val"><?php echo count($tour['scenes']); ?></div>
          </div>
          <?php if (!empty($tour['description'])): ?>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Description</div>
            <div class="detail-stat__val" style="color:var(--ink-2);font-size:13px;line-height:1.5;"><?php echo e($tour['description']); ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ============ PROPERTY DETAILS PANEL ============ -->
      <?php if (!empty($property)): ?>
      <div class="card" style="margin-top:20px;">
        <div class="card-header">
          <span class="card-title" style="font-size:15px;">Place details</span>
          <a href="<?php echo url('/properties/' . $property['id'] . '/edit'); ?>" class="btn btn-sm">Edit</a>
        </div>
        <div class="card-body" style="padding:0;">
          <?php if (!empty($property['address'])): ?>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Address</div>
            <div class="detail-stat__val" style="font-size:13px;color:var(--ink-2);"><?php echo e($property['address']); ?></div>
          </div>
          <?php endif; ?>

          <?php if (!empty($property['monthly_rent'])): ?>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Monthly rent</div>
            <div class="detail-stat__val" style="font-family:var(--font-display);font-size:22px;color:var(--gold);">€<?php echo number_format((float)$property['monthly_rent'], 0); ?></div>
          </div>
          <?php endif; ?>

          <?php
            // Build a chip line for layout/features
            $chips = [];
            if (!empty($property['building_type'])) $chips[] = ucfirst($property['building_type']);
            if (!empty($property['bedrooms']))     $chips[] = $property['bedrooms'] . ' BR';
            if (!empty($property['bathrooms']))    $chips[] = $property['bathrooms'] . ' BA';
            if (isset($property['floor']) && $property['floor'] !== null && $property['floor'] !== '') {
              $f = (int)$property['floor'];
              $suf = ($f===1?'st':($f===2?'nd':($f===3?'rd':'th')));
              $chips[] = $f . $suf . ' floor';
            }
            if (!empty($property['rooms_total'])) $chips[] = $property['rooms_total'] . ' rooms';
          ?>
          <?php if ($chips || !empty($property['has_parking'])): ?>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Layout</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">
              <?php foreach ($chips as $c): ?>
                <span class="chip"><?php echo e($c); ?></span>
              <?php endforeach; ?>
              <?php if (!empty($property['has_parking'])): ?>
                <span class="chip chip-good">🅿 Parking</span>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if (!empty($property['deposit']) || !empty($property['monthly_utilities'])): ?>
          <?php if (!empty($property['deposit'])): ?>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Deposit</div>
            <div class="detail-stat__val">€<?php echo number_format((float)$property['deposit'], 0); ?></div>
          </div>
          <?php endif; ?>
          <?php if (!empty($property['monthly_utilities'])): ?>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Utilities / mo</div>
            <div class="detail-stat__val">€<?php echo number_format((float)$property['monthly_utilities'], 0); ?></div>
          </div>
          <?php endif; ?>
          <?php endif; ?>

          <?php if (!empty($property['specialties'])): ?>
          <div class="detail-stat" style="padding:14px 20px;">
            <div class="detail-stat__key">Specialties</div>
            <div class="detail-stat__val" style="font-size:13px;color:var(--ink-2);line-height:1.55;"><?php echo nl2br(e($property['specialties'])); ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
// Copy share link to clipboard
(function() {
  const btn = document.getElementById('copy-link-btn');
  const inp = document.getElementById('share-url');
  const lbl = document.getElementById('copy-label');
  if (!btn || !inp) return;
  btn.addEventListener('click', function() {
    inp.select();
    inp.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(inp.value).then(function() {
      lbl.textContent = 'Copied ✓';
      setTimeout(function() { lbl.textContent = 'Copy link'; }, 1800);
    }).catch(function() {
      document.execCommand('copy');
      lbl.textContent = 'Copied ✓';
      setTimeout(function() { lbl.textContent = 'Copy link'; }, 1800);
    });
  });
})();
</script>

<style>
.share-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 20px 24px;
  background: var(--bg-elev);
  border: 1px solid var(--gold-line, rgba(201,169,97,0.25));
  border-radius: var(--r-lg);
  margin-bottom: 24px;
  box-shadow: 0 0 0 1px rgba(201,169,97,0.04) inset;
}
.share-box--draft {
  border-color: var(--line);
  background: var(--surface);
}
.share-box__lead { flex: 1; min-width: 0; }
.share-box__label {
  font-family: var(--font-mono);
  font-size: 10px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--ink-3);
  margin-bottom: 10px;
}
.share-box__url-row {
  display: flex; gap: 8px; align-items: stretch;
  max-width: 560px;
}
.share-box__url {
  flex: 1; min-width: 0;
  background: var(--bg);
  border: 1px solid var(--line);
  border-radius: var(--r-sm);
  padding: 8px 12px;
  color: var(--gold);
  font-family: var(--font-mono);
  font-size: 12px;
  letter-spacing: 0.02em;
}
.share-box__url:focus { outline: none; border-color: var(--gold); }
.share-box__actions { display: flex; gap: 8px; flex-shrink: 0; }
@media (max-width: 760px) {
  .share-box { flex-direction: column; align-items: stretch; }
  .share-box__actions { flex-wrap: wrap; }
}
</style>
