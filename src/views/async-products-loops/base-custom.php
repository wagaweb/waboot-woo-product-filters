<?php
	/*
	 * This is a base template to display the product list asynchronously. You can copy and customize it in your theme.
	 *
	 * This template is used if "use_custom_product_loop_template" is set to TRUE.
	 */
?>

<div class="wbwpf-product-list" data-async>
	<?php wc_get_template( 'loop/loop-start.php' ); ?>
		<wbwpf-product v-for="product in products" :key="product.ID" :data="product"></wbwpf-product>
	<?php wc_get_template( 'loop/loop-end.php' ); ?>
</div>

<template id="wbwpf-product-template">
	<li :class="data.post_class">
		<a href="{{ data.product_link }}" class="woocommerce-LoopProduct-link">
			{{ data.img }}
			<h3>{{ data.title }}</h3>
			<div class="star-rating" title="{{ data.rating_label }}">
				<span style="width:100%">
					<strong class="rating">{{ data.rating }}</strong> out of 5
				</span>
			</div>
			{{ data.price }}
		</a>
		{{ data.add_to_cart }}
	</li>
</template>