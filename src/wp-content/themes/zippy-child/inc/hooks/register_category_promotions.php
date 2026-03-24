<?php
/**
 * Zippy Promotions
 *
 * - WooCommerce → Promotions admin page
 * - Each promotion: name, image, category, redirect url, date range, active toggle
 * - [zippy_promo_banner] shortcode → shows image grid of active promos for current category
 * - Auto-renders on WooCommerce category archive pages
 *
 * Usage in functions.php:
 *   require_once get_stylesheet_directory() . '/inc/zippy-promotions.php';
 *
 * Shortcode:
 *   [zippy_promo_banner]                  — auto-detect current category
 *   [zippy_promo_banner category="dogs"]  — specific category slug
 *   [zippy_promo_banner columns="2"]      — override columns (default: 2)
 */

if ( ! defined('ABSPATH') ) exit;

define('ZIPPY_PROMO_OPTION', 'zippy_promotions');


// ============================================================
// Helpers
// ============================================================

function zippy_get_promotions() {
    return get_option(ZIPPY_PROMO_OPTION, []);
}

function zippy_save_promotions( $promos ) {
    update_option(ZIPPY_PROMO_OPTION, array_values($promos));
}

/**
 * Get all active promotions for a given category slug
 * respecting date range
 */
function zippy_get_active_promos_for_category( $cat_slug ) {
    $promos = zippy_get_promotions();
    $now    = current_time('timestamp');
    $result = [];

    foreach ( $promos as $index => $promo ) {
        if ( empty($promo['active']) )                     continue;
        if ( ($promo['category'] ?? '') !== $cat_slug )   continue;

        $start = ! empty($promo['date_start']) ? strtotime($promo['date_start'])                    : 0;
        $end   = ! empty($promo['date_end'])   ? strtotime($promo['date_end'] . ' 23:59:59') : PHP_INT_MAX;

        if ( $now >= $start && $now <= $end ) {
            $promo['_index'] = $index;
            $result[]        = $promo;
        }
    }

    return $result;
}


// ============================================================
// 1. Register Admin Menu under WooCommerce
// ============================================================
add_action('admin_menu', function() {
    add_submenu_page(
        'woocommerce',
        __('Promotions', 'flatsome-child'),
        __('Promotions', 'flatsome-child'),
        'manage_woocommerce',
        'zippy-promotions',
        'zippy_promotions_page'
    );
});


