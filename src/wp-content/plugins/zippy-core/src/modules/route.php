<?php

namespace Zippy_Core;

abstract class Core_Route {
    protected static $_instances = [];

    /**
     * Get (or create) the singleton instance for each subclass.
     */
    public static function get_instance() {
        $called_class = static::class;

        if ( ! isset( self::$_instances[ $called_class ] ) ) {
            self::$_instances[ $called_class ] = new static();
        }

        return self::$_instances[ $called_class ];
    }


    /**
     * Auto run & init function
     * @return void;
     */

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'init_module_api' ] );
    }

     /**
     * Each child class should define its own API routes here.
     *
     * @return void
     */
    abstract public function init_module_api();
}