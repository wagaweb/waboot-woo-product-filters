<?php foreach ($values as $k => $v): ?>
	<label>
		<input type="checkbox" value="<?php echo $k ?>" name="<?php echo $input_name; ?>[]">
	</label><?php echo $v; ?>
<?php endforeach; ?>
