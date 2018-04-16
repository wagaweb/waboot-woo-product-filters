<label>
	<select name="<?php echo $input_name; ?>" v-model="currentValues" v-on:change="valueSelected">
		<option v-for="item in items" v-show="item.visible" :value="item.id">{{ item.label }}</option>
	</select>
</label>