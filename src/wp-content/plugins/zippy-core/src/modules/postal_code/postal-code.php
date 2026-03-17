<?php

namespace Zippy_Core;

use Zippy_Core\Core_Module;
use Zippy_Core\Utils\Zippy_Utils_Core;


class Core_Postal_Code extends Core_Module
{
    public function __construct()
    {
        if (!Zippy_Utils_Core::check_exits_woocommerce()) return;
        if (!Zippy_Utils_Core::check_is_active_feature('_zippy_postal_code') || is_admin()) return;
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
        add_filter('woocommerce_checkout_fields', array($this, 'ehancement_checkout_fields'), 10, 1);
        add_action('wp_footer', array($this, 'postcode_script'), 10, 1);
        add_filter('woocommerce_default_address_fields', array($this, 'modify_default_fields'));
        add_filter('woocommerce_billing_fields', array($this, 'modify_billing_fields'));
        add_filter('woocommerce_shipping_fields', array($this, 'modify_shipping_fields'), 1);
        add_filter('woocommerce_form_field_button', array($this, 'create_button_form_field_type'), 10, 4);
    }

    public function zippy_check_shipping_method()
    {
        global $woocommerce;

        $shipping_method = 'local_pickup:2';

        $chosen_methods = $woocommerce->session->get('chosen_shipping_methods');

        $chosen_shipping = $chosen_methods[0];

        if ($chosen_shipping == $shipping_method) {
            return false;
        }
        return true;
    }

    public function modify_default_fields($fields)
    {
        if ($this->zippy_check_shipping_method()) {
            return $this->modify_fields($fields, '');
        }
        return $fields;
    }

    public function modify_billing_fields($fields)
    {
        if ($this->zippy_check_shipping_method()) {
            return $this->modify_fields($fields, 'billing');
        }
        return $fields;
    }

    public function modify_shipping_fields($fields)
    {
        if ($this->zippy_check_shipping_method()) {
            return $this->modify_fields($fields, 'shipping');
        }
        return $fields;
    }


    private function modify_fields($fields, $type = 'billing')
    {
        if (!empty($type)) {
            $type .= '_';
        }

        $country_priority = $fields[$type . 'country']['priority'];
        $fields[$type . 'postcode']['priority'] = $country_priority + 5;
        $fields[$type . 'postcode']['required'] = true;
        $fields[$type . 'company']['required'] = true;


        return $fields;
    }
    public function postcode_script()
    {

        if (!is_checkout()) return;

        echo Zippy_Utils_Core::get_template('scripts.php', [], dirname(__FILE__), '/templates');
    }

    function create_button_form_field_type($field, $key, $args, $value)
    {
        return sprintf(
            '<p class="form-row %s" id="%s_field" data-priority="%s">
        <button type="%s" id="%s" class="%s">%s</button>
    </p>',
            implode(' ', $args['class']),
            esc_attr($key),
            $args['priority'] ? $args['priority'] : '',
            $args['type'],
            esc_attr($key),
            $args['input-class'],
            $args['label']
        );
    }
    public function ehancement_checkout_fields($fields)
    {
        unset($fields['billing']['billing_city']);
        return $fields;
    }
}
