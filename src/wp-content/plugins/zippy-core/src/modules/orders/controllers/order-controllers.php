<?php


namespace Zippy_Core\Orders\Controllers;

use WP_REST_Request;
use WP_Error;
use Zippy_Core\Orders\Services\Order_Services;
use Zippy_Core\Utils\Zippy_Request_Helper;
use Zippy_Core\Utils\Zippy_Response_Handler;
use Zippy_Core\Utils\Zippy_Wc_Calculate_Helper;
use WC_Coupon;
use Zippy_Core\Orders\Services\Order_Detail_Services;

class Order_Controllers
{
    public static function get_all_orders_with_pagination(WP_REST_Request $request)
    {
        try {
            $infos = Zippy_Request_Helper::get_params($request);
            $data = Order_Services::handle_orders($infos);

            return Zippy_Response_Handler::success($data, 'Get orders successfully!');
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error($e->getMessage());
        }
    }

    public static function get_order_detail_by_id(WP_REST_Request $request)
    {
        try {
            $order_id = $request->get_param('id');
            $data = Order_Detail_Services::get_order_detail_by_id($order_id);

            if (empty($data)) {
                return Zippy_Response_Handler::error('Order not found or no details available.');
            }

            return Zippy_Response_Handler::success($data, 'Get order detail successfully!');
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error($e->getMessage());
        }
    }

    public static function bulk_action_update_order_status(WP_REST_Request $request)
    {
        $infos = Zippy_Request_Helper::get_params($request);
        $result = Order_Services::bulk_action_update_order_status($infos);
        return Zippy_Response_Handler::success($result, 'Orders updated successfully.');
    }

    public static function get_order_info(WP_REST_Request $request)
    {
        $order_id = $request->get_param('order_id');
        $result = Order_Detail_Services::get_order_info($order_id);

        if (empty($result)) {
            return Zippy_Response_Handler::error('Order not found or no details available.');
        }

        return Zippy_Response_Handler::success($result, 'Get order info successfully.');
    }


    public static function remove_order_item(WP_REST_Request $request)
    {
        $paramsInfo = Zippy_Request_Helper::get_params($request);
        $removed = Order_Detail_Services::remove_order_item($paramsInfo);

        if (!$removed) {
            return Zippy_Response_Handler::error('Failed to remove order item.');
        }

        return Zippy_Response_Handler::success([
            'order_id' => $paramsInfo['order_id'],
            'item_id'  => $paramsInfo['item_id'],
        ], 'Order item deleted successfully.');
    }

    public static function update_quantity_order_item(WP_REST_Request $request)
    {
        $paramInfos = Zippy_Request_Helper::get_params($request);
        $result = Order_Detail_Services::update_quantity_order_item($paramInfos);

        if (empty($result)) {
            return Zippy_Response_Handler::error('Failed to update order item quantity.');
        }

        return Zippy_Response_Handler::success($result, 200);
    }

    public static function apply_coupon_to_order(WP_REST_Request $request)
    {
        try {
            $paramsInfo = Zippy_Request_Helper::get_params($request);
            $updated = Order_Detail_Services::apply_coupon_to_order($paramsInfo);

            if (!$updated) {
                return Zippy_Response_Handler::error('Failed to apply coupon to order.');
            }

            return Zippy_Response_Handler::success([
                'order_id' => $paramsInfo['order_id'],
                'coupon_code' => $paramsInfo['coupon_code'],
                'message' => 'Coupon applied successfully.'
            ]);
        } catch (\Throwable $th) {
            return Zippy_Response_Handler::error('An error occurred while applying the coupon.');
        }
    }

    public static function move_to_trash(WP_REST_Request $request)
    {
        $order_ids = $request->get_param('order_ids');

        $trashed = Order_Services::move_orders_to_trash($order_ids);
        if (empty($trashed)) {
            return Zippy_Response_Handler::error('No orders were trashed.');
        }

        return Zippy_Response_Handler::success([
            'trashed_orders' => $trashed,
        ], 'Orders moved to trash.');
    }

    public static function add_product_to_order(WP_REST_Request $request)
    {
        $paramsInfo = Zippy_Request_Helper::get_params($request);
        $result = Order_Detail_Services::add_product_to_order($paramsInfo);

        if (empty($result)) {
            return Zippy_Response_Handler::error('Failed to add products to order.');
        }

        return Zippy_Response_Handler::success($result, 'Products added to order successfully.');
    }

    public static function export_orders(WP_REST_Request $request)
    {
        try {
            $paramInfos = Zippy_Request_Helper::get_params($request);
            $result = Order_Services::export_orders($paramInfos);

            if (is_wp_error($result)) {
                return Zippy_Response_Handler::error($result->get_error_message());
            }

            return Zippy_Response_Handler::success($result, 'Orders exported successfully.');
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error('An error occurred while exporting orders.');
        }
    }

    public static function download_invoice(WP_REST_Request $request)
    {
        try {
            $order_id = $request->get_param('order_id');
            $result = Order_Detail_Services::download_invoice($order_id);

            if (is_wp_error($result)) {
                return Zippy_Response_Handler::error($result->get_error_message());
            }

            return Zippy_Response_Handler::success($result, 'Invoice downloaded successfully.');
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error('An error occurred while downloading the invoice.');
        }
    }

    public static function handle_send_order_email(WP_REST_Request $request)
    {
        try {
            $order_id = $request->get_param('order_id');
            $email_type = $request->get_param('email_type');
            $sent = Order_Services::custom_send_wc_email($order_id, $email_type);
            return $sent
                ? Zippy_Response_Handler::success([], "Email '$email_type' sent successfully")
                : Zippy_Response_Handler::error('Failed to send email');
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error('An error occurred while downloading the invoice.');
        }
    }

    public static function get_orders_by_keyword(WP_REST_Request $request)
    {
       try {
            $infos = Zippy_Request_Helper::get_params($request);
            $data = Order_Services::search_orders($infos);

            return Zippy_Response_Handler::success($data, 'Get orders successfully!');
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error($e->getMessage());
        }
    }
}
