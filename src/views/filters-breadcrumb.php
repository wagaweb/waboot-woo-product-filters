<?php if($has_items): ?>
	<ul class="wbwpf_filters_breadcrumb">
	<?php foreach ($breadcrumb as $item): ?>
		<li><a href="<?php echo $item['link']; ?>"><?php echo $item['label']; ?></a>[<a href="<?php echo $item['delete_link']; ?>">X</a>]</li>
	<?php endforeach; ?>
	</ul>
    <div style="display: inline-block; text-align: right;"><a href="<?php echo $clear_all_url; ?>"><?php echo $clear_all_label ?></a></div>
<?php endif; ?>
