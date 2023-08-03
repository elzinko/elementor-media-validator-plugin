<?php

function get_elementor_posts()
{
    $args = array(
        'post_type' => 'page', // Changez ceci pour le type de post que vous voulez récupérer. 'any' obtiendra tous les types de post.
        'posts_per_page' => -1, // Obtenir tous les posts. Attention à ne pas le faire sur un site avec beaucoup de posts, car cela pourrait causer des problèmes de performance.
        'meta_key' => '_elementor_edit_mode',
        'meta_value' => 'builder'
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        return $query->posts;
    }

    wp_reset_postdata();

    return array();
}

/**
 * Get elementor data from given post. If PLUGIN_DEBUG is enabled, then data are print on screen and saved in a file.
 *
 * @param [type] $post_id the post id
 * @return mixed
 */
function get_elementor_data($post_id): mixed
{
    $elementor_data = get_post_meta($post_id, '_elementor_data', true);

    $my_plugin_folder = WP_PLUGIN_DIR . '/elementor-media-validation-plugin';

    if (PLUGIN_DEBUG == true) {
        if (is_dir($my_plugin_folder)) {
            file_put_contents($my_plugin_folder . "/samples/accueil.json", $elementor_data);
            echo '<div>';
            echo $elementor_data;
            echo '</div>';
        }
    }

    $elementor_data = json_decode($elementor_data, true);

    return $elementor_data;
}
/**
 * Find images in section
 *
 * @param [type] $section the section
 * @return mixed
 */
function find_images_in_section($section): mixed
{
    $images = array();

    // check recursively for images
    foreach ($section['elements'] as $element) {
        // Check if the element is a widget and if it's an image widget.
        if ($element['elType'] === 'widget' && $element['widgetType'] === 'image') {
            $image_id = $element['settings']['image']['id'];
            $url = $element['settings']['image']['url'];
            $metadata = wp_get_attachment_metadata($image_id);

            // description
            $description = get_post_field('post_content', $image_id);

            // source
            $source = get_image_source($url);
            // TODO move next 2 lines in get_image_source
            $source_url = !empty($source['url']) ? esc_url($source['url']) : 'Unknown';
            $source_site = !empty($source['site']) ? esc_html($source['site']) : 'Unknown';

            // edit url
            $edit_url = "https://media-plugin.local/wp-admin/upload.php?item={$image_id}";

            // thumbnail
            $thumbnail = wp_get_attachment_image($image_id, 'thumbnail');


            $images[] = array(
                'id_wp' => $image_id,
                'id' => $element['id'],
                'url' => $element['settings']['image']['url'],
                'file' => $metadata['file'],
                'description' => $description,
                'format' => pathinfo($element['settings']['image']['url'], PATHINFO_EXTENSION),
                'dimensions' => "{$metadata['width']}x{$metadata['height']}",
                'credit' => get_image_credit($metadata),
                'source_url' => $source_url,
                'source_site' => $source_site,
                'thumbnail' => $thumbnail,
                'edit_url' => $edit_url,

            );
        }

        // Si l'élément a des enfants, recherchez les images dans ces enfants.
        if (!empty($element['elements'])) {
            $images = array_merge($images, find_images_in_section($element));
        }
    }

    return $images;
}


function get_image_credit($metadata)
{
    $credits = $metadata['image_meta']['credit'];
    $file = $metadata['file'];

    if (!empty($credit)) {
        switch ($credit) {
            case 'get image':
                return 'gettyimage';
            case 'shutterstock':
                return 'shutterstock';
        }
    } else if (!empty($file)) {
        if (strpos($file, 'gettyimage') !== false) {
            return 'gettyimage';
        } else if (strpos($file, 'shutterstock') !== false) {
            return 'shutterstock';
        }
    }
}

/**
 * Get name of a section
 * 1 - get H1 of section if name and css id not found
 * 2 - get css id of section if name not found
 * 3 - if anything found set name to "A DEFINIR" 
 *
 * @param [type] $section
 * @return string name of section
 */
function get_bloc_name_from_section($section): String
{
    $anchor_name = find_anchor_recursive($section);
    if (!empty($anchor_name)) {
        return $anchor_name;
    }

    // Parcourir les éléments de la section
    foreach ($section['elements'] as $element) {
        // Vérifier si l'élément est un widget de type heading de taille h1
        if (
            $element['elType'] === 'widget' &&
            $element['widgetType'] === 'heading' &&
            $element['settings']['header_size'] === 'h1'
        ) {
            // Renvoyer le titre du widget
            return $element['settings']['title'];
        }
    }

    // Si aucun widget heading de taille h1 n'a été trouvé, utiliser l'ID CSS de la section (si disponible)
    if (!empty($section['settings']['_element_id'])) {
        return $section['settings']['_element_id'];
    }



    // Si aucune des conditions ci-dessus n'est remplie, renvoyer 'A DEFINIR'
    return 'A DEFINIR';
}

function find_anchor_recursive($array)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = find_anchor_recursive($value);
            if ($result) return $result;
        } else {
            if ($key == 'widgetType' && $value == 'menu-anchor') {
                $anchor = $array['settings']['anchor'] ?? null;
                if (!empty($anchor)) return $anchor;
            }
        }
    }
    return null;
}

function get_url_for_section($section)
{
    $base_url = get_site_url(); // récupère l'URL de base du site

    // Rechercher récursivement l'ancre
    $anchor = find_anchor_recursive($section);

    if ($anchor) {
        // Construire le lien d'ancre
        $anchor_link = $base_url . '#' . $anchor;
        return $anchor_link;
    }

    // Si aucun menu-anchor avec un anchor n'a été trouvé, retourner null
    return null;
}