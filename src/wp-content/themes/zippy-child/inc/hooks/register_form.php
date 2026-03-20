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