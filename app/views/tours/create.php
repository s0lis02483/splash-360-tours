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

  <form id="walkthrough-form" method="POST" enctype="multipart/form-data"
        action="<?php echo url('/tours/create'); ?>" style="max-width:700px;">
    <?php echo CSRF::field(); ?>

    <!-- Place name -->
    <div class="wt-section">
      <label class="wt-label">Name of the place</label>
      <input class="input wt-name-input" type="text" name="place_name" required
             placeholder="e.g. Oceanview Penthouse, Grand Villa, Downtown Loft…"
             value="<?php $oi = $_SESSION['old_input'] ?? null; echo e(is_array($oi) && isset($oi['place_name']) ? $oi['place_name'] : ''); ?>">
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

</div>

<style>
.wt-section { display:flex;flex-direction:column;gap:8px; }
.wt-label { font-family:var(--font-mono);font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:var(--ink-3); }
.wt-hint { font-size:13px;color:var(--ink-3);margin:0 0 12px; }
.wt-name-input { font-size:17px;padding:14px 16px;height:auto; }

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
  }

  function removeFile(idx) {
    files.splice(idx, 1);
    render();
  }

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

  /* ---------- form submit: attach files in order ---------- */
  form.addEventListener('submit', function(e) {
    if (files.length === 0) { e.preventDefault(); return; }
    try {
      var dt = new DataTransfer();
      files.forEach(function(f) { dt.items.add(f); });
      input.files = dt.files;
    } catch(err) { /* fallback: original input files will upload */ }
    input.style.display = 'block';
    submitBtn.disabled = true;
    submitLbl.textContent = 'Uploading…';
  });
})();
</script>
