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

// Fonctions pour interagir avec Elementor
function get_elementor_data($post_id)
{
    // Obtenez les données d'Elementor pour le post.
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


    // Convertissez les données JSON en un tableau PHP.
    $elementor_data = json_decode($elementor_data, true);

    return $elementor_data;
}

function find_images_in_section($section)
{
    $images = array();

    // Parcourez les éléments de la section pour trouver les widgets avec des images.
    foreach ($section['elements'] as $element) {
        // Vérifiez si l'élément est un widget et si son type est 'image'.
        if ($element['elType'] === 'widget' && $element['widgetType'] === 'image') {
            $images[] = array(
                'url' => $element['settings']['image']['url'],
                'id' => $element['id'], // Vous pouvez utiliser cet ID pour créer une ancre.
            );
        }

        // Si l'élément a des enfants, recherchez les images dans ces enfants.
        if (!empty($element['elements'])) {
            $images = array_merge($images, find_images_in_section($element));
        }
    }

    return $images;
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
