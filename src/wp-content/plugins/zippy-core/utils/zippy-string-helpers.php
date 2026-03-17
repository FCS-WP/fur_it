<?php

/**
 * Zippy WooCommerce Calculate Helper
 *
 * @package Shin
 */

namespace Zippy_Core\Utils;

defined('ABSPATH') or die();

class Zippy_String_Helpers
{
    /**
     * Convert from slug to string
     * Example: 'phone_number' or 'phone-number' => 'Phone Number'
     * @param mixed $slug
     * @return string
     */
    public static function convert_slug_to_name($slug)
    {
        $words = preg_split('/[-_]+/', $slug);
        $capitalizedWords = array_map('ucfirst', $words);
        return implode(' ', $capitalizedWords);
    }
}
