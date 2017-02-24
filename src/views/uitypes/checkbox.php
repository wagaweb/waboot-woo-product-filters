<?php foreach ($values as $k => $v): ?>
    <label>
		<?php if(in_array($k,$selected_values)): ?>
            <input type="checkbox" value="<?php echo $k ?>" name="<?php echo $input_name; ?>[]" checked>
		<?php else: ?>
            <input type="checkbox" value="<?php echo $k ?>" name="<?php echo $input_name; ?>[]">
		<?php endif; ?>
		<?php echo $v; ?>
    </label>
<?php endforeach; ?>
