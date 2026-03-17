<?php

namespace Zippy_Core;

defined('ABSPATH') || exit;

/**
 * Class Core_Autoload_Module
 * 
 * Auto load module in src/modules/
 * Each module has the structure:
 *   src/modules/{module_name}/{module-name}.php
 * 
 * Example:
 *   src/modules/postal_code/postal-code.php => class Core_Postal_Code
 *   src/modules/orders/orders.php => class Core_Orders
 */
class Core_Autoload_Module
{
    public static function init()
    {
        $modules_path = ZIPPY_CORE_DIR_PATH . 'src/modules/';

        if (!is_dir($modules_path)) {
            return;
        }

        $module_dirs = glob($modules_path . '*', GLOB_ONLYDIR);

        foreach ($module_dirs as $dir) {
            $module_name = basename($dir);
            $main_file = "{$dir}/" . str_replace('_', '-', $module_name) . ".php";

            if (!file_exists($main_file)) {
                continue;
            }

            require_once $main_file;

            $class_name = 'Zippy_Core\\Core_' . str_replace(
                ' ',
                '_',
                ucwords(str_replace(['-', '_'], ' ', $module_name))
            );

            if (class_exists($class_name)) {
                new $class_name();
            }
        }
    }
}
