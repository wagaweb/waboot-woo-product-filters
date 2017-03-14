<div class="wbwpf-filter-wrapper" data-filter='<?php echo $slug; ?>'<?php if($display_hidden): ?> style="display: none;"<?php endif; ?>>
    <h3><?php echo $label; ?></h3>
    <div class="wbwpf-input-wrapper">
        <?php echo $content; ?>
        <input type='hidden' name='wbwpf_active_filters[<?php echo $slug; ?>][slug]' value='<?php echo $slug; ?>'>
        <input type='hidden' name='wbwpf_active_filters[<?php echo $slug; ?>][type]' value='<?php echo $uiType; ?>'>
        <input type='hidden' name='wbwpf_active_filters[<?php echo $slug; ?>][dataType]' value='<?php echo $dataType; ?>'>
    </div>
</div>

