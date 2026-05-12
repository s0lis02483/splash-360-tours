<?php // FILE: /app/views/tours/create.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Workspace</span>
    <span class="sep">/</span>
    <a href="<?php echo url('/tours'); ?>" style="color:var(--ink-3);">Walkthroughs</a>
    <span class="sep">/</span>
    <span class="here">New</span>
  </div>
  <div class="top__spacer"></div>
</header>

<div class="page-body fade-up">

  <h1 class="page-title">Create <em>walkthrough</em></h1>
  <p class="page-subtitle">Name the place, drop your 360° photos — we build the tour automatically.</p>

  <div class="wt-layout">

  <form id="walkthrough-form" method="POST" enctype="multipart/form-data"
        action="<?php echo url('/tours/create'); ?>" class="wt-form">
    <?php echo CSRF::field(); ?>

    <?php $oi = $_SESSION['old_input'] ?? []; if (!is_array($oi)) $oi = []; ?>

    <!-- Place name -->
    <div class="wt-section">
      <label class="wt-label">Name of the place</label>
      <input class="input wt-name-input" type="text" name="place_name" required
             placeholder="e.g. Oceanview Penthouse, Grand Villa, Downtown Loft…"
             value="<?php echo e($oi['place_name'] ?? ''); ?>">
    </div>

    <!-- Location & building -->
    <div class="wt-section" style="margin-top:24px;">
      <label class="wt-label">Address / Location</label>
      <input class="input" type="text" name="address"
             placeholder="Street, city, country"
             value="<?php echo e($oi['address'] ?? ''); ?>">
    </div>

    <div class="wt-grid-2" style="margin-top:18px;">
      <div class="wt-section">
        <label class="wt-label">Building type</label>
        <select class="select" name="building_type">
          <?php $bt = $oi['building_type'] ?? 'apartment'; ?>
          <option value="apartment" <?php echo $bt==='apartment'?'selected':''; ?>>Apartment block</option>
          <option value="house"     <?php echo $bt==='house'?'selected':''; ?>>House</option>
          <option value="villa"     <?php echo $bt==='villa'?'selected':''; ?>>Villa</option>
          <option value="studio"    <?php echo $bt==='studio'?'selected':''; ?>>Studio</option>
          <option value="office"    <?php echo $bt==='office'?'selected':''; ?>>Office</option>
          <option value="other"     <?php echo $bt==='other'?'selected':''; ?>>Other</option>
        </select>
      </div>
      <div class="wt-section">
        <label class="wt-label">Floor (if apartment)</label>
        <input class="input" type="number" name="floor" min="-2" max="100"
               placeholder="e.g. 3"
               value="<?php echo e($oi['floor'] ?? ''); ?>">
      </div>
    </div>

    <!-- Layout -->
    <div class="wt-grid-3" style="margin-top:18px;">
      <div class="wt-section">
        <label class="wt-label">Total rooms</label>
        <input class="input" type="number" name="rooms_total" min="0" max="50"
               placeholder="4" value="<?php echo e($oi['rooms_total'] ?? ''); ?>">
      </div>
      <div class="wt-section">
        <label class="wt-label">Bedrooms</label>
        <input class="input" type="number" name="bedrooms" min="0" max="20"
               placeholder="2" value="<?php echo e($oi['bedrooms'] ?? ''); ?>">
      </div>
      <div class="wt-section">
        <label class="wt-label">Bathrooms / toilets</label>
        <input class="input" type="number" name="bathrooms" min="0" max="20"
               placeholder="1" value="<?php echo e($oi['bathrooms'] ?? ''); ?>">
      </div>
    </div>

    <!-- Parking -->
    <div class="wt-section" style="margin-top:18px;">
      <label class="wt-checkbox">
        <input type="checkbox" name="has_parking" value="1"
               <?php echo !empty($oi['has_parking'])?'checked':''; ?>>
        <span>Includes a parking spot</span>
      </label>
    </div>

    <!-- Pricing -->
    <h3 class="wt-subhead">Pricing</h3>
    <div class="wt-grid-3">
      <div class="wt-section">
        <label class="wt-label">Monthly rent (€)</label>
        <input class="input" type="number" name="monthly_rent" min="0" step="10"
               placeholder="850" value="<?php echo e($oi['monthly_rent'] ?? ''); ?>">
      </div>
      <div class="wt-section">
        <label class="wt-label">Deposit / first payment (€)</label>
        <input class="input" type="number" name="deposit" min="0" step="10"
               placeholder="1700" value="<?php echo e($oi['deposit'] ?? ''); ?>">
      </div>
      <div class="wt-section">
        <label class="wt-label">Monthly utilities (€)</label>
        <input class="input" type="number" name="monthly_utilities" min="0" step="5"
               placeholder="120" value="<?php echo e($oi['monthly_utilities'] ?? ''); ?>">
      </div>
    </div>
    <p class="wt-hint" style="margin-top:6px;">Utilities = electricity, water, heating, internet, building fee.</p>

    <!-- Specialties -->
    <div class="wt-section" style="margin-top:24px;">
      <label class="wt-label">Other specialties</label>
      <textarea class="input" name="specialties" rows="3"
                placeholder="e.g. balcony with sea view, fully furnished, pet-friendly, fiber internet, smart-home, A/C…"
                style="resize:vertical;min-height:80px;"><?php echo e($oi['specialties'] ?? ''); ?></textarea>
    </div>

    <!-- ===== 3D Gaussian Splat scene (optional) ===== -->
    <h3 class="wt-subhead">3D scene <span style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.1em;color:var(--ink-4);font-weight:400;text-transform:uppercase;margin-left:8px;">optional</span></h3>
    <p class="wt-hint" style="margin-top:-8px;">
      Walk-through 3D model created with <a href="https://lumalabs.ai" target="_blank" style="color:var(--gold);">Luma AI</a> or <a href="https://superspl.at" target="_blank" style="color:var(--gold);">SuperSplat</a>.
      Just paste the scene URL — the viewer embeds it next to the 360° tour.
    </p>
    <div class="wt-section">
      <label class="wt-label">3D scene URL</label>
      <input class="input" type="url" name="splat_url"
             placeholder="https://superspl.at/scene/91c1e47e   or   https://lumalabs.ai/capture/…"
             value="<?php echo e($oi['splat_url'] ?? ''); ?>">
      <p class="wt-hint" style="margin-top:6px;">
        How to get one: shoot a 1-min video of the place with the Luma AI iPhone app → upload → copy the scene URL → paste here.
      </p>
    </div>

    <!-- Drop zone -->
    <div class="wt-section" style="margin-top:28px;">
      <label class="wt-label">360° panoramic photos</label>
      <p class="wt-hint">Upload as many as you want — each photo becomes a room in the walkthrough. Drag to reorder.</p>

      <div id="drop-zone" class="wt-drop">
        <input type="file" id="images-input" name="images[]" multiple
               accept="image/jpeg,image/jpg,image/png,image/webp" style="display:none;">
        <div id="drop-idle" class="wt-drop__idle">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--gold);margin:0 auto 16px;display:block;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
          </svg>
          <div style="font-family:var(--font-display);font-size:18px;color:var(--ink);margin-bottom:6px;">Drop photos here</div>
          <div style="font-size:13px;color:var(--ink-3);margin-bottom:20px;">or click to browse your files</div>
          <button type="button" id="browse-btn" class="btn btn-ghost btn-sm">Browse files</button>
        </div>
        <div id="drop-preview" class="wt-preview-grid" style="display:none;"></div>
      </div>

      <div id="file-count" style="margin-top:12px;font-family:var(--font-mono);font-size:11px;letter-spacing:0.1em;color:var(--ink-3);text-transform:uppercase;display:none;">
        <span id="count-num">0</span> photos selected &nbsp;&middot;&nbsp; drag thumbnails to reorder
      </div>
    </div>

    <div style="margin-top:36px;display:flex;align-items:center;gap:12px;">
      <button type="submit" id="submit-btn" class="btn btn-primary" disabled>
        <span id="submit-label">Add photos to create</span>
      </button>
      <a href="<?php echo url('/tours'); ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>

  <!-- ====== LIVE PREVIEW PANEL (right column) ====== -->
  <aside class="wt-preview-panel">
    <div class="wt-preview-card">
      <div class="wt-preview-card__hero" id="prev-hero">
        <div class="wt-preview-card__hero-empty">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
          <span>360° preview appears here</span>
        </div>
      </div>

      <div class="wt-preview-card__body">
        <div class="wt-preview-card__pricerow">
          <span class="wt-preview-card__price" id="prev-price">€—</span>
          <span class="wt-preview-card__price-suf">/ month</span>
        </div>

        <h3 class="wt-preview-card__title" id="prev-title">Your place name</h3>
        <div class="wt-preview-card__addr" id="prev-addr">Address will appear here</div>

        <div class="wt-preview-card__chips" id="prev-chips">
          <span class="wt-chip wt-chip--muted">Add details to populate</span>
        </div>

        <div class="wt-preview-card__divider"></div>

        <div class="wt-preview-card__row">
          <span class="wt-preview-card__row-k">Deposit</span>
          <span class="wt-preview-card__row-v" id="prev-deposit">—</span>
        </div>
        <div class="wt-preview-card__row">
          <span class="wt-preview-card__row-k">Utilities / mo</span>
          <span class="wt-preview-card__row-v" id="prev-utilities">—</span>
        </div>
        <div class="wt-preview-card__row" id="prev-monthtotal-wrap" style="display:none;">
          <span class="wt-preview-card__row-k wt-preview-card__row-k--strong">Total / month</span>
          <span class="wt-preview-card__row-v wt-preview-card__row-v--gold" id="prev-monthtotal">—</span>
        </div>

        <div class="wt-preview-card__specialties" id="prev-specialties" style="display:none;">
          <div class="wt-preview-card__row-k" style="margin-bottom:8px;">Specialties</div>
          <p id="prev-specialties-text"></p>
        </div>
      </div>
    </div>

    <p class="wt-preview-foot">Live preview · updates as you type</p>
  </aside>

  </div><!-- /.wt-layout -->

