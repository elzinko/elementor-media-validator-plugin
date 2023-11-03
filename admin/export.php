<?php

/**
 * Add export page under the main plugin menu
 *
 * @return void
 */
function add_menu_export()
{
    if (current_user_can('manage_options') || current_user_can('emvp_access_export')) {
        add_submenu_page(
            'media-validator',
            'Export data',
            'Export',
            'emvp_access_export',
            'export',
            'render_page_export'
        );
    }
}
add_action('admin_menu', 'add_menu_export');


/**
 * Export page render function
 *
 * @return void
 */
// Render the export page
function render_page_export()
{
?>
<div class="wrap">
    <h2>Exporter les données</h2>

    <!-- Formulaire pour déclencher l'export -->
    <form method="post">
        <label for="send_email_checkbox">
            <input type="checkbox" id="send_email_checkbox" name="send_email" value="1">
            Envoyer par e-mail
        </label>
        <div id="email_field_container" style="display:none;">
            <input type="email" name="email" id="email">
        </div>
        <!-- <label for="email">Adresse e-mail : </label> -->
        <input type="hidden" name="export_action" value="1">
        <?php submit_button('Générer CSV'); ?>
    </form>
</div>
<?php
}


/**
 * Export handler
 *
 * @return void
 */
function handle_export_action()
{
    // Check if export action is triggered
    if (empty($_POST['export_action'])) {
        return;
    }

    // Get export data
    $data = build_media_data();


    // get the wordpress upload directory
    $temp_dir = wp_upload_dir()['basedir'] . '/temp_exports';

    // check if the directory exists
    wp_mkdir_p($temp_dir);

    // Create a unique file name
    $date = new DateTime();
    $filename = 'export_' . $date->format('YmdHis') . '.csv';
    $file_path = $temp_dir . '/' . $filename;

    // open the file
    $output = fopen($file_path, 'w');

    // Write the header
    fputcsv($output, array_keys($data[0]));

    // Write the data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    // Close the file
    fclose($output);

    // upload file
    $file_url = wp_upload_dir()['baseurl'] . '/temp_exports/' . $filename;

    // Display success message with download link
    add_action('admin_notices', function () use ($file_url, $filename) {
    ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e('Le fichier CSV a été généré avec succès!', 'media-info-tracker'); ?>
        <a href="<?php echo $file_url; ?>">Télécharger <?php echo $filename; ?></a>
    </p>
</div>
<?php
    });

    // If the user wants to send the file by e-mail
    if (isset($_POST['send_email']) && $_POST['send_email'] == 1) {
        $to = $_POST['email'];
        $subject = 'Votre fichier CSV exporté';
        $message = "Veuillez trouver ci-joint le fichier CSV exporté : " . $filename;
        $headers = array();
        $attachments = array($file_path);
        wp_mail($to, $subject, $message, $headers, $attachments);

        // Display success message
        add_action('admin_notices', function () {
        ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e('Le fichier CSV a été envoyé par e-mail avec succès!', 'media-info-tracker'); ?></p>
</div>
<?php
        });
    }
}

add_action('admin_init', 'handle_export_action');