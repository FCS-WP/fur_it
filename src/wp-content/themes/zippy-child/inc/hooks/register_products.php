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

add_filter('woocommerce_before_main_content', function() {
    // Get featured product categories (those marked as featured in WC)
    $featured_cats = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'exclude'    => get_option('default_product_cat'),
        'meta_query' => [
            [
                'key'   => 'order',           // WC uses this to mark featured
                'compare' => 'EXISTS',
            ],
        ],
        'orderby'    => 'meta_value_num',
        'order'      => 'ASC',
    ]);

    // Fallback: just get top-level categories if none are "featured"
    if ( empty($featured_cats) || is_wp_error($featured_cats) ) {
        $featured_cats = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
            'exclude'    => get_option('default_product_cat'),
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
    }

    if ( empty($featured_cats) || is_wp_error($featured_cats) ) return;

    // Detect current active category
    $current_cat_slug = is_product_category() ? get_queried_object()->slug : '';

    // Build tabs
    $tabs = '';
    foreach ( $featured_cats as $cat ) {
        // Get category thumbnail
        $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
        $icon_url     = $thumbnail_id
            ? wp_get_attachment_url($thumbnail_id)
            : '';

        $is_active = $current_cat_slug === $cat->slug ? 'true' : 'false';
        $cat_url   = get_term_link($cat);

        $tabs .= sprintf(
            '[zippy_category_tab label="%s" url="%s" icon="%s" active="%s"]',
            esc_attr($cat->name),
            esc_url($cat_url),
            esc_url($icon_url),
            $is_active
        );
    }

    echo '<div class="cat-tabs-wrapper">';
    echo do_shortcode('[zippy_category_tabs align="left"]' . $tabs . '[/zippy_category_tabs]');
    echo '</div>';
});

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