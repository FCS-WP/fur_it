<?php
/**
 * Zippy Custom Shortcodes
 * Child theme of Flatsome
 */

function myContentFooter() {
    ?>
    <div class="ppocta-ft-fix">
        <a id="whatsappButton" href="https://wa.me/+6581865029" target="_blank"><span>Whatsapp: +65 8186 5029</span></a>
    </div>
    <?php
}
add_action( 'wp_footer', 'myContentFooter' );

// ============================================
// [zippy_section_title]
// ============================================
function zippy_section_title( $atts, $content = null ) {
    $atts = shortcode_atts([
        'color'       => '#191919',
        'font_size'   => '',
        'font_weight' => '',
        'font_family' => '',
        'align'       => '',
        'class'       => '',
        'tag'         => 'h2',
    ], $atts, 'zippy_section_title');

    $style = zippy_build_style([
        'color'       => $atts['color'],
        'font-size'   => $atts['font_size'],
        'font-weight' => $atts['font_weight'],
        'font-family' => $atts['font_family'],
        'text-align'  => $atts['align'],
    ]);

    $tag   = sanitize_key($atts['tag']);
    $class = 'zippy-section-title' . ($atts['class'] ? ' ' . esc_attr($atts['class']) : '');

    return sprintf(
        '<%1$s class="%2$s"%3$s>%4$s</%1$s>',
        $tag,
        $class,
        $style,
        wp_kses_post($content)
    );
}
add_shortcode('zippy_section_title', 'zippy_section_title');


// ============================================
// [zippy_section_sub_title]
// ============================================
function zippy_section_sub_title( $atts, $content = null ) {
    $atts = shortcode_atts([
        'color'       => '#191919',
        'font_size'   => '',
        'font_weight' => '',
        'font_family' => '',
        'align'       => '',
        'class'       => '',
        'tag'         => 'h4',
    ], $atts, 'zippy_section_sub_title');

    $style = zippy_build_style([
        'color'       => $atts['color'],
        'font-size'   => $atts['font_size'],
        'font-weight' => $atts['font_weight'],
        'font-family' => $atts['font_family'],
        'text-align'  => $atts['align'],
    ]);

    $tag   = sanitize_key($atts['tag']);
    $class = 'zippy-section-sub-title' . ($atts['class'] ? ' ' . esc_attr($atts['class']) : '');

    return sprintf(
        '<%1$s class="%2$s"%3$s>%4$s</%1$s>',
        $tag,
        $class,
        $style,
        wp_kses_post($content)
    );
}
add_shortcode('zippy_section_sub_title', 'zippy_section_sub_title');


// ============================================
// [zippy_section_text]
// ============================================
function zippy_section_text( $atts, $content = null ) {
    $atts = shortcode_atts([
        'color'           => '',
        'font_size'       => '',
        'font_weight'     => '',
        'font_family'     => '',
        'line_height'     => '',
        'letter_spacing'  => '',
        'align'           => '',
        'class'           => '',
    ], $atts, 'zippy_section_text');

    $style = zippy_build_style([
        'color'          => $atts['color'],
        'font-size'      => $atts['font_size'],
        'font-weight'    => $atts['font_weight'],
        'font-family'    => $atts['font_family'],
        'line-height'    => $atts['line_height'],
        'letter-spacing' => $atts['letter_spacing'],
        'text-align'     => $atts['align'],
    ]);

    $class = 'zippy-section-text' . ($atts['class'] ? ' ' . esc_attr($atts['class']) : '');

    return sprintf(
        '<p class="%s"%s>%s</p>',
        $class,
        $style,
        wp_kses_post($content)
    );
}
add_shortcode('zippy_section_text', 'zippy_section_text');


