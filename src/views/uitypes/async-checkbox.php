<label v-for="item in items">
	<input type="checkbox" :value="item.id" name="<?php echo $input_name; ?>[]" checked v-if="item.selected">
	<input type="checkbox" :value="item.id" name="<?php echo $input_name; ?>[]" v-else>
	{{ item.label }}
</label>