</div>

<style>
/* ===== two-column layout: form left, sticky preview right ===== */
.wt-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 360px;
  gap: 56px;
  align-items: start;
  margin-top: 8px;
}
.wt-form { max-width: 700px; min-width: 0; }
.wt-preview-panel { position: sticky; top: 28px; }
@media (max-width: 1100px) {
  .wt-layout { grid-template-columns: 1fr; gap: 32px; }
  .wt-preview-panel { position: static; max-width: 700px; }
}

/* ===== preview card ===== */
.wt-preview-card {
  background: var(--bg-elev);
  border: 1px solid var(--line);
  border-radius: var(--r-lg);
  overflow: hidden;
}
.wt-preview-card__hero {
  aspect-ratio: 4/3;
  background: linear-gradient(135deg, var(--surface) 0%, var(--bg) 100%);
  position: relative;
  overflow: hidden;
  border-bottom: 1px solid var(--line);
}
.wt-preview-card__hero img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.wt-preview-card__hero-empty {
  position: absolute; inset: 0;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 10px;
  color: var(--ink-4);
  font-family: var(--font-mono);
  font-size: 10px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
}
.wt-preview-card__body { padding: 22px; }

.wt-preview-card__pricerow {
  display: flex; align-items: baseline; gap: 6px;
  margin-bottom: 14px;
}
.wt-preview-card__price {
  font-family: var(--font-display);
  font-size: 28px;
  color: var(--gold);
  letter-spacing: -0.01em;
  line-height: 1;
}
.wt-preview-card__price-suf {
  font-size: 12px; color: var(--ink-3);
  font-family: var(--font-mono);
  letter-spacing: 0.06em;
}