// ============================================
// [zippy_button]
// ============================================
function zippy_button( $atts, $content = null ) {
    $atts = shortcode_atts([
        'url'             => '#',
        'target'          => '_self',
        'color'           => '',
        'bg_color'        => '',
        'font_size'       => '',
        'font_weight'     => '',
        'font_family'     => '',
        'border_color'    => '',
        'border_radius'   => '',
        'padding'         => '',
        'hover_color'     => '',
        'hover_bg_color'  => '',
        'class'           => '',
    ], $atts, 'zippy_button');

    $style = zippy_build_style([
        'color'           => $atts['color'],
        'background-color'=> $atts['bg_color'],
        'font-size'       => $atts['font_size'],
        'font-weight'     => $atts['font_weight'],
        'font-family'     => $atts['font_family'],
        'border-color'    => $atts['border_color'],
        'border-radius'   => $atts['border_radius'],
        'padding'         => $atts['padding'],
    ]);

    $class = 'zippy-button' . ($atts['class'] ? ' ' . esc_attr($atts['class']) : '');

    // Hover styles via inline <style> with unique ID
    $hover_css = '';
    if ( $atts['hover_color'] || $atts['hover_bg_color'] ) {
        $uid = 'zippy-btn-' . uniqid();
        $class .= ' ' . $uid;
        $hover_style = zippy_build_style([
            'color'            => $atts['hover_color'],
            'background-color' => $atts['hover_bg_color'],
        ], true);
        $hover_css = sprintf(
            '<style>.%s:hover{%s}</style>',
            $uid,
            $hover_style
        );
    }

    return $hover_css . sprintf(
        '<a href="%s" target="%s" class="%s"%s>%s</a>',
        esc_url($atts['url']),
        esc_attr($atts['target']),
        $class,
        $style,
        wp_kses_post($content)
    );
}
add_shortcode('zippy_button', 'zippy_button');


// ============================================
// Helper: Build inline style string
// ============================================
function zippy_build_style( $props, $raw = false ) {
    $styles = [];
    foreach ( $props as $prop => $value ) {
        if ( ! empty($value) ) {
            $styles[] = $raw
                ? esc_attr($prop) . ':' . esc_attr($value)
                : esc_attr($prop) . ':' . esc_attr($value);
        }
    }
    if ( empty($styles) ) return '';
    return ' style="' . implode(';', $styles) . '"';
}


// ============================================
// [zippy_review_box]
// ============================================
function zippy_review_box( $atts, $content = null ) {
    $atts = shortcode_atts([
        'icon'      => '',
        'title'     => '',
        'sub_title' => '',
        'class'     => '',
    ], $atts, 'zippy_review_box');

    $class = 'custom-review-box' . ( $atts['class'] ? ' ' . esc_attr($atts['class']) : '' );

    // Icon
    $icon_html = '';
    if ( ! empty($atts['icon']) ) {
        $icon_html = sprintf(
            '<div class="float-icon"><img src="%s" alt="icon" /></div>',
            esc_url($atts['icon'])
        );
    }

    // Sub title — only render if provided
    $sub_title_html = '';
    if ( ! empty($atts['sub_title']) ) {
        $sub_title_html = sprintf(
            '<h6 class="box-sub-title">%s</h6>',
            esc_html($atts['sub_title'])
        );
    }

    return sprintf(
        '<div class="%s">
            %s
            <div class="box-content">
                <p class="">%s</p>
                <div>
                    <h5 class="box-title">%s</h5>
                    %s
                </div>
            </div>
        </div>',
        $class,
        $icon_html,
        wp_kses_post($content),
        esc_html($atts['title']),
        $sub_title_html
    );
}
add_shortcode('zippy_review_box', 'zippy_review_box');

