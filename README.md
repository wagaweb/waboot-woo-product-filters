# Waboot Custom Filters for WooCommerce

This plugin allows shop owners to implement filterable products lists. Compared to WooCommerce defaults filters, the plugin builds a custom table to enhance performaces.

Products can be filtered by any meta fields or taxonomy.

The plugin can be set to apply the filters without reloading the page.

Download the last compiled version [here](http://update.waboot.org/?action=get_metadata&slug=waboot-woo-product-filters&type=plugin) (coming soon). 

## Usage

- Activate the plugin.

- Create the product index. You can do this by going to "Filters settings" section under "WooCommerce" menu in dashboard.

    Here you can select which data should be indexed for each product. After you done it, click the "Create table" button.

- Put the "Waboot Product Filters" widget where do you want to be displayed. It must be displayed in the same page of the product listing.

- If you don't have chose to use async filters, then the setup is now completed.

## Setup async filters

If you want to filter the products without reloading the page, some additional steps must be done.

- In the "Filters Settings" section under "WooCommerce" menu, click the "Options" tab and check "Use async product list".

- In the "Waboot Product Filters" widget options, make sure "Update filters asynchronously" is checked.

- If do not have overwritten the `archive-product.php` file in your theme, the setup is completed.

## Setup async filters with custom `archive-product.php`

If you have overwritten the default WooCommerce `archive-product.php` template and you want to use the filters async feature, you must edit this template.

The only thing that needs to be done is the following:

- Replace the entire products loop with the function: `wbwpf_show_products_async()`

    The products loop starts with the line: `<?php if ( have_posts() ) : ?>` and ends with the line: `<?php endif; ?>`

    More specifically, in the default WooCommerce template it looks like this:

    ```php
    <?php if ( have_posts() ) : ?>

			<?php
				/**
				 * woocommerce_before_shop_loop hook.
				 *
				 * @hooked wc_print_notices - 10
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
			?>

			<?php woocommerce_product_loop_start(); ?>

				<?php woocommerce_product_subcategories(); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php
						/**
						 * woocommerce_shop_loop hook.
						 *
						 * @hooked WC_Structured_Data::generate_product_data() - 10
						 */
						do_action( 'woocommerce_shop_loop' );
					?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php
				/**
				 * woocommerce_after_shop_loop hook.
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
			?>

	<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

			<?php
				/**
				 * woocommerce_no_products_found hook.
				 *
				 * @hooked wc_no_products_found - 10
				 */
				do_action( 'woocommerce_no_products_found' );
			?>

	<?php endif; ?>
    ```