.wt-preview-card__title {
  font-family: var(--font-display);
  font-size: 20px;
  color: var(--ink);
  margin: 0 0 4px;
  line-height: 1.2;
  font-weight: 400;
}
.wt-preview-card__addr {
  font-size: 12px;
  color: var(--ink-3);
  margin-bottom: 14px;
  line-height: 1.5;
}

.wt-preview-card__chips {
  display: flex; flex-wrap: wrap; gap: 6px;
}
.wt-chip {
  font-family: var(--font-mono);
  font-size: 10px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 4px 9px;
  border-radius: 999px;
  background: var(--surface);
  color: var(--ink-2);
  border: 1px solid var(--line);
  white-space: nowrap;
}
.wt-chip--gold { color: var(--gold); border-color: rgba(201,169,97,0.35); background: var(--gold-soft); }
.wt-chip--muted { color: var(--ink-4); background: transparent; border-style: dashed; }

.wt-preview-card__divider {
  height: 1px;
  background: var(--line);
  margin: 18px 0;
}
.wt-preview-card__row {
  display: flex; justify-content: space-between; align-items: baseline;
  padding: 6px 0;
  font-size: 13px;
}
.wt-preview-card__row-k {
  color: var(--ink-3);
  font-family: var(--font-mono);
  font-size: 11px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}
