<?php

/**
 * Zippy WooCommerce Calculate Helper
 *
 * @package Shin
 */

namespace Zippy_Core\Utils;

use WC_Tax;

defined('ABSPATH') or die();

class Zippy_Wc_Calculate_Helper
{
    public static function round_price_wc($price)
    {
        return wc_format_decimal($price, wc_get_price_decimals());
    }

    /**
     * Get tax from price include tax
     * @param mixed $priceIncludeTax
     * @return string
     */
    public static function get_tax($priceIncludeTax)
    {
        $tax = self::get_tax_percent();

        if (empty($tax)) {
            return self::round_price_wc(0);
        }

        $tax_rate = floatval($tax->tax_rate);
        $tax_price = $priceIncludeTax - $priceIncludeTax / (1 + $tax_rate / 100);
        return self::round_price_wc($tax_price);
    }

    /**
     * Get tax from price exclude tax
     * @param mixed $price
     * @return string
     */
    public static function get_tax_by_price_exclude_tax($priceExcludeTax)
    {
        $total_price_including_tax = self::get_total_price_including_tax($priceExcludeTax);
        return self::get_tax($total_price_including_tax);
    }


    /**
     * Get total price including tax from price exclude tax
     * @param mixed $priceExcludeTax
     * @return string
     */
    public static function get_total_price_including_tax($priceExcludeTax): string
    {
        $tax = self::get_tax_percent();

        if (empty($tax)) {
            return self::round_price_wc($priceExcludeTax);
        }

        $tax_rate  = floatval($tax->tax_rate);
        return self::round_price_wc($priceExcludeTax * (1 + $tax_rate / 100));
    }

    public static function get_total_price_exclude_tax($priceIncludeTax): string
    {
        $tax       = self::get_tax($priceIncludeTax);
        $cost_excl_tax = $priceIncludeTax - $tax;
        return self::round_price_wc($cost_excl_tax);
    }

    public static function get_tax_percent()
    {
        $all_tax_rates = [];
        $tax_classes = WC_Tax::get_tax_classes();
        if (!in_array('', $tax_classes)) {
            array_unshift($tax_classes, '');
        }

        foreach ($tax_classes as $tax_class) {
            $taxes = WC_Tax::get_rates_for_tax_class($tax_class);
            $all_tax_rates = array_merge($all_tax_rates, $taxes);
        }

        if (empty($all_tax_rates)) return null;
        return $all_tax_rates[0];
    }
}
