<?php
/**
 * Zippy Shop Filter Shortcode
 *
 * - [zippy_shop_filter]
 *
 * Features:
 * - Collapsible sections: Price Range, Category (hierarchical), Brand
 * - Reads current URL params to keep state on page reload
 * - Submits via GET to shop/archive page
 * - Works with WooCommerce
 */

if ( ! defined('ABSPATH') ) exit;


// ============================================================
// [zippy_shop_filter]
// ============================================================
function zippy_shop_filter( $atts ) {
    $atts = shortcode_atts([
        // Price
        'show_price'      => 'true',
        'price_label'     => 'Price range',
        'min_price'       => '0',
        'max_price'       => '1000',
        'price_step'      => '1',

        // Category
        'show_category'   => 'true',
        'category_label'  => 'Category',
        'cat_depth'       => '3',           // how many levels deep

        // Brand
        'show_brand'      => 'true',
        'brand_label'     => 'Brand',
        'brand_taxonomy'  => 'product_brand', // change to your brand taxonomy slug

        // Layout
        'submit_text'     => 'Apply Filters',
        'clear_text'      => 'Clear All',
        'action_url'      => '',            // defaults to shop page
        'open_sections'   => 'price,category,brand', // comma list of open by default
        'class'           => '',
    ], $atts, 'zippy_shop_filter');

    if ( ! function_exists('WC') ) return '<p>WooCommerce is required.</p>';

    // ── Current filter state from URL ──
    $current_min   = isset($_GET['min_price'])    ? (float) $_GET['min_price']    : (float) $atts['min_price'];
    $current_max   = isset($_GET['max_price'])    ? (float) $_GET['max_price']    : (float) $atts['max_price'];
    $current_cats  = isset($_GET['filter_cat'])   ? (array) $_GET['filter_cat']   : [];
    $current_cats  = array_map('intval', $current_cats);
    $current_brands = isset($_GET['filter_brand']) ? (array) $_GET['filter_brand'] : [];
    $current_brands = array_map('intval', $current_brands);

    // Action URL
    $action = ! empty($atts['action_url'])
        ? esc_url($atts['action_url'])
        : esc_url(get_permalink(wc_get_page_id('shop')));

    // Open sections
    $open_sections = array_map('trim', explode(',', $atts['open_sections']));

    // Has any active filters
    $has_filters = ! empty($current_cats) || ! empty($current_brands)
        || $current_min != (float) $atts['min_price']
        || $current_max != (float) $atts['max_price'];

    $uid = 'zippy-filter-' . uniqid();

    ob_start();
    ?>

    <div id="<?php echo $uid; ?>" class="zippy-filter <?php echo esc_attr($atts['class']); ?>">

        <form class="zippy-filter__form" method="GET" action="<?php echo $action; ?>">

            <!-- Clear All -->
            <?php if ( $has_filters ) : ?>
            <div class="zippy-filter__clear">
                <a href="<?php echo $action; ?>" class="zippy-filter__clear-btn">
                    <?php echo esc_html($atts['clear_text']); ?>
                </a>
            </div>
            <?php endif; ?>

            <?php
            // ── SECTION: Price Range ──────────────────────────
            if ( $atts['show_price'] === 'true' ) :
                $is_open = in_array('price', $open_sections);
            ?>
            <div class="zippy-filter__section <?php echo $is_open ? 'is-open' : ''; ?>">
                <button type="button" class="zippy-filter__section-toggle" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
                    <span><?php echo esc_html($atts['price_label']); ?></span>
                    <svg class="zippy-filter__chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                </button>
                <div class="zippy-filter__section-body">
                    <div class="zippy-filter__price-wrap">
                        <div class="zippy-filter__price-range">
                            <!-- Dual range slider -->
                            <div class="zippy-filter__range-track">
                                <div class="zippy-filter__range-fill" id="<?php echo $uid; ?>-fill"></div>
                            </div>
                            <input
                                type="range"
                                class="zippy-filter__range zippy-filter__range--min"
                                id="<?php echo $uid; ?>-min"
                                name="min_price"
                                min="<?php echo esc_attr($atts['min_price']); ?>"
                                max="<?php echo esc_attr($atts['max_price']); ?>"
                                step="<?php echo esc_attr($atts['price_step']); ?>"
                                value="<?php echo esc_attr($current_min); ?>"
                            />
                            <input
                                type="range"
                                class="zippy-filter__range zippy-filter__range--max"
                                id="<?php echo $uid; ?>-max"
                                name="max_price"
                                min="<?php echo esc_attr($atts['min_price']); ?>"
                                max="<?php echo esc_attr($atts['max_price']); ?>"
                                step="<?php echo esc_attr($atts['price_step']); ?>"
                                value="<?php echo esc_attr($current_max); ?>"
                            />
                        </div>
                        <div class="zippy-filter__price-labels">
                            <span id="<?php echo $uid; ?>-label-min">$<?php echo number_format($current_min, 2); ?></span>
                            <span>–</span>
                            <span id="<?php echo $uid; ?>-label-max">$<?php echo number_format($current_max, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>


            <?php
            // ── SECTION: Category ─────────────────────────────
            if ( $atts['show_category'] === 'true' ) :
                $is_open = in_array('category', $open_sections);

                $top_cats = get_terms([
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'parent'     => 0,
                    'exclude'    => get_option('default_product_cat'),
                ]);

                if ( ! empty($top_cats) && ! is_wp_error($top_cats) ) :
            ?>
            <div class="zippy-filter__section <?php echo $is_open ? 'is-open' : ''; ?>">
                <button type="button" class="zippy-filter__section-toggle" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
                    <span><?php echo esc_html($atts['category_label']); ?></span>
                    <svg class="zippy-filter__chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                </button>
                <div class="zippy-filter__section-body">
                    <ul class="zippy-filter__tree">
                        <?php
                        // Recursive category tree
                        function zippy_render_cat_tree( $parent_id, $depth, $max_depth, $current_cats, $uid ) {
                            if ( $depth > $max_depth ) return;

                            $terms = get_terms([
                                'taxonomy'   => 'product_cat',
                                'hide_empty' => true,
                                'parent'     => $parent_id,
                                'exclude'    => get_option('default_product_cat'),
                            ]);

                            if ( empty($terms) || is_wp_error($terms) ) return;

                            foreach ( $terms as $term ) :
                                $checked  = in_array($term->term_id, $current_cats) ? 'checked' : '';
                                $child_id = 'cat-' . $uid . '-' . $term->term_id;

                                // Check if has children
                                $children = get_terms([
                                    'taxonomy'   => 'product_cat',
                                    'hide_empty' => true,
                                    'parent'     => $term->term_id,
                                ]);
                                $has_children = ! empty($children) && ! is_wp_error($children);
                            ?>
                            <li class="zippy-filter__tree-item" style="--depth:<?php echo $depth; ?>">
                                <label class="zippy-filter__checkbox-label" for="<?php echo $child_id; ?>">
                                    <input
                                        type="checkbox"
                                        id="<?php echo $child_id; ?>"
                                        name="filter_cat[]"
                                        value="<?php echo esc_attr($term->term_id); ?>"
                                        <?php echo $checked; ?>
                                        class="zippy-filter__checkbox"
                                    />
                                    <span class="zippy-filter__checkbox-custom"></span>
                                    <span class="zippy-filter__checkbox-text"><?php echo esc_html($term->name); ?></span>
                                </label>
                                <?php if ( $has_children ) : ?>
                                <ul class="zippy-filter__tree zippy-filter__tree--child">
                                    <?php zippy_render_cat_tree($term->term_id, $depth + 1, $max_depth, $current_cats, $uid); ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                            <?php endforeach;
                        }

                        zippy_render_cat_tree(0, 0, (int) $atts['cat_depth'], $current_cats, $uid);
                        ?>
                    </ul>
                </div>
            </div>
            <?php endif; endif; ?>


            <?php
            // ── SECTION: Brand ────────────────────────────────
            if ( $atts['show_brand'] === 'true' ) :
                $brand_tax = sanitize_key($atts['brand_taxonomy']);

                // Check if taxonomy exists
                if ( taxonomy_exists($brand_tax) ) :
                    $brands = get_terms([
                        'taxonomy'   => $brand_tax,
                        'hide_empty' => true,
                    ]);

                    if ( ! empty($brands) && ! is_wp_error($brands) ) :
                        $is_open = in_array('brand', $open_sections);
                    ?>
                    <div class="zippy-filter__section <?php echo $is_open ? 'is-open' : ''; ?>">
                        <button type="button" class="zippy-filter__section-toggle" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
                            <span><?php echo esc_html($atts['brand_label']); ?></span>
                            <svg class="zippy-filter__chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                        </button>
                        <div class="zippy-filter__section-body">
                            <ul class="zippy-filter__tree">
                                <?php foreach ( $brands as $brand ) :
                                    $checked  = in_array($brand->term_id, $current_brands) ? 'checked' : '';
                                    $brand_id = 'brand-' . $uid . '-' . $brand->term_id;
                                ?>
                                <li class="zippy-filter__tree-item" style="--depth:0">
                                    <label class="zippy-filter__checkbox-label" for="<?php echo $brand_id; ?>">
                                        <input
                                            type="checkbox"
                                            id="<?php echo $brand_id; ?>"
                                            name="filter_brand[]"
                                            value="<?php echo esc_attr($brand->term_id); ?>"
                                            <?php echo $checked; ?>
                                            class="zippy-filter__checkbox"
                                        />
                                        <span class="zippy-filter__checkbox-custom"></span>
                                        <span class="zippy-filter__checkbox-text"><?php echo esc_html($brand->name); ?></span>
                                    </label>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif;
                endif;
            endif; ?>

            <!-- Submit -->
            <div class="zippy-filter__submit">
                <button type="submit" class="zippy-filter__submit-btn">
                    <?php echo esc_html($atts['submit_text']); ?>
                </button>
            </div>

        </form>
    </div>

    <script>
    (function() {
        var wrap = document.getElementById('<?php echo $uid; ?>');
        if (!wrap) return;

        // ── Collapse toggle ──
        var toggles = wrap.querySelectorAll('.zippy-filter__section-toggle');
        toggles.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var section = this.closest('.zippy-filter__section');
                var isOpen  = section.classList.contains('is-open');
                section.classList.toggle('is-open', !isOpen);
                this.setAttribute('aria-expanded', String(!isOpen));
            });
        });

        // ── Dual range slider ──
        var minInput   = document.getElementById('<?php echo $uid; ?>-min');
        var maxInput   = document.getElementById('<?php echo $uid; ?>-max');
        var fill       = document.getElementById('<?php echo $uid; ?>-fill');
        var labelMin   = document.getElementById('<?php echo $uid; ?>-label-min');
        var labelMax   = document.getElementById('<?php echo $uid; ?>-label-max');

        if (!minInput || !maxInput) return;

        var absMin = parseFloat(minInput.min);
        var absMax = parseFloat(minInput.max);

        function updateSlider() {
            var min = parseFloat(minInput.value);
            var max = parseFloat(maxInput.value);

            // Prevent crossing
            if (min > max) {
                if (this === minInput) minInput.value = max;
                else maxInput.value = min;
                min = parseFloat(minInput.value);
                max = parseFloat(maxInput.value);
            }

            var pctMin = ((min - absMin) / (absMax - absMin)) * 100;
            var pctMax = ((max - absMin) / (absMax - absMin)) * 100;

            fill.style.left  = pctMin + '%';
            fill.style.width = (pctMax - pctMin) + '%';

            labelMin.textContent = '$' + min.toFixed(2);
            labelMax.textContent = '$' + max.toFixed(2);
        }

        minInput.addEventListener('input', updateSlider);
        maxInput.addEventListener('input', updateSlider);
        updateSlider();

        // ── Parent checkbox checks all children ──
        var checkboxes = wrap.querySelectorAll('.zippy-filter__checkbox');
        checkboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                var item     = this.closest('.zippy-filter__tree-item');
                var children = item ? item.querySelectorAll('.zippy-filter__tree--child .zippy-filter__checkbox') : [];
                children.forEach(function(child) { child.checked = cb.checked; });
            });
        });
    })();
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('zippy_shop_filter', 'zippy_shop_filter');