// ============================================================
// 2. Enqueue Media Uploader on this page only
// ============================================================
add_action('admin_enqueue_scripts', function( $hook ) {
    if ( strpos($hook, 'zippy-promotions') === false ) return;
    wp_enqueue_media();

    add_action('admin_head', function() { ?>
        <style>
            .zippy-wrap { max-width:1100px; }
            .zippy-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }

            /* Table */
            .zippy-table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.07); }
            .zippy-table th { background:#f8f9fa; padding:11px 16px; text-align:left; font-size:12px; color:#666; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid #eee; }
            .zippy-table td { padding:12px 16px; border-bottom:1px solid #f5f5f5; vertical-align:middle; font-size:13px; }
            .zippy-table tr:last-child td { border-bottom:none; }
            .zippy-table td img { width:90px; height:56px; object-fit:cover; border-radius:6px; border:1px solid #eee; display:block; }
            .zippy-no-img { width:90px; height:56px; background:#f5f5f5; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:11px; color:#bbb; }

            /* Status badges */
            .z-badge { display:inline-block; padding:3px 10px; border-radius:50px; font-size:11px; font-weight:600; }
            .z-badge.active   { background:#e8f5e9; color:#2e7d32; }
            .z-badge.inactive { background:#f5f5f5; color:#aaa; }
            .z-badge.expired  { background:#fce4ec; color:#c62828; }
            .z-badge.pending  { background:#fff8e1; color:#e65100; }

            /* Form */
            .zippy-form-box { background:#fff; padding:24px 28px; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,.07); max-width:640px; }
            .zippy-form-box .form-table th { width:160px; padding:10px 0; font-weight:600; }
            .zippy-form-box .form-table td { padding:8px 0; }
            .zippy-form-box input[type=text],
            .zippy-form-box input[type=url],
            .zippy-form-box input[type=date],
            .zippy-form-box select { width:100%; max-width:380px; }
            .zippy-img-preview { margin-top:8px; }
            .zippy-img-preview img { width:160px; height:90px; object-fit:cover; border-radius:6px; border:1px solid #ddd; display:block; margin-bottom:6px; }
            .zippy-actions a { margin-right:6px; }
            .zippy-empty { text-align:center; padding:56px 24px; color:#bbb; }
            .zippy-empty p { font-size:15px; }
        </style>
        <script>
        jQuery(function($) {
            // Open WP media uploader
            $(document).on('click', '.z-upload-btn', function(e) {
                e.preventDefault();
                var target = $(this).data('target');
                var frame  = wp.media({ title: 'Select Image', button: { text: 'Use this image' }, multiple: false });
                frame.on('select', function() {
                    var att = frame.state().get('selection').first().toJSON();
                    $('#' + target).val(att.url);
                    var wrap = $('[data-target="' + target + '"].z-upload-btn').closest('td').find('.zippy-img-preview');
                    wrap.html('<img src="' + att.url + '" /><button type="button" class="button button-small z-remove-img" data-target="' + target + '">Remove</button>');
                });
                frame.open();
            });
            // Remove image
            $(document).on('click', '.z-remove-img', function(e) {
                e.preventDefault();
                var target = $(this).data('target');
                $('#' + target).val('');
                $(this).closest('.zippy-img-preview').html('');
            });
        });
        </script>
    <?php });
});


// ============================================================
// 3. Handle Actions: save / delete / toggle
// ============================================================
add_action('admin_init', function() {
    if ( ( $_GET['page'] ?? '' ) !== 'zippy-promotions' ) return;
    if ( ! current_user_can('manage_woocommerce') )       return;

    // Save
    if ( isset($_POST['zippy_promo_save']) && check_admin_referer('zippy_promo_save') ) {
        $promos = zippy_get_promotions();
        $id     = $_POST['promo_id'] !== '' ? (int) $_POST['promo_id'] : null;

        $data = [
            'name'       => sanitize_text_field($_POST['promo_name']       ?? ''),
            'image'      => esc_url_raw($_POST['promo_image']              ?? ''),
            'link'       => esc_url_raw($_POST['promo_link']               ?? ''),
            'category'   => sanitize_key($_POST['promo_category']          ?? ''),
            'date_start' => sanitize_text_field($_POST['promo_date_start'] ?? ''),
            'date_end'   => sanitize_text_field($_POST['promo_date_end']   ?? ''),
            'active'     => isset($_POST['promo_active']) ? 1 : 0,
        ];

        if ( $id !== null ) {
            $promos[$id] = $data;
        } else {
            $promos[] = $data;
        }

        zippy_save_promotions($promos);
        wp_redirect(admin_url('admin.php?page=zippy-promotions&saved=1'));
        exit;
    }

    // Delete
    if ( ( $_GET['action'] ?? '' ) === 'delete' && isset($_GET['id']) ) {
        $id = (int) $_GET['id'];
        check_admin_referer('zippy_promo_delete_' . $id);
        $promos = zippy_get_promotions();
        unset($promos[$id]);
        zippy_save_promotions($promos);
        wp_redirect(admin_url('admin.php?page=zippy-promotions&deleted=1'));
        exit;
    }

    // Toggle active
    if ( ( $_GET['action'] ?? '' ) === 'toggle' && isset($_GET['id']) ) {
        $id = (int) $_GET['id'];
        check_admin_referer('zippy_promo_toggle_' . $id);
        $promos = zippy_get_promotions();
        if ( isset($promos[$id]) ) {
            $promos[$id]['active'] = empty($promos[$id]['active']) ? 1 : 0;
            zippy_save_promotions($promos);
        }
        wp_redirect(admin_url('admin.php?page=zippy-promotions'));
        exit;
    }
});


// ============================================================
// 4. Admin Page Render
// ============================================================
function zippy_promotions_page() {
    $promos  = zippy_get_promotions();
    $action  = $_GET['action'] ?? 'list';
    $edit_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $promo   = ( $action === 'edit' && $edit_id !== null ) ? ( $promos[$edit_id] ?? [] ) : [];

    $categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'exclude'    => get_option('default_product_cat'),
        'orderby'    => 'name',
    ]);
    ?>
    <div class="wrap zippy-wrap">

        <div class="zippy-header">
            <h1>🎯 <?php _e('Promotions', 'flatsome-child'); ?></h1>
            <?php if ( ! in_array($action, ['add','edit']) ) : ?>
            <a href="<?php echo admin_url('admin.php?page=zippy-promotions&action=add'); ?>" class="page-title-action">
                + <?php _e('Add Promotion', 'flatsome-child'); ?>
            </a>
            <?php endif; ?>
        </div>

        <?php if ( isset($_GET['saved']) )   : ?><div class="notice notice-success is-dismissible"><p><?php _e('Saved!', 'flatsome-child'); ?></p></div><?php endif; ?>
        <?php if ( isset($_GET['deleted']) ) : ?><div class="notice notice-success is-dismissible"><p><?php _e('Deleted.', 'flatsome-child'); ?></p></div><?php endif; ?>


        <?php if ( in_array($action, ['add', 'edit']) ) :
            $is_edit = $action === 'edit';
        ?>

        <!-- ── Add / Edit Form ────────────────────────────── -->
        <h2><?php echo $is_edit ? __('Edit Promotion', 'flatsome-child') : __('Add Promotion', 'flatsome-child'); ?></h2>
        <a href="<?php echo admin_url('admin.php?page=zippy-promotions'); ?>">← <?php _e('Back', 'flatsome-child'); ?></a>
        <br><br>

        <div class="zippy-form-box">
            <form method="POST">
                <?php wp_nonce_field('zippy_promo_save'); ?>
                <input type="hidden" name="promo_id" value="<?php echo $is_edit ? esc_attr($edit_id) : ''; ?>" />

                <table class="form-table">

                    <tr>
                        <th><label for="promo_active"><?php _e('Active', 'flatsome-child'); ?></label></th>
                        <td>
                            <input type="checkbox" id="promo_active" name="promo_active" value="1" <?php checked($promo['active'] ?? 1, 1); ?> />
                            <label for="promo_active"><?php _e('Enable this promotion', 'flatsome-child'); ?></label>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="promo_name"><?php _e('Name', 'flatsome-child'); ?> *</label></th>
                        <td>
                            <input type="text" id="promo_name" name="promo_name" value="<?php echo esc_attr($promo['name'] ?? ''); ?>" placeholder="e.g. Spring Sale Banner" required />
                            <p class="description"><?php _e('Internal reference only.', 'flatsome-child'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="promo_category"><?php _e('Category', 'flatsome-child'); ?> *</label></th>
                        <td>
                            <select id="promo_category" name="promo_category" required>
                                <option value=""><?php _e('— Select —', 'flatsome-child'); ?></option>
                                <?php foreach ( $categories as $cat ) : ?>
                                    <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected($promo['category'] ?? '', $cat->slug); ?>>
                                        <?php echo esc_html($cat->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Show on this category archive page.', 'flatsome-child'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><label><?php _e('Image', 'flatsome-child'); ?> *</label></th>
                        <td>
                            <input type="url" id="promo_image" name="promo_image" value="<?php echo esc_attr($promo['image'] ?? ''); ?>" placeholder="https://" style="max-width:280px;" />
                            <button type="button" class="button z-upload-btn" data-target="promo_image"><?php _e('Choose', 'flatsome-child'); ?></button>
                            <div class="zippy-img-preview">
                                <?php if ( ! empty($promo['image']) ) : ?>
                                    <img src="<?php echo esc_url($promo['image']); ?>" />
                                    <button type="button" class="button button-small z-remove-img" data-target="promo_image"><?php _e('Remove', 'flatsome-child'); ?></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="promo_link"><?php _e('Redirect URL', 'flatsome-child'); ?></label></th>
                        <td>
                            <input type="url" id="promo_link" name="promo_link" value="<?php echo esc_attr($promo['link'] ?? ''); ?>" placeholder="https:// (optional)" />
                            <p class="description"><?php _e('Where clicking the image goes. Leave blank for no link.', 'flatsome-child'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e('Date Range', 'flatsome-child'); ?></th>
                        <td>
                            <label><?php _e('Start', 'flatsome-child'); ?></label>
                            <input type="date" name="promo_date_start" value="<?php echo esc_attr($promo['date_start'] ?? ''); ?>" style="width:150px;max-width:150px;" />
                            &nbsp;
                            <label><?php _e('End', 'flatsome-child'); ?></label>
                            <input type="date" name="promo_date_end" value="<?php echo esc_attr($promo['date_end'] ?? ''); ?>" style="width:150px;max-width:150px;" />
                            <p class="description"><?php _e('Leave blank for no expiry.', 'flatsome-child'); ?></p>
                        </td>
                    </tr>

                </table>

                <p class="submit">
                    <button type="submit" name="zippy_promo_save" class="button button-primary">
                        <?php echo $is_edit ? __('Update', 'flatsome-child') : __('Save Promotion', 'flatsome-child'); ?>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=zippy-promotions'); ?>" class="button"><?php _e('Cancel', 'flatsome-child'); ?></a>
                </p>
            </form>
        </div>

        <?php else : ?>

        <!-- ── List ───────────────────────────────────────── -->
        <?php if ( empty($promos) ) : ?>
            <div class="zippy-empty">
                <p style="font-size:40px">🎯</p>
                <p><?php _e('No promotions yet.', 'flatsome-child'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=zippy-promotions&action=add'); ?>" class="button button-primary"><?php _e('Add First Promotion', 'flatsome-child'); ?></a>
            </div>
        <?php else : ?>
            <table class="zippy-table">
                <thead>
                    <tr>
                        <th><?php _e('Image', 'flatsome-child'); ?></th>
                        <th><?php _e('Name', 'flatsome-child'); ?></th>
                        <th><?php _e('Category', 'flatsome-child'); ?></th>
                        <th><?php _e('Date Range', 'flatsome-child'); ?></th>
                        <th><?php _e('Status', 'flatsome-child'); ?></th>
                        <th><?php _e('Shortcode', 'flatsome-child'); ?></th>
                        <th><?php _e('Actions', 'flatsome-child'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $promos as $id => $promo ) :
                        $now   = current_time('timestamp');
                        $start = ! empty($promo['date_start']) ? strtotime($promo['date_start']) : 0;
                        $end   = ! empty($promo['date_end'])   ? strtotime($promo['date_end'] . ' 23:59:59') : PHP_INT_MAX;

                        if ( empty($promo['active']) ) {
                            $status = 'inactive'; $label = __('Inactive', 'flatsome-child');
                        } elseif ( $start > 0 && $now < $start ) {
                            $status = 'pending';  $label = __('Pending', 'flatsome-child');
                        } elseif ( $now > $end ) {
                            $status = 'expired';  $label = __('Expired', 'flatsome-child');
                        } else {
                            $status = 'active';   $label = __('Active', 'flatsome-child');
                        }

                        $date_str = trim(
                            ( ! empty($promo['date_start']) ? $promo['date_start'] : '' ) .
                            ( ! empty($promo['date_end'])   ? ' → ' . $promo['date_end'] : '' )
                        ) ?: '—';
                    ?>
                    <tr>
                        <td>
                            <?php if ( ! empty($promo['image']) ) : ?>
                                <img src="<?php echo esc_url($promo['image']); ?>" alt="" />
                            <?php else : ?>
                                <div class="zippy-no-img">No image</div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo esc_html($promo['name'] ?? '—'); ?></strong></td>
                        <td><?php echo esc_html($promo['category'] ?? '—'); ?></td>
                        <td style="font-size:12px;color:#888;"><?php echo esc_html($date_str); ?></td>
                        <td><span class="z-badge <?php echo $status; ?>"><?php echo $label; ?></span></td>
                        <td><code style="font-size:11px;">[zippy_promo_banner category="<?php echo esc_attr($promo['category'] ?? ''); ?>"]</code></td>
                        <td class="zippy-actions">
                            <a href="<?php echo admin_url('admin.php?page=zippy-promotions&action=edit&id=' . $id); ?>" class="button button-small"><?php _e('Edit', 'flatsome-child'); ?></a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=zippy-promotions&action=toggle&id=' . $id), 'zippy_promo_toggle_' . $id); ?>" class="button button-small">
                                <?php echo empty($promo['active']) ? __('Enable', 'flatsome-child') : __('Disable', 'flatsome-child'); ?>
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=zippy-promotions&action=delete&id=' . $id), 'zippy_promo_delete_' . $id); ?>"
                               class="button button-small"
                               style="color:#c62828;"
                               onclick="return confirm('<?php esc_attr_e('Delete this promotion?', 'flatsome-child'); ?>')">
                                <?php _e('Delete', 'flatsome-child'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <?php endif; ?>

    </div>
    <?php
}


// ============================================================
// 5. [zippy_promo_banner] Shortcode
//    Shows image grid of active promos for a category
// ============================================================
if ( ! function_exists('zippy_promo_banner') ) :
function zippy_promo_banner( $atts ) {
    $atts = shortcode_atts([
        'category' => '',   // category slug — auto-detects if empty
        'columns'  => '2',
        'gap'      => '16px',
        'class'    => '',
    ], $atts, 'zippy_promo_banner');

    // Resolve category slug
    $cat_slug = ! empty($atts['category'])
        ? sanitize_key($atts['category'])
        : ( is_product_category() ? get_queried_object()->slug : '' );

    if ( empty($cat_slug) ) return '';

    $promos = zippy_get_active_promos_for_category($cat_slug);

    if ( empty($promos) ) return '';

    return zippy_render_promo_grid($promos, (int) $atts['columns'], $atts['gap'], $atts['class']);
}
add_shortcode('zippy_promo_banner', 'zippy_promo_banner');
endif;


// ============================================================
// 6. Render Helper
// ============================================================
function zippy_render_promo_grid( $promos, $columns = 2, $gap = '16px', $extra_class = '' ) {
    if ( empty($promos) ) return '';

    // Filter out promos with no image
    $promos = array_filter($promos, fn($p) => ! empty($p['image']));
    if ( empty($promos) ) return '';

    $uid   = 'zpromo-' . uniqid();
    $class = 'zippy-promo-grid' . ( $extra_class ? ' ' . esc_attr($extra_class) : '' );

    // Single promo = full width
    $cols = count($promos) === 1 ? 1 : $columns;

    ob_start(); ?>
    <style>
        #<?php echo $uid; ?>{display:grid;grid-template-columns:repeat(<?php echo $cols; ?>,1fr);gap:<?php echo esc_attr($gap); ?>;margin-bottom:28px;}
        @media(max-width:649px){#<?php echo $uid; ?>{grid-template-columns:1fr;}}
    </style>
    <div id="<?php echo $uid; ?>" class="<?php echo esc_attr($class); ?>">
        <?php foreach ( $promos as $promo ) :
            $has_link = ! empty($promo['link']);
            $tag_open  = $has_link
                ? '<a href="' . esc_url($promo['link']) . '" class="zippy-promo-grid__link">'
                : '<div class="zippy-promo-grid__link">';
            $tag_close = $has_link ? '</a>' : '</div>';
        ?>
        <div class="zippy-promo-grid__item">
            <?php echo $tag_open; ?>
                <img
                    src="<?php echo esc_url($promo['image']); ?>"
                    alt="<?php echo esc_attr($promo['name'] ?? ''); ?>"
                    class="zippy-promo-grid__image"
                    loading="lazy"
                />
            <?php echo $tag_close; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}


// ============================================================
// 7. Auto-render on WooCommerce Category Archive
// ============================================================
add_action('woocommerce_before_shop_loop', function() {
    if ( ! is_product_category() ) return;

    $slug   = get_queried_object()->slug;
    $promos = zippy_get_active_promos_for_category($slug);

    if ( empty($promos) ) return;

    echo zippy_render_promo_grid($promos);
}, 5);