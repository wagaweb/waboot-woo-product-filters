<?php
	/*
	 * This is a base template to display the product list asynchronously. You can copy and customize it in your theme.
	 */
?>

<div class="wbwpf-product-list" data-async>
	<ul class="products">
		<wbwpf-product v-for="product in products" :data="product"></wbwpf-product>
	</ul>
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