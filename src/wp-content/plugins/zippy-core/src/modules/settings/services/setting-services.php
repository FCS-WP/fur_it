<?php

namespace Zippy_Core\Settings\Services;

use Zippy_Core\Core_Settings;

class Setting_Services
{

    /**
     * init core configs
     */
    public static function init_modules_option()
    {
        $modules = [
            'orders'      => 'no',
            'products'    => 'no',
            'analytics'   => 'no',
            'postal_code' => 'no',
        ];

        $option_key = 'core_module_configs';
        $existing = get_option($option_key);

        if (! is_array($existing)) {
            add_option($option_key, $modules);
            $existing = $modules;
        }

        return $existing;
    }

    /**
     * Get configs
     */

    public static function get_saved_options($option_key)
    {
        if (!$option_key) return [];
        $configs = get_option($option_key, []);

        if (! is_array($configs)) {
            $configs = [];
        }
        $formatted = [];

        foreach ($configs as $key => $value) {
            $formatted[] = [
                'key'   => $key,
                'data' => $value,
            ];
        }

        return $formatted;
    }

    /**
     * update core config by key
     */

    public static function update_module_config_by_key($module_key, $status)
    {
        $option_key = 'core_module_configs';

        // Get current config
        $configs = get_option($option_key, []);

        // Normalize: if the option doesn’t exist, make it an array
        if (! is_array($configs)) {
            $configs = [];
        }

        // Accept both single or multiple module updates
        if (is_array($module_key)) {
            foreach ($module_key as $key => $value) {
                $configs[$key] = $value === 'yes' ? 'yes' : 'no';
            }
        } else {
            $configs[$module_key] = $status === 'yes' ? 'yes' : 'no';
        }

        // Save back to database
        update_option($option_key, $configs);

        return $configs;
    }

    public static function update_saved_options($option_key, $data)
    {
        $configs = get_option($option_key, []);

        // Normalize: if the option doesn’t exist, make it an array
        if (! is_array($configs)) {
            $configs = [];
        }

        foreach ($data as $new_item) {
            $configs[$new_item['key']] = $new_item['value'];
        }

        update_option($option_key, $configs);
        return $configs;
    }

    /**
     * Get core config by key
     */

    public static function get_core_config($key = null)
    {
        $option_key = Core_Settings::OPTIONS_KEY_CORE_MODULES;

        $configs = get_option($option_key, []);

        if (! is_array($configs)) {
            $configs = [];
        }

        if ($key === null) {
            return $configs;
        }

        return isset($configs[$key]) ? $configs[$key] : 'no';
    }

    public static function get_sub_configs($option_key, $key = null)
    {
        if (!$option_key) return 'no';
        $configs = get_option($option_key, []);

        if (! is_array($configs)) {
            $configs = [];
        }

        if ($key === null) {
            return $configs;
        }

        return isset($configs[$key]) ? $configs[$key] : 'no';
    }
}
