<?php

/**
 * Admin Setting
 *
 * @package Shin
 */

namespace Zippy_Core\Src\Core;

use WP_User;

defined('ABSPATH') or die();

class Zippy_Activate
{
    public static function activate()
    {

        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        self::check_active_required_plugins();
        self::create_default_dev_account();
    }

    private static function create_default_dev_account()
    {
        if (!isset($_ENV['SITE_MODE']) || empty($_ENV['SITE_MODE'])) return;

        if ($_ENV['SITE_MODE'] == 'local') {

            $username = $_ENV['DEV_USERNAME'];
            $password = $_ENV['DEV_PASSWORD'];
            $email    = $_ENV['DEV_EMAIL'];

            if (!username_exists($username) && !email_exists($email)) {
                $user_id = wp_create_user($username, $password, $email);
                $user = new WP_User($user_id);
                $user->set_role('administrator');
            }
        }
    }

    public static function check_active_required_plugins()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        if (!is_plugin_active('aryo-activity-log/aryo-activity-log.php')) {
            deactivate_plugins(plugin_basename(__FILE__));

            wp_die(
                '<p><strong>Zippy Core</strong> requires the <strong>Activity Log</strong> plugin to be activated first.</p>
             <p><a href="' . admin_url('plugins.php') . '">&larr; Go back to Plugins</a></p>'
            );
        }
    }
}
