<div class="wbwpf-filters" data-filters>
	<?php if($has_filters): ?>
        <?php foreach ($filters as $filter): ?>
            <?php $filter->display(); ?>
        <?php endforeach; ?>
    <?php else: ?>
        <?php _e("No filters defined in calling function.",$textdomain); ?>
    <?php endif; ?>
</div>