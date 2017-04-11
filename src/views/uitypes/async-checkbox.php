<label v-for="item in items">
	<input type="checkbox" value="<?php echo $k ?>" name="<?php echo $input_name; ?>[]" checked v-if="item.selected">
	<input type="checkbox" value="<?php echo $k ?>" name="<?php echo $input_name; ?>[]" v-else>
	{{ item }}
</label>