<?php

namespace Zippy_Core;

defined('ABSPATH') || exit;

abstract class Core_Module
{
    protected $module_key;

    public function __construct()
    {

        if (! $this->is_module_active()) {
            return;
        }

        add_action('plugins_loaded', [$this, 'load_required_files']);
        add_action('plugins_loaded', [$this, 'init_module']);
    }

    abstract public function load_required_files();
    abstract public function init_module();

    /**
     * Check module is actigve
     */
    protected function is_module_active()
    {
        // Default load
        if (empty($this->module_key)) {
            return true;
        }

        $configs = get_option('core_module_configs', []);

        if (! is_array($configs)) {
            $configs = [];
        }

        $status = isset($configs[$this->module_key]) ? $configs[$this->module_key] : 'yes';
        return $status === 'yes';
    }
}