.wt-preview-card__row-k--strong { color: var(--ink); }
.wt-preview-card__row-v { color: var(--ink); font-weight: 500; }
.wt-preview-card__row-v--gold { color: var(--gold); font-family: var(--font-display); font-size: 17px; }

.wt-preview-card__specialties {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--line);
}
.wt-preview-card__specialties p {
  font-size: 13px; color: var(--ink-2); line-height: 1.55;
  margin: 0;
}

.wt-preview-foot {
  text-align: center;
  font-family: var(--font-mono);
  font-size: 10px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--ink-4);
  margin: 14px 0 0;
}

.wt-section { display:flex;flex-direction:column;gap:8px; }
.wt-label { font-family:var(--font-mono);font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:var(--ink-3); }
.wt-hint { font-size:13px;color:var(--ink-3);margin:0 0 12px; }
.wt-name-input { font-size:17px;padding:14px 16px;height:auto; }

.wt-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:18px; }
.wt-grid-3 { display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px; }
@media (max-width:680px) {
  .wt-grid-2, .wt-grid-3 { grid-template-columns:1fr; }
}

.wt-subhead {
  font-family:var(--font-display);
  font-size:22px;
  font-weight:400;
  color:var(--ink);
  margin:36px 0 16px;
  padding-bottom:10px;
  border-bottom:1px solid var(--line);
  letter-spacing:-0.01em;
}

.wt-checkbox {
  display:flex;align-items:center;gap:10px;
  cursor:pointer;color:var(--ink-2);font-size:14px;
  user-select:none;padding:6px 0;
}
.wt-checkbox input[type=checkbox] {
  appearance:none;
  width:18px;height:18px;
  border:1.5px solid var(--line);
  border-radius:4px;
  background:var(--surface);
  cursor:pointer;
  position:relative;
  flex-shrink:0;
  transition:all 0.15s;
}
.wt-checkbox input[type=checkbox]:checked {
  background:var(--gold);
  border-color:var(--gold);
}
.wt-checkbox input[type=checkbox]:checked::after {
  content:'';
  position:absolute;
  left:5px;top:1px;
  width:5px;height:10px;
  border:solid var(--bg);
  border-width:0 2px 2px 0;
  transform:rotate(45deg);
}

.wt-drop {
  border: 1.5px dashed var(--line);
  border-radius: var(--r-lg);
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: border-color 0.2s, background 0.2s;
  cursor: pointer;
}
.wt-drop.drag-over {
  border-color: var(--gold);
  background: var(--gold-soft);
}
.wt-drop__idle { text-align:center;padding:40px 20px; }

