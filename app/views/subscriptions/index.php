<?php // FILE: /app/views/subscriptions/index.php ?>

<header class="top">
  <div class="top__crumb">
    <span>Account</span>
    <span class="sep">/</span>
    <span class="here">Subscription</span>
  </div>
  <div class="top__spacer"></div>
  <div class="top__actions">
    <a href="<?php echo url('/subscriptions/plans'); ?>" class="btn btn-outline-gold btn-sm">Change plan</a>
  </div>
</header>

<div class="page-body fade-up">

  <?php if ($subscription): ?>
  <!-- Current plan -->
  <div class="metrics" style="grid-template-columns:repeat(3,1fr);margin-bottom:28px;">
    <div class="metric">
      <div class="metric__label">Current plan</div>
      <div class="metric__value" style="font-size:24px;color:var(--gold);"><?php echo e($subscription['plan_name']); ?></div>
      <div class="metric__delta">
        <?php $sc = $subscription['status'] === 'active' ? 'chip-good' : ''; ?>
        <span class="chip <?php echo $sc; ?>" style="margin-top:4px;"><?php echo ucfirst($subscription['status']); ?></span>
      </div>
    </div>
    <div class="metric">
      <div class="metric__label">Properties used</div>
      <div class="metric__value"><?php echo $usage_data['usage']['properties']; ?></div>
      <div class="metric__delta">
        of <?php echo $usage_data['limits']['properties'] == -1 ? 'unlimited' : $usage_data['limits']['properties']; ?>
      </div>
    </div>
    <div class="metric">
      <div class="metric__label">Tours used</div>
      <div class="metric__value"><?php echo $usage_data['usage']['tours']; ?></div>
      <div class="metric__delta">
        of <?php echo $usage_data['limits']['tours'] == -1 ? 'unlimited' : $usage_data['limits']['tours']; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Invoices -->
  <div class="section-header">
    <h2 class="section-title">Billing <em>history</em></h2>
  </div>

  <div class="card">
    <div class="card-body" style="padding:0;">
      <?php if (!empty($invoices)): ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Invoice</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoices as $inv): ?>
          <tr>
            <td style="font-family:var(--font-mono);font-size:12px;"><?php echo e($inv['invoice_number']); ?></td>
            <td style="font-family:var(--font-mono);"><?php echo isset($inv['amount']) ? '€' . number_format($inv['amount'], 2) : '—'; ?></td>
            <td>
              <?php $sc = $inv['status'] === 'paid' ? 'chip-good' : ($inv['status'] === 'pending' ? 'chip-gold' : 'chip-danger'); ?>
              <span class="chip <?php echo $sc; ?>"><?php echo ucfirst($inv['status']); ?></span>
            </td>
            <td style="font-size:12px;color:var(--ink-3);"><?php echo isset($inv['created_at']) ? date('d M Y', strtotime($inv['created_at'])) : '—'; ?></td>
            <td>
              <?php if ($inv['status'] === 'pending'): ?>
              <form method="POST" action="<?php echo url('/subscriptions/invoices/' . $inv['id'] . '/pay'); ?>">
                <?php echo CSRF::field(); ?>
                <button type="submit" class="btn btn-sm btn-primary">Pay now</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="empty">
        <div class="empty__title">No invoices yet</div>
        <div class="empty__sub">Your billing history will appear here</div>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
