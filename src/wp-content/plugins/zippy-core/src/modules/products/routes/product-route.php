<?php

namespace Zippy_Core\Products\Routes;

use Zippy_Core\Core_Middleware;
use Zippy_Core\Core_Route;
use Zippy_Core\Products\Controllers\Product_Controllers;

class Product_Route extends Core_Route
{

    public function init_module_api()
    {
        register_rest_route(ZIPPY_CORE_API_PREFIX, '/products', [
            'methods'  => 'GET',
            'callback' => [Product_Controllers::class, 'get_products'],
            'permission_callback' => [Core_Middleware::class, 'admin_only'],
            'args' => [
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'default' => 10,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
        register_rest_route(ZIPPY_CORE_API_PREFIX, '/categories', array(
            'methods' => 'GET',
            'callback' => [Product_Controllers::class, 'get_categories'],
            'permission_callback' => [Core_Middleware::class, 'admin_only'],
            'args' => [
                'category' => [
                    'default' => 0,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ));
    }
}
