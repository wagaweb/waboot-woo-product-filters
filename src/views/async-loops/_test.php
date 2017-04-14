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
	<div v-html="data.content"></div>
</template>