<?php

namespace Zippy_Core;

use Zippy_Core\Core_Module;
use Zippy_Core\Orders\Controllers\Order_Controllers;
use Zippy_Core\Orders\Routes\Order_Route;

class Core_Orders extends Core_Module
{
    protected $module_key = 'orders';

    public function load_required_files()
    {
        $paths = [
            __DIR__ . '/controllers',
            __DIR__ . '/routes',
            __DIR__ . '/services',
            __DIR__ . '/models',
        ];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            foreach (glob($path . '/*.php') as $file) {
                require_once $file;
            }
        }
    }

    public function init_module()
    {
        Order_Route::get_instance();

        /**
         * Add cutomize orders pages
         * 
         */

        add_action('admin_menu', [$this, 'add_custom_orders_page']);
        add_action('admin_menu', [$this, 'hide_default_orders'], 999);

        if ($this->is_custom_order_items_active()) {
            add_shortcode('admin_order_table', array($this, 'generate_admin_order_table_div'));
            add_action('woocommerce_admin_order_items_after_line_items', [$this, 'render_admin_order_table']);
            add_action('admin_head', [$this, 'custom_admin_order_styles']);
        }
    }

    protected function is_custom_order_items_active()
    {
        $configs = get_option('core_module_configs_order_details', []);

        if (! is_array($configs)) {
            $configs = [];
        }

        $status = isset($configs['custom_order_items']) ? $configs['custom_order_items'] : 'no';
        return $status === 'yes';
    }

    function custom_admin_order_styles()
    {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'woocommerce_page_wc-orders') {
            echo '<style>
            .woocommerce_order_items_wrapper .woocommerce_order_items {
                display: none !important;
            }

            .wc-order-data-row.wc-order-totals-items {
                display: none !important;
            }

            .wc-order-data-row.wc-order-bulk-actions {
                display: none !important;
            }
        </style>';
        }
    }

    function render_admin_order_table($order_id)
    {
        $order = wc_get_order($order_id);
        $enable_edit = true;

        echo do_shortcode('[admin_order_table order_id="' . esc_attr($order_id) . '" enable_edit="' . esc_attr($enable_edit) . '"]');
    }

    // Start handle setting tabs
    public function add_zippy_woo_tab($tabs)
    {
        $tabs['zippy_woo'] = __('Zippy - Woo', 'woocommerce');
        return $tabs;
    }

    function generate_admin_order_table_div($atts)
    {
        $atts = shortcode_atts([
            'order_id' => 0,
            'enable_edit' => false,
        ], $atts, 'admin_order_table');

        $order_id = intval($atts['order_id']);
        $enable_edit = (isset($_GET['action']) && $_GET['action'] === 'edit');
        if (!$order_id) {
            return '';
        }

        return '<div id="admin-table-order" data-order-id="' . esc_attr($order_id) . '" data-enable-edit="' . esc_attr($enable_edit) . '"></div>';
    }

    /**
     * Hide defaults orders
     * 
     */

    function hide_default_orders()
    {
        add_action('admin_enqueue_scripts', [$this, 'add_scripts_to_hide_menu_items']);
    }

    function add_scripts_to_hide_menu_items()
    {
        wp_enqueue_script('ordee-admin-scripts', ZIPPY_CORE_URL . '/assets/admin/js/custom-order-scripts.js', [], '1.0', true);
    }

    /**
     * Add custom orders page
     */

    function add_custom_orders_page()
    {
        add_submenu_page(
            'woocommerce',                 // Parent slug (WooCommerce menu)
            'Orders - v2',               // Page title
            'Orders - v2',               // Menu title
            'manage_woocommerce',          // Capability
            'custom-orders',               // Menu slug
            [$this, 'render_custom_orders_page']    // Callback function
        );
    }

    function render_custom_orders_page()
    {
        echo '<div id="orders-page"></div>';
    }
}
