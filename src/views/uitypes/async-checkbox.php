<label v-for="item in items">
	<input type="checkbox" :value="item.id" name="<?php echo $input_name; ?>[]" checked v-if="item.selected" v-model="currentValues" v-on:click="valueSelected">
	<input type="checkbox" :value="item.id" name="<?php echo $input_name; ?>[]" v-else v-model="currentValues" v-on:click="valueSelected">
	{{ item.label }}
</label>