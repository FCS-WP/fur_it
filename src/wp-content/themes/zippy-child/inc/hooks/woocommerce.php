<?php

function import_services_from_csv()
{

    $file = get_stylesheet_directory() . '/demo_services.csv';


    $import_version = 'v3'; // 🔥 change this when you update CSV
    $saved_version  = get_option('services_import_version');


    if ($saved_version === $import_version) {
        echo "Already imported (version: $import_version)";
        return;
    }

    if (!file_exists($file)) return;

    $rows = array_map('str_getcsv', file($file));
    $header = array_shift($rows);

    foreach ($rows as $row) {

        $data = array_combine($header, $row);

        // Create post
        $content_raw = $data['content'] ?? '';
        $content = null;
        // Turn on when import multiline content
        // $content = '<ul><li>' .
        //     implode('</li><li>', array_map('trim', explode('|', $content_raw)))
        //     . '</li></ul>';

        $post_id = wp_insert_post([
            'post_type'   => 'services',
            'post_title'  => $data['title'],
            'post_content' => $content ?? $data['content'],
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) continue;

        // Taxonomy
        if (!empty($data['category'])) {
            wp_set_object_terms($post_id, $data['category'], 'services_category');
        }

        // Meta
        update_post_meta($post_id, '_price', $data['price']);
        update_post_meta($post_id, '_price_unit', $data['price_unit']);
        update_post_meta($post_id, '_btn_url', $data['btn_url']);
        update_post_meta($post_id, '_icon', $data['icon']);
    }

    update_option('services_import_version', $import_version);

    echo "Import done! Version: $import_version";
}

add_action('init', function () {
    if (isset($_GET['import_services'])) {
        import_services_from_csv();
        exit;
    }
});
