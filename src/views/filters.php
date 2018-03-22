<?php if($use_js): ?>
    <div class="wbwpf-filters" data-async>
        <filters-list
                inline-template
                data-filters='<?php echo wbwpf_get_current_active_filters(true); ?>'
        >
            <div class="filters-list">
                <div class="filters-loading" v-show="!updated"><?php _e("Loading...",$textdomain); ?></div>
                <form method="get" action="<?php echo $form_action_url; ?>" data-filters-form v-show="updated">
		            <?php if($has_filters): ?>
			            <?php foreach ($filters as $filter): ?>
				            <?php $filter->display(true); ?>
			            <?php endforeach; ?>
		            <?php endif; ?>
                    <button v-if="hasSubmitButton" name="wbwpf_search_by_filters" value="1" type="submit" data-apply_button :disabled="!updated"><?php _ex("Search","Filters search button",$textdomain); ?></button>
		                <input type="hidden" v-if="!hasSubmitButton" name="wbwpf_search_by_filters" value="1">
                    <?php do_action("wbwpf/form/async/after_submit"); ?>
                </form>
            </div>
        </filters-list>
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
            <?php if($has_products && $display_apply_button): ?>
            <button name="wbwpf_search_by_filters" value="1" type="submit"><?php _ex("Search","Filters search button",$textdomain); ?></button>
            <?php endif; ?>
	        <?php do_action("wbwpf/form/after_submit"); ?>
        </form>
    </div>
<?php endif; ?>
