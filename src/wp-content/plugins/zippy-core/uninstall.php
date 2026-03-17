<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
  die;
}

// We need to remove all key in config plugin after unintall plugin.

delete_option('_zippy_woocommerce_key');
delete_option('_zippy_postal_code');
