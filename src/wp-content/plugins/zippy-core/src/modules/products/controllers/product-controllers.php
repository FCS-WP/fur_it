<?php


namespace Zippy_Core\Products\Controllers;

use WP_REST_Request;
use WP_Error;
use Zippy_Core\Orders\Services\Order_Services;
use Zippy_Core\Products\Services\Product_Services;
use Zippy_Core\Utils\Zippy_Request_Helper;
use Zippy_Core\Utils\Zippy_Response_Handler;
use Zippy_Core\Utils\Zippy_Wc_Calculate_Helper;
use WC_Coupon;

class Product_Controllers
{
    public static function get_products(WP_REST_Request $request)
    {
        try {
            $paramsInfo = Zippy_Request_Helper::get_params($request);
            $result = Product_Services::get_products($paramsInfo);

            return empty($result['data'])
                ? Zippy_Response_Handler::error('No products found.', 200)
                : Zippy_Response_Handler::success($result, "Products retrieved successfully.");
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error('Error retrieving products', 200);
        }
    }

    public static function get_categories(WP_REST_Request $request)
    {
        try {
            $data = Product_Services::get_product_categories();

            return empty($data)
                ? Zippy_Response_Handler::error("No categories found.")
                : Zippy_Response_Handler::success(['data' => $data], "Products categories retrieved successfully.");
        } catch (\Exception $e) {
            return Zippy_Response_Handler::error('Empty categories', 500);
        }
    }
}
