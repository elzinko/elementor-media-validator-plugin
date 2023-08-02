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