// ============================================
// [zippy_service_card]
// ============================================
function zippy_service_card( $atts, $content = null ) {
    $atts = shortcode_atts([
        'icon'        => '',
        'title'       => '',
        'btn_text'    => 'See Catalogue',
        'btn_url'     => '#',
        'btn_target'  => '_self',
        'variant'     => 'light',   // light | dark
        'class'       => '',
    ], $atts, 'zippy_service_card');

    $class = 'zippy-service-card zippy-service-card--' . esc_attr($atts['variant']);
    if ( $atts['class'] ) $class .= ' ' . esc_attr($atts['class']);

    // Icon + Title header (only for light variant with icon)
    $header_html = '';
    if ( ! empty($atts['icon']) || ! empty($atts['title']) ) {
        $icon_html = '';
        if ( ! empty($atts['icon']) ) {
            $icon_html = sprintf(
                '<img class="zippy-service-card__icon" src="%s" alt="%s icon" />',
                esc_url($atts['icon']),
                esc_attr($atts['title'])
            );
        }
        $header_html = sprintf(
            '<div class="zippy-service-card__header">
                %s
                <h4 class="zippy-service-card__title">%s</h4>
            </div>',
            $icon_html,
            esc_html($atts['title'])
        );
    }

    // Button
    $btn_html = '';
    if ( ! empty($atts['btn_text']) ) {
        $btn_html = sprintf(
            '<div class="zippy-service-card__footer">
                <a class="zippy-service-card__btn" href="%s" target="%s">%s</a>
            </div>',
            esc_url($atts['btn_url']),
            esc_attr($atts['btn_target']),
            esc_html($atts['btn_text'])
        );
    }

    return sprintf(
        '<div class="%s">
            %s
            <div class="zippy-service-card__body">
                <p class="zippy-service-card__text">%s</p>
            </div>
            %s
        </div>',
        $class,
        $header_html,
        wp_kses_post($content),
        $btn_html
    );
}
add_shortcode('zippy_service_card', 'zippy_service_card');


