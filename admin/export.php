<?php

/**
 * Add export page under the main plugin menu
 *
 * @return void
 */
function add_export_page()
{
    add_submenu_page(
        'media-validator',
        'Export data',
        'Export',
        'manage_options',
        'export_data',
        'render_export_page'
    );
}
add_action('admin_menu', 'add_export_page');


/**
 * Export page render function
 *
 * @return void
 */
function render_export_page()
{
?>
<div class="wrap">
    <h2>Export media data</h2>

    <!-- form to trigger export -->
    <form method="post">
        <input type="hidden" name="export_action" value="1">
        <?php submit_button('CSV export'); ?>
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
    // Vérifie si l'action d'export a été déclenchée
    if (empty($_POST['export_action'])) {
        return;
    }

    // Récupération des données à exporter
    $data = build_media_data();

    // Définition de l'en-tête pour télécharger le fichier CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export.csv"');

    // Ouverture du flux de sortie
    $output = fopen('php://output', 'w');

    // Écriture de l'en-tête du CSV
    fputcsv($output, array_keys($data[0]));

    // Écriture des données
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    // Fermeture du flux
    fclose($output);

    // Arrêt du script pour ne pas afficher la page d'administration
    die();
}
add_action('admin_init', 'handle_export_action');