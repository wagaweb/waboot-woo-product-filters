<?php
	/*
	 * This is a base template to display the product list asynchronously. You can copy and customize it in your theme.
	 *
	 * This template is used if "use_custom_product_loop_template" is set to FALSE. In this case, "content" contains the whole HTML from content-product.php.
	 */
?>

<div class="wbwpf-product-list" data-async>
    <products-list inline-template>
        <div class="wbwpf-product-list-wrapper">
            <div class="products-loading" v-show="!updated"></div>
            <p class="woocommerce-result-count" v-html="result_count_label"></p>
	        <?php woocommerce_catalog_ordering(); ?>
	        <?php woocommerce_product_loop_start(); ?>
            <wbwpf-product v-for="product in products" :key="product.ID" :data="product"></wbwpf-product>
	        <?php woocommerce_product_loop_end(); ?>
            <wbwpf-pagination :current_page="current_page" :total_pages="total_pages" :mid_size="3"></wbwpf-pagination>
        </div>
    </products-list>
</div>

<script type="text/x-template" id="wbwpf-product-template">
    <?php do_action('wbwpf/woocommerce/single-product/async/display'); ?>
</script>