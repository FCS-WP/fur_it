<?php


namespace Zippy_Core\Orders\Models;

class Order_Arguments
{
    public static function get_orders_args()
    {
        return [
            'page' => [
                'default'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => fn($value) => $value > 0,
            ],
            'per_page' => [
                'default'           => 10,
                'sanitize_callback' => 'absint',
                'validate_callback' => fn($value) => $value > 0 && $value <= 100,
            ],
            'orderby' => [
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'orderval' => [
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'order_status' => [
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'date_from' => [
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'date_to' => [
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }

    public static function get_bulk_action_update_order_status_args()
    {
        return [
            'order_ids' => [
                'required' => true,
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
            ],
            'status' => [
                'required' => true,
                'type' => 'string',
            ],
        ];
    }

    public static function get_move_to_trash_args()
    {
        return [
            'order_ids' => [
                'required' => true,
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                ],
            ],
        ];
    }

    public static function get_order_info_args()
    {
        return [
            'order_id' => [
                'required' => true,
                'type' => 'integer',
            ],
        ];
    }

    public static function get_remove_item_order_args()
    {
        return [
            'order_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'item_id' => [
                'required' => true,
                'type' => 'integer',
            ],
        ];
    }

    public static function get_update_quantity_order_item_args()
    {
        return [
            'order_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'item_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'quantity' => [
                'required' => true,
                'type' => 'integer',
            ],
        ];
    }

    public static function get_apply_coupon_to_order_args()
    {
        return [
            'order_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'coupon_code' => [
                'required' => true,
                'type' => 'string',
            ],
        ];
    }

    public static function get_add_items_order_args()
    {
        return [
            'order_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'products' => [
                'required' => true,
                'type' => 'array',
            ],
        ];
    }

    public static function get_export_orders_args()
    {
        return [
            'date_from' => [
                'required' => false,
                'type' => 'string',
            ],
            'date_to' => [
                'required' => false,
                'type' => 'string',
            ],
            'format' => [
                'required' => false,
                'type' => 'string',
            ],
        ];
    }

    public static function get_order_details_args()
    {
        return [
            'order_id' => [
                'required' => true,
                'type' => 'integer',
            ],
        ];
    }

    public static function get_download_invoice_args()
    {
        return [
            'order_id' => [
                'required' => true,
                'type' => 'integer',
            ],
        ];
    }

    public static function send_order_email_args()
    {
        return [
            'order_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'email_type' => [
                'required' => false,
                'type' => 'string',
                'default' => 'customer_invoice',
                'enum' => [
                    'new_order',
                    'cancelled_order',
                    'customer_processing_order',
                    'customer_completed_order',
                    'customer_invoice',
                    'customer_refunded_order',
                    'customer_note',
                ],
            ],
        ];
    }
}
