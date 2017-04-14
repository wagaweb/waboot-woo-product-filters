<?php
/*
 * This is a base template to display the product list asynchronously. You can copy and customize it in your theme.
 */
?>

<div class="wbwpf-product-list" data-async>
	<ul class="products">
		<wbwpf-product v-for="product in products" :data="product" :key="product.ID"></wbwpf-product>
	</ul>
</div>

<template id="wbwpf-product-template">
	<li :class="data.post_class">
		<a :href="data.product_link" class="woocommerce-LoopProduct-link">
			<div v-html="data.img_html"></div>
			<h3>{{ data.title }}</h3>
			<div v-html="data.rating_html"></div>
			<div v-html="data.price_html"></div>
		</a>
		{{ data.add_to_cart }}
	</li>
</template>