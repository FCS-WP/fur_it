<?php

namespace Zippy_Core\Orders\Services;

use WC_Order_Item_Product;
use WC_Tax;
use Zippy_Core\Utils\Zippy_Wc_Calculate_Helper;
use WC_Coupon;
use WC_Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use Zippy_Core\Core_Settings;

class Order_Detail_Services
{
    /**
     * Get order detail info
     * @param array $data (['order_id' => int])
     * @return array|array{data: array}
     */
    public static function get_order_info(int $order_id)
    {
        $order = wc_get_order($order_id);
        if (empty($order)) {
            return [];
        }

        $items = $order->get_items();
        $shipping_items = $order->get_items('shipping');
        $fee = $order->get_items('fee');
        $coupon_items = $order->get_items('coupon');

        $result = [];

        [$result['products'], $subtotalOrder, $taxTotalOrder] = self::get_products_info($items);
        [$result['shipping'], $totalShipping, $taxShipping] = self::get_shipping_info($shipping_items);
        [$result['fees'], $totalFee, $taxFee] = self::get_fees_info($fee);
        [$result['coupons'], $totalCoupon] = self::get_coupons_info($coupon_items);

        $taxTotal = Zippy_Wc_Calculate_Helper::round_price_wc($taxTotalOrder + $taxShipping + $taxFee);
        $totalCalculated = Zippy_Wc_Calculate_Helper::round_price_wc(
            ($subtotalOrder + $totalShipping + $totalFee - $totalCoupon)
        );

        $result['order_info'] = [
            'subtotal'   => $subtotalOrder,
            'tax_total'  => $taxTotal,
            'total'      => $totalCalculated,
        ];

        return ['data' => $result];
    }

    private static function get_products_info($items)
    {
        $products = [];
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($items as $item_id => $item) {
            $product = $item->get_product();
            $price_total = Zippy_Wc_Calculate_Helper::round_price_wc($item->get_subtotal());
            $tax_total = $item->get_subtotal_tax();

            $products[$item_id] = [
                'product_id'        => $product ? $product->get_id() : 0,
                'name'              => $product ? $product->get_name() : '',
                'img_url'           => $product ? wp_get_attachment_url($product->get_image_id()) : '',
                'sku'               => $product ? $product->get_sku() : '',
                'quantity'          => $item->get_quantity(),
                'price_total'       => $price_total,
                'tax_total'         => $tax_total,
                'price_per_item'    => Zippy_Wc_Calculate_Helper::round_price_wc($item->get_subtotal() / max(1, $item->get_quantity())),
                'tax_per_item'      => Zippy_Wc_Calculate_Helper::round_price_wc($item->get_subtotal_tax() / max(1, $item->get_quantity())),
                'min_order'         => get_post_meta($product->get_id(), '_custom_minimum_order_qty', true) ?: 0,
            ];

            $subtotal += ($price_total + Zippy_Wc_Calculate_Helper::round_price_wc($tax_total));
        }

        $taxSubtotal = Zippy_Wc_Calculate_Helper::get_tax($subtotal);
        $subtotal = Zippy_Wc_Calculate_Helper::round_price_wc($subtotal);

        return [$products, $subtotal, $taxSubtotal];
    }

    private static function get_shipping_info($shipping_items)
    {
        $shipping = [];
        $total = 0;
        $tax = 0;

        foreach ($shipping_items as $ship_id => $item) {
            $amount = floatval($item->get_total());
            $taxItem = Zippy_Wc_Calculate_Helper::get_tax_by_price_exclude_tax($amount);

            $amount += $taxItem;
            $shipping[] = [
                'method'       => $item->get_name(),
                'total'        => $amount,
                'tax_shipping' => $taxItem,
            ];

            $total += $amount;
            $tax += $taxItem;
        }

        return [$shipping, $total, $tax];
    }

    private static function get_fees_info($fee_items)
    {
        $fees = [];
        $total = 0;
        $tax = 0;

        foreach ($fee_items as $fee_id => $item) {
            $amount = floatval($item->get_total());
            $taxItem = Zippy_Wc_Calculate_Helper::get_tax_by_price_exclude_tax($amount);

            $amount += $taxItem;
            $fees[] = [
                'name'     => $item->get_name(),
                'total'    => $amount,
                'tax_fee'  => $taxItem,
            ];

            $total += $amount;
            $tax += $taxItem;
        }

        return [$fees, $total, $tax];
    }

    private static function get_coupons_info($coupon_items)
    {
        $coupons = [];
        $total = 0;

        foreach ($coupon_items as $coupon_id => $item) {
            $amount = floatval($item->get_discount());
            $coupons[] = ['total' => $amount];
            $total += $amount;
        }

        return [$coupons, $total];
    }

