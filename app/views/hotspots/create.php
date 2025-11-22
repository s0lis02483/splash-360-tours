<?php // FILE: /app/views/hotspots/create.php ?>
<h1>Add Hotspot</h1>
<form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/create'); ?>">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Type</label><select name="type" id="hotspot-type" required><?php foreach($types as $k=>$v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Yaw</label><input type="number" name="yaw" required value="0" step="0.1"></div>
    <div class="form-group"><label>Pitch</label><input type="number" name="pitch" required value="0" step="0.1"></div>
    <div class="form-group"><label>Label</label><input type="text" name="label"></div>
    <div class="form-group"><label>Description</label><textarea name="description"></textarea></div>
    <div class="form-group" id="target-scene-field"><label>Target Scene</label><select name="target_scene_id"><?php foreach($tour['scenes'] as $s): if($s['id']!=$scene['id']): ?><option value="<?php echo $s['id']; ?>"><?php echo e($s['name']); ?></option><?php endif; endforeach; ?></select></div>
    <div class="form-group" id="external-url-field" style="display:none;"><label>External URL</label><input type="url" name="external_url"></div>
    <div class="form-group"><label>Icon Type</label><select name="icon_type"><?php foreach($icon_types as $k=>$v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?></select></div>
    <button type="submit" class="btn btn-primary">Add Hotspot</button>
    <a href="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/edit'); ?>" class="btn">Cancel</a>
</form>
<script>
document.getElementById('hotspot-type').addEventListener('change', function() {
    document.getElementById('target-scene-field').style.display = this.value === 'navigation' ? 'block' : 'none';
    document.getElementById('external-url-field').style.display = this.value === 'link' ? 'block' : 'none';
});
</script>
