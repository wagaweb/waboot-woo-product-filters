<?php if($async): ?>
    <div class="wbwpf-filters" data-filters='<?php echo wbwpf_get_current_active_filters(true); ?>' data-has_button="<?php echo $display_apply_button; ?>" data-async>
        <div class="loading" v-show="!updated">Loading...</div>
        <form method="get" action="<?php echo $form_action_url; ?>" data-filters-form v-show="updated">
        <?php if($has_filters): ?>
		    <?php foreach ($filters as $filter): ?>
			    <?php $filter->display(true); ?>
		    <?php endforeach; ?>
	    <?php endif; ?>
	        <?php if($display_apply_button): ?>
                <button name="wbwpf_search_by_filters" value="1" type="submit" data-apply_button><?php _ex("Search","Filters search button",$textdomain); ?></button>
	        <?php endif; ?>
        </form>
    </div>
<?php else: ?>
    <div class="wbwpf-filters" data-filters>
        <form method="get" action="<?php echo $form_action_url; ?>" data-filters-form>
        <?php if($has_filters): ?>
            <?php foreach ($filters as $filter): ?>
                <?php $filter->display(); ?>
            <?php endforeach; ?>
        <?php else: ?>
            <?php _e("No filters defined in calling function.",$textdomain); ?>
        <?php endif; ?>
            <?php if($has_products): ?>
            <button name="wbwpf_search_by_filters" value="1" type="submit"><?php _ex("Search","Filters search button",$textdomain); ?></button>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>