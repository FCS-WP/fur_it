<?php

/**
 * Analytics Dashboard
 *
 * @package Shin
 */

namespace Zippy_Core\Src\Analytics;

defined('ABSPATH') or die();

use WP_REST_Response;
use WP_REST_Request;
use Zippy_Core\Src\Api\Zippy_Core_Api;

use Zippy_Core\Utils\Zippy_Utils_Core;

class Zippy_Analytics
{

  protected static $_instance = null;

  /**
   * @return Zippy_Analytics
   */

  public static function get_instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct()
  {
    if (!Zippy_Utils_Core::check_exits_woocommerce()) return;

    add_action('rest_api_init', array($this, 'zippy_init_api'));

    add_action('admin_enqueue_scripts', array($this, 'analytics_assets'));

    add_action('admin_menu',  array($this, 'zippy_dashboard'));
  }

  /**
   *
   * Assests Resource
   */


  public function analytics_assets()
  {
    // $version = time();
    // $current_user_id = get_current_user_id();

    // // Pass the user ID to the script
    // wp_enqueue_script('admin_script', ZIPPY_CORE_URL . '/assets/dist/js/admin.min.js', [], $version, true);
    // wp_enqueue_style('zippy-css', ZIPPY_CORE_URL . '/assets/dist/css/admin.min.css', [], $version);

    // wp_localize_script('admin_script', 'admin_id', array(
    //   'userID' => $current_user_id,
    // ));
  }


  public function analytics_menu($report_pages)
  {
    $shin['woocommerce-analytics-dashboard'] = array(
      'id' => 'woocommerce-analytics-dashboard',
      'title' => __('Dashboard2', 'woocommerce-admin'),
      'parent' => 'woocommerce-analytics',
      'path' => '/analytics/dashboard-shin',
    );
    array_splice($report_pages, 2, 0, $shin);
    return $report_pages;
  }

  public function zippy_dashboard($reports)
  {
    $is_authenticated =  get_option('_zippy_woocommerce_key');

    if (!isset($is_authenticated) || empty($is_authenticated['consumer_key'])) return;

    add_submenu_page('woocommerce', 'Dashboard', 'Dashboard', 'manage_options', 'admin.php?page=wc-zippy-dashboard', array($this, 'render'), 1);
  }

  public function zippy_init_api()
  {
    register_rest_route('zippy-core/v1', '/credentials', array(
      'methods' => 'POST',
      'callback' => array($this, 'check_api_credentials'),
      'permission_callback' => function () {
        return true;
      }
    ));
    register_rest_route('zippy-core/v1', '/call', array(
      'methods' => 'GET',
      'callback' => array($this, 'call_api_to_woocommerce'),
      'permission_callback' => function () {
        return true;
      }
    ));
  }

  public function check_api_credentials(WP_REST_Request $request)
  {
    // check Authentication;
    $data = $request->get_json_params();

    if (!isset($data)) return new WP_REST_Response($data, 400);

    $consumer_key = Zippy_Utils_Core::encrypt_data_input($data['consumer_key']);

    $consumer_secret = Zippy_Utils_Core::encrypt_data_input($data['consumer_secret']);

    $keys = array(
      'consumer_key' => $consumer_key,
      'consumer_secret' => $consumer_secret,

    );

    update_option('_zippy_woocommerce_key', $keys);

    $data = array(
      'status' => 'success',
      'message' => 'Autheticated',
    );

    return new WP_REST_Response($data, 200);
  }

  public function call_api_to_woocommerce(WP_REST_Request $request)
  {
    $api_request =   $request->get_params();
    $endpoint = $api_request['endpoint'];
    unset($api_request['endpoint']);
    $api = new Zippy_Core_Api();
    $response = $api->create_request($endpoint, $api_request);

    return new WP_REST_Response($response, 200);
  }

  public function render()
  {
    echo  '<link as="style" rel="stylesheet preload prefetch"  href="/wp-content/plugins/woocommerce/assets/client/admin/app/style.css?ver=7.9.0" as="style" />';


    echo '<div id="zippy-root">';
    echo '<div id="zippy-main"></div>';

    // if (!isset($is_authenticated) || empty($is_authenticated['consumer_key'])) {
    //   echo '<div id="zippy-authentication"></div>';
    // } else {
    // }

    echo '</div>';
  }
}
