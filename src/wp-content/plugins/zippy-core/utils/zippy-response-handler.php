<?php

/**
 * API ResponHandler
 *
 * @package Shin
 */

namespace Zippy_Core\Utils;

defined('ABSPATH') or die();

use WP_REST_Response;

class Zippy_Response_Handler
{
  // Handle success responses
  public static function success($data, $message = 'Success', $status_code = 200)
  {
    $response = [
      'status' => 'success',
      'message' => $message,
    ];

    $response = array_merge($response, (array) $data);
    return new WP_REST_Response($response, $status_code);
  }

  // Handle error responses
  public static function error($message = 'Error', $status_code = 200)
  {
    return new WP_REST_Response(array(
      'status' => 'error',
      'message' => $message,
    ), $status_code);
  }

  // Handle custom responses
  public static function custom($status, $message, $data = null, $status_code = 200)
  {
    $response = array(
      'status' => $status,
      'message' => $message,
    );

    if (!is_null($data)) {
      $response['data'] = $data;
    }

    return new WP_REST_Response($response, $status_code);
  }
}
