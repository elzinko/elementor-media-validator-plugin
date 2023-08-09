<?php

define('ACTION_LOG_TABLE_NAME', 'action_log');

function render_history_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . ACTION_LOG_TABLE_NAME;

    // Get the log data
    $logs = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<div class="wrap"><h2>Historique des Actions sur les Médias</h2>';

    // Display the logs in a table
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Thumbnail</th><th>Utilisateur</th><th>Clé</th><th>Valeur</th><th>Date et Heure</th></tr></thead>';
    echo '<tbody>';
    foreach ($logs as $log) {
        $user_info = get_userdata($log->user_id);
        $log->user_name = $user_info ? $user_info->display_name : 'Inconnu';

        // Obtenez l'URL du thumbnail à partir de l'ID du média
        $thumbnail_id = $log->media_id;
        $thumbnail_src = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
        $thumbnail = '';
        if ($thumbnail_src) {
            $item_url = admin_url("upload.php?item=" . $thumbnail_id);
            $thumbnail = "<a href='" . $item_url . "'><img src='" . $thumbnail_src[0] . "' alt='Thumbnail'></a>";
        }

        echo "<tr><td>{$thumbnail}</td><td>{$log->user_name}</td><td>{$log->log_key}</td><td>{$log->log_value}</td><td>{$log->action_time}</td></tr>";
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}



function add_history_page()
{
    add_submenu_page(
        'media-validator',
        'History',
        'History',
        'manage_options',
        'history',
        'render_history_page'
    );
}
add_action('admin_menu', 'add_history_page');


function create_media_log_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . ACTION_LOG_TABLE_NAME;

    // Charset
    $charset_collate = $wpdb->get_charset_collate();

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

        // SQL for creating table
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            media_id mediumint(9),
            log_key varchar(255) NOT NULL,
            log_value varchar(255) NOT NULL,
            action_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Including the upgrade library for creating/updating tables
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Creating the table
        $result = dbDelta($sql);
        error_log(print_r($result, TRUE));
        add_option('media_log_db_version', '1.0');
    }
}


function log_media_action($media_id, $key, $value)
{
    global $wpdb;
    $table_name = $wpdb->prefix . ACTION_LOG_TABLE_NAME;

    $user_id = get_current_user_id();

    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'media_id' => $media_id,
            'log_key' => $key,
            'log_value' => $value,
        )
    );
}
