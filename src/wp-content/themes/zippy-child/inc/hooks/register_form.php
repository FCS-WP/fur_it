<?php

/**
 * Zippy Contact Form Shortcode
 *
 * - [zippy_contact_form]
 *
 * Features:
 * - Fields: Name, Email, Phone, Subject, Order Number (optional), Message
 * - Server-side validation with inline errors
 * - Client-side (HTML5 + JS) validation
 * - PRG pattern to prevent duplicate submissions on refresh
 * - wp_mail() delivery
 * - Nonce security
 */

if (! defined('ABSPATH')) exit;


// ============================================================
// Validation Rules
// ============================================================
function zippy_cf_validate($fields)
{
    $errors = [];

    // Name
    if (empty($fields['name'])) {
        $errors['name'] = 'Full name is required.';
    } elseif (mb_strlen($fields['name']) < 2) {
        $errors['name'] = 'Name must be at least 2 characters.';
    } elseif (mb_strlen($fields['name']) > 100) {
        $errors['name'] = 'Name must not exceed 100 characters.';
    }

    // Email
    if (empty($fields['email'])) {
        $errors['email'] = 'Email address is required.';
    } elseif (! is_email($fields['email'])) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Phone
    if (empty($fields['phone'])) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (! preg_match('/^[+\d\s\-().]{7,20}$/', $fields['phone'])) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }

    // Subject
    if (empty($fields['subject'])) {
        $errors['subject'] = 'Subject is required.';
    } elseif (mb_strlen($fields['subject']) < 3) {
        $errors['subject'] = 'Subject must be at least 3 characters.';
    } elseif (mb_strlen($fields['subject']) > 150) {
        $errors['subject'] = 'Subject must not exceed 150 characters.';
    }

    // Order Number (optional — only validate format if provided)
    if (! empty($fields['order_number'])) {
        if (! preg_match('/^[#\w\-]{1,50}$/', $fields['order_number'])) {
            $errors['order_number'] = 'Order number format is invalid.';
        }
    }

    // Message
    if (empty($fields['message'])) {
        $errors['message'] = 'Message is required.';
    } elseif (mb_strlen($fields['message']) < 10) {
        $errors['message'] = 'Message must be at least 10 characters.';
    } elseif (mb_strlen($fields['message']) > 3000) {
        $errors['message'] = 'Message must not exceed 3000 characters.';
    }

    return $errors;
}


// ============================================================
// Form Handler — runs on init (before any output)
// ============================================================
function zippy_contact_form_handler()
{
    if (
        ! isset($_POST['zippy_contact_submit']) ||
        ! isset($_POST['zippy_contact_nonce']) ||
        ! wp_verify_nonce($_POST['zippy_contact_nonce'], 'zippy_contact_form')
    ) return;

    // Sanitize
    $fields = [
        'name'         => sanitize_text_field($_POST['zippy_name']         ?? ''),
        'email'        => sanitize_email($_POST['zippy_email']             ?? ''),
        'phone'        => sanitize_text_field($_POST['zippy_phone']        ?? ''),
        'subject'      => sanitize_text_field($_POST['zippy_subject']      ?? ''),
        'order_number' => sanitize_text_field($_POST['zippy_order_number'] ?? ''),
        'message'      => sanitize_textarea_field($_POST['zippy_message']  ?? ''),
    ];

    // Validate
    $errors = zippy_cf_validate($fields);

    $success = false;

    if (empty($errors)) {
        $to           = sanitize_email($_POST['zippy_recipient'] ?? get_option('admin_email'));
        $subject_line = '[Contact Form] ' . $fields['subject'];

        $body  = "Name: {$fields['name']}\n";
        $body .= "Email: {$fields['email']}\n";
        $body .= "Phone: {$fields['phone']}\n";
        $body .= "Subject: {$fields['subject']}\n";
        if (! empty($fields['order_number'])) {
            $body .= "Order Number: {$fields['order_number']}\n";
        }
        $body .= "\nMessage:\n{$fields['message']}\n";

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: '     . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
            'Reply-To: ' . $fields['name'] . ' <' . $fields['email'] . '>',
        ];

        $sent = wp_mail($to, $subject_line, $body, $headers);

        if ($sent) {
            $success = true;
            $fields  = [];
        } else {
            // Grab the actual mailer error
            global $phpmailer;
            if (isset($phpmailer) && is_object($phpmailer) && ! empty($phpmailer->ErrorInfo)) {
                $mail_error = $phpmailer->ErrorInfo;
            } else {
                $mail_error = 'Unknown error — check debug.log';
            }

            // Log it
            error_log('zippy_contact_form wp_mail error: ' . $mail_error);

            // Show detailed error only in debug mode
            $error_msg = WP_DEBUG
                ? 'Mail error: ' . $mail_error
                : 'Sorry, the message could not be sent. Please try again later.';

            $errors['_global'] = $error_msg;
        }
    }

    // Store result via transient (PRG)
    $key = 'zippy_cf_' . md5($_SERVER['REMOTE_ADDR'] . microtime());
    set_transient($key, [
        'errors'  => $errors,
        'success' => $success,
        'old'     => $fields,
    ], 60);

    // Redirect back with key
    $redirect = add_query_arg('zippy_cf', $key, wp_get_referer() ?: get_permalink());
    wp_safe_redirect($redirect);
    exit;
}
add_action('init', 'zippy_contact_form_handler');