// ============================================
// [zippy_menu]
// ============================================
function zippy_menu( $atts ) {
    $atts = shortcode_atts([
        // Menu
        'menu'           => '',         // menu ID or name or slug
        'direction'      => 'horizontal', // horizontal | vertical

        // Style
        'align'          => 'left',     // left | center | right
        'gap'            => '32px',     // space between items
        'font_size'      => '',
        'font_weight'    => '',
        'font_family'    => '',
        'color'          => '',
        'color_hover'    => '',
        'color_active'   => '',
        'indicator'      => 'false',    // show active underline indicator
        'class'          => '',
    ], $atts, 'zippy_menu');

    if ( empty($atts['menu']) ) return '<!-- zippy_menu: no menu specified -->';

    // ── Resolve menu by ID, slug, or name ──
    $menu_obj = null;
    if ( is_numeric($atts['menu']) ) {
        $menu_obj = wp_get_nav_menu_object( (int) $atts['menu'] );
    }
    if ( ! $menu_obj ) {
        $menu_obj = wp_get_nav_menu_object( $atts['menu'] );
    }
    if ( ! $menu_obj ) return '<!-- zippy_menu: menu not found -->';

    // ── Unique ID for scoped CSS ──
    static $menu_index = 0;
    $menu_index++;
    $uid = 'zippy-menu-' . $menu_index;

    // ── Build scoped CSS vars ──
    $css_rules = [];

    if ( $atts['direction'] === 'horizontal' ) {
        $css_rules[] = 'display:flex;flex-wrap:wrap;align-items:center;';
        $css_rules[] = 'justify-content:' . ( $atts['align'] === 'center' ? 'center' : ( $atts['align'] === 'right' ? 'flex-end' : 'flex-start' ) ) . ';';
        $css_rules[] = 'gap:' . esc_attr($atts['gap']) . ';';
    } else {
        $css_rules[] = 'display:flex;flex-direction:column;';
        $css_rules[] = 'align-items:' . ( $atts['align'] === 'center' ? 'center' : ( $atts['align'] === 'right' ? 'flex-end' : 'flex-start' ) ) . ';';
        $css_rules[] = 'gap:' . esc_attr($atts['gap']) . ';';
    }

    $link_css   = [];
    $hover_css  = [];
    $active_css = [];

    if ( $atts['font_size'] )   $link_css[] = 'font-size:'   . esc_attr($atts['font_size']);
    if ( $atts['font_weight'] ) $link_css[] = 'font-weight:' . esc_attr($atts['font_weight']);
    if ( $atts['font_family'] ) $link_css[] = 'font-family:' . esc_attr($atts['font_family']);
    if ( $atts['color'] )       $link_css[] = 'color:'       . esc_attr($atts['color']);
    if ( $atts['color_hover'] ) $hover_css[] = 'color:'      . esc_attr($atts['color_hover']);
    if ( $atts['color_active']) $active_css[] = 'color:'     . esc_attr($atts['color_active']);

    // Indicator underline
    $indicator_css = '';
    if ( $atts['indicator'] === 'true' ) {
        $indicator_color = $atts['color_active'] ?: $atts['color'] ?: 'currentColor';
        $indicator_css = "
            #{$uid} .zippy-menu > li > a::after {
                content: '';
                display: block;
                height: 2px;
                width: 0;
                background: {$indicator_color};
                transition: width 0.25s ease;
                margin-top: 4px;
            }
            #{$uid} .zippy-menu > li > a:hover::after,
            #{$uid} .zippy-menu > li.current-menu-item > a::after {
                width: 100%;
            }
        ";
    }

    ob_start();
    ?>

    <style>
        #<?php echo $uid; ?> .zippy-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            <?php echo implode('', $css_rules); ?>
        }
        #<?php echo $uid; ?> .zippy-menu li {
            margin: 0;
            padding: 0;
        }
        #<?php echo $uid; ?> .zippy-menu li a {
            text-decoration: none;
            display: inline-block;
            transition: color 0.2s ease;
            <?php echo implode(';', $link_css); ?>
        }
        <?php if ( $hover_css ) : ?>
        #<?php echo $uid; ?> .zippy-menu li a:hover {
            <?php echo implode(';', $hover_css); ?>
        }
        <?php endif; ?>
        <?php if ( $active_css ) : ?>
        #<?php echo $uid; ?> .zippy-menu li.current-menu-item > a,
        #<?php echo $uid; ?> .zippy-menu li.current-menu-ancestor > a {
            <?php echo implode(';', $active_css); ?>
        }
        <?php endif; ?>
        <?php echo $indicator_css; ?>
    </style>

    <nav id="<?php echo $uid; ?>"
         class="zippy-menu-wrap zippy-menu--<?php echo esc_attr($atts['direction']); ?> <?php echo esc_attr($atts['class']); ?>"
         aria-label="<?php echo esc_attr($menu_obj->name); ?>">
        <?php
        wp_nav_menu([
            'menu'            => $menu_obj,
            'menu_class'      => 'zippy-menu',
            'container'       => false,
            'items_wrap'      => '<ul class="%2$s">%3$s</ul>',
            'depth'           => 1,   // 0 = all levels, 1 = top level only
        ]);
        ?>
    </nav>

    <?php
    return ob_get_clean();
}
add_shortcode('zippy_menu', 'zippy_menu');


