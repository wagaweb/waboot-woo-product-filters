<?php if($has_items): ?>
	<ul class="wbwpf_filters_breadcrumb">
	<?php foreach ($breadcrumb as $item): ?>
		<li><a href="<?php echo $item['link']; ?>"><?php echo $item['label']; ?></a></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>