// ============================================================
// [zippy_contact_form]
// ============================================================
function zippy_contact_form($atts)
{
    $atts = shortcode_atts([
        // Recipient
        'email'           => '',

        // Success message
        'success_message' => 'Thank you! Your message has been sent. We will get back to you shortly.',

        // Field labels
        'label_name'     => 'Full Name',
        'label_email'    => 'Email Address',
        'label_phone'    => 'Phone Number',
        'label_subject'  => 'Subject',
        'label_order'    => 'Order Number',
        'label_message'  => 'Message',
        'label_submit'   => 'Send Message',

        // Style
        'class'          => '',
    ], $atts, 'zippy_contact_form');

    // Retrieve flash data from PRG redirect
    $errors  = [];
    $success = false;
    $old     = [];

    if (isset($_GET['zippy_cf'])) {
        $key  = sanitize_key($_GET['zippy_cf']);
        $data = get_transient($key);
        if ($data) {
            $errors  = $data['errors']  ?? [];
            $success = $data['success'] ?? false;
            $old     = $data['old']     ?? [];
            delete_transient($key);
        }
    }

    // Helper: old field value
    $val = fn($f) => esc_attr($old[$f] ?? '');

    // Helper: field error HTML
    $err = function ($f) use ($errors) {
        if (empty($errors[$f])) return '';
        return sprintf('<span class="zippy-cf-field-error" role="alert">%s</span>', esc_html($errors[$f]));
    };

    // Helper: field class (has-error)
    $fcls = fn($f) => isset($errors[$f]) ? ' zippy-cf-field--error' : '';

    $recipient = ! empty($atts['email']) ? sanitize_email($atts['email']) : get_option('admin_email');

    ob_start();
?>

    <div class="zippy-contact-form-wrap <?php echo esc_attr($atts['class']); ?>">

        <?php if ($success) : ?>
            <div class="zippy-cf-notice zippy-cf-notice--success" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <span><?php echo esc_html($atts['success_message']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($errors['_global'])) : ?>
            <div class="zippy-cf-notice zippy-cf-notice--error" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                </svg>
                <span><?php echo esc_html($errors['_global']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (! $success) : ?>
            <form
                id="zippy-contact-form"
                class="zippy-contact-form"
                method="POST"
                action="<?php echo esc_url(get_permalink()); ?>"
                novalidate>
                <?php wp_nonce_field('zippy_contact_form', 'zippy_contact_nonce'); ?>
                <input type="hidden" name="zippy_contact_submit" value="1" />
                <input type="hidden" name="zippy_recipient" value="<?php echo esc_attr($recipient); ?>" />

                <!-- Row 1: Name + Email -->
                <div class="zippy-cf-row">

                    <div class="zippy-cf-field<?php echo $fcls('name'); ?>">
                        <label for="zippy_name">
                            <?php echo esc_html($atts['label_name']); ?>
                            <span class="zippy-cf-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="text"
                            id="zippy_name"
                            name="zippy_name"
                            value="<?php echo $val('name'); ?>"
                            placeholder="<?php echo esc_attr($atts['label_name']); ?>"
                            minlength="2"
                            maxlength="100"
                            required
                            autocomplete="name" />
                        <?php echo $err('name'); ?>
                    </div>

                    <div class="zippy-cf-field<?php echo $fcls('email'); ?>">
                        <label for="zippy_email">
                            <?php echo esc_html($atts['label_email']); ?>
                            <span class="zippy-cf-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="email"
                            id="zippy_email"
                            name="zippy_email"
                            value="<?php echo $val('email'); ?>"
                            placeholder="you@example.com"
                            required
                            autocomplete="email" />
                        <?php echo $err('email'); ?>
                    </div>

                </div>

                <!-- Row 2: Phone + Subject -->
                <div class="zippy-cf-row">

                    <div class="zippy-cf-field<?php echo $fcls('phone'); ?>">
                        <label for="zippy_phone">
                            <?php echo esc_html($atts['label_phone']); ?>
                            <span class="zippy-cf-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="tel"
                            id="zippy_phone"
                            name="zippy_phone"
                            value="<?php echo $val('phone'); ?>"
                            placeholder="+65 9123 4567"
                            pattern="[+\d\s\-().]{7,20}"
                            required
                            autocomplete="tel" />
                        <?php echo $err('phone'); ?>
                    </div>

                    <div class="zippy-cf-field<?php echo $fcls('subject'); ?>">
                        <label for="zippy_subject">
                            <?php echo esc_html($atts['label_subject']); ?>
                            <span class="zippy-cf-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="text"
                            id="zippy_subject"
                            name="zippy_subject"
                            value="<?php echo $val('subject'); ?>"
                            placeholder="<?php echo esc_attr($atts['label_subject']); ?>"
                            minlength="3"
                            maxlength="150"
                            required />
                        <?php echo $err('subject'); ?>
                    </div>

                </div>

                <!-- Row 3: Order Number (optional, full width) -->
                <div class="zippy-cf-row">
                    <div class="zippy-cf-field zippy-cf-field--full<?php echo $fcls('order_number'); ?>">
                        <label for="zippy_order_number">
                            <?php echo esc_html($atts['label_order']); ?>
                            <span class="zippy-cf-optional">(Optional)</span>
                        </label>
                        <input
                            type="text"
                            id="zippy_order_number"
                            name="zippy_order_number"
                            value="<?php echo $val('order_number'); ?>"
                            placeholder="e.g. #1234"
                            maxlength="50" />
                        <?php echo $err('order_number'); ?>
                    </div>
                </div>

                <!-- Row 4: Message -->
                <div class="zippy-cf-row">
                    <div class="zippy-cf-field zippy-cf-field--full<?php echo $fcls('message'); ?>">
                        <label for="zippy_message">
                            <?php echo esc_html($atts['label_message']); ?>
                            <span class="zippy-cf-required" aria-hidden="true">*</span>
                        </label>
                        <textarea
                            id="zippy_message"
                            name="zippy_message"
                            rows="6"
                            placeholder="Write your message here..."
                            minlength="10"
                            maxlength="3000"
                            required><?php echo esc_textarea($old['message'] ?? ''); ?></textarea>
                        <span class="zippy-cf-char-count">
                            <span id="zippy-msg-count">0</span> / 3000
                        </span>
                        <?php echo $err('message'); ?>
                    </div>
                </div>

                <!-- Row 5: Submit -->
                <div class="zippy-cf-row zippy-cf-row--submit">
                    <button type="submit" class="zippy-cf-submit">
                        <span class="zippy-cf-submit__text"><?php echo esc_html($atts['label_submit']); ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                        </svg>
                    </button>
                </div>

            </form>

            <script>
                (function() {
                    var form = document.getElementById('zippy-contact-form');
                    var textarea = document.getElementById('zippy_message');
                    var counter = document.getElementById('zippy-msg-count');

                    // ── Char counter ──
                    if (textarea && counter) {
                        var update = function() {
                            counter.textContent = textarea.value.length;
                            counter.closest('.zippy-cf-char-count').classList.toggle(
                                'zippy-cf-char-count--warn',
                                textarea.value.length > 2700
                            );
                        };
                        textarea.addEventListener('input', update);
                        update();
                    }

                    if (!form) return;

                    // ── Live validation on blur ──
                    var rules = {
                        zippy_name: {
                            minLength: 2,
                            maxLength: 100,
                            label: 'Full name'
                        },
                        zippy_email: {
                            isEmail: true,
                            label: 'Email'
                        },
                        zippy_phone: {
                            pattern: /^[+\d\s\-().]{7,20}$/,
                            label: 'Phone number'
                        },
                        zippy_subject: {
                            minLength: 3,
                            maxLength: 150,
                            label: 'Subject'
                        },
                        zippy_message: {
                            minLength: 10,
                            maxLength: 3000,
                            label: 'Message'
                        },
                    };

                    function validateField(input) {
                        var name = input.name;
                        var value = input.value.trim();
                        var rule = rules[name];
                        var field = input.closest('.zippy-cf-field');
                        var err = field ? field.querySelector('.zippy-cf-field-error') : null;
                        var msg = '';

                        if (!rule) return true;

                        if (input.required && value === '') {
                            msg = rule.label + ' is required.';
                        } else if (value !== '') {
                            if (rule.isEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                                msg = 'Please enter a valid email address.';
                            } else if (rule.minLength && value.length < rule.minLength) {
                                msg = rule.label + ' must be at least ' + rule.minLength + ' characters.';
                            } else if (rule.maxLength && value.length > rule.maxLength) {
                                msg = rule.label + ' must not exceed ' + rule.maxLength + ' characters.';
                            } else if (rule.pattern && !rule.pattern.test(value)) {
                                msg = 'Please enter a valid ' + rule.label.toLowerCase() + '.';
                            }
                        }

                        if (field) field.classList.toggle('zippy-cf-field--error', !!msg);

                        if (err) {
                            err.textContent = msg;
                        } else if (msg && field) {
                            var span = document.createElement('span');
                            span.className = 'zippy-cf-field-error';
                            span.setAttribute('role', 'alert');
                            span.textContent = msg;
                            field.appendChild(span);
                        }

                        return !msg;
                    }

                    // Attach blur listeners
                    Object.keys(rules).forEach(function(name) {
                        var input = form.querySelector('[name="' + name + '"]');
                        if (input) {
                            input.addEventListener('blur', function() {
                                validateField(this);
                            });
                            input.addEventListener('input', function() {
                                if (this.closest('.zippy-cf-field--error')) validateField(this);
                            });
                        }
                    });

                    // ── Pre-submit validation ──
                    form.addEventListener('submit', function(e) {
                        var valid = true;
                        Object.keys(rules).forEach(function(name) {
                            var input = form.querySelector('[name="' + name + '"]');
                            if (input && !validateField(input)) valid = false;
                        });

                        if (!valid) {
                            e.preventDefault();
                            // Scroll to first error
                            var first = form.querySelector('.zippy-cf-field--error');
                            if (first) first.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            return;
                        }

                        // Disable submit to prevent double-click
                        var btn = form.querySelector('.zippy-cf-submit');
                        if (btn) {
                            btn.disabled = true;
                            btn.querySelector('.zippy-cf-submit__text').textContent = 'Sending...';
                        }
                    });
                })();
            </script>

        <?php endif; ?>
    </div>

<?php
    return ob_get_clean();
}
add_shortcode('zippy_contact_form', 'zippy_contact_form');

/**
 * Zippy Form Builder Shortcodes
 *
 * Build dynamic forms entirely via shortcodes.
 *
 * Usage in functions.php:
 *   require_once get_stylesheet_directory() . '/inc/zippy-form-builder.php';
 *
 * Example:
 *   [booking_form submit_text="Send" recipient="hello@site.com"]
 *       [form_input type="text"     label="Full Name"   required="true" width="50%"]
 *       [form_input type="email"    label="Email"       required="true" width="50%"]
 *       [form_input type="tel"      label="Phone"       width="50%"]
 *       [form_input type="date"     label="Date"        width="50%"]
 *       [form_input type="number"   label="Guests"      min="1" max="20" width="50%"]
 *       [form_input type="textarea" label="Message"     rows="4"]
 *       [form_select label="Service" options="Grooming, Spa, Vaccine" required="true" width="50%"]
 *       [form_select label="Time Slot" options="9:00am, 11:00am, 2:00pm" width="50%"]
 *       [form_checkbox label="I agree to the terms and conditions" required="true"]
 *   [/booking_form]
 */

if ( ! defined('ABSPATH') ) exit;


// ============================================================
// Global state — collect fields registered inside [booking_form]
// ============================================================
global $zippy_form_fields;
$zippy_form_fields = [];


// ============================================================
// Helper: generate field ID
// ============================================================
function zippy_field_id( $label, $form_uid ) {
    return $form_uid . '-' . sanitize_title($label);
}


// ============================================================
// Helper: parse width to CSS grid span
// Width = percentage string e.g. "50%", "33%", "100%"
// Maps to CSS grid column span in a 12-col grid
// ============================================================
function zippy_width_to_style( $width ) {
    if ( empty($width) || $width === '100%' ) return '';
    return 'style="flex:0 0 ' . esc_attr($width) . ';max-width:' . esc_attr($width) . ';"';
}


// ============================================================
// [form_input] — text, email, tel, number, date, time, textarea
// ============================================================
function zippy_form_input( $atts ) {
    global $zippy_form_fields;

    $atts = shortcode_atts([
        'type'        => 'text',
        'label'       => '',
        'placeholder' => '',
        'required'    => 'false',
        'width'       => '100%',
        'value'       => '',
        'min'         => '',
        'max'         => '',
        'step'        => '',
        'rows'        => '4',        // for textarea
        'name'        => '',         // override field name
        'class'       => '',
    ], $atts, 'form_input');

    // Register field for server-side processing
    $zippy_form_fields[] = [
        'type'     => $atts['type'],
        'label'    => $atts['label'],
        'name'     => $atts['name'] ?: sanitize_title($atts['label']),
        'required' => $atts['required'] === 'true',
    ];

    return '__ZIPPY_FIELD__' . base64_encode(json_encode($atts)) . '__';
}
add_shortcode('form_input', 'zippy_form_input');


// ============================================================
// [form_select] — dropdown select
// ============================================================
function zippy_form_select( $atts ) {
    global $zippy_form_fields;

    $atts = shortcode_atts([
        'label'       => '',
        'options'     => '',         // comma-separated: "Option A, Option B"
        'placeholder' => 'Select...',
        'required'    => 'false',
        'width'       => '100%',
        'name'        => '',
        'class'       => '',
    ], $atts, 'form_select');

    $zippy_form_fields[] = [
        'type'     => 'select',
        'label'    => $atts['label'],
        'name'     => $atts['name'] ?: sanitize_title($atts['label']),
        'required' => $atts['required'] === 'true',
    ];

    return '__ZIPPY_FIELD__' . base64_encode(json_encode(array_merge($atts, ['_type' => 'select']))) . '__';
}
add_shortcode('form_select', 'zippy_form_select');


// ============================================================
// [form_checkbox] — single checkbox
// ============================================================
function zippy_form_checkbox( $atts ) {
    global $zippy_form_fields;

    $atts = shortcode_atts([
        'label'    => '',
        'required' => 'false',
        'width'    => '100%',
        'name'     => '',
        'class'    => '',
    ], $atts, 'form_checkbox');

    $zippy_form_fields[] = [
        'type'     => 'checkbox',
        'label'    => $atts['label'],
        'name'     => $atts['name'] ?: sanitize_title($atts['label']),
        'required' => $atts['required'] === 'true',
    ];

    return '__ZIPPY_FIELD__' . base64_encode(json_encode(array_merge($atts, ['_type' => 'checkbox']))) . '__';
}
add_shortcode('form_checkbox', 'zippy_form_checkbox');


// ============================================================
// [booking_form] — wrapper that renders everything
// ============================================================
function zippy_booking_form( $atts, $content = null ) {
    global $zippy_form_fields;

    $atts = shortcode_atts([
        'submit_text'     => 'Submit',
        'recipient'       => '',          // email recipient — defaults to admin
        'subject'         => 'New Form Submission',
        'success_message' => 'Thank you! Your submission has been received.',
        'id'              => '',          // custom form ID
        'class'           => '',
    ], $atts, 'booking_form');

    // Reset field registry for this form instance
    $zippy_form_fields = [];

    // Process child shortcodes — this populates $zippy_form_fields
    // and returns placeholder tokens
    $processed = do_shortcode($content);

    // Snapshot fields registered by child shortcodes
    $fields_snapshot = $zippy_form_fields;
    $zippy_form_fields = []; // reset for next form on page

    $uid        = $atts['id'] ?: 'zform-' . uniqid();
    $form_class = 'zippy-form' . ( $atts['class'] ? ' ' . esc_attr($atts['class']) : '' );
    $recipient  = ! empty($atts['recipient']) ? sanitize_email($atts['recipient']) : get_option('admin_email');

    // ── Handle PRG flash ──
    $errors  = [];
    $success = false;
    $old     = [];

    if ( isset($_GET['zf']) ) {
        $flash_key = sanitize_key($_GET['zf']);
        $flash     = get_transient($flash_key);
        if ( $flash ) {
            $errors  = $flash['errors']  ?? [];
            $success = $flash['success'] ?? false;
            $old     = $flash['old']     ?? [];
            delete_transient($flash_key);
        }
    }

    ob_start();
    ?>

    <div class="zippy-form-wrap <?php echo esc_attr($atts['class']); ?>">

        <?php if ( $success ) : ?>
        <div class="zippy-form-notice zippy-form-notice--success" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            <span><?php echo esc_html($atts['success_message']); ?></span>
        </div>
        <?php endif; ?>

        <?php if ( ! empty($errors) ) : ?>
        <div class="zippy-form-notice zippy-form-notice--error" role="alert">
            <ul>
                <?php foreach ($errors as $err) : ?>
                    <li><?php echo esc_html($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if ( ! $success ) : ?>
        <form
            id="<?php echo esc_attr($uid); ?>"
            class="<?php echo esc_attr($form_class); ?>"
            method="POST"
            action="<?php echo esc_url(get_permalink()); ?>"
            novalidate
        >
            <?php wp_nonce_field('zippy_form_submit_' . $uid, 'zippy_form_nonce'); ?>
            <input type="hidden" name="zippy_form_id"        value="<?php echo esc_attr($uid); ?>" />
            <input type="hidden" name="zippy_form_recipient" value="<?php echo esc_attr($recipient); ?>" />
            <input type="hidden" name="zippy_form_subject"   value="<?php echo esc_attr($atts['subject']); ?>" />
            <input type="hidden" name="zippy_form_fields"    value="<?php echo esc_attr(base64_encode(json_encode($fields_snapshot))); ?>" />

            <div class="zippy-form__fields">
                <?php
                // ── Render each field token ──
                $tokens = explode('__ZIPPY_FIELD__', $processed);
                foreach ( $tokens as $token ) :
                    if ( empty(trim($token)) ) continue;

                    // Check if this is a field token
                    $end_pos = strpos($token, '__');
                    if ( $end_pos === false ) {
                        // Plain text/HTML between fields
                        echo wp_kses_post($token);
                        continue;
                    }

                    $encoded   = substr($token, 0, $end_pos);
                    $remainder = substr($token, $end_pos + 2);
                    $field     = json_decode(base64_decode($encoded), true);

                    if ( ! $field ) {
                        echo wp_kses_post($token);
                        continue;
                    }

                    $field_type = $field['_type'] ?? $field['type'] ?? 'text';
                    $label      = $field['label'] ?? '';
                    $name       = $field['name'] ?? sanitize_title($label);
                    $required   = ($field['required'] ?? 'false') === 'true';
                    $width      = $field['width'] ?? '100%';
                    $field_id   = $uid . '-' . $name;
                    $old_val    = $old[$name] ?? '';
                    $width_style = zippy_width_to_style($width);
                    $req_attr   = $required ? 'required' : '';
                    $req_star   = $required ? '<span class="zippy-form__required" aria-hidden="true">*</span>' : '';
                    $has_error  = in_array($name, array_column($errors, 'field') ?: []);
                    $field_class = 'zippy-form__field' . ( ! empty($field['class']) ? ' ' . esc_attr($field['class']) : '' );
                    ?>

                    <div class="<?php echo $field_class; ?>" <?php echo $width_style; ?>>
                        <?php if ( $field_type !== 'checkbox' && ! empty($label) ) : ?>
                        <label for="<?php echo esc_attr($field_id); ?>">
                            <?php echo esc_html($label); ?><?php echo $req_star; ?>
                        </label>
                        <?php endif; ?>

                        <?php
                        switch ( $field_type ) {

                            case 'textarea':
                                printf(
                                    '<textarea id="%s" name="%s" rows="%s" placeholder="%s" %s>%s</textarea>',
                                    esc_attr($field_id),
                                    esc_attr($name),
                                    esc_attr($field['rows'] ?? '4'),
                                    esc_attr($field['placeholder'] ?? $label),
                                    $req_attr,
                                    esc_textarea($old_val)
                                );
                                break;

                            case 'select':
                                $options = array_map('trim', explode(',', $field['options'] ?? ''));
                                echo '<select id="' . esc_attr($field_id) . '" name="' . esc_attr($name) . '" ' . $req_attr . '>';
                                echo '<option value="">' . esc_html($field['placeholder'] ?? 'Select...') . '</option>';
                                foreach ( $options as $opt ) {
                                    $selected = $old_val === $opt ? 'selected' : '';
                                    echo '<option value="' . esc_attr($opt) . '" ' . $selected . '>' . esc_html($opt) . '</option>';
                                }
                                echo '</select>';
                                break;

                            case 'checkbox':
                                $checked = $old_val ? 'checked' : '';
                                printf(
                                    '<label class="zippy-form__checkbox-wrap" for="%s">
                                        <input type="checkbox" id="%s" name="%s" value="1" %s %s />
                                        <span class="zippy-form__checkbox-custom"></span>
                                        <span class="zippy-form__checkbox-label">%s%s</span>
                                    </label>',
                                    esc_attr($field_id),
                                    esc_attr($field_id),
                                    esc_attr($name),
                                    $checked,
                                    $req_attr,
                                    esc_html($label),
                                    $req_star
                                );
                                break;

                            default:
                                // text, email, tel, number, date, time, url
                                $extra = '';
                                if ( ! empty($field['min']) )  $extra .= ' min="'  . esc_attr($field['min'])  . '"';
                                if ( ! empty($field['max']) )  $extra .= ' max="'  . esc_attr($field['max'])  . '"';
                                if ( ! empty($field['step']) ) $extra .= ' step="' . esc_attr($field['step']) . '"';

                                printf(
                                    '<input type="%s" id="%s" name="%s" value="%s" placeholder="%s" %s %s />',
                                    esc_attr($field['type'] ?? 'text'),
                                    esc_attr($field_id),
                                    esc_attr($name),
                                    esc_attr($old_val ?: ($field['value'] ?? '')),
                                    esc_attr($field['placeholder'] ?? $label),
                                    $req_attr,
                                    $extra
                                );
                                break;
                        }
                        ?>
                    </div>

                    <?php
                    // Print remainder (text after token)
                    if ( ! empty(trim($remainder)) ) {
                        echo wp_kses_post($remainder);
                    }

                endforeach;
                ?>
            </div>

            <!-- Submit -->
            <div class="zippy-form__submit">
                <button type="submit" class="zippy-form__submit-btn">
                    <?php echo esc_html($atts['submit_text']); ?>
                </button>
            </div>

        </form>

        <script>
        (function() {
            var form = document.getElementById('<?php echo esc_js($uid); ?>');
            if (!form) return;

            // ── Live validation on blur ──
            form.querySelectorAll('[required]').forEach(function(input) {
                input.addEventListener('blur', function() { validateField(this); });
                input.addEventListener('input', function() {
                    if (this.closest('.zippy-form__field').classList.contains('has-error')) {
                        validateField(this);
                    }
                });
            });

            function validateField(input) {
                var field = input.closest('.zippy-form__field');
                if (!field) return true;

                var msg = '';
                var val = input.value.trim();
                var label = field.querySelector('label');
                var labelText = label ? label.textContent.replace('*','').trim() : 'This field';

                if (input.required && val === '' && input.type !== 'checkbox') {
                    msg = labelText + ' is required.';
                } else if (input.required && input.type === 'checkbox' && !input.checked) {
                    msg = labelText + ' must be checked.';
                } else if (input.type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                    msg = 'Please enter a valid email address.';
                } else if (input.type === 'tel' && val && !/^[+\d\s\-().]{7,}$/.test(val)) {
                    msg = 'Please enter a valid phone number.';
                }

                field.classList.toggle('has-error', !!msg);

                var errEl = field.querySelector('.zippy-field-error');
                if (msg) {
                    if (!errEl) {
                        errEl = document.createElement('span');
                        errEl.className = 'zippy-field-error';
                        errEl.setAttribute('role', 'alert');
                        field.appendChild(errEl);
                    }
                    errEl.textContent = msg;
                } else if (errEl) {
                    errEl.remove();
                }

                return !msg;
            }

            // ── Pre-submit ──
            form.addEventListener('submit', function(e) {
                var valid = true;
                form.querySelectorAll('[required]').forEach(function(input) {
                    if (!validateField(input)) valid = false;
                });
                if (!valid) {
                    e.preventDefault();
                    var first = form.querySelector('.has-error');
                    if (first) first.scrollIntoView({ behavior:'smooth', block:'center' });
                    return;
                }
                var btn = form.querySelector('.zippy-form__submit-btn');
                if (btn) { btn.disabled = true; btn.textContent = 'Sending...'; }
            });
        })();
        </script>
        <?php endif; ?>

    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('booking_form', 'zippy_booking_form');


// ============================================================
// Form Handler — runs on init (PRG pattern)
// ============================================================
add_action('init', function() {
    if ( empty($_POST['zippy_form_id']) ) return;

    $form_id = sanitize_key($_POST['zippy_form_id']);

    if (
        ! isset($_POST['zippy_form_nonce']) ||
        ! wp_verify_nonce($_POST['zippy_form_nonce'], 'zippy_form_submit_' . $form_id)
    ) return;

    $recipient = sanitize_email($_POST['zippy_form_recipient'] ?? get_option('admin_email'));
    $subject   = sanitize_text_field($_POST['zippy_form_subject'] ?? 'New Form Submission');
    $fields    = json_decode(base64_decode($_POST['zippy_form_fields'] ?? ''), true) ?: [];

    $errors  = [];
    $old     = [];
    $success = false;
    $body    = '';

    // ── Validate + collect values ──
    foreach ( $fields as $field ) {
        $name     = sanitize_key($field['name']);
        $label    = $field['label'];
        $type     = $field['type'];
        $required = $field['required'];

        if ( $type === 'checkbox' ) {
            $value   = isset($_POST[$name]) ? 'Yes' : 'No';
            $old[$name] = isset($_POST[$name]) ? '1' : '';
            if ( $required && ! isset($_POST[$name]) ) {
                $errors[] = $label . ' must be checked.';
            }
        } else {
            $raw   = $_POST[$name] ?? '';
            $value = sanitize_text_field($raw);
            $old[$name] = $value;

            if ( $required && $value === '' ) {
                $errors[] = $label . ' is required.';
                continue;
            }

            if ( $type === 'email' && $value && ! is_email($value) ) {
                $errors[] = 'Please enter a valid email for ' . $label . '.';
            }
        }

        $body .= $label . ': ' . $value . "\n";
    }

    // ── Send email ──
    if ( empty($errors) ) {
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];

        // Try to set reply-to from email field
        foreach ( $fields as $field ) {
            if ( $field['type'] === 'email' && ! empty($old[sanitize_key($field['name'])]) ) {
                $headers[] = 'Reply-To: ' . $old[sanitize_key($field['name'])];
                break;
            }
        }

        $sent = wp_mail($recipient, '[' . get_bloginfo('name') . '] ' . $subject, $body, $headers);
        $success = $sent;
        if ( ! $sent ) $errors[] = 'Message could not be sent. Please try again.';
    }

    // ── PRG: store result in transient ──
    $flash_key = 'zf_' . md5($form_id . time());
    set_transient($flash_key, [ 'errors' => $errors, 'success' => $success, 'old' => $old ], 60);

    $redirect = add_query_arg('zf', $flash_key, wp_get_referer() ?: get_permalink());
    wp_safe_redirect($redirect);
    exit;
});