<?php

/**
 * MPDA Consent Management
 *
 * @package MPDA_Consent
 */

namespace Zippy_Core\Src\User;



defined('ABSPATH') or die();

use Zippy_Core\Utils\Zippy_Utils_Core;

class Zippy_MPDA_Consent
{
    protected static $_instance = null;

    /**
     * @return Zippy_MPDA_Consent
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
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_action('admin_init', [$this, 'mpda_consent_settings_init']);
            add_shortcode('privacy_policy_link', [$this, 'privacy_policy_link_shortcode']);
            add_shortcode('mpda_consent_checkbox', [$this, 'mpda_consent_checkbox_shortcode']);
            add_action('woocommerce_register_form', [$this, 'add_consent_checkbox_to_registration_form']);
            add_action('wp_footer', [$this, 'disable_submit_if_consent_not_checked']);
            add_filter('woocommerce_registration_errors', [$this, 'validate_consent_checkbox'], 10, 3);
            add_action('woocommerce_created_customer', [$this, 'save_consent_checkbox_data']);
            add_action('show_user_profile', [$this, 'show_consent_in_user_profile'],19,2);
            add_action('edit_user_profile', [$this, 'show_consent_in_user_profile'],19,2);
            add_action('personal_options_update', [$this, 'save_consent_in_user_profile'],19,2);
            add_action('edit_user_profile_update', [$this, 'save_consent_in_user_profile'],19,2);
            register_activation_hook(__FILE__, [$this, 'set_default_consent_time']);
        }
    }

    public function mpda_consent_settings_init()
    {
        add_settings_section(
            'mpda_consent_section',
            __('MPDA Consent Settings', 'mpda-consent'),
            [$this, 'mpda_consent_section_callback'],
            'general'
        );

        add_settings_field(
            'mpda_consent_description',
            __('MPDA Consent Description', 'mpda-consent'),
            [$this, 'mpda_consent_description_callback'],
            'general',
            'mpda_consent_section'
        );


        add_settings_field(
            'mpda_consent_checkbox_label',
            __('Checkbox Label', 'mpda-consent'),
            [$this, 'mpda_consent_checkbox_label_callback'],
            'general',
            'mpda_consent_section'
        );

        add_settings_field(
            'mpda_consent_time',
            __('Default retention period', 'mpda-consent'),
            [$this, 'mpda_consent_time_callback'],
            'general',
            'mpda_consent_section'
        );

        register_setting('general', 'mpda_consent_time', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => '5'
        ));

        register_setting('general', 'mpda_consent_description', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',

        ));
        register_setting('general', 'mpda_consent_checkbox_label', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'I have read and agree with the terms and conditions.'
        ));
    }

    public function privacy_policy_link_shortcode()
    {
        $privacy_policy_page_id = get_option('wp_page_for_privacy_policy');

        if (!$privacy_policy_page_id) {
            return 'Privacy policy page is not set.';
        }
        $privacy_policy_url = get_permalink($privacy_policy_page_id);

        if (!$privacy_policy_url) {
            return 'Privacy policy page link is not available.';
        }
        return sprintf(
            '<a href="%s" target="_blank">Privacy Policy</a>',
            esc_url($privacy_policy_url)
        );
    }
    public function mpda_set_default_consent_time() {
        if (get_option('mpda_consent_time') === false) {  
            update_option('mpda_consent_time', '5');
        }
    }

    public function mpda_consent_time_callback($user)
    {

        $retention_period = get_option('mpda_consent_time');
        echo Zippy_Utils_Core::get_template('consent-time.php', [
            'retention_period' =>   $retention_period,
        ], dirname(__FILE__), '/templates');
    }
    public function mpda_consent_section_callback()
    {
        echo '<p>' . __('Customize the consent description text and checkbox label.', 'mpda-consent') . '</p>';
    }

    public function mpda_consent_checkbox_label_callback()
    {
        $label = get_option('mpda_consent_checkbox_label', 'I have read and agree with the terms and conditions.');
        echo '<input type="text" name="mpda_consent_checkbox_label" value="' . esc_attr($label) . '" class="regular-text" />';
    }

    public function mpda_consent_description_callback()
    {
        $site_name = get_bloginfo('name');
        $description = get_option('mpda_consent_description', "$site_name may collect, use and disclose your personal data, which you have provided in this form, for providing marketing material that you have agreed to receive, in accordance with the Personal Data Protection Act 2012 and our data protection policy.");

        echo '<textarea name="mpda_consent_description" rows="3" cols="50">' . esc_html($description) . '</textarea>';
    }

    public function mpda_consent_checkbox_shortcode()
    {
        $site_name = get_bloginfo('name');
        $mpda_description = get_option('mpda_consent_description', "$site_name may collect, use and disclose your personal data, which you have provided in this form, for providing marketing material that you have agreed to receive, in accordance with the Personal Data Protection Act 2012 and our data protection policy.");
        $checkbox_label = get_option('mpda_consent_checkbox_label', 'I have read and agree with the terms and conditions.');

        return '<div class="form-row terms custom-consent">
                <div class="description"> ' . esc_html($mpda_description) . '</div>
                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                    <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="mpda_consent" id="mpda_consent" value="1" />
                    <span>' . esc_html($checkbox_label) . '</span>
                </label>
            </div>';
    }

    public function add_consent_checkbox_to_registration_form()
    {
        if (is_account_page()) {
        echo do_shortcode('[mpda_consent_checkbox]');
        }
    }

    public function disable_submit_if_consent_not_checked()
    {
        if (is_account_page()) {
?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                var consentCheckbox = document.getElementById('mpda_consent');
                var submitButton = document.querySelector('button[name="register"]');

                submitButton.disabled = true;

                consentCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        submitButton.disabled = false;
                    } else {
                        submitButton.disabled = true;
                    }
                });
            });
        </script>
        <style>
            .custom-consent .description {
                margin-bottom: 1rem;
            }

            .custom-consent label {
                margin: 10px 0;
            }

            .woocommerce-privacy-policy-text {
                display: none;
            }
        </style>
<?php
        }
    }

    public function validate_consent_checkbox($errors, $username, $email)
    {
        if (!isset($_POST['mpda_consent'])) {
            $errors->add('consent_error', __('You must consent to the terms and conditions.', 'woocommerce'));
        }
        return $errors;
    }

    public function save_consent_checkbox_data($customer_id)
    {
        if (isset($_POST['mpda_consent'])) {
            update_user_meta($customer_id, 'mpda_consent', 'yes');
            update_user_meta($customer_id, 'marketing_consent', 'yes');
        } else {
            update_user_meta($customer_id, 'mpda_consent', 'no');
            update_user_meta($customer_id, 'marketing_consent', 'no');
        }
    }

    public function show_consent_in_user_profile($user)
    {
        $mpda_consent = get_user_meta($user->ID, 'mpda_consent', true);
        $marketing_consent = get_user_meta($user->ID, 'marketing_consent', true);
        echo Zippy_Utils_Core::get_template('admin-mpda-consent.php', [
            'mpda_consent' =>   $mpda_consent,
            'marketing_consent' =>   $marketing_consent,
        ], dirname(__FILE__), '/templates');
    }


    public function save_consent_in_user_profile($user_id)
    {
        if (!current_user_can('administrator')) {
            return false;
        }

        if (isset($_POST['mpda_consent']) && isset($_POST['marketing_consent'])) {
            update_user_meta($user_id, 'mpda_consent', sanitize_text_field($_POST['mpda_consent']));
            update_user_meta($user_id, 'marketing_consent', sanitize_text_field($_POST['marketing_consent']));
        }
    }
}
