<p xmlns="http://www.w3.org/1999/html">
    <?php _e("Here you can customize some of the plugin behaviors.",$textdomain); ?>
</p>

<form action="" method="post">
    <h3><?php _e("Active filters settings",$textdomain); ?></h3>
    <?php foreach ($current_settings['filters_params'] as $filter_slug => $filter_params): ?>
        <h4><?php echo $filter_slug; ?></h4>
        <select name="wbwpf_options[filters_params][<?php echo $filter_slug; ?>][uiType]">
            <?php foreach ($available_uiTypes as $uiType_slug => $uiType_class) : ?>
                <?php $selected = isset($current_settings['filters_params'][$filter_slug]['uiType']) && $current_settings['filters_params'][$filter_slug]['uiType'] == $uiType_slug; ?>
                <option value="<?php echo $uiType_slug; ?>" <?php if($selected): ?>selected<?php endif; ?>><?php echo $uiType_slug; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="wbwpf_options[filters_params][<?php echo $filter_slug; ?>][dataType]" value="<?php echo $filter_params['dataType']; ?>">
    <?php endforeach; ?>
    <h3><?php _e("Catalog settings",$textdomain); ?></h3>
    <label style="display: block; margin-bottom: 1em;">
        <?php $checked = isset($current_settings['show_variations']) && $current_settings['show_variations']; ?>
        <input type="checkbox" value="1" name="wbwpf_options[show_variations]" <?php if($checked): ?>checked<?php endif; ?>>
	    <?php _e("Show variations alongside products.",$textdomain); ?>
    </label>
    <label style="display: block; margin-bottom: 1em;">
	    <?php $checked = isset($current_settings['hide_parent_products']) && $current_settings['hide_parent_products']; ?>
        <input type="checkbox" value="1" name="wbwpf_options[hide_parent_products]" <?php if($checked): ?>checked<?php endif; ?>>
	    <?php _e("Hide parent products when variations are displayed.",$textdomain); ?>
    </label>
    <label style="display: block; margin-bottom: 1em;">
		<?php $checked = isset($current_settings['use_async_product_list']) && $current_settings['use_async_product_list']; ?>
        <input type="checkbox" value="1" name="wbwpf_options[use_async_product_list]" <?php if($checked): ?>checked<?php endif; ?>>
		<?php _e("Use async product list.",$textdomain); ?>
    </label>
    <button type="submit" class="button button-primary" name="wbwpf_save_settings" value="1"><?php _e("Save settings",$textdomain); ?></button>
</form>