// ============================================================
// Hook filter params into WooCommerce query
// ============================================================
add_action('woocommerce_product_query', function( $q ) {

    $tax_query = (array) $q->get('tax_query');
    $meta_query = (array) $q->get('meta_query');

    // ── Category filter ──
    if ( ! empty($_GET['filter_cat']) ) {
        $cat_ids = array_map('intval', (array) $_GET['filter_cat']);
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => $cat_ids,
            'operator' => 'IN',
        ];
        $q->set('tax_query', $tax_query);
    }

    // ── Brand filter ──
    if ( ! empty($_GET['filter_brand']) ) {
        $brand_ids   = array_map('intval', (array) $_GET['filter_brand']);
        $brand_tax   = 'product_brand'; // match your taxonomy
        if ( taxonomy_exists($brand_tax) ) {
            $tax_query[] = [
                'taxonomy' => $brand_tax,
                'field'    => 'term_id',
                'terms'    => $brand_ids,
                'operator' => 'IN',
            ];
            $q->set('tax_query', $tax_query);
        }
    }

    // ── Price filter ──
    if ( isset($_GET['min_price']) || isset($_GET['max_price']) ) {
        $min = isset($_GET['min_price']) ? (float) $_GET['min_price'] : 0;
        $max = isset($_GET['max_price']) ? (float) $_GET['max_price'] : PHP_INT_MAX;
        $meta_query[] = [
            'key'     => '_price',
            'value'   => [ $min, $max ],
            'compare' => 'BETWEEN',
            'type'    => 'NUMERIC',
        ];
        $q->set('meta_query', $meta_query);
    }
});