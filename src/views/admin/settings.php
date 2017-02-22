<p>
	<?php _e("Here you can choose the parameters on which the filters table has to be based.",$textdomain); ?>
</p>
<form id="custom-table-parameters" method="post" action="">
	<?php if($has_data): ?>
		<?php foreach ($data as $d): ?>
            <h3><?php echo $d['label']; ?></h3>
            <p>
				<?php echo $d['description']; ?>
            </p>
            <div class="<?php echo $d['slug'] ?>_list">
				<?php foreach ($d['data'] as $name => $label): ?>
                    <label>
                        <input type="checkbox" name="wbwpf_use_<?php echo $d['slug'] ?>[]" value="<?php echo $name; ?>" data-datatype="<?php echo $d['slug'] ?>"><?php echo $label; ?>
                    </label>
				<?php endforeach; ?>
            </div>
		<?php endforeach; ?>
        <p class="submit">
            <button type="submit" class="button button-primary"><?php _e("Create table",$textdomain); ?></button>
        </p>
	<?php endif; ?>
</form>
<div id="progress-wrapper" style="margin: 10px 0;"></div>
<script type="text/template" id="progress-tpl">
    <div class="progress-meter">
        Indexing <%= total %> products...
    </div>
    <div class="progress-bar-wrapper" style="width: 100%">
        <div class="progress-bar" style="text-align: center; width: <%= current_percentage %>%; background-color: #00a8c6;"><%= current_percentage %>%</div>
    </div>
</script>