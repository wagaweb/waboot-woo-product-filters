<div class=wbwpf-filter-wrapper data-filter='<?php echo $slug; ?>'>
    <h3><?php echo $label; ?></h3>
    <?php echo $content; ?>
    <input type='hidden' name='wbwpf_active_filters[<?php echo $slug; ?>][slug]' value='<?php echo $slug; ?>'>
    <input type='hidden' name='wbwpf_active_filters[<?php echo $slug; ?>][type]' value='<?php echo $uiType; ?>'>
    <input type='hidden' name='wbwpf_active_filters[<?php echo $slug; ?>][dataType]' value='<?php echo $dataType; ?>'>
</div>

