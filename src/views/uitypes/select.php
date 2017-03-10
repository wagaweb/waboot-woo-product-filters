<label>
    <select>
        <?php foreach ($values as $k => $v): ?>
            <?php $hidden = in_array($v,$hidden_values); ?>
	        <?php if($hidden){ continue; } //Skip hidden values ?>
            <?php if(in_array($k,$selected_values)): ?>
                <option type="checkbox" value="<?php echo $k ?>" name="<?php echo $input_name; ?>[]" selected></option>
            <?php else: ?>
                <option type="checkbox" value="<?php echo $k ?>" name="<?php echo $input_name; ?>[]"><?php echo $v; ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
</label>