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

            $images = find_images_in_section($section);

            foreach ($images as $image) {

                // page and bloc
                $media_info = array(
                    'page' => $page->post_title,
                    'bloc' => $bloc_name,
                    'url' => $image['url'],
                    'provenance' => 'Elementor'
                );

                // source
                $source = get_image_source($image['url']);
                $source_url = !empty($source['url']) ? esc_url($source['url']) : 'Unknown';
                $source_site = !empty($source['site']) ? esc_html($source['site']) : 'Unknown';

                // description
                $image_id = $image['id_wp'];
                $edit_url = "https://media-plugin.local/wp-admin/upload.php?item={$image_id}";
                $url = !empty($image['url']) ? esc_url($image['url']) : 'Unknown';

                if (empty($image['description'])) {
                    $description = "<a href='{$edit_url}'>Unknown, click to edit</a>";
                } else {
                    $description = "<a href='{$url}'>" . esc_html($image['description']) . "</a>";
                }

                // thumbnail
                $thumbnail = wp_get_attachment_image($image_id, 'thumbnail');

                // Add to media list
                $media_list[] = [
                    'page' => esc_html($media_info['page']),
                    'bloc' => esc_html($media_info['bloc']),
                    'bloc_url' => $bloc_url,
                    'format' => esc_html($image['format']),
                    'description' => $description,
                    'source_url' => $source_url,
                    'source_site' => $source_site,
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
