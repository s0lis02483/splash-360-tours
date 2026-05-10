<?php // FILE: /app/views/users/index.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Account</span>
    <span class="sep">/</span>
    <span class="here">Team</span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <a href="<?php echo url('/users/create'); ?>" class="btn btn-primary btn-sm">+ Add member</a>
  </div>
</header>

<div class="page-body fade-up">

  <div class="tape-head">
    <h1 class="tape-head__title">Team <em>members</em></h1>
    <span class="tape-head__meta"><?php echo count($users); ?> users</span>
  </div>

  <div class="card">
    <div class="card-body" style="padding:0;">
      <table class="data-table">
        <thead>
          <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <div class="side__avatar" style="width:26px;height:26px;font-size:12px;"><?php echo strtoupper(substr($u['name'], 0, 1)); ?></div>
                <?php echo e($u['name']); ?>
              </div>
            </td>
            <td style="color:var(--ink-3);font-size:12px;"><?php echo e($u['email']); ?></td>
            <td><span class="chip"><?php echo ucfirst(str_replace('_', ' ', $u['role'])); ?></span></td>
            <td>
              <?php $sc = $u['status'] === 'active' ? 'chip-good' : 'chip-danger'; ?>
              <span class="chip <?php echo $sc; ?>"><?php echo ucfirst($u['status']); ?></span>
            </td>
            <td>
              <a href="<?php echo url('/users/' . $u['id'] . '/edit'); ?>" class="btn btn-sm btn-ghost">Edit</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
