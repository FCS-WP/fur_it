<?php

namespace Zippy_Core;

use Zippy_Core\Settings\Routes\Setting_Routes;
use Zippy_Core\Settings\Services\Order_Setting_Services;
use Zippy_Core\Settings\Services\Setting_Services;

class Core_Settings extends Core_Module
{
    const OPTIONS_KEY_CORE_MODULES = 'core_module_configs';
    const OPTIONS_KEY_MODULES_CONFIGS = 'core_module_configs_order_details';
    const OPTIONS_KEY_ORDER_INVOICES_CONFIGS = 'core_module_configs_order_invoices';
    const OPTIONS_KEY_ORDER_DETAILS_CONFIGS = 'core_module_configs_order_details';
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
        Setting_Routes::get_instance();
        add_action('admin_enqueue_scripts', array($this, 'remove_default_stylesheets'));

        $this->init_required_options();

        add_action('admin_menu', [$this, 'register_settings_page']);
    }

    public function init_required_options()
    {
        Setting_Services::init_modules_option();
        Order_Setting_Services::init_invoices_option();
        Order_Setting_Services::init_order_detail_option();
    }

    public function register_settings_page()
    {
        add_menu_page(
            'Core Settings',          // Page title
            'Core Settings',          // Menu title
            'manage_options',          // Capability
            'core-settings',          // Menu slug
            [$this, 'render_settings_page'], // Callback function
            'dashicons-admin-generic', // Icon
            3                          // Position
        );

        $is_active_orders = Setting_Services::get_core_config("orders");
        if ($is_active_orders == 'yes') {
            add_submenu_page(
                'core-settings',          // Parent slug
                'Orders Settings',        // Page title
                'Orders',                 // Menu title
                'manage_options',          // Capability
                'core-settings-orders',  // Menu slug
                [$this, 'render_settings_orders_page']  // Callback
            );
        }
    }

    function render_settings_page()
    {
        echo '<div id="core_settings"></div>';
    }

    function render_settings_orders_page()
    {
        echo '<div id="core_settings_orders"></div>';
    }

    public function remove_default_stylesheets($handle)
    {
        $apply_urls = [
            'core-settings_page_core-settings-orders',
            'toplevel_page_core-settings',
        ];

        if (in_array($handle, $apply_urls)) {
            // Deregister the 'forms' stylesheet
            wp_deregister_style('forms');

            add_action('admin_head', function () {
                $admin_url = get_admin_url();
                $styles_to_load = [
                    'dashicons',
                    'admin-bar',
                    'common',
                    'admin-menu',
                    'dashboard',
                    'list-tables',
                    'edit',
                    'revisions',
                    'media',
                    'themes',
                    'about',
                    'nav-menus',
                    'wp-pointer',
                    'widgets',
                    'site-icon',
                    'l10n',
                    'buttons',
                    'wp-auth-check'
                ];

                $wp_version = get_bloginfo('version');

                // Generate the styles URL
                $styles_url = $admin_url . '/load-styles.php?c=0&dir=ltr&load=' . implode(',', $styles_to_load) . '&ver=' . $wp_version;

                // Enqueue the stylesheet
                echo '<link rel="stylesheet" href="' . esc_url($styles_url) . '" media="all">';
            });
        }
    }
}
