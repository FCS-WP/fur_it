<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

get_header('shop');

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
echo '<div class="container">';
echo '<div class="row">';
echo '<div class="col large-3">';
echo do_shortcode('[zippy_shop_filter
    show_price="true"
    min_price="0"
    show_category="true"
    show_brand="true"
    brand_taxonomy="product_brand"
]');
echo '</div>';
echo '<div class="col large-9">';
echo do_shortcode('[zippy_category_tabs align="left"]
    [zippy_category_tab label="Dogs"       icon="/wp-content/uploads/2026/03/cropped-Fur-It_secondary-logo.jpeg"  url="/product-category/pet"]
    [zippy_category_tab label="Cats"       icon="/wp-content/uploads/2026/03/cropped-Fur-It_secondary-logo.jpeg"  url="/product-category/pet"]
    [zippy_category_tab label="Fish"       icon="/wp-content/uploads/2026/03/cropped-Fur-It_secondary-logo.jpeg" url="/product-category/pet"]
    [zippy_category_tab label="Birds"      icon="/wp-content/uploads/2026/03/cropped-Fur-It_secondary-logo.jpeg" url="/product-category/pes"]
    [zippy_category_tab label="Small Pets" icon="/wp-content/uploads/2026/03/cropped-Fur-It_secondary-logo.jpeg" url="/product-category/pet"]
[/zippy_category_tabs]');

do_action( 'woocommerce_before_main_content' );

do_action('woocommerce_shop_loop_header');

if (woocommerce_product_loop()) {

    /**
     * Hook: woocommerce_before_shop_loop.
     *
     * @hooked woocommerce_output_all_notices - 10
     * @hooked woocommerce_result_count - 20
     * @hooked woocommerce_catalog_ordering - 30
     */
    do_action('woocommerce_before_shop_loop');

    woocommerce_product_loop_start();

    if (wc_get_loop_prop('total')) {
        while (have_posts()) {
            the_post();

            /**
             * Hook: woocommerce_shop_loop.
             */
            do_action('woocommerce_shop_loop');

            wc_get_template_part('content', 'product');
        }
    }

    woocommerce_product_loop_end();

    /**
     * Hook: woocommerce_after_shop_loop.
     *
     * @hooked woocommerce_pagination - 10
     */
    do_action('woocommerce_after_shop_loop');
} else {
    /**
     * Hook: woocommerce_no_products_found.
     *
     * @hooked wc_no_products_found - 10
     */
    do_action('woocommerce_no_products_found');
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

echo '</div>';
echo '</div>';
get_footer('shop');
