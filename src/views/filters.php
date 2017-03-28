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