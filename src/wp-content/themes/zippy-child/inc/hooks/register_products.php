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

/**
 * Zippy Shop Section Shortcodes
 *
 * - [zippy_category_tabs]  + [zippy_category_tab]
 * - [zippy_promo_banner]
 * - [zippy_product_grid]
 */


// ============================================================
// [zippy_category_tabs] & [zippy_category_tab]
// ============================================================
function zippy_category_tabs( $atts, $content = null ) {
    $atts = shortcode_atts([
        'align' => 'left',   // left | center | right
        'class' => '',
    ], $atts, 'zippy_category_tabs');

    $class = 'zippy-cat-tabs';
    if ( $atts['class'] ) $class .= ' ' . esc_attr($atts['class']);

    $align_map = [ 'center' => 'center', 'right' => 'flex-end', 'left' => 'flex-start' ];
    $justify   = $align_map[ $atts['align'] ] ?? 'flex-start';

    return sprintf(
        '<nav class="%s" style="justify-content:%s" role="tablist">%s</nav>',
        $class,
        $justify,
        do_shortcode($content)
    );
}
add_shortcode('zippy_category_tabs', 'zippy_category_tabs');


function zippy_category_tab( $atts ) {
    $atts = shortcode_atts([
        'label'   => '',
        'url'     => '#',
        'icon'    => '',       // image URL
        'active'  => 'false',  // true | false
        'class'   => '',
    ], $atts, 'zippy_category_tab');

    $class = 'zippy-cat-tab';
    if ( $atts['active'] === 'true' ) $class .= ' zippy-cat-tab--active';
    if ( $atts['class'] ) $class .= ' ' . esc_attr($atts['class']);

    $icon_html = '';
    if ( ! empty($atts['icon']) ) {
        $icon_html = sprintf(
            '<span class="zippy-cat-tab__icon"><img src="%s" alt="%s" /></span>',
            esc_url($atts['icon']),
            esc_attr($atts['label'])
        );
    }

    return sprintf(
        '<a href="%s" class="%s" role="tab" aria-selected="%s">%s<span class="zippy-cat-tab__label">%s</span></a>',
        esc_url($atts['url']),
        $class,
        $atts['active'] === 'true' ? 'true' : 'false',
        $icon_html,
        esc_html($atts['label'])
    );
}
add_shortcode('zippy_category_tab', 'zippy_category_tab');


// ============================================================
// [zippy_promo_banner]
// ============================================================
function zippy_promo_banner( $atts, $content = null ) {
    $atts = shortcode_atts([
        // Left panel
        'tag_label'   => '',           // e.g. "SPRING SEASON"
        'tag_color'   => '#fff',
        'tag_bg'      => '#b5651d',
        'title'       => '',
        'description' => '',
        'btn_text'    => 'Shop Now',
        'btn_url'     => '#',
        'btn_target'  => '_self',

        // Right panel
        'image'       => '',           // image URL
        'image_alt'   => '',

        // Layout
        'bg_color'    => '#fdf6ec',
        'split'       => '50',         // left panel % width e.g. 50
        'min_height'  => '320px',
        'border_radius' => '20px',
        'class'       => '',
    ], $atts, 'zippy_promo_banner');

    $class = 'zippy-promo-banner';
    if ( $atts['class'] ) $class .= ' ' . esc_attr($atts['class']);

    $split_right = 100 - (int) $atts['split'];

    // Tag badge
    $tag_html = '';
    if ( ! empty($atts['tag_label']) ) {
        $tag_html = sprintf(
            '<span class="zippy-promo-banner__tag" style="color:%s;background:%s">%s</span>',
            esc_attr($atts['tag_color']),
            esc_attr($atts['tag_bg']),
            esc_html($atts['tag_label'])
        );
    }

    // Button
    $btn_html = '';
    if ( ! empty($atts['btn_text']) ) {
        $btn_html = sprintf(
            '<a class="zippy-promo-banner__btn" href="%s" target="%s">%s</a>',
            esc_url($atts['btn_url']),
            esc_attr($atts['btn_target']),
            esc_html($atts['btn_text'])
        );
    }

    // Right image
    $image_html = '';
    if ( ! empty($atts['image']) ) {
        $image_html = sprintf(
            '<img src="%s" alt="%s" class="zippy-promo-banner__image" />',
            esc_url($atts['image']),
            esc_attr($atts['image_alt'] ?: $atts['title'])
        );
    }

    return sprintf(
        '<div class="%s" style="background:%s;border-radius:%s;min-height:%s">
            <div class="zippy-promo-banner__left" style="flex:0 0 %s%%">
                %s
                <h2 class="zippy-promo-banner__title">%s</h2>
                <p class="zippy-promo-banner__desc">%s</p>
                %s
            </div>
            <div class="zippy-promo-banner__right" style="flex:0 0 %s%%;border-radius:%s">
                %s
            </div>
        </div>',
        $class,
        esc_attr($atts['bg_color']),
        esc_attr($atts['border_radius']),
        esc_attr($atts['min_height']),
        (int) $atts['split'],
        $tag_html,
        wp_kses_post($atts['title']),
        wp_kses_post($atts['description']),
        $btn_html,
        $split_right,
        esc_attr($atts['border_radius']),
        $image_html
    );
}
add_shortcode('zippy_promo_banner', 'zippy_promo_banner');


