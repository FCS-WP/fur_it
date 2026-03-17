<?php

namespace Zippy_Core\Settings\Controllers;

use WP_REST_Request;
use Zippy_Core\Core_Settings;
use Zippy_Core\Settings\Services\Order_Setting_Services;
use Zippy_Core\Settings\Services\Setting_Services;
use Zippy_Core\Utils\Zippy_Response_Handler;

class Order_Setting_Controllers
{

    public static function get_invoices_options(WP_REST_Request $request)
    {
        try {
            $configs = Setting_Services::get_saved_options(Core_Settings::OPTIONS_KEY_ORDER_INVOICES_CONFIGS);
            return Zippy_Response_Handler::success(["result" => $configs], 'Get invoices settings successfully!');
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error($e->getMessage());
        }
    }

    public static function handle_update_invoices_options(WP_REST_Request $request)
    {
        try {
            $data = $request->get_param('new_invoices_options');
            $newConfigs = Order_Setting_Services::update_invoices_options($data);
            return Zippy_Response_Handler::success(["result" => $newConfigs], 'Update invoices successfully!');
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error($e->getMessage());
        }
    }

    // Order detail setting 
    public static function get_order_detail_options(WP_REST_Request $request)
    {
        try {
            $configs = Setting_Services::get_saved_options(Core_Settings::OPTIONS_KEY_ORDER_DETAILS_CONFIGS);
            return Zippy_Response_Handler::success(["result" => $configs], 'Get details settings successfully!');
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error($e->getMessage());
        }
    }

    public static function handle_update_order_detail_options(WP_REST_Request $request)
    {
        try {
            $data = $request->get_param('new_values');
            $newConfigs = Setting_Services::update_saved_options(Core_Settings::OPTIONS_KEY_ORDER_DETAILS_CONFIGS, $data);
            return Zippy_Response_Handler::success(["result" => $newConfigs], 'Update details successfully!');
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error($e->getMessage());
        }
    }
}
