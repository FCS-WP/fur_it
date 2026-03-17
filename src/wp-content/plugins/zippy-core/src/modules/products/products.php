<?php

namespace Zippy_Core;

use Zippy_Core\Core_Module;
use Zippy_Core\Orders\Routes\Order_Detail_Route;
use Zippy_Core\Orders\Routes\Order_Route;
use Zippy_Core\Products\Routes\Product_Route;

class Core_Products extends Core_Module
{
    public function load_required_files()
    {
        $paths = [
            __DIR__ . '/controllers',
            __DIR__ . '/routes',
            __DIR__ . '/services',
            __DIR__ . '/models',
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
        Product_Route::get_instance();
    }
}
