<?php

// Ajouter une nouvelle page d'option sous le menu "Outils"
function add_export_page()
{
    add_submenu_page(
        'media-validator', // Slug du menu parent
        'Exporter les données', // Titre de la page
        'Exporter', // Titre du menu
        'manage_options', // Capability
        'export_data', // Slug de la page
        'render_export_page' // Fonction de rendu
    );
}
add_action('admin_menu', 'add_export_page');


// Rendu de la page d'export
// Rendu de la page d'export
function render_export_page()
{
?>
<div class="wrap">
    <h2>Exporter les données</h2>

    <!-- formulaire pour déclencher l'export -->
    <form method="post">
        <input type="hidden" name="export_action" value="1">
        <?php submit_button('Exporter en CSV'); ?>
    </form>
</div>
<?php
}

// Gestion de l'export
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