    /**
     * Remove item in order
     * @param array $infos
     * @return bool
     */
    public static function remove_order_item(array $infos): bool
    {
        $order_id = $infos['order_id'];
        $item_id = $infos['item_id'];

        if (empty($order_id) || empty($item_id)) {
            return false;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        $deleted = wc_delete_order_item($item_id);

        if (!$deleted) {
            return false;
        }

        $order->calculate_totals();
        return true;
    }

    public static function update_quantity_order_item(array $infos)
    {
        $order_id = $infos['order_id'];
        $item_id = $infos['item_id'];
        $quantity = $infos['quantity'];

        if (empty($order_id) || empty($item_id) || !is_numeric($quantity)) {
            return [];
        }

        $order = wc_get_order($order_id);
        if (empty($order)) {
            return [];
        }

        $item = $order->get_item($item_id);
        if (empty($item)) {
            return [];
        }

        $product = $item->get_product();
        if (empty($product)) {
            return [];
        }

        $product_price = $product->get_price();
        if (empty($product_price)) {
            return [];
        }

        // Set quantity
        $item->set_quantity($quantity, true);

        // Update tax
        self::set_order_item_totals_with_wc_tax($item, $product_price, $quantity);
        $order->calculate_totals();
        return [
            'order_id' => $order_id,
            'item_id' => $item_id,
            'quantity' => $quantity
        ];
    }

    public static function set_order_item_totals_with_wc_tax($item, $price_incl_tax, $quantity = 1)
    {
        if (get_option('woocommerce_prices_include_tax') !== 'yes') {
            $item->set_total($price_incl_tax * $quantity);
            $item->calculate_taxes();
            $item->save();
        }

        if (! $item instanceof WC_Order_Item_Product) {
            return false;
        }

        $product = $item->get_product();
        if (! $product) {
            return false;
        }

        // Get tax rates for this product
        $tax_rates = WC_Tax::get_rates($product->get_tax_class());

        if (empty($tax_rates)) {
            // No tax: treat as tax-free
            $subtotal = $price_incl_tax * $quantity;
            $item->set_subtotal($subtotal);
            $item->set_total($subtotal);
            $item->set_taxes(['total' => [], 'subtotal' => []]);
            $item->save();
            return [
                'subtotal_excl_tax' => $subtotal,
                'total_tax'         => 0,
                'total_incl_tax'    => $subtotal,
            ];
        }

        // Calculate inclusive tax breakdown
        $line_price = $price_incl_tax * $quantity;
        $taxes      = WC_Tax::calc_inclusive_tax($line_price, $tax_rates);

        $total_tax        = array_sum($taxes);
        $subtotal_excl_tax = $line_price - $total_tax;

        // Update item totals
        $item->set_subtotal($subtotal_excl_tax);
        $item->set_total($subtotal_excl_tax);
        $item->set_taxes([
            'total'    => $taxes,
            'subtotal' => $taxes,
        ]);

        $item->save();

        return [
            'subtotal_excl_tax' => $subtotal_excl_tax,
            'total_tax'         => $total_tax,
            'total_incl_tax'    => $line_price,
        ];
    }

    public static function apply_coupon_to_order(array $infos)
    {
        $order_id = $paramsInfo['order_id'] ?? null;
        $coupon_code = $paramsInfo['coupon_code'] ?? null;

        if (empty($order_id) || empty($coupon_code)) {
            return false;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        $coupon = new WC_Coupon($coupon_code);
        if (!$coupon || !$coupon->get_id()) {
            return false;
        }

        foreach ($order->get_items('coupon') as $item) {
            if ($item->get_code() === $coupon_code) {
                return false;
            }
        }

        $item_id = $order->apply_coupon($coupon_code);
        if (is_wp_error($item_id)) {
            return false;
        }

        $order = wc_get_order($order_id);
        $order->calculate_totals();
        return true;
    }

    public static function add_product_to_order(array $infos): array
    {
        $order_id = $infos['order_id'];
        $order    = wc_get_order($order_id);
        if (!$order) {
            return [];
        }

        $user_id = $order->get_user_id() ?? 0;
        $products = $infos['products'] ?? [];
        if (empty($products) || !is_array($products)) {
            return [];
        }

        $added_items = [];
        foreach ($products as $product) {
            $added_item = self::add_single_product_to_order($order, $product, $user_id);
            if (!empty($added_item)) {
                $added_items[] = $added_item;
            }
        }

        return [
            'order_id' => $order_id,
            'added_items' => $added_items,
        ];
    }

    private static function add_single_product_to_order($order, $productData, $user_id)
    {
        $product_id = intval($productData['parent_product_id'] ?? 0);
        $product    = wc_get_product($product_id);

        if (!$product) {
            return [];
        }

        return self::add_simple_product_to_order($order, $productData, $user_id);
    }

    private static function add_simple_product_to_order($order, $productData, $user_id)
    {
        $product_id = intval($productData['parent_product_id'] ?? 0);
        $quantity   = max(1, intval($productData['quantity'] ?? 1));

        $product = wc_get_product($product_id);
        $product_price = $product->get_price();
        if (empty($product_price)) {
            return [];
        }

        $item_id = $order->add_product($product, $quantity);
        if (is_wp_error($item_id)) {
            return [];
        }

        $item = $order->get_item($item_id);

        Order_Detail_Services::set_order_item_totals_with_wc_tax($item, $product_price, $quantity);
        $order->calculate_totals();
        return [
            'product_id' => $product_id,
            'quantity'   => $quantity,
            'item_id'    => $item_id,
        ];
    }

    public static function get_order_detail_by_id($order_id): array
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            return [];
        }

        $data = Order_Services::parse_order_data($order);
        return $data;
    }

