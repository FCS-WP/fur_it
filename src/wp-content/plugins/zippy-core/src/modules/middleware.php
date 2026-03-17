<?php
namespace Zippy_Core;

use WP_Error;
use WP_REST_Request;

class Core_Middleware {

    /**
     * Default middleware for all modules.
     *
     * This ensures only logged-in users can access the API.
     * Modules can override this with their own logic if needed.
     *
     * @param WP_REST_Request $request
     * @return true|WP_Error
     */
    public static function default( WP_REST_Request $request ) {
        if ( ! is_user_logged_in() ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'Authentication required. Please log in.', 'zippy-core' ),
                [ 'status' => 401 ]
            );
        }

        return true;
    }

    /**
     * Check for admin or shop manager permission.
     */
    public static function admin_only( WP_REST_Request $request ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'You do not have permission to perform this action.', 'zippy-core' ),
                [ 'status' => 403 ]
            );
        }

        return true;
    }

    /**
     * Middleware chain support.
     *
     * @param array $middlewares Array of [class, method] callbacks
     * @param WP_REST_Request $request
     * @return true|WP_Error
     */
    public static function chain( array $middlewares, WP_REST_Request $request ) {
        foreach ( $middlewares as $middleware ) {
            $result = call_user_func( $middleware, $request );
            if ( is_wp_error( $result ) ) {
                return $result; // stop chain if any check fails
            }
        }
        return true;
    }
    
}