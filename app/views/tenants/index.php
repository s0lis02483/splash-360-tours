<?php // FILE: /app/views/tenants/index.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Platform</span>
    <span class="sep">/</span>
    <span class="here">Tenants</span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <a href="<?php echo url('/tenants/create'); ?>" class="btn btn-primary btn-sm">+ Add tenant</a>
  </div>
</header>

<div class="page-body fade-up">

  <div class="tape-head">
    <h1 class="tape-head__title">All <em>tenants</em></h1>
    <span class="tape-head__meta"><?php echo count($tenants); ?> studios</span>
  </div>

  <div class="tape">
    <?php foreach ($tenants as $i => $t): ?>
    <div class="tape__row">
      <div class="tape__num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></div>
      <div class="tape__thumb" style="display:grid;place-items:center;font-family:var(--font-display);font-size:18px;color:var(--gold);">
        <?php echo strtoupper(substr($t['name'], 0, 1)); ?>
      </div>
      <div class="tape__info">
        <div class="tape__title"><?php echo e($t['name']); ?></div>
        <div class="tape__sub"><?php echo e($t['email']); ?></div>
        <div class="tape__meta">
          <span><?php echo $t['user_count']; ?> users</span>
        </div>
      </div>
      <div class="tape__chips">
        <?php $sc = $t['status'] === 'active' ? 'chip-good' : 'chip-danger'; ?>
        <span class="chip <?php echo $sc; ?>"><?php echo ucfirst($t['status']); ?></span>
      </div>
      <div class="tape__actions">
        <a href="<?php echo url('/tenants/' . $t['id'] . '/edit'); ?>" class="btn btn-sm btn-ghost">Edit</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

</div>
