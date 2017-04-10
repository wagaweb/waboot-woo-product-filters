<?php if($async): ?>
    <filter data-filter data-slug="<?php echo $slug; ?>" data-hidden="<?php if($display_hidden) echo "1"; else "0"; ?>"></filter>
<?php else: ?>
    <div class="wbwpf-filter-wrapper" data-filter='<?php echo $slug; ?>'<?php if($display_hidden): ?> style="display: none;"<?php endif; ?>>
        <h3><?php echo $label; ?></h3>
        <div class="wbwpf-input-wrapper">
            <?php echo $content; ?>
        </div>
    </div>
<?php endif; ?>
