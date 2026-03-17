<?php
/*
Plugin Name: ZippySG Core
Plugin URI: https://zippy.sg/
Description: Support change default URL Admin, provide Advanced Analytics Woocommrece, Remove thirt party default of Wordpress, Setting SMTP Mail Server, Optime Wordpress Core...
Version: 8.0
Author: Zippy SG
Author URI: https://zippy.sg/
License: GNU General Public
License v3.0 License
URI: https://zippy.sg/
Domain Path: /languages

Copyright 2024

*/

namespace Zippy_Core;

use Zippy_Core\Src\Admin\Orders\Zippy_Admin_Orders;
use Zippy_Core\Src\Core\Zippy_Activate;

defined('ABSPATH') or die('°_°’');

/* ------------------------------------------
 // Constants
 ------------------------------------------------------------------------ */
/* Set plugin version constant. */

if (!defined('ZIPPY_CORE_VERSION')) {
  define('ZIPPY_CORE_VERSION', '4.0');
}

/* Set plugin name. */

if (!defined('ZIPPY_CORE_NAME')) {
  define('ZIPPY_CORE_NAME', 'Zippy Core');
}

if (!defined('ZIPPY_CORE_PREFIX')) {
  define('ZIPPY_CORE_PREFIX', 'zippy_core');
}

if (!defined('ZIPPY_CORE_BASENAME')) {
  define('ZIPPY_CORE_BASENAME', plugin_basename(__FILE__));
}

/* Set constant path to the plugin directory. */

if (!defined('ZIPPY_CORE_DIR_PATH')) {
  define('ZIPPY_CORE_DIR_PATH', plugin_dir_path(__FILE__));
}

/* Set constant url to the plugin directory. */

if (!defined('ZIPPY_CORE_URL')) {
  define('ZIPPY_CORE_URL', plugin_dir_url(__FILE__));
}

/* Set API prefix url */

if (!defined('ZIPPY_CORE_API_PREFIX')) {
  define('ZIPPY_CORE_API_PREFIX', 'zippy-core/v2');
}


/* ------------------------------------------
// i18n
---------------------------- --------------------------------------------- */

load_plugin_textdomain('zippy-core', false, basename(dirname(__FILE__)) . '/languages');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


/* ------------------------------------------
// Includes
 --------------------------- --------------------------------------------- */
require_once ZIPPY_CORE_DIR_PATH . 'vendor/autoload.php';

require ZIPPY_CORE_DIR_PATH . '/includes/autoload.php';

require_once __DIR__ . '/src/core/zippy-activate.php';

register_activation_hook(__FILE__, [Zippy_Activate::class, 'activate']);

use  Zippy_Core\Src\Admin\Zippy_Admin_Setting;

use  Zippy_Core\Src\Admin\Zippy_Admin_Url;

use Zippy_Core\Src\Core\Zippy_Core;

use Zippy_Core\Src\User\Zippy_MPDA_Consent;

use Zippy_Core\Src\User\Zippy_User_Account_Expiry;

use Zippy_Core\Src\Analytics\Zippy_Analytics;
use Zippy_Core\Src\Woocommerce\Zippy_Woocommerce;

/**
 *
 * Init Zippy Core
 */

Zippy_Admin_Setting::get_instance();

Zippy_Core::get_instance();

Zippy_Admin_Url::get_instance();

Zippy_MPDA_Consent::get_instance();

Zippy_User_Account_Expiry::get_instance();

Zippy_Analytics::get_instance();

Zippy_Woocommerce::get_instance();


/**
 * Zippy Core V2: Import modules
 */

require_once ZIPPY_CORE_DIR_PATH . 'src/modules/route.php';
require_once ZIPPY_CORE_DIR_PATH . 'src/modules/module.php';
require_once ZIPPY_CORE_DIR_PATH . 'src/modules/middleware.php';
require_once ZIPPY_CORE_DIR_PATH . 'src/modules/autoload-modules.php';

//Autoload modules
if (class_exists(Core_Autoload_Module::class)) {
  Core_Autoload_Module::init();
}
