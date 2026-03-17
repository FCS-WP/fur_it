<?php

namespace Zippy_Core\Settings\Routes;

use Zippy_Core\Core_Middleware;
use Zippy_Core\Core_Route;
use Zippy_Core\Settings\Controllers\Order_Setting_Controllers;
use Zippy_Core\Settings\Controllers\Setting_Controllers;
use Zippy_Core\Settings\Models\Order_Arguments;
use Zippy_Core\Settings\Models\Setting_Arguments;

class Setting_Routes extends Core_Route
{

    public function init_module_api()
    {
        /**
         * Common Setting Routes
         */
        register_rest_route(ZIPPY_CORE_API_PREFIX, '/core-settings', [
            'methods'  => 'GET',
            'callback' => [Setting_Controllers::class, 'get_modules'],
            'permission_callback' => [Core_Middleware::class, 'admin_only'],
            'args' => Setting_Arguments::get_setting_args(),
        ]);
        register_rest_route(ZIPPY_CORE_API_PREFIX, '/core-settings', [
            'methods'  => 'POST',
            'callback' => [Setting_Controllers::class, 'handle_update_module_settings'],
            'permission_callback' => [Core_Middleware::class, 'admin_only'],
            'args' => Setting_Arguments::update_modules_settings_args(),
        ]);

        /**
         * Order setting Routes: 
         * @return Order_setting_routes
         */

        register_rest_route(ZIPPY_CORE_API_PREFIX, '/core-settings/orders/invoices', [
            'methods'  => 'GET',
            'callback' => [Order_Setting_Controllers::class, 'get_invoices_options'],
            'permission_callback' => [Core_Middleware::class, 'admin_only'],
        ]);
        register_rest_route(ZIPPY_CORE_API_PREFIX, '/core-settings/orders/invoices', [
            'methods'  => 'POST',
            'callback' => [Order_Setting_Controllers::class, 'handle_update_invoices_options'],
            'permission_callback' => [Core_Middleware::class, 'admin_only'],
            'args' => Setting_Arguments::update_invoices_options_args(),
        ]);

        register_rest_route(ZIPPY_CORE_API_PREFIX, '/core-settings/orders/details', [
            'methods'  => 'GET',
            'callback' => [Order_Setting_Controllers::class, 'get_order_detail_options'],
            'permission_callback' => [Core_Middleware::class, 'admin_only'],
        ]);
        register_rest_route(ZIPPY_CORE_API_PREFIX, '/core-settings/orders/details', [
            'methods'  => 'POST',
            'callback' => [Order_Setting_Controllers::class, 'handle_update_order_detail_options'],
            'permission_callback' => [Core_Middleware::class, 'admin_only'],
            'args' => Setting_Arguments::update_order_detail_options_args(),
        ]);
    }
}
