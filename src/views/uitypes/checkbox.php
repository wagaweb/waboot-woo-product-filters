<?php foreach ($values as $k => $v): ?>
	<?php $hidden = in_array($v,$hidden_values); ?>
    <label <?php if($hidden): ?>style="display: none;"<?php endif; ?>>
		<?php if(in_array($k,$selected_values)): ?>
            <input type="checkbox" value="<?php echo $k ?>" name="<?php echo $input_name; ?>[]" checked>
		<?php else: ?>
            <input type="checkbox" value="<?php echo $k ?>" name="<?php echo $input_name; ?>[]">
		<?php endif; ?>
		<?php echo $v; ?>
    </label>
<?php endforeach; ?>