.wt-preview-grid {
  padding: 16px;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 10px;
  width: 100%;
  align-items: start;
}
.wt-thumb {
  aspect-ratio: 16/9;
  border-radius: var(--r-sm);
  overflow: hidden;
  position: relative;
  background: var(--surface);
  border: 1px solid var(--line);
  cursor: grab;
  user-select: none;
  transition: opacity 0.15s, box-shadow 0.15s;
}
.wt-thumb:active { cursor: grabbing; }
.wt-thumb.dragging { opacity: 0.35; }
.wt-thumb.drag-target { box-shadow: 0 0 0 2px var(--gold); border-color: var(--gold); }
.wt-thumb img { width:100%;height:100%;object-fit:cover;display:block;pointer-events:none; }
.wt-thumb__num {
  position: absolute;
  top: 5px; left: 5px;
  background: rgba(10,9,8,0.78);
  backdrop-filter: blur(6px);
  color: var(--gold);
  font-family: var(--font-mono);
  font-size: 10px;
  letter-spacing: 0.1em;
  padding: 2px 6px;
  border-radius: 4px;
  pointer-events: none;
}
.wt-thumb__rm {
  position: absolute;
  top: 5px; right: 5px;
  width: 22px; height: 22px;
  background: rgba(10,9,8,0.8);
  border: none;
  border-radius: 50%;
  color: var(--ink-2);
  font-size: 15px;
  cursor: pointer;
  display: grid;
  place-items: center;
  opacity: 0;
  transition: opacity 0.15s;
  line-height: 1;
}
.wt-thumb:hover .wt-thumb__rm { opacity: 1; }
.wt-thumb__name {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  background: rgba(10,9,8,0.7);
  font-family: var(--font-mono);
  font-size: 9px;
  letter-spacing: 0.04em;
  padding: 3px 6px;
  color: var(--ink-3);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  pointer-events: none;
}
</style>

