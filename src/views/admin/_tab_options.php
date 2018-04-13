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
    <label class="option">
        <?php $checked = isset($current_settings['show_variations']) && $current_settings['show_variations']; ?>
        <input type="checkbox" value="1" name="wbwpf_options[show_variations]" <?php if($checked): ?>checked<?php endif; ?>>
	    <?php _e("Show variations alongside products.",$textdomain); ?>
    </label>
    <label class="option">
	    <?php $checked = isset($current_settings['hide_parent_products']) && $current_settings['hide_parent_products']; ?>
        <input type="checkbox" value="1" name="wbwpf_options[hide_parent_products]" <?php if($checked): ?>checked<?php endif; ?>>
	    <?php _e("Hide parent products when variations are displayed.",$textdomain); ?>
    </label>
    <h3><?php _e("Layout settings",$textdomain); ?></h3>
    <label class="option">
		<?php $checked = isset($current_settings['use_async_product_list']) && $current_settings['use_async_product_list']; ?>
        <input type="checkbox" value="1" name="wbwpf_options[use_async_product_list]" <?php if($checked): ?>checked<?php endif; ?>>
		<?php _e("Use async product list.",$textdomain); ?>
        <p><?php _e('With this setting enabled, the <code>archive-product.php</code> template within the plugin will be used. If your theme already override this template, you will need to change your template to make it work.'); ?></p>
        <p><?php _e('To customize your template to support the async product list, you have to insert the function <code>wbwpf_show_products_async()</code> where do you want to display the products.') ?></p>
    </label>
    <label class="option">
		<?php $checked = isset($current_settings['use_custom_product_loop_template']) && $current_settings['use_custom_product_loop_template']; ?>
        <input type="checkbox" value="1" name="wbwpf_options[use_custom_product_loop_template]" <?php if($checked): ?>checked<?php endif; ?>>
		<?php _e("Use a custom product loop template instead of content-product.php",$textdomain); ?>
        <p><?php _e('Enable this setting if you want to use a custom template for products loop within the <code>wbwpf_show_products_async()</code> function. The custom loop template can be created at <em>/waboot-woo-product-filters/async-products-loops/base-custom.php</em> within your theme directory.'); ?></p>
    </label>
    <h3><?php _e("Widget settings",$textdomain); ?></h3>
    <label class="option">
		<?php $checked = isset($current_settings['widget_use_js']) && $current_settings['widget_use_js']; ?>
        <input type="checkbox" value="1" name="wbwpf_options[widget_use_js]" <?php if($checked): ?>checked<?php endif; ?>>
		<?php _e("Use Javascript to reload the page",$textdomain); ?>
        <p><?php _e('Enable this setting if you want to reload the page when a filter is selected', $textdomain) ?></p>
    </label>
    <label class="option">
		<?php $checked = isset($current_settings['widget_display_apply_button']) && $current_settings['widget_display_apply_button']; ?>
        <input type="checkbox" value="1" name="wbwpf_options[widget_display_apply_button]" <?php if($checked): ?>checked<?php endif; ?>>
		<?php _e("Display the apply button",$textdomain); ?>
    </label>
    <button type="submit" class="button button-primary" name="wbwpf_save_settings" value="1"><?php _e("Save settings",$textdomain); ?></button>
</form>