// ============================================================
// [zippy_product_grid]
// ============================================================
function zippy_product_grid( $atts ) {
    $atts = shortcode_atts([
        // Query
        'category'       => '',
        'featured'       => 'false',
        'limit'          => '3',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'ids'            => '',        // comma separated product IDs

        // Layout
        'columns'        => '3',
        'columns_tablet' => '2',
        'columns_mobile' => '1',

        // Card options
        'show_badge'     => 'true',    // Editor's choice / sale badge
        'show_cat_label' => 'true',    // category label above title
        'show_cart'      => 'true',    // add to cart button

        'class'          => '',
    ], $atts, 'zippy_product_grid');

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

    if ( ! empty($atts['ids']) ) {
        $args['post__in'] = array_map('intval', explode(',', $atts['ids']));
        $args['orderby']  = 'post__in';
    }

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

    $uid = 'zippy-pgrid-' . uniqid();

    ob_start(); ?>

    <style>
        #<?php echo $uid; ?> .zippy-pgrid {
            grid-template-columns: repeat(<?php echo (int)$atts['columns']; ?>, 1fr);
        }
        @media (max-width: 849px) {
            #<?php echo $uid; ?> .zippy-pgrid {
                grid-template-columns: repeat(<?php echo (int)$atts['columns_tablet']; ?>, 1fr);
            }
        }
        @media (max-width: 549px) {
            #<?php echo $uid; ?> .zippy-pgrid {
                grid-template-columns: repeat(<?php echo (int)$atts['columns_mobile']; ?>, 1fr);
            }
        }
    </style>

    <div id="<?php echo $uid; ?>" class="zippy-pgrid-wrapper <?php echo esc_attr($atts['class']); ?>">
        <div class="zippy-pgrid">

        <?php while ( $products->have_posts() ) : $products->the_post();
            $product_id  = get_the_ID();
            $product_obj = wc_get_product($product_id);
            $image       = get_the_post_thumbnail_url($product_id, 'woocommerce_thumbnail');
            $title       = get_the_title();
            $permalink   = get_permalink($product_id);
            $price_html  = $product_obj->get_price_html();
            $on_sale     = $product_obj->is_on_sale();
            $is_featured = $product_obj->is_featured();

            // Sale percentage
            $sale_badge = '';
            if ( $on_sale && $atts['show_badge'] === 'true' ) {
                $regular = (float) $product_obj->get_regular_price();
                $sale    = (float) $product_obj->get_sale_price();
                if ( $regular > 0 ) {
                    $pct        = round( ( $regular - $sale ) / $regular * 100 );
                    $sale_badge = '<span class="zippy-pgrid-card__badge zippy-pgrid-card__badge--sale">SALE -' . $pct . '%</span>';
                }
            }

            // Editor's choice badge (featured + not on sale)
            $editor_badge = '';
            if ( $is_featured && ! $on_sale && $atts['show_badge'] === 'true' ) {
                $editor_badge = '<span class="zippy-pgrid-card__badge zippy-pgrid-card__badge--editor">EDITOR\'S CHOICE</span>';
            }

            // Category label
            $cat_label_html = '';
            if ( $atts['show_cat_label'] === 'true' ) {
                $terms = get_the_terms($product_id, 'product_cat');
                if ( $terms && ! is_wp_error($terms) ) {
                    // Filter out hidden categories
                    $terms = array_filter($terms, fn($t) => $t->slug !== 'uncategorized');
                    if ( ! empty($terms) ) {
                        $term = array_values($terms)[0];
                        $cat_label_html = sprintf(
                            '<span class="zippy-pgrid-card__cat">%s</span>',
                            esc_html(strtoupper($term->name))
                        );
                    }
                }
            }

            // Add to cart
            $cart_html = '';
            if ( $atts['show_cart'] === 'true' && $product_obj->is_purchasable() && $product_obj->is_in_stock() ) {
                $cart_url   = $product_obj->get_type() === 'simple'
                    ? esc_url(wc_get_cart_url() . '?add-to-cart=' . $product_id)
                    : esc_url($permalink);
                $cart_html = sprintf(
                    '<a href="%s" class="zippy-pgrid-card__cart" aria-label="Add %s to cart">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM5.2 5H21l-1.68 8.39A2 2 0 0 1 17.36 15H8.64a2 2 0 0 1-1.96-1.61L5.2 5zM5.2 5L4.27 1H1"/>
                        </svg>
                    </a>',
                    $cart_url,
                    esc_attr($title)
                );
            }
        ?>

        <div class="zippy-pgrid-card">
            <!-- Image -->
            <a href="<?php echo esc_url($permalink); ?>" class="zippy-pgrid-card__image-wrap">
                <?php if ( $image ) : ?>
                    <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" />
                <?php else : ?>
                    <div class="zippy-pgrid-card__no-image"></div>
                <?php endif; ?>

                <!-- Badges (top right of image) -->
                <?php echo $sale_badge; ?>
                <?php echo $editor_badge; ?>
            </a>

            <!-- Info -->
            <div class="zippy-pgrid-card__info">
                <div class="zippy-pgrid-card__meta">
                    <?php echo $cat_label_html; ?>
                    <a href="<?php echo esc_url($permalink); ?>" class="zippy-pgrid-card__title">
                        <?php echo esc_html($title); ?>
                    </a>
                    <div class="zippy-pgrid-card__price"><?php echo $price_html; ?></div>
                </div>
                <?php echo $cart_html; ?>
            </div>
        </div>

        <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('zippy_product_grid', 'zippy_product_grid');