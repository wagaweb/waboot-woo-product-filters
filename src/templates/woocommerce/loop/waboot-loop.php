<?php

/**
 * Hook: woocommerce_before_shop_loop.
 *
 * @hooked wc_print_notices - 10
 */
do_action( 'woocommerce_before_shop_loop' );

woocommerce_product_loop_start();

wbwpf_show_products_async();

woocommerce_product_loop_end();

/**
 * Hook: woocommerce_after_shop_loop.
 *
 * @hooked woocommerce_pagination - 10
 */
do_action( 'woocommerce_after_shop_loop' );