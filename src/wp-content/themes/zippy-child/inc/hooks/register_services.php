<?php
if (! defined('ABSPATH')) exit;


// ============================================================
// Register "services" Custom Post Type
// ============================================================
add_action('init', function () {
    register_post_type('services', [
        'label'         => __('Services', 'flatsome-child'),
        'labels'        => [
            'name'          => __('Services', 'flatsome-child'),
            'singular_name' => __('Service', 'flatsome-child'),
            'add_new_item'  => __('Add New Service', 'flatsome-child'),
            'edit_item'     => __('Edit Service', 'flatsome-child'),
        ],
        'public'        => true,
        'menu_icon'     => 'dashicons-clipboard',
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest'  => true,
        'rewrite'       => ['slug' => 'services'],
    ]);
});


// ============================================================
// Register "services_category" Taxonomy
// ============================================================
add_action('init', function () {
    register_taxonomy('services_category', 'services', [
        'label'             => __('Service Categories', 'flatsome-child'),
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'services-category'],
        'show_admin_column' => true,
    ]);
});


// ============================================================
// META BOX
// ============================================================
add_action('add_meta_boxes', function () {
    add_meta_box(
        'service_meta',
        __('Service Details', 'flatsome-child'),
        'service_meta_box',
        'services',
        'normal',
        'high'
    );
});

function service_meta_box($post)
{
    wp_nonce_field('service_meta_save', 'service_nonce');

    $price      = get_post_meta($post->ID, '_price', true);
    $price_unit = get_post_meta($post->ID, '_price_unit', true) ?: 'hour';
    $btn_url    = get_post_meta($post->ID, '_btn_url', true);
    $icon       = get_post_meta($post->ID, '_icon', true);
?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        <div>
            <label>Price</label>
            <input type="text" name="price" value="<?php echo esc_attr($price); ?>" />
        </div>

        <div>
            <label>Price Unit</label>
            <input name="price_unit" type="text" />
        </div>

        <div style="grid-column:1/-1;">
            <label>Button URL</label>
            <input type="url" name="btn_url" value="<?php echo esc_attr($btn_url); ?>" />
        </div>

        <div style="grid-column:1/-1;">
            <label>Icon URL</label>
            <input type="url" name="icon" value="<?php echo esc_attr($icon); ?>" />
        </div>

    </div>

<?php
}


// SAVE META
add_action('save_post_services', function ($post_id) {

    if (! isset($_POST['service_nonce'])) return;
    if (! wp_verify_nonce($_POST['service_nonce'], 'service_meta_save')) return;

    update_post_meta($post_id, '_price', sanitize_text_field($_POST['price'] ?? ''));
    update_post_meta($post_id, '_price_unit', sanitize_text_field($_POST['price_unit'] ?? ''));
    update_post_meta($post_id, '_btn_url', esc_url_raw($_POST['btn_url'] ?? ''));
    update_post_meta($post_id, '_icon', esc_url_raw($_POST['icon'] ?? ''));
});


