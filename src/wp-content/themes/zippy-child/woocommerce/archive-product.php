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
    max_price="500"
    show_category="true"
    cat_depth="3"
    show_brand="true"
    brand_taxonomy="product_brand"
    open_sections="price,category,brand"
    submit_text="Apply Filters"
    clear_text="Clear All"
]');
echo '</div>'; 
echo '<div class="col large-9">';
echo do_shortcode('[zippy_category_tabs align="left"]
    [zippy_category_tab label="Dogs"       icon="/wp-content/uploads/2026/03/cropped-Fur-It_secondary-logo.jpeg"  url="/category/dogs"  active="true"]
    [zippy_category_tab label="Cats"       icon="/wp-content/uploads/2026/03/cropped-Fur-It_secondary-logo.jpeg"  url="/category/cats"]
    [zippy_category_tab label="Fish"       icon="/wp-content/uploads/2026/03/cropped-Fur-It_secondary-logo.jpeg" url="/category/fish"]
    [zippy_category_tab label="Birds"      icon="/wp-content/uploads/2026/03/cropped-Fur-It_secondary-logo.jpeg" url="/category/birds"]
    [zippy_category_tab label="Small Pets" icon="/wp-content/uploads/2026/03/cropped-Fur-It_secondary-logo.jpeg" url="/category/small-pets"]
[/zippy_category_tabs]');


echo do_shortcode('[zippy_promo_banner
    tag_label="SPRING SEASON"
    tag_color="#fff"
    tag_bg="#b5651d"
    title="Spring Sale: 20% off all Gourmet Treats"
    description="Pamper your best friend with artisanal flavors they willl crave. Offer ends this Sunday!"
    btn_text="Shop the Sale"
    btn_url="/shop/gourmet"
    image="/wp-content/uploads/2026/03/Asset-18@4x.png"
    bg_color="#fdf6ec"
    split="50"
    min_height="320px"
]');

echo do_shortcode(('[zippy_product_grid
    category=""
    limit="3"
    columns="3"
    columns_tablet="2"
    columns_mobile="1"
    show_badge="true"
    show_cat_label="true"
    show_cart="true"
]'));
echo '</div>';

echo '</div>';
echo '</div>';
get_footer('shop');
