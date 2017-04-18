<?php
	/*
	 * This is a base template to display the product list asynchronously. You can copy and customize it in your theme.
	 *
	 * This template is used if "use_custom_product_loop_template" is set to FALSE. In this case, "content" contains the whole HTML from content-product.php.
	 */
?>

<div class="wbwpf-product-list" data-async>
	<ul class="products">
		<wbwpf-product v-for="product in products" :data="product"></wbwpf-product>
	</ul>
</div>

<template id="wbwpf-product-template">
    <div v-html="data.content"></div>
</template>