// ============================================================
// [zippy_breadcrumb]
// ============================================================
function zippy_breadcrumb( $atts ) {
    $atts = shortcode_atts([
        'separator'       => '/',
        'show_home'       => 'true',
        'home_text'       => 'Home',
        'home_icon'       => 'false',
        'font_size'       => '',
        'font_weight'     => '',
        'color'           => '',
        'color_active'    => '',
        'color_hover'     => '',
        'color_separator' => '',
        'align'           => 'left',
        'class'           => '',
    ], $atts, 'zippy_breadcrumb');
 
    // Don't show on homepage
    if ( is_front_page() ) return '';
 
    static $bc_index = 0;
    $bc_index++;
    $uid = 'zippy-bc-' . $bc_index;
 
    // ── Build items ──
    $items = [];
 
    // Home item
    if ( $atts['show_home'] === 'true' ) {
        $home_label = $atts['home_icon'] === 'true'
            ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>'
            : esc_html($atts['home_text']);
 
        $items[] = [
            'label'  => $home_label,
            'url'    => home_url('/'),
            'active' => false,
        ];
    }
 
    // ── WooCommerce pages ──
    if ( function_exists('WC') ) {
 
        if ( is_shop() ) {
            $items[] = [ 'label' => get_the_title(wc_get_page_id('shop')), 'url' => '', 'active' => true ];
 
        } elseif ( is_product() ) {
            $items[] = [
                'label'  => get_the_title(wc_get_page_id('shop')),
                'url'    => get_permalink(wc_get_page_id('shop')),
                'active' => false,
            ];
            $terms = get_the_terms(get_the_ID(), 'product_cat');
            if ( $terms && ! is_wp_error($terms) ) {
                $term = array_reduce($terms, function($carry, $item) {
                    return (!$carry || $item->parent > $carry->parent) ? $item : $carry;
                });
                foreach ( array_reverse(get_ancestors($term->term_id, 'product_cat')) as $ancestor_id ) {
                    $ancestor = get_term($ancestor_id, 'product_cat');
                    $items[]  = [ 'label' => $ancestor->name, 'url' => get_term_link($ancestor), 'active' => false ];
                }
                $items[] = [ 'label' => $term->name, 'url' => get_term_link($term), 'active' => false ];
            }
            $items[] = [ 'label' => get_the_title(), 'url' => '', 'active' => true ];
 
        } elseif ( is_product_category() ) {
            $items[] = [
                'label'  => get_the_title(wc_get_page_id('shop')),
                'url'    => get_permalink(wc_get_page_id('shop')),
                'active' => false,
            ];
            $term = get_queried_object();
            foreach ( array_reverse(get_ancestors($term->term_id, 'product_cat')) as $ancestor_id ) {
                $ancestor = get_term($ancestor_id, 'product_cat');
                $items[]  = [ 'label' => $ancestor->name, 'url' => get_term_link($ancestor), 'active' => false ];
            }
            $items[] = [ 'label' => $term->name, 'url' => '', 'active' => true ];
        }
 
    }
 
    // ── Standard WordPress pages ──
    // Only run if no WooCommerce items were added beyond Home
    $wc_handled = count($items) > 1;
 
    if ( ! $wc_handled ) {
 
        if ( is_single() ) {
            $categories = get_the_category();
            if ( $categories ) {
                $items[] = [
                    'label'  => $categories[0]->name,
                    'url'    => get_category_link($categories[0]->term_id),
                    'active' => false,
                ];
            }
            $items[] = [ 'label' => get_the_title(), 'url' => '', 'active' => true ];
 
        } elseif ( is_page() ) {
            $ancestors = array_reverse(get_post_ancestors(get_the_ID()));
            foreach ( $ancestors as $ancestor_id ) {
                $items[] = [
                    'label'  => get_the_title($ancestor_id),
                    'url'    => get_permalink($ancestor_id),
                    'active' => false,
                ];
            }
            $items[] = [ 'label' => get_the_title(), 'url' => '', 'active' => true ];
 
        } elseif ( is_category() ) {
            $category = get_queried_object();
            foreach ( array_reverse(get_ancestors($category->term_id, 'category')) as $ancestor_id ) {
                $ancestor = get_term($ancestor_id, 'category');
                $items[]  = [ 'label' => $ancestor->name, 'url' => get_term_link($ancestor), 'active' => false ];
            }
            $items[] = [ 'label' => $category->name, 'url' => '', 'active' => true ];
 
        } elseif ( is_tag() ) {
            $items[] = [ 'label' => 'Tag: ' . single_tag_title('', false), 'url' => '', 'active' => true ];
 
        } elseif ( is_author() ) {
            $items[] = [ 'label' => 'Author: ' . get_the_author(), 'url' => '', 'active' => true ];
 
        } elseif ( is_date() ) {
            $items[] = [ 'label' => get_the_date('F Y'), 'url' => '', 'active' => true ];
 
        } elseif ( is_search() ) {
            $items[] = [ 'label' => 'Search: ' . get_search_query(), 'url' => '', 'active' => true ];
 
        } elseif ( is_404() ) {
            $items[] = [ 'label' => '404 - Page Not Found', 'url' => '', 'active' => true ];
        }
    }
 
    // Need at least 2 items to show breadcrumb
    if ( count($items) <= 1 ) return '';
 
    // ── Scoped CSS ──
    $link_css   = [];
    $hover_css  = [];
    $active_css = [];
    $sep_css    = [];
 
    if ( $atts['font_size'] )       $link_css[]   = 'font-size:'   . esc_attr($atts['font_size']);
    if ( $atts['font_weight'] )     $link_css[]   = 'font-weight:' . esc_attr($atts['font_weight']);
    if ( $atts['color'] )           $link_css[]   = 'color:'       . esc_attr($atts['color']);
    if ( $atts['color_hover'] )     $hover_css[]  = 'color:'       . esc_attr($atts['color_hover']);
    if ( $atts['color_active'] )    $active_css[] = 'color:'       . esc_attr($atts['color_active']);
    if ( $atts['color_separator'] ) $sep_css[]    = 'color:'       . esc_attr($atts['color_separator']);
 
    ob_start();
    ?>
 
    <?php if ( $link_css || $hover_css || $active_css || $sep_css ) : ?>
    <style>
        <?php if ($link_css) : ?>
        #<?php echo $uid; ?> .zippy-breadcrumb__item a,
        #<?php echo $uid; ?> .zippy-breadcrumb__item span { <?php echo implode(';', $link_css); ?> }
        <?php endif; ?>
        <?php if ($hover_css) : ?>
        #<?php echo $uid; ?> .zippy-breadcrumb__item a:hover { <?php echo implode(';', $hover_css); ?> }
        <?php endif; ?>
        <?php if ($active_css) : ?>
        #<?php echo $uid; ?> .zippy-breadcrumb__item--active span { <?php echo implode(';', $active_css); ?> }
        <?php endif; ?>
        <?php if ($sep_css) : ?>
        #<?php echo $uid; ?> .zippy-breadcrumb__sep { <?php echo implode(';', $sep_css); ?> }
        <?php endif; ?>
    </style>
    <?php endif; ?>
 
    <nav id="<?php echo $uid; ?>"
         class="zippy-breadcrumb <?php echo esc_attr($atts['class']); ?>"
         aria-label="Breadcrumb"
         style="text-align:<?php echo esc_attr($atts['align']); ?>">
        <ol class="zippy-breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">
            <?php foreach ( $items as $index => $item ) :
                $is_last  = $index === array_key_last($items);
                $position = $index + 1;
            ?>
            <li class="zippy-breadcrumb__item<?php echo $is_last ? ' zippy-breadcrumb__item--active' : ''; ?>"
                itemprop="itemListElement"
                itemscope
                itemtype="https://schema.org/ListItem">
 
                <?php if ( ! $is_last && $item['url'] ) : ?>
                    <a href="<?php echo esc_url($item['url']); ?>" itemprop="item">
                        <span itemprop="name"><?php echo wp_kses_post($item['label']); ?></span>
                    </a>
                <?php else : ?>
                    <span itemprop="name" aria-current="page"><?php echo wp_kses_post($item['label']); ?></span>
                <?php endif; ?>
 
                <meta itemprop="position" content="<?php echo $position; ?>" />
 
                <?php if ( ! $is_last ) : ?>
                    <span class="zippy-breadcrumb__sep" aria-hidden="true"><?php echo esc_html($atts['separator']); ?></span>
                <?php endif; ?>
 
            </li>
            <?php endforeach; ?>
        </ol>
    </nav>
 
    <?php
    return ob_get_clean();
}
add_shortcode('zippy_breadcrumb', 'zippy_breadcrumb');


