<?php
$retention_period = get_option('mpda_consent_time');
?>

<select name="mpda_consent_time" id="mpda_consent_time">
    <option value="">Choose Year</option>
    <?php
    for ($i = 1; $i <= 10; $i++) {
        echo '<option' . selected($retention_period, $i)  . ' value=' . $i . '>' . $i . ' Year' . '</option>';
    } ?>

</select>
<?php ?>