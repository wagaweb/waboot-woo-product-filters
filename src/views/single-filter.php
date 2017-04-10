<?php if($async): ?>
    <Filter inline-template label="<?php echo $label; ?>" slug="<?php echo $slug; ?>" hidden="<?php echo $display_hidden; ?>" v-show="!hidden">
        <h3>{{ label }}</h3>
        {{ content }}
    </Filter>
<?php else: ?>
    <div class="wbwpf-filter-wrapper" data-filter='<?php echo $slug; ?>'<?php if($display_hidden): ?> style="display: none;"<?php endif; ?>>
        <h3><?php echo $label; ?></h3>
        <div class="wbwpf-input-wrapper">
            <?php echo $content; ?>
        </div>
    </div>
<?php endif; ?>
