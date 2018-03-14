<?php if($async): ?>
    <wbwpf-filter
        inline-template
        label="<?php echo $label; ?>"
        slug="<?php echo $slug; ?>"
        v-bind:hidden="<?php echo $display_hidden ? "true" : "false"; ?>"
        v-bind:is_current="<?php echo $display_hidden ? "true" : "false"; ?>"
        v-on:value-selected="updateChildrenValues"
    >
        <div class="wbwpf-filter-wrapper" data-filter='<?php echo $slug; ?>' v-if="!hidden">
            <h3><?php echo $label; ?></h3>
            <?php echo $content; ?>
        </div>
    </wbwpf-filter>
<?php else: ?>
    <div class="wbwpf-filter-wrapper" data-filter='<?php echo $slug; ?>'<?php if($display_hidden): ?> style="display: none;"<?php endif; ?>>
        <h3><?php echo $label; ?></h3>
        <div class="wbwpf-input-wrapper">
            <?php echo $content; ?>
        </div>
    </div>
<?php endif; ?>
