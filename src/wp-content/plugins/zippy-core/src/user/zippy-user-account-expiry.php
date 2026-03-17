<?php

namespace Zippy_Core\Src\User;

defined('ABSPATH') or die();
use Zippy_Core\Utils\Zippy_Utils_Core;
class Zippy_User_Account_Expiry
{
    protected static $_instance = null;

    /**
     * @return Zippy_User_Account_Expiry
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
        $this->set_hooks();
    }

    protected function set_hooks()
    {
        add_action('show_user_profile', [$this, 'add_expiry_date_field']);
        add_action('edit_user_profile', [$this, 'add_expiry_date_field']);

        add_action('personal_options_update', [$this, 'save_expiry_date_field']);
        add_action('edit_user_profile_update', [$this, 'save_expiry_date_field']);

        add_action('user_register', [$this, 'set_expiry_date_on_registration']);

        add_filter('manage_users_columns', [$this, 'add_creation_date_column']);
        add_filter('manage_users_columns', [$this, 'add_expiry_date_column']);
        add_filter('manage_users_columns', [$this, 'add_pdpa_column']);
        add_filter('manage_users_columns', [$this, 'add_mmc_column']);

        add_action('manage_users_custom_column', [$this, 'show_creation_date_column_content'], 10, 3);
        add_action('manage_users_custom_column', [$this, 'show_expiry_date_column_content'], 10, 3);
        add_action('manage_users_custom_column', [$this, 'show_pdpa_column_content'], 10, 3);
        add_action('manage_users_custom_column', [$this, 'show_mmc_column_content'], 10, 3);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_datepicker_script']);
        add_action('wp_login', [$this, 'check_user_expiry_date'], 10, 2);

        add_action('template_redirect', [$this, 'show_account_expired_message']);
        add_action('wp_head', [$this, 'add_inline_css_for_account_expired_message']);

        add_action('admin_footer', [$this, 'initialize_datepicker']);
        add_action('admin_footer', [$this, 'show_toast_expiry_message']);

        // date_default_timezone_set('Asia/Singapore');
    }

    public function add_expiry_date_field($user)
    {
        $current_user = wp_get_current_user();
        $is_admin = in_array('administrator', $current_user->roles);

        $expiry_date = get_the_author_meta('expiry_date', $user->ID);
        $formatted_expiry_date = $expiry_date ? date('d/m/Y', strtotime($expiry_date)) : '';

        $creation_date = get_the_author_meta('user_registered', $user->ID);
        $formatted_creation_date = date('d/m/Y', strtotime($creation_date));

        echo Zippy_Utils_Core::get_template('admin-user-account-expiry.php', [
            'current_user' =>   $current_user,
            'is_admin' =>   $is_admin,
            'expiry_date' =>   $expiry_date,
            'creation_date' =>   $creation_date,
            'formatted_expiry_date' =>   $formatted_expiry_date,
            'formatted_creation_date' =>   $formatted_creation_date
        ], dirname(__FILE__), '/templates');
    }

    public function save_expiry_date_field($user_id)
    {
        if (!current_user_can('edit_user', $user_id) || !in_array('administrator', wp_get_current_user()->roles)) {
            return false;
        }

        $expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : '';
        $formatted_expiry_date = date('Y-m-d', strtotime(str_replace('/', '-', $expiry_date)));

        update_user_meta($user_id, 'expiry_date', sanitize_text_field($formatted_expiry_date));
    }

    public function set_expiry_date_on_registration($user_id)
    {
        $retention_period = get_option('mpda_consent_time');
        if ($retention_period === false) {
            $retention_period = 5;
            update_option('mpda_consent_time', $retention_period);
        }
        $user_info = get_userdata($user_id);
        $creation_date = $user_info->user_registered;
        $expiry_date = date('Y-m-d', strtotime($creation_date . ' + ' . $retention_period . ' years'));
        update_user_meta($user_id, 'expiry_date', $expiry_date);
    }

    public function add_creation_date_column($columns)
    {
        $columns['creation_date'] = __('Date Joined', 'zippy-core-sg');
        return $columns;
    }

    public function add_expiry_date_column($columns)
    {
        $columns['expiry_date'] = __('Retention due date', 'zippy-core-sg');
        return $columns;
    }
    public function add_pdpa_column($columns)
    {
        $columns['pdpa_column'] = __('PDPA Consent', 'zippy-core-sg');
        return $columns;
    }
    public function add_mmc_column($columns)
    {
        $columns['mmc_column'] = __('Marketing Materials Consent', 'zippy-core-sg');
        return $columns;
    }

    public function show_creation_date_column_content($value, $column_name, $user_id)
    {
        if ($column_name == 'creation_date') {
            $creation_date = get_the_author_meta('user_registered', $user_id);
            $formatted_creation_date = $creation_date ? date('d/m/Y', strtotime($creation_date)) : '';
            return esc_html($formatted_creation_date);
        }
        return $value;
    }

    public function show_expiry_date_column_content($value, $column_name, $user_id)
    {
        if ($column_name == 'expiry_date') {
            $expiry_date = get_user_meta($user_id, 'expiry_date', true);
            $formatted_expiry_date = $expiry_date ? date('d/m/Y', strtotime($expiry_date)) : '-';
            if ($expiry_date && strtotime($expiry_date) < time()) {
                return __('<span class="user_expired">' . $formatted_expiry_date . '</span>', 'zippy-core-sg');
            } else {
                return esc_html($formatted_expiry_date);
            }
        }

        return $value;
    }

    public function show_pdpa_column_content($value, $column_name, $user_id)
    {
        if ($column_name == 'pdpa_column') {
            $pdpa_consent = get_user_meta($user_id, 'mpda_consent
            ', true);
            if (empty($pdpa_consent)) {
                $pdpa_consent_html = '-';
            } elseif ($pdpa_consent == "yes") {
                $pdpa_consent_html = 'True';
            } else {
                $pdpa_consent_html = 'False';
            }
            return esc_html($pdpa_consent_html);
        }
        return $value;
    }
    public function show_mmc_column_content($value, $column_name, $user_id)
    {
        if ($column_name == 'mmc_column') {
            $pdpa_consent = get_user_meta($user_id, 'marketing_consent', true);
            if (empty($pdpa_consent)) {
                $pdpa_consent_html = '-';
            } elseif ($pdpa_consent == "yes") {
                $pdpa_consent_html = 'True';
            } else {
                $pdpa_consent_html = 'False';
            }
            return esc_html($pdpa_consent_html);
        }
        return $value;
        return $value;
    }


    public function enqueue_datepicker_script()
    {
        wp_enqueue_script('jquery-ui-datepicker');
    }

    public function initialize_datepicker()
    {
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.datepicker').datepicker({
                    dateFormat: 'dd/mm/yy'
                });
            });
        </script>
<?php
    }

    public function check_user_expiry_date($user_login, $user)
    {
        $expiry_date = get_user_meta($user->ID, 'expiry_date', true);

        if ($expiry_date && strtotime($expiry_date) < time()) {
            update_user_meta($user->ID, 'account_disabled', true);
            wp_logout();

            wp_redirect(home_url('/?account_status=expired'));
            exit;
        }
    }

    public function show_account_expired_message()
    {
        if (isset($_GET['account_status']) && $_GET['account_status'] === 'expired') {
            $message = __('Your account has expired. Please contact the administrator for further details.', 'zippy-core-sg');
            echo '<div class="account-expired-message">' . esc_html($message) . '</div>';
        }
    }

    public function add_inline_css_for_account_expired_message()
    {
        if (isset($_GET['account_status']) && $_GET['account_status'] === 'expired') {
            $custom_css = "
                <style>
                    .account-expired-message {
                        background-color: #f8d7da;
                        color: #721c24;
                        padding: 15px;
                        border: 1px solid #f5c6cb;
                        border-radius: 5px;
                        text-align: center;
                        font-size: 16px;
                    }
                        .expiry_date .user_expired{
                            color: #d02800;
                        }
                </style>
            ";
            echo $custom_css;
        }
        echo ' <style> .expiry_date .user_expired{ color: #d02800; }</style>';
    }
    public function count_expired_user()
    {
        $users = get_users(array('fields' => array('ID')));
        $user_expired = 0;
        foreach ($users as $user) {
            $expiry_date = get_user_meta($user->ID, 'expiry_date', true);
            if ($expiry_date && strtotime($expiry_date) < time()) {
                $user_expired++;
            }
        }
        return  $user_expired;
    }
    public function show_toast_expiry_message()
    {
        echo '<div id="wpcontent"><div id="toast"></div>  ';
        if (is_admin() && isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], 'users.php') !== false) {

            echo ' <style>.expiry_date .user_expired{color:#d02800;font-weight:bold}#toast{position:fixed;top:45px;right:32px;z-index:999999}.toast{display:flex;align-items:center;background-color:#ffc9c9;border-radius:2px;padding:10px 0;min-width:400px;color:#d02800;max-width:450px;border-left:4px solid;box-shadow:0 5px 8px rgba(0,0,0,.08);transition:all linear .3s}@keyframes slideInLeft{from{opacity:0;transform:translateX(calc(100% + 32px))}to{opacity:1;transform:translateX(0)}}@keyframes fadeOut{to{opacity:0}}.toast--error{border-color:#ff623d}.toast--error .toast__icon{color:#ff623d}.toast+.toast{margin-top:24px}.toast__icon{font-size:24px}.toast__icon,.toast__close{padding:0 16px}.toast__body{flex-grow:1}.toast__title{font-size:16px;font-weight:600;color:#d02800}.toast__msg{font-size:14px;color:#d02800;margin-top:6px;line-height:1.5}.toast__close{font-size:20px;color:rgba(0,0,0,.3);cursor:pointer}
            </style>';
            echo '<script>function toast({ title = "", message = "", type = "info", duration = 3000 }) {
              const main = document.getElementById("toast");
              if (main) {
                const toast = document.createElement("div");
                // Auto remove toast
                const autoRemoveId = setTimeout(function () {
                  main.removeChild(toast);
                }, duration + 1000);
                // Remove toast when clicked
                toast.onclick = function (e) {
                  if (e.target.closest(".toast__close")) {
                    main.removeChild(toast);
                    clearTimeout(autoRemoveId);
                  }
                };
            
                const icons = {
                  warning: "fas fa-exclamation-circle",
                  error: "fas fa-exclamation-circle",
                };
                const icon = icons[type];
                const delay = (duration / 1000).toFixed(2);
            
                toast.classList.add("toast", `toast--${type}`);
                toast.style.animation = `slideInLeft ease .3s, fadeOut linear 1s ${delay}s forwards`;
            
                toast.innerHTML = `
                                <div class="toast__icon">
                                    <i class="${icon}"></i>
                                </div>
                                <div class="toast__body">
                                    <h3 class="toast__title">${title}</h3>
                                    <p class="toast__msg">${message}</p>
                                </div>
                                <div class="toast__close">
                                    <i class="fas fa-times"></i>
                                </div>
                            `;
                main.appendChild(toast);
              }
            }</script>';
            if ($this->count_expired_user() > 0) {
                echo '<script>toast({ title: "", message: "' . $this->count_expired_user() . ' customers exceeded data retention due date!", type: "error", duration: 5000 })</script>';
            }
        }
    }
}
