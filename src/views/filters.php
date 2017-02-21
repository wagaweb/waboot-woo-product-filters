<div class="wbwpf-filters" data-filters>
    <form method="post" action="<?php echo wc_get_page_permalink("shop"); ?>" data-filters-form>
	<?php if($has_filters): ?>
        <?php foreach ($filters as $filter): ?>
            <?php $filter->display(); ?>
        <?php endforeach; ?>
    <?php else: ?>
        <?php _e("No filters defined in calling function.",$textdomain); ?>
    <?php endif; ?>
        <button name="wbwpf_search_by_filters" type="submit"><?php _ex("Search","Filters search button",$textdomain); ?></button>
    </form>
</div>