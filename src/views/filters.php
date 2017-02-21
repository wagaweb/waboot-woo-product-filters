<div class="wbwpf-filters" data-filters>
    <form method="post" action="" data-filters-form>
	<?php if($has_filters): ?>
        <?php foreach ($filters as $filter): ?>
            <?php $filter->display(); ?>
        <?php endforeach; ?>
    <?php else: ?>
        <?php _e("No filters defined in calling function.",$textdomain); ?>
    <?php endif; ?>
        <button type="submit"><?php _ex("Search","Filters search button",$textdomain); ?></button>
    </form>
</div>