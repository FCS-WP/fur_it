<?php 

// ============================================
// [zippy_products]
// ============================================
function zippy_products( $atts ) {
    $atts = shortcode_atts([
        // Query
        'category'       => '',       // slug e.g. "grooming, spa"
        'featured'       => 'false',  // true | false
        'limit'          => '4',
        'orderby'        => 'date',   // date | price | popularity | rating | rand
        'order'          => 'DESC',

        // Layouts
        'columns'          => '4',
        'columns_tablet'   => '2',
        'columns_mobile'   => '2',

        // Button
        'btn_text'       => 'Shop Now',
        'btn_url'        => '/shop',
        'btn_target'     => '_self',
        'show_btn'       => 'false',

        'class'          => '',
    ], $atts, 'zippy_products');

    if ( ! function_exists('WC') ) return '<p>WooCommerce is required.</p>';

    // ── Query ──
    $args = [
        'post_type'      => 'product',
        'posts_per_page' => (int) $atts['limit'],
        'orderby'        => sanitize_key($atts['orderby']),
        'order'          => sanitize_key($atts['order']),
        'post_status'    => 'publish',
        'tax_query'      => [],
    ];

    if ( ! empty($atts['category']) ) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => array_map('trim', explode(',', $atts['category'])),
        ];
    }

    if ( $atts['featured'] === 'true' ) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => 'featured',
        ];
    }

    $products = new WP_Query($args);

    if ( ! $products->have_posts() ) return '<p>No products found.</p>';

    // ── CSS columns vars ──
    $uid = 'zippy-products-' . uniqid();

    ob_start();
    ?>

    <style>
        #<?php echo $uid; ?> .zippy-products-grid {
            grid-template-columns: repeat(<?php echo (int)$atts['columns']; ?>, 1fr);
        }
        @media (max-width: 849px) {
            #<?php echo $uid; ?> .zippy-products-grid {
                grid-template-columns: repeat(<?php echo (int)$atts['columns_tablet']; ?>, 1fr);
            }
        }
        @media (max-width: 549px) {
            #<?php echo $uid; ?> .zippy-products-grid {
                grid-template-columns: repeat(<?php echo (int)$atts['columns_mobile']; ?>, 1fr);
            }
        }
    </style>

    <div id="<?php echo $uid; ?>" class="zippy-products-wrapper <?php echo esc_attr($atts['class']); ?>">
        <div class="zippy-products-grid">
            <?php while ( $products->have_posts() ) : $products->the_post();
                global $product;
                $product_id    = get_the_ID();
                $product_obj   = wc_get_product($product_id);
                $image         = get_the_post_thumbnail_url($product_id, 'woocommerce_thumbnail');
                $title         = get_the_title();
                $price         = $product_obj->get_price_html();
                $permalink     = get_permalink($product_id);
            ?>
            <div class="zippy-product-card">
                <a href="<?php echo esc_url($permalink); ?>" class="zippy-product-card__image-wrap">
                    <?php if ( $image ) : ?>
                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" />
                    <?php else : ?>
                        <div class="zippy-product-card__no-image"></div>
                    <?php endif; ?>
                </a>
                <div class="zippy-product-card__info">
                    <span class="zippy-product-card__title"><?php echo esc_html($title); ?></span>
                    <span class="zippy-product-card__price"><?php echo $price; ?></span>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <?php if ( $atts['show_btn'] === 'true' ) : ?>
        <div class="zippy-products-footer">
            <a href="<?php echo esc_url($atts['btn_url']); ?>"
               target="<?php echo esc_attr($atts['btn_target']); ?>"
               class="zippy-products-btn">
                <?php echo esc_html($atts['btn_text']); ?>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('zippy_products', 'zippy_products');