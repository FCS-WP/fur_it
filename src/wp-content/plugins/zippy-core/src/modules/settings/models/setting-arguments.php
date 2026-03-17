<?php

namespace Zippy_Core\Settings\Models;

class Setting_Arguments
{
    public static function get_setting_args()
    {
        return [];
    }

    public static function update_modules_settings_args()
    {
        return [
            'new_values' => [
                'required' => true,
                'type' => 'array',
                'items' => [
                    'type'       => 'object',
                    'properties' => [
                        'key' => [
                            'type'        => 'string',
                            'required'    => true,
                            'description' => 'Module key name.',
                        ],
                        'value' => [
                            'type'        => 'string',
                            'required'    => true,
                            'description' => 'Module value (e.g. yes/no).',
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function update_invoices_options_args()
    {
        return [
            'new_invoices_options' => [
                'required' => true,
                'type' => 'array',
                'items' => [
                    'type'       => 'object',
                    'properties' => [
                        'key' => [
                            'type'        => 'string',
                            'required'    => true,
                            'description' => 'key name.',
                        ],
                        'data' => [
                            'type'        => 'object',
                            'required'    => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function update_order_detail_options_args()
    {
        return [
            'new_values' => [
                'required' => true,
                'type' => 'array',
                'items' => [
                    'type'       => 'object',
                    'properties' => [
                        'key' => [
                            'type'        => 'string',
                            'required'    => true,
                        ],
                        'value' => [
                            'type'        => 'string',
                            'required'    => true,
                            'description' => 'value (e.g. yes/no).',
                        ],
                    ],
                ],
            ],
        ];
    }
}
