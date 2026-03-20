<?php

function zippy_register_swiper() {
    wp_enqueue_style(
        'swiper-css',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css'
    );
    wp_enqueue_script(
        'swiper-js',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        [],
        null,
        true // load in footer
    );
}
add_action('wp_enqueue_scripts', 'zippy_register_swiper');

// ============================================
// [zippy_slider] & [zippy_slide]
// ============================================
function zippy_slider( $atts, $content = null ) {
    $atts = shortcode_atts([
        // Slides per view
        'slides_per_view'        => '1',
        'slides_per_view_tablet' => '',       // fallback to slides_per_view
        'slides_per_view_mobile' => '1',

        // Spacing
        'space_between'          => '24',
        'space_between_tablet'   => '',
        'space_between_mobile'   => '16',

        // Navigation
        'arrows'                 => 'true',
        'dots'                   => 'true',

        // Behaviour
        'autoplay'               => 'false',  // ms e.g. "3000", or "false"
        'loop'                   => 'false',
        'centered'               => 'false',
        'free_mode'              => 'false',
        'slides_per_group'       => '1',

        // Style
        'height'                 => '',
        'class'                  => '',
        'id'                     => '',
    ], $atts, 'zippy_slider');

    // Unique ID for each slider instance on page
    static $slider_index = 0;
    $slider_index++;
    $uid = 'zippy-swiper-' . $slider_index;

    $wrapper_class = 'zippy-slider-wrapper';
    if ( $atts['class'] ) $wrapper_class .= ' ' . esc_attr($atts['class']);

    $wrapper_style = $atts['height'] ? ' style="height:' . esc_attr($atts['height']) . '"' : '';

    // Build responsive breakpoints
    $spv_tablet  = $atts['slides_per_view_tablet'] ?: $atts['slides_per_view'];
    $spb_tablet  = $atts['space_between_tablet']   ?: $atts['space_between'];

    // Build JS config
    $config = [
        'slidesPerView'  => is_numeric($atts['slides_per_view']) ? (float)$atts['slides_per_view'] : $atts['slides_per_view'],
        'spaceBetween'   => (int) $atts['space_between'],
        'loop'           => $atts['loop'] === 'true',
        'centeredSlides' => $atts['centered'] === 'true',
        'freeMode'       => $atts['free_mode'] === 'true',
        'slidesPerGroup' => (int) $atts['slides_per_group'],
        'pagination'     => $atts['dots'] === 'true'
                                ? [ 'el' => '#' . $uid . ' .swiper-pagination', 'clickable' => true ]
                                : false,
        'navigation'     => $atts['arrows'] === 'true'
                                ? [ 'nextEl' => '#' . $uid . ' .swiper-button-next', 'prevEl' => '#' . $uid . ' .swiper-button-prev' ]
                                : false,
        'breakpoints'    => [
            '768'  => [
                'slidesPerView' => is_numeric($spv_tablet) ? (float)$spv_tablet : $spv_tablet,
                'spaceBetween'  => (int) $spb_tablet,
            ],
            '1024' => [
                'slidesPerView' => is_numeric($atts['slides_per_view']) ? (float)$atts['slides_per_view'] : $atts['slides_per_view'],
                'spaceBetween'  => (int) $atts['space_between'],
            ],
        ],
    ];

    // Mobile is the base (Swiper is mobile-first)
    $config['slidesPerView'] = is_numeric($atts['slides_per_view_mobile']) ? (float)$atts['slides_per_view_mobile'] : $atts['slides_per_view_mobile'];
    $config['spaceBetween']  = (int) $atts['space_between_mobile'];

    if ( $atts['autoplay'] !== 'false' ) {
        $config['autoplay'] = [
            'delay'                => (int) $atts['autoplay'],
            'disableOnInteraction' => false,
        ];
    }

    $config_json = json_encode($config);

    // Arrows & dots HTML
    $arrows_html = $atts['arrows'] === 'true'
        ? '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>'
        : '';
    $dots_html = $atts['dots'] === 'true'
        ? '<div class="swiper-pagination"></div>'
        : '';

    $inline_script = sprintf(
        '<script>
            document.addEventListener("DOMContentLoaded", function() {
                new Swiper("#%s .swiper", %s);
            });
        </script>',
        $uid,
        $config_json
    );

    return sprintf(
        '<div id="%s" class="%s"%s>
            <div class="swiper">
                <div class="swiper-wrapper">
                    %s
                </div>
                %s
                %s
            </div>
        </div>
        %s',
        $uid,
        $wrapper_class,
        $wrapper_style,
        do_shortcode($content),
        $arrows_html,
        $dots_html,
        $inline_script
    );
}
add_shortcode('zippy_slider', 'zippy_slider');


// ============================================
// [zippy_slide]
// ============================================
function zippy_slide( $atts, $content = null ) {
    $atts = shortcode_atts([
        'bg'       => '',
        'bg_color' => '',
        'align'    => 'center',   // left | center | right
        'valign'   => 'middle',   // top | middle | bottom
        'class'    => '',
    ], $atts, 'zippy_slide');

    $style = 'text-align:' . esc_attr($atts['align']) . ';';
    $style .= 'align-items:' . ( $atts['valign'] === 'top' ? 'flex-start' : ( $atts['valign'] === 'bottom' ? 'flex-end' : 'center' ) ) . ';';
    if ( $atts['bg'] )       $style .= 'background-image:url(' . esc_url($atts['bg']) . ');background-size:cover;background-position:center;';
    if ( $atts['bg_color'] ) $style .= 'background-color:' . esc_attr($atts['bg_color']) . ';';

    $class = 'swiper-slide zippy-slide';
    if ( $atts['class'] ) $class .= ' ' . esc_attr($atts['class']);

    return sprintf(
        '<div class="%s" style="%s">%s</div>',
        $class,
        $style,
        do_shortcode($content)
    );
}
add_shortcode('zippy_slide', 'zippy_slide');