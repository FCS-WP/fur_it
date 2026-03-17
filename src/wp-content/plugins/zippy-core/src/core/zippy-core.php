<?php

namespace Zippy_Core\Src\Core;

use Zippy_Core\Src\Core\Zippy_Admin;
use Zippy_Core\Src\Core\Zippy_Optimise;
use Zippy_Core\Src\Core\Zippy_Settings;
use Dotenv\Dotenv;


defined('ABSPATH') or die();

class Zippy_Core
{
  protected static $_instance = null;

  /**
   * @return Zippy_Core
   */

  public static function get_instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }

    // Load project_ENV
    $dotenv = Dotenv::createImmutable(ABSPATH);
    $dotenv->safeLoad();

    return self::$_instance;
  }

  public function __construct()
  {
    new Zippy_Settings;
    new Zippy_Admin;
    new Zippy_Optimise;

    add_action('phpmailer_init', array($this, 'setup_phpmailer_init'));

    add_filter('login_headerurl', array($this, 'custom_loginlogo_url'));

    add_action('login_enqueue_scripts', array($this, 'my_login_stylesheet'));

    add_filter('plugin_action_links', array($this, 'disable_plugin_deactivation'), 10, 4); //Prevent deactive

    add_filter('rest_authentication_errors',  array($this, 'remove_rest_api_users'));
  }

  public function setup_phpmailer_init($phpmailer)
  {
    $phpmailer->Host = 'smtp.gmail.com';
    $phpmailer->Port = 587;
    $phpmailer->Username = 'dev@zippy.sg';
    $phpmailer->Password = 'cqoyfqhbywzguowa';
    $phpmailer->SMTPAuth = true;
    $phpmailer->SMTPSecure = 'tls';
    $phpmailer->IsSMTP();
  }

  function disable_plugin_deactivation($actions, $plugin_file, $plugin_data, $context)
  {
    if ($plugin_file == 'zippy-core/zippy-core.php') {
      unset($actions['deactivate']);
    }

    return $actions;
  }

  public function custom_loginlogo_url($url)
  {
    return '/';
  }

  public  function my_login_stylesheet()
  {
    $style = '<style type="text/css">body.login div#login h1 a{background-image:url("' . ZIPPY_CORE_URL . 'assets/images/logo-zippy.png");width:100%;background-size:100%}#login form#loginform .input,#login form#registerform .input,#login form#lostpasswordform .input{border-width:0px;border-radius:0px;border-bottom:1px solid #d2d2d2;-webkit-box-shadow:unset;box-shadow:unset}#login form .submit .button{background-color:#40A944;width:100%;height:40px;border-width:0px;margin-top:10px}.login .button.wp-hide-pw{color:#40A944}

  </style>';

    echo $style;
  }

  function remove_rest_api_users($rest_endpoints)
  {

    if (isset($rest_endpoints['/wp/v2/users'])) {
      unset($rest_endpoints['/wp/v2/users']);
    }

    if (isset($rest_endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
      unset($rest_endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }

    return $rest_endpoints;
  }
}
