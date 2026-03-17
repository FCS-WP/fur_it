<?php
$current_user = wp_get_current_user();
$is_admin = in_array('administrator', $current_user->roles);

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $user = get_user_by('ID', $user_id);
} else {
    $user = $current_user;
}
$expiry_date = get_the_author_meta('expiry_date', $user->ID);
$formatted_expiry_date = $expiry_date ? date('d/m/Y', strtotime($expiry_date)) : '';

$creation_date = get_the_author_meta('user_registered', $user->ID);
$formatted_creation_date = date('d/m/Y', strtotime($creation_date));
?>
<h3><?php _e('Account Details', 'zippy-core-sg'); ?></h3>
<table class="form-table">
    <tr>
        <th><label for="creation_date"><?php _e('Date Joined', 'zippy-core-sg'); ?></label></th>
        <td>
            <p class="form-control-static"><?php echo esc_html($formatted_creation_date); ?></p>
        </td>
    </tr>
    <tr>
        <th><label for="expiry_date"><?php _e('Retention due date', 'zippy-core-sg'); ?></label></th>
        <td>
            <?php if ($is_admin) : ?>
                <input type="text" name="expiry_date" id="expiry_date" value="<?php echo esc_attr($formatted_expiry_date); ?>" class="regular-text datepicker" />
                <p class="description"><?php _e('Set the retention due date for this user\'s account. Format: dd/mm/yyyy', 'zippy-core-sg'); ?></p>
            <?php else : ?>
                <p class="form-control-static"><?php echo esc_html($formatted_expiry_date); ?></p>
            <?php endif; ?>
        </td>
    </tr>
</table>



