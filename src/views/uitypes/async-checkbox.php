<label v-for="item in items" v-show="item.visible">
	<input type="checkbox" :value="item.id" name="<?php echo $input_name; ?>[]" v-model="currentValues" v-on:change="valueSelected">
	{{ item.label }}
</label>