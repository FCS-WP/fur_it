<?php

namespace Zippy_Core;

use Zippy_Core\Core_Module;
use Zippy_Core\Shipping\Services\Shipping_Services;
use Zippy_Core\Utils\Zippy_Wc_Calculate_Helper;
use WC_Tax;

class Core_Shipping extends Core_Module
{
    public function __construct()
    {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            return;
        }

        parent::__construct();
    }

    public function load_required_files()
    {
        $paths = [
            __DIR__ . '/controllers',
            __DIR__ . '/routes',
            __DIR__ . '/services',
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
        if ($this->is_shipping_tax_enabled()) {
            add_filter('woocommerce_package_rates', [$this, 'recalculate_shipping_rates'], 9999, 2);
        }
    }

    private function is_shipping_tax_enabled()
    {
        $standard_rates = WC_Tax::get_rates_for_tax_class('');
        foreach ($standard_rates as $rate) {
            if (!empty($rate->tax_rate_shipping)) {
                return true;
            }
        }
        return false;
    }
}
