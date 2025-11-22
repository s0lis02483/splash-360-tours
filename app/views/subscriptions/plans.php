<?php // FILE: /app/views/subscriptions/plans.php ?>
<h1>Available Plans</h1>
<div class="plans-grid">
<?php foreach ($plans as $plan): ?>
<div class="plan-card"><h3><?php echo e($plan['name']); ?></h3><p class="price"><?php echo formatCurrency($plan['price']); ?>/<?php echo $plan['billing_period']; ?></p><ul><li>Properties: <?php echo $plan['max_properties']==-1?'Unlimited':$plan['max_properties']; ?></li><li>Tours: <?php echo $plan['max_tours']==-1?'Unlimited':$plan['max_tours']; ?></li><li>Scenes: <?php echo $plan['max_scenes']==-1?'Unlimited':$plan['max_scenes']; ?></li><li>Storage: <?php echo $plan['max_storage_size']; ?>MB</li></ul><form method="POST" action="<?php echo url('/subscriptions/change-plan'); ?>"><?php echo CSRF::field(); ?><input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>"><button type="submit" class="btn btn-primary">Select Plan</button></form></div>
<?php endforeach; ?>
</div>