    public static function download_invoice($order_id)
    {
        $format = 'pdf';

        if (empty($order_id)) {
            return false;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        $content = self::generate_invoice_pdf($order);
        $file_base64 = base64_encode($content);
        $file_name = 'orders_export_' . date('Y-m-d_H-i-s') . '.' . $format;

        return [
            'file_base64' => $file_base64,
            'file_name'   => $file_name,
            'file_type'   => $format,
        ];
    }

    private static function generate_invoice_pdf($order = null)
    {
        //Billing
        $billing  = $order->get_address('billing');
        $billing_parts = array_filter([
            $billing['company'] ?? '',
            $billing['address_1'] ?? '',
            $billing['address_2'] ?? '',
            $billing['city'] ?? '',
            $billing['state'] ?? '',
            $billing['postcode'] ?? '',
            $billing['country'] ?? '',
        ]);
        $billing_to = implode(', ', $billing_parts);

        //Date payment
        $payment_due = $order->get_date_paid();
        if ($payment_due) {
            $payment_due = $payment_due->date_i18n('F j, Y');
        } else {
            $payment_due = 'N/A';
        }

        //Total
        $total_amounts = $order->get_total();

        //Items
        $items = $order->get_items();
        $shipping_items = $order->get_items('shipping');
        $fee = $order->get_items('fee');
        $coupon_items = $order->get_items('coupon');
        $result = [];

        [$result['products'], $subtotalOrder, $taxTotalOrder] = self::get_products_info($items);
        [$result['shipping'], $totalShipping, $taxShipping] = self::get_shipping_info($shipping_items);
        [$result['fees'], $totalFee, $taxFee] = self::get_fees_info($fee);
        [$result['coupons'], $totalCoupon] = self::get_coupons_info($coupon_items);

        //Calculate totals
        $taxTotal = Zippy_Wc_Calculate_Helper::round_price_wc($taxTotalOrder + $taxShipping + $taxFee);
        $totalCalculated = Zippy_Wc_Calculate_Helper::round_price_wc(
            ($subtotalOrder + $totalShipping + $totalFee - $totalCoupon)
        );

        $options_custom_value = get_option(Core_Settings::OPTIONS_KEY_ORDER_INVOICES_CONFIGS, []);

        $data = [
            'store_logo' => $options_custom_value['invoice-logo']['value'] ?? '',
            'store_name' => $options_custom_value['store-name']['value'] ?? '',
            'gst_reg' => $order->get_id(),
            'bill_to' => $billing_to,
            'invoice_number' => $order->get_id(),
            'invoice_date' => date('F j, Y'),
            'payment_due' => $payment_due,
            'amount_due' => $total_amounts,
            'items' => $result['products'],
            'subtotal' => $subtotalOrder,
            'gst' => $taxTotal,
            'total' => $totalCalculated,
        ];
        $header_options = self::get_options_by_position($options_custom_value, 'header');
        $footer_options = self::get_options_by_position($options_custom_value, 'footer');

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        ob_start();
        include ZIPPY_CORE_DIR_PATH . 'src/modules/orders/templates/invoice-template.php';
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private static function get_options_by_position(array $options_custom_value, string $position): array
    {
        $options = [];
        foreach ($options_custom_value as $key => $option) {
            if ($option['position'] === $position) {
                $options[$key] = $option['value'];
            }
        }
        return $options;
    }
}
