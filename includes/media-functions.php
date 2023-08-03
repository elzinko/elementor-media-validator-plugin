<?php

function build_media_data()
{
    $elementor_pages = get_elementor_posts();

    $media_list = [];

    foreach ($elementor_pages as $page) {
        $elementor_page_data = get_elementor_data($page->ID);

        foreach ($elementor_page_data as $section) {

            // bloc
            $bloc_name = get_bloc_name_from_section($section);
            $bloc_url = get_url_for_section($section);

            // images
            $images = find_images_in_section($section);
            foreach ($images as $image) {

                // page
                $page_name = $page->post_title;

                // bloc
                if (empty($bloc_url)) {
                    $bloc = $bloc_name;
                } else {
                    $bloc = "<a href='$bloc_url'>$bloc_name</a>";
                }

                // description
                $description = $image['description'];
                if (empty($image['description'])) {
                    $description = "<a href='{$image['edit_url']}'>Click to add description</a>";
                } else {
                    if (mb_strlen($description) > 30) {
                        $description = mb_substr($description, 0, 30);
                        $description .= "...";
                    }
                }

                // thumbnail
                $thumbnail = '';
                if (empty($image['url'])) {
                    $thumbnail = $image['thumbnail'];
                } else {
                    $thumbnail = "<a href='" . $image['url'] . "'>" . $image['thumbnail'] . "</a>";
                }

                $media_list[] = [
                    'page' => $page_name,
                    'bloc' => $bloc,
                    'format' => $image['format'],
                    'description' => $description,
                    'dimentions' => $image['dimensions'],
                    'source_url' => $image['source_url'],
                    'source_site' => $image['source_site'],
                    'thumbnail' => $thumbnail,
                ];
            }
        }
    }

    return $media_list;
}


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

        $media_info[] = array(
            'page' => 'à déterminer',
            'bloc' => 'à déterminer',
            'url' => wp_get_attachment_url($file->ID),
            'provenance' => 'à déterminer',
            'validation' => false,
        );
    }

    return $media_info;
}


function get_image_source($image_url)
{
    // Extrait le nom du fichier de l'URL de l'image.
    $filename = basename($image_url);

    // Utilisez une expression régulière pour extraire le nom du site et le numéro de l'image pour iStockphoto.
    if (preg_match('/^([a-z]+)-(\d+)-\d+x\d+\.\w+$/', $filename, $matches)) {
        // Construit l'URL de la source de l'image.
        $source_url = sprintf('https://www.%s.com/fr/search/2/image?family=phrase=%s', $matches[1], $matches[2]);

        // Renvoie le nom du site et l'URL de la source.
        return array(
            'site' => $matches[1],
            'url' => $source_url,
        );
    }

    // Utilisez une expression régulière pour extraire le numéro de l'image pour Envato.
    if (preg_match('/^([a-z-]+)-(\d{6})\w\.\w+$/', $filename, $matches)) {
        // Construit l'URL de la source de l'image.
        $source_url = sprintf('https://elements.envato.com/elements-api/items/%s.json?languageCode=fr', $matches[2]);

        // Renvoie le nom du site et l'URL de la source.
        return array(
            'site' => 'envato',
            'url' => $source_url,
        );
    }

    // Si le nom du fichier ne correspond pas à l'un des formats attendus, renvoie un tableau vide.
    return array();
}