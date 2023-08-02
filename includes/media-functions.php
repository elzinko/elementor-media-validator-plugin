<?php
// Obtenir les informations des médias
function get_media_info()
{

    global $wpdb;

    $query = "SELECT * FROM {$wpdb->posts} WHERE post_type = 'attachment'";
    $media = $wpdb->get_results($query);

    $media_info = array();

    foreach ($media as $file) {
        // Extraction des informations du bloc.
        $block_query = "SELECT * FROM {$wpdb->postmeta} WHERE post_id = {$file->ID}";
        $block = $wpdb->get_results($block_query);

        // Ici, il faudra parser l'information du bloc pour récupérer l'information sur la provenance.
        // Cette étape dépendra de comment cette information est stockée. 

        $media_info[] = array(
            'page' => 'à déterminer', // dépendra de comment vous pouvez lier le média à une page
            'bloc' => 'à déterminer', // dépendra de comment vous pouvez lier le média à un bloc
            'url' => wp_get_attachment_url($file->ID),
            'provenance' => 'à déterminer', // dépendra de comment l'information de la provenance est stockée
            'validation' => false, // Initialement, l'image n'est pas validée
        );
    }

    return $media_info;
}


function get_image_source($image_url)
{
    // Extrait le nom du fichier de l'URL de l'image.
    $filename = basename($image_url);

    // Utilisez une expression régulière pour extraire le nom du site et le numéro de l'image.
    if (preg_match('/^([a-z]+)-(\d+)-\d+x\d+\.\w+$/', $filename, $matches)) {
        // Construit l'URL de la source de l'image.
        $source_url = sprintf('https://www.%s.com/fr/search/2/image?family=phrase=%s', $matches[1], $matches[2]);

        // Renvoie le nom du site et l'URL de la source.
        return array(
            'site' => $matches[1],
            'url' => $source_url,
        );
    }

    // Si le nom du fichier ne correspond pas au format attendu, renvoie un tableau vide.
    return array();
}
