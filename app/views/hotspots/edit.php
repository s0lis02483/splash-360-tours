<?php // FILE: /app/views/hotspots/edit.php - Similar to create.php but with pre-filled values ?>
<h1>Edit Hotspot</h1>
<form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/' . $hotspot['id'] . '/edit'); ?>">
    <?php echo CSRF::field(); ?>
    <div class="form-group"><label>Type</label><select name="type" id="hotspot-type"><?php foreach($types as $k=>$v): ?><option value="<?php echo $k; ?>" <?php echo $hotspot['type']==$k?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Yaw</label><input type="number" name="yaw" value="<?php echo $hotspot['yaw']; ?>" step="0.1"></div>
    <div class="form-group"><label>Pitch</label><input type="number" name="pitch" value="<?php echo $hotspot['pitch']; ?>" step="0.1"></div>
    <div class="form-group"><label>Label</label><input type="text" name="label" value="<?php echo e($hotspot['label']); ?>"></div>
    <div class="form-group"><label>Description</label><textarea name="description"><?php echo e($hotspot['description']); ?></textarea></div>
    <div class="form-group"><label>Target Scene</label><select name="target_scene_id"><?php foreach($tour['scenes'] as $s): if($s['id']!=$scene['id']): ?><option value="<?php echo $s['id']; ?>" <?php echo $hotspot['target_scene_id']==$s['id']?'selected':''; ?>><?php echo e($s['name']); ?></option><?php endif; endforeach; ?></select></div>
    <div class="form-group"><label>External URL</label><input type="url" name="external_url" value="<?php echo e($hotspot['external_url']); ?>"></div>
    <div class="form-group"><label>Icon</label><select name="icon_type"><?php foreach($icon_types as $k=>$v): ?><option value="<?php echo $k; ?>" <?php echo $hotspot['icon_type']==$k?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?></select></div>
    <button type="submit" class="btn btn-primary">Update</button>
    <form method="POST" action="<?php echo url('/tours/' . $tour['id'] . '/scenes/' . $scene['id'] . '/hotspots/' . $hotspot['id'] . '/delete'); ?>" style="display:inline;">
        <?php echo CSRF::field(); ?>
        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</button>
    </form>
</form>