<script>
(function() {
  const dropZone   = document.getElementById('drop-zone');
  const input      = document.getElementById('images-input');
  const browseBtn  = document.getElementById('browse-btn');
  const dropIdle   = document.getElementById('drop-idle');
  const preview    = document.getElementById('drop-preview');
  const countEl    = document.getElementById('file-count');
  const countNum   = document.getElementById('count-num');
  const submitBtn  = document.getElementById('submit-btn');
  const submitLbl  = document.getElementById('submit-label');
  const form       = document.getElementById('walkthrough-form');

  let files = []; // ordered file list

  /* ---------- open file picker ---------- */
  dropZone.addEventListener('click', function(e) {
    if (e.target === browseBtn || browseBtn.contains(e.target)) return;
    if (e.target.closest('.wt-thumb__rm')) return;
    if (files.length > 0 && e.target.closest('.wt-thumb')) return;
    input.click();
  });
  browseBtn.addEventListener('click', function(e) { e.stopPropagation(); input.click(); });

  input.addEventListener('change', function() {
    addFiles(Array.from(this.files));
    this.value = '';
  });

  /* ---------- drag & drop from OS ---------- */
  ['dragenter','dragover'].forEach(ev => {
    dropZone.addEventListener(ev, function(e) { e.preventDefault(); dropZone.classList.add('drag-over'); });
  });
  dropZone.addEventListener('dragleave', function(e) {
    if (!dropZone.contains(e.relatedTarget)) dropZone.classList.remove('drag-over');
  });
  dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const dropped = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
    if (dropped.length) addFiles(dropped);
  });

  function addFiles(newFiles) {
    newFiles.forEach(f => files.push(f));
    render();
    updatePreviewHero();
  }

  function removeFile(idx) {
    files.splice(idx, 1);
    render();
    updatePreviewHero();
  }

  /* ========== LIVE PREVIEW PANEL ========== */
  const $ = id => document.getElementById(id);
  const fmtMoney = v => {
    const n = parseFloat(v);
    return (isFinite(n) && n > 0) ? '€' + n.toLocaleString('en-US', {maximumFractionDigits: 0}) : null;
  };

  function updatePreviewHero() {
    const hero = $('prev-hero');
    if (files.length > 0) {
      const url = URL.createObjectURL(files[0]);
      hero.innerHTML = '<img src="' + url + '" alt="">';
    } else {
      hero.innerHTML = '<div class="wt-preview-card__hero-empty">' +
        '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>' +
        '<span>360° preview appears here</span></div>';
    }
  }

  function updatePreview() {
    const v = name => (form.elements[name] && form.elements[name].value || '').trim();
    const cb = name => form.elements[name] && form.elements[name].checked;

    // Title + address
    $('prev-title').textContent = v('place_name') || 'Your place name';
    $('prev-addr').textContent  = v('address') || 'Address will appear here';

    // Price
    const rentMoney = fmtMoney(v('monthly_rent'));
    $('prev-price').textContent = rentMoney || '€—';

    // Chips: bedrooms, bathrooms, floor, building type, parking
    const chips = [];
    const bt = v('building_type');
    if (bt) chips.push({label: bt.charAt(0).toUpperCase() + bt.slice(1), gold: false});
    const bd = v('bedrooms');     if (bd) chips.push({label: bd + ' BR', gold: false});
    const ba = v('bathrooms');    if (ba) chips.push({label: ba + ' BA', gold: false});
    const fl = v('floor');        if (fl !== '') chips.push({label: fl + (fl == '1' ? 'st' : fl == '2' ? 'nd' : fl == '3' ? 'rd' : 'th') + ' floor', gold: false});
    const rt = v('rooms_total');  if (rt) chips.push({label: rt + ' rooms', gold: false});
    if (cb('has_parking'))        chips.push({label: '🅿 Parking', gold: true});

    const chipsEl = $('prev-chips');
    if (chips.length === 0) {
      chipsEl.innerHTML = '<span class="wt-chip wt-chip--muted">Add details to populate</span>';
    } else {
      chipsEl.innerHTML = chips.map(c =>
        '<span class="wt-chip' + (c.gold ? ' wt-chip--gold' : '') + '">' + c.label + '</span>'
      ).join('');
    }

    // Deposit + utilities + month total
    const dep = fmtMoney(v('deposit'));
    const utl = fmtMoney(v('monthly_utilities'));
    $('prev-deposit').textContent = dep || '—';
    $('prev-utilities').textContent = utl || '—';

    const rentNum = parseFloat(v('monthly_rent')) || 0;
    const utlNum  = parseFloat(v('monthly_utilities')) || 0;
    const total   = rentNum + utlNum;
    if (total > 0) {
      $('prev-monthtotal-wrap').style.display = '';
      $('prev-monthtotal').textContent = fmtMoney(total);
    } else {
      $('prev-monthtotal-wrap').style.display = 'none';
    }

    // Specialties
    const sp = v('specialties');
    if (sp) {
      $('prev-specialties').style.display = '';
      $('prev-specialties-text').textContent = sp;
    } else {
      $('prev-specialties').style.display = 'none';
    }
  }

  // Wire every form field to the live preview
  form.addEventListener('input',  updatePreview);
  form.addEventListener('change', updatePreview);
  updatePreview(); // initial paint with old_input values

  function render() {
    if (files.length === 0) {
      dropIdle.style.display = '';
      preview.style.display = 'none';
      countEl.style.display = 'none';
      submitBtn.disabled = true;
      submitLbl.textContent = 'Add photos to create';
      return;
    }

    dropIdle.style.display = 'none';
    preview.style.display = '';
    countEl.style.display = '';
    countNum.textContent = files.length;
    submitBtn.disabled = false;
    submitLbl.textContent = 'Create walkthrough (' + files.length + ' room' + (files.length > 1 ? 's' : '') + ')';

    preview.innerHTML = '';
    files.forEach(function(f, i) {
      const thumb = document.createElement('div');
      thumb.className = 'wt-thumb';
      thumb.draggable = true;
      thumb.dataset.idx = i;

      const img = document.createElement('img');
      const url = URL.createObjectURL(f);
      img.src = url;
      img.onload = function() { URL.revokeObjectURL(url); };

      const num = document.createElement('div');
      num.className = 'wt-thumb__num';
      num.textContent = String(i + 1).padStart(2, '0');

      const name = document.createElement('div');
      name.className = 'wt-thumb__name';
      name.textContent = f.name;

      const rm = document.createElement('button');
      rm.type = 'button';
      rm.className = 'wt-thumb__rm';
      rm.innerHTML = '&times;';
      rm.addEventListener('click', function(e) { e.stopPropagation(); removeFile(i); });

      thumb.appendChild(img);
      thumb.appendChild(num);
      thumb.appendChild(name);
      thumb.appendChild(rm);
      preview.appendChild(thumb);
    });

    setupReorder();
  }

  /* ---------- drag-to-reorder thumbnails ---------- */
  var dragSrcIdx = null;
  function setupReorder() {
    preview.querySelectorAll('.wt-thumb').forEach(function(thumb) {
      thumb.addEventListener('dragstart', function(e) {
        dragSrcIdx = parseInt(this.dataset.idx);
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
      });
      thumb.addEventListener('dragend', function() {
        this.classList.remove('dragging');
        preview.querySelectorAll('.wt-thumb').forEach(function(t) { t.classList.remove('drag-target'); });
        dragSrcIdx = null;
      });
      thumb.addEventListener('dragover', function(e) {
        e.preventDefault();
        const tIdx = parseInt(this.dataset.idx);
        if (dragSrcIdx === null || dragSrcIdx === tIdx) return;
        preview.querySelectorAll('.wt-thumb').forEach(function(t) { t.classList.remove('drag-target'); });
        this.classList.add('drag-target');
      });
      thumb.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const targetIdx = parseInt(this.dataset.idx);
        if (dragSrcIdx === null || dragSrcIdx === targetIdx) return;
        var moved = files.splice(dragSrcIdx, 1)[0];
        files.splice(targetIdx, 0, moved);
        render();
      });
    });
  }

  /* ---------- form submit: upload each file DIRECTLY to Supabase ---------- */
  /*           — bypasses Vercel's 4.5MB request-body cap                     */

  const SIGN_URL = <?php echo json_encode(url('/api/storage/sign-upload')); ?>;

  async function getSignedUrl(filename) {
    const res = await fetch(SIGN_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ filename: filename, folder: 'scenes' })
    });
    if (!res.ok) throw new Error('Failed to get signed upload URL (' + res.status + ')');
    return res.json();
  }

  function uploadFileToSupabase(file, signedUploadUrl, onProgress) {
    return new Promise(function(resolve, reject) {
      const xhr = new XMLHttpRequest();
      xhr.open('PUT', signedUploadUrl, true);
      // Supabase needs the right content-type so the served file is recognised as an image
      xhr.setRequestHeader('Content-Type', file.type || 'application/octet-stream');
      xhr.setRequestHeader('x-upsert', 'true');
      xhr.upload.onprogress = function(ev) {
        if (ev.lengthComputable && onProgress) onProgress(ev.loaded / ev.total);
      };
      xhr.onload  = function() {
        if (xhr.status >= 200 && xhr.status < 300) resolve();
        else reject(new Error('Upload failed (' + xhr.status + '): ' + xhr.responseText));
      };
      xhr.onerror = function() { reject(new Error('Network error')); };
      xhr.send(file);
    });
  }

  function setProgress(done, total, currentPct) {
    const overall = ((done + (currentPct || 0)) / total) * 100;
    submitLbl.textContent = 'Uploading ' + Math.min(done + 1, total) + ' / ' + total + ' · ' + Math.round(overall) + '%';
  }

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (files.length === 0) return;

    submitBtn.disabled = true;
    const total = files.length;
    const publicUrls = [];

    try {
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        setProgress(i, total, 0);

        const signed = await getSignedUrl(file.name);
        await uploadFileToSupabase(file, signed.signedUploadUrl, function(pct) {
          setProgress(i, total, pct);
        });
        publicUrls.push(signed.publicUrl);
      }

      submitLbl.textContent = 'Saving walkthrough…';

      // Replace the file input with hidden URL inputs and submit the form
      input.remove();
      publicUrls.forEach(function(u) {
        const hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = 'image_urls[]';
        hidden.value = u;
        form.appendChild(hidden);
      });

      // Submit programmatically — the listener already fired so this won't recurse
      HTMLFormElement.prototype.submit.call(form);

    } catch (err) {
      console.error(err);
      alert('Upload failed: ' + err.message + '\n\nTip: check your network, or try fewer / smaller files.');
      submitBtn.disabled = false;
      submitLbl.textContent = 'Create walkthrough (' + total + ' room' + (total > 1 ? 's' : '') + ')';
    }
  });
})();
</script>