// ============================================================
// SHORTCODE
// ============================================================
function services_shortcode($atts)
{
    $atts = shortcode_atts([
        'category'       => '',       // service_category slug — required
        'columns'        => '3',
        'columns_tablet' => '2',
        'columns_mobile' => '1',
        'limit'          => '-1',     // -1 = all
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'btn_text'       => 'Book Now',
        'btn_url'        => '#',      // default — overridden per service if set
        'show_price'     => 'true',
        'show_icon'      => 'true',
        'class'          => '',
    ], $atts, 'services');

    // ── Query ──
    $args = [
        'post_type'      => 'services',
        'posts_per_page' => (int) $atts['limit'],
        'orderby'        => sanitize_key($atts['orderby']),
        'order'          => sanitize_key($atts['order']),
        'post_status'    => 'publish',
    ];

    if (! empty($atts['category'])) {
        $args['tax_query'] = [[
            'taxonomy' => 'services_category',
            'field'    => 'slug',
            'terms'    => array_map('trim', explode(',', $atts['category'])),
        ]];
    }

    $services = new WP_Query($args);

    if (! $services->have_posts()) {
        return '<p class="zippy-services-empty">' . __('No services found.', 'flatsome-child') . '</p>';
    }

    $uid   = 'zippy-services-' . uniqid();
    $class = 'zippy-services-grid' . ($atts['class'] ? ' ' . esc_attr($atts['class']) : '');

    ob_start();
?>

    <style>
        #<?php echo $uid; ?> {
            grid-template-columns: repeat(<?php echo (int)$atts['columns']; ?>, 1fr);
        }

        @media (max-width: 849px) {
            #<?php echo $uid; ?> {
                grid-template-columns: repeat(<?php echo (int)$atts['columns_tablet']; ?>, 1fr);
            }
        }

        @media (max-width: 549px) {
            #<?php echo $uid; ?> {
                grid-template-columns: repeat(<?php echo (int)$atts['columns_mobile']; ?>, 1fr);
            }
        }
    </style>

    <div id="<?php echo $uid; ?>" class="<?php echo esc_attr($class); ?>">

        <?php while ($services->have_posts()) : $services->the_post();
            $service_id = get_the_ID();
            $title      = get_the_title();
            $excerpt    = get_the_excerpt();
            $content = get_the_content();
            $price      = get_post_meta($service_id, '_price',      true);
            $price_unit = get_post_meta($service_id, '_price_unit', true) ?: null;
            $icon  = get_post_meta(get_the_ID(), '_icon', true);
            $item_url   = get_post_meta($service_id, '_btn_url',    true) ?: $atts['btn_url'];
        ?>

            <div class="zippy-service-card-v2">

                <!-- Header: icon + title -->
                <div class="zippy-service-card-v2__header">
                    <?php if ($atts['show_icon'] === 'true' && ! empty($icon)) : ?>
                        <div class="zippy-service-card-v2__icon">
                            <img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($title); ?>" />
                        </div>
                    <?php endif; ?>
                    <h3 class="zippy-service-card-v2__title"><?php echo esc_html($title); ?></h3>
                </div>

                <!-- Description -->
                <div class="zippy-service-card-v2__desc">
                    <?php echo wpautop($content) ?>
                </div>

                <!-- Footer: button + price -->
                <div class="zippy-service-card-v2__footer">
                    <a href="<?php echo esc_url($item_url); ?>" class="zippy-service-card-v2__btn">
                        <?php echo esc_html($atts['btn_text']); ?>
                    </a>
                    <?php if ($atts['show_price'] === 'true' && ! empty($price)) : ?>
                        <div class="zippy-service-card-v2__price">
                            <span class="zippy-service-card-v2__price-amount"><?php echo esc_html($price); ?></span>
                            <span class="zippy-service-card-v2__price-unit"><?php echo !empty($price_unit) ? ('/' . esc_html($price_unit)) : '' ?></span>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        <?php endwhile;
        wp_reset_postdata(); ?>

    </div>

<?php
    return ob_get_clean();
}
add_shortcode('services', 'services_shortcode');

function services_list_shortcode($atts)
{

    $atts = shortcode_atts([
        'limit'    => -1,
        'category' => '',
        'columns'  => 2,
    ], $atts);

    $args = [
        'post_type'      => 'services',
        'posts_per_page' => (int) $atts['limit'],
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ];

    if (!empty($atts['category'])) {
        $args['tax_query'] = [[
            'taxonomy' => 'services_category',
            'field'    => 'slug',
            'terms'    => explode(',', $atts['category']),
        ]];
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No services found</p>';
    }

    // Convert posts to array
    $items = [];
    while ($query->have_posts()) {
        $query->the_post();
        $items[] = [
            'title' => get_the_title(),
            'price' => get_post_meta(get_the_ID(), '_price', true),
        ];
    }
    wp_reset_postdata();

    // Split into columns
    $columns = (int) $atts['columns'];
    $chunked = array_chunk($items, ceil(count($items) / $columns));

    ob_start();
?>

    <div class="services-list-columns">

        <?php foreach ($chunked as $col): ?>
            <div class="services-col">

                <?php foreach ($col as $item): ?>
                    <div class="services-row">
                        <div class="services-title"><?php echo esc_html($item['title']); ?></div>
                        <div class="services-price"><?php echo esc_html($item['price']); ?></div>
                    </div>
                <?php endforeach; ?>

            </div>
        <?php endforeach; ?>

    </div>

    <style>
        .services-list-columns {
            display: grid;
            grid-template-columns: repeat(<?php echo $columns; ?>, 1fr);
            gap: 4rem;
        }

        /* Tablet */
        @media (max-width: 849px) {
            .services-list-columns {
                gap: 2rem;
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Mobile */
        @media (max-width: 549px) {
            .services-list-columns {
                gap: 1rem;
                grid-template-columns: 1fr;
            }
        }

        .services-row {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px dashed #ddd;
        }

        .services-title {
            font-weight: 500;
        }

        .services-price {
            font-weight: 600;
            white-space: nowrap;
        }
    </style>

<?php
    return ob_get_clean();
}
add_shortcode('services_list', 'services_list_shortcode');
