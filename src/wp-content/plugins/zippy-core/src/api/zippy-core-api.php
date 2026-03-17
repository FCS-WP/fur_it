<?php

/**
 * Admin Setting
 *
 * @package Shin
 */

namespace Zippy_Core\Src\Api;

defined('ABSPATH') or die();

use WC_Admin_Settings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Exception\RequestException;


use Zippy_Core\Utils\Zippy_Utils_Core;

class Zippy_Core_Api
{
  private $client;
  private $consumer_key;
  private $consumer_secret;

  public function __construct()
  {

    $zippy_woocommerce_key =  WC_Admin_Settings::get_option('_zippy_woocommerce_key');

    $this->consumer_key =  $zippy_woocommerce_key['consumer_key'];
    $this->consumer_secret = $zippy_woocommerce_key['consumer_secret'];

    $handler = new \GuzzleHttp\Handler\CurlHandler();
    $stack = HandlerStack::create($handler);

    $middleware = new Oauth1([
      'consumer_key'    => Zippy_Utils_Core::decrypt_data_input($this->consumer_key),
      'consumer_secret' => Zippy_Utils_Core::decrypt_data_input($this->consumer_secret),
      'token_secret'    => '',
      'token'           => '',
      'request_method' => Oauth1::REQUEST_METHOD_QUERY,
      'signature_method' => Oauth1::SIGNATURE_METHOD_HMAC
    ]);

    $stack->push($middleware);
    $this->client = new Client([
      'base_uri' => $this->get_origin_domain(),
      'handler' => $stack,
      'headers' => [
        'Content-Type' => 'application/json',
      ],
      'timeout'  => 10,
    ]);
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

  /**
   * Create request
   */

  public function create_request($enpoint, $params)
  {
    $body_params = json_encode($params);
    try {
      $response = $this->client->getAsync(
        $enpoint,
        [
          'auth' => 'oauth',
          'body' => $body_params
        ]

      )->then(function ($response) {
        $statusCode = $response->getStatusCode();

        if ($statusCode == 200) {
          $response = json_decode($response->getBody());
        } else {
          $response = $statusCode;
        }
        return $response;
      })->wait();
    } catch (ConnectException $e) {
      $response = false;
    } catch (RequestException $e) {
      $response = $e->getResponse()->getStatusCode();
    }

    return $response;
  }
}
