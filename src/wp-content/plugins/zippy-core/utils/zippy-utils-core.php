<?php

/**
 * Admin Setting
 *
 * @package Shin
 */

namespace Zippy_Core\Utils;

defined('ABSPATH') or die();

class Zippy_Utils_Core
{
  public static function check_exits_woocommerce()
  {
    if (!function_exists('is_plugin_active')) {

      include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    if (!is_plugin_active('woocommerce/woocommerce.php')) return false;

    return true;
  }

  public static function check_is_active_feature($key)
  {
    $is_active = '';

    $is_active = get_option($key);

    if (empty($is_active) || $is_active == 0) return false;

    return true;
  }
  public static function encrypt_data_input($input)
  {
    $encryption_key = ZIPPY_CORE_PREFIX;
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $input_data = openssl_encrypt($input, 'aes-256-cbc', $encryption_key, 0, $iv);
    $input_data_with_iv = base64_encode($iv . '::' . $input_data);
    return $input_data_with_iv;
  }

  public static function decrypt_data_input($data_encryption)
  {

    if (!isset($data_encryption) || empty($data_encryption)) return false;

    $encryption_key = ZIPPY_CORE_PREFIX;

    list($iv, $data) = explode('::', base64_decode($data_encryption), 2);

    $data_descypt = openssl_decrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);

    return $data_descypt;
  }

  /**
   * Recursive sanitation for an array
   *
   * @param $array
   *
   * @return mixed
   */
  public static function recursive_sanitize_text_field($array)
  {
    foreach ($array as $key => &$value) {
      if (is_array($value)) {
        $value = self::recursive_sanitize_text_field($value);
      } else {
        $value = sanitize_text_field($value);
      }
    }

    return $array;
  }

  public static function separator()
  {
    return [
      'title'       => '',
      'type'        => 'title',
      'description' => '<hr>'
    ];
  }

  public static function divider()
  {
    return array(
      'id'          => ZIPPY_CORE_PREFIX . '_divider',
      'name'       => __('', ZIPPY_CORE_PREFIX . 'woocommerce-settings-tab'),
      'type'        => 'title',
      'desc' => '<hr>'
    );
  }

  /**
   * Gets the client IP
   *
   * @return string
   */
  public static function get_client_ip()
  {

    $ip = $_SERVER['REMOTE_ADDR'];

    if (empty($ip)) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    }

    if (empty($ip)) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    return $ip; //'192.168.0.1'
  }

  /**
   * Get the Locale
   *
   * @return string
   */

  public static function get_locale()
  {
    return str_replace(['_informal', '_formal'], '', get_locale());
  }
  /**
   * Get the userAgent
   *
   * @return string
   */

  public static function get_user_agent()
  {
    return $_SERVER['HTTP_USER_AGENT'];
  }

  /**
   * Get the userAgent
   *
   * @return string
   */

  public static function get_http_accept()
  {
    return $_SERVER['HTTP_ACCEPT'];
  }

  /**
   * Get the userAgent
   *
   * @return string
   */

  public static function get_reference($order_id)
  {
    return $_SERVER['HTTP_HOST'] . '_' . $order_id;
  }

  public static function get_domain_name()
  {
    $original_url = "https://" . $_SERVER['SERVER_NAME'];
    $pieces = parse_url($original_url);
    $domain = isset($pieces['host']) ? $pieces['host'] : '';
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain,    $regs)) {
      $domain = strstr($regs['domain'], '.', true);
    }
    return $domain;
  }

  /**
   * Retrieves the shop domain used for generating origin keys.
   *
   * @return string
   */
  public static function get_origin_domain()
  {

    $incl_port = get_option('incl_server_port', 'yes');
    $protocol  = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'https://';
    $port      = in_array($_SERVER['SERVER_PORT'], ['80', '443']) ? '' : ':' . $_SERVER['SERVER_PORT'];
    $domain    = 'yes' === $incl_port ? $protocol . $_SERVER['HTTP_HOST'] . $port : $protocol . $_SERVER['HTTP_HOST'];

    return $domain;
  }

  public static function get_merchant_reference($order_key)
  {

    return md5($order_key . '_' . self::get_origin_domain());
  }

  /**
   * Gets content of a given path file.
   *
   * @param string $template_name - the end part of the file path including the template name
   * @param array $vars - arguments passed to the template
   * @param string $absolute_path - plugin's DIR_PATH
   * @param mixed $relative_path - relative path added between the absolute path and the template name
   * @return string $content
   */
  public static function get_template($template_name, $vars = array(), $absolute_path = '', $relative_path = '')
  {

    extract($vars);

    $content = '';

    $template_name = empty($absolute_path) && empty($relative_path) ? $template_name : trim($template_name, "/\\");
    $absolute_path = empty($absolute_path) ? '' : trailingslashit($absolute_path);
    $relative_path = empty($relative_path) ? '' : trailingslashit(trim($relative_path, "/\\"));

    $template = $absolute_path . $relative_path . $template_name;

    //check for template in plugin's folder `includes/`
    if (file_exists(ZIPPY_CORE_DIR_PATH . $relative_path . $template_name)) {
      $template = ZIPPY_CORE_DIR_PATH . $relative_path . $template_name;
    }

    $template = apply_filters_deprecated(ZIPPY_CORE_PREFIX . '\util\get_template\path_file', [$template, $vars], '1.0.0', ZIPPY_CORE_PREFIX . '\util\get_template\template');
    $template = apply_filters(ZIPPY_CORE_PREFIX . '\util\get_template\template', $template, $template_name, $absolute_path, $relative_path);

    if (file_exists($template)) {

      ob_start();

      include $template;

      $content = ob_get_clean();
    }

    return $content;
  }
}
