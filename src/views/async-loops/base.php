<?php
	/*
	 * This is a base template to display the product list asynchronously. You can copy and customize it in your theme.
	 *
	 * This template is used if "use_custom_product_loop_template" is set to FALSE. In this case, "content" contains the whole HTML from content-product.php.
	 */
?>

<div class="wbwpf-product-list" data-async>
    <?php woocommerce_catalog_ordering(); ?>
    <p class="woocommerce-result-count" v-html="result_count_label"></p>
	<?php wc_get_template( 'loop/loop-start.php' ); ?>
		<wbwpf-product v-for="product in products" :key="product.ID" :data="product"></wbwpf-product>
	<?php wc_get_template( 'loop/loop-end.php' ); ?>
    <wbwpf-pagination :current_page="current_page" :total_pages="total_pages" :mid_size="3"></wbwpf-pagination>
</div>

<template id="wbwpf-product-template">
    <span v-html="data.content"></span>
</template>