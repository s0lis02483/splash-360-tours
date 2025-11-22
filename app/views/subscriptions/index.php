<?php // FILE: /app/views/subscriptions/index.php ?>
<h1>Subscription & Billing</h1>
<?php if($subscription): ?><div class="subscription-info"><h2>Current Plan: <?php echo e($subscription['plan_name']); ?></h2><p>Status: <?php echo ucfirst($subscription['status']); ?></p><a href="<?php echo url('/subscriptions/plans'); ?>" class="btn">Change Plan</a></div><?php endif; ?>
<h2>Usage</h2>
<p>Properties: <?php echo $usage_data['usage']['properties']; ?> / <?php echo $usage_data['limits']['properties']==-1?'Unlimited':$usage_data['limits']['properties']; ?></p>
<p>Tours: <?php echo $usage_data['usage']['tours']; ?> / <?php echo $usage_data['limits']['tours']==-1?'Unlimited':$usage_data['limits']['tours']; ?></p>
<h2>Recent Invoices</h2>
<table class="data-table"><thead><tr><th>Invoice #</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($invoices as $inv): ?><tr><td><?php echo e($inv['invoice_number']); ?></td><td><?php echo formatCurrency($inv['amount']); ?></td><td><?php echo ucfirst($inv['status']); ?></td><td><?php if($inv['status']=='pending'): ?><form method="POST" action="<?php echo url('/subscriptions/invoices/'.$inv['id'].'/pay'); ?>"><?php echo CSRF::field(); ?><button type="submit" class="btn btn-sm">Pay Now</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table>
