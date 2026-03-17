
<?php 

if (!current_user_can('administrator')) {
            // Display read-only fields for non-admin users
            $mpda_consent = get_user_meta($user->ID, 'mpda_consent', true);
            $marketing_consent = get_user_meta($user->ID, 'marketing_consent', true);
        ?>
            <h3><?php _e("User Consent Information", "mpda-consent"); ?></h3>
            <table class="form-table custom-consent-table" style="width: 30%;">
                <tr>
                    <th>
                        <label for="mpda_consent" style="font-weight: bold;"><?php _e("PDPA Consent", 'mpda-consent'); ?></label>
                    </th>
                    <th>
                        <label for="marketing_consent" style="font-weight: bold;"><?php _e("Marketing Materials Consent", 'mpda-consent'); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <p><?php echo esc_html($mpda_consent === 'yes' ? 'Yes' : 'No'); ?></p>
                    </td>
                    <td>
                        <p><?php echo esc_html($marketing_consent === 'yes' ? 'Yes' : 'No'); ?></p>
                    </td>
                </tr>
            </table>
        <?php
        } else {
            // Display editable fields for administrator
        ?>
            <h3><?php _e("User Consent Information", "mpda-consent"); ?></h3>
            <table class="form-table custom-consent-table" style="width: 30%;">
                <tr>
                    <th>
                        <label for="mpda_consent" style="font-weight: bold;"><?php _e("PDPA Consent", 'mpda-consent'); ?></label>
                    </th>
                    <th>
                        <label for="marketing_consent" style="font-weight: bold;"><?php _e("Marketing Materials Consent", 'mpda-consent'); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <select name="mpda_consent" id="mpda_consent">
                            <option value="yes" <?php selected($mpda_consent, 'yes'); ?>><?php _e('Yes', 'mpda-consent'); ?></option>
                            <option value="no" <?php selected($mpda_consent, 'no'); ?>><?php _e('No', 'mpda-consent'); ?></option>
                        </select>
                    </td>
                    <td>
                        <select name="marketing_consent" id="marketing_consent">
                            <option value="yes" <?php selected($marketing_consent, 'yes'); ?>><?php _e('Yes', 'mpda-consent'); ?></option>
                            <option value="no" <?php selected($marketing_consent, 'no'); ?>><?php _e('No', 'mpda-consent'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
<?php
        }
?>