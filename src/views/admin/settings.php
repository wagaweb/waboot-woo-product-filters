<p>
	<?php _e("Here you can choose the parameters on which the filters table has to be based.",$textdomain); ?>
</p>
<form id="custom-table-parameters" method="post" action="">
	<?php if($has_taxonomies): ?>
		<h3><?php _e("Taxonomies",$textdomain); ?></h3>
		<p>
			<?php _e("Select one or more taxonomies",$textdomain); ?>
		</p>
		<div class="tax_list">
			<?php foreach ($taxonomies as $tax_name => $tax_label) : ?>
				<label>
					<input type="checkbox" name="wbwpf_use_tax[]" value="<?php echo $tax_name; ?>"><?php echo $tax_label; ?>
				</label>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if($has_metas): ?>
		<h3><?php _e("Product metas",$textdomain); ?></h3>
		<p>
			<?php _e("Select one or more metas",$textdomain); ?>
		</p>
		<div class="meta_list">
			<?php foreach ($metas as $meta_name) : ?>
				<label>
					<input type="checkbox" name="wbwpf_use_meta[]" value="<?php echo $meta_name; ?>"><?php echo $meta_name; ?>
				</label>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<p class="submit">
		<button type="submit" class="button button-primary"><?php _e("Create table",$textdomain); ?></button>
	</p>
</form>