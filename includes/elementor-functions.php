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

    if (is_dir($my_plugin_folder)) {
        file_put_contents($my_plugin_folder . "/samples/accueil.json", $elementor_data);
        echo '<div>';
        echo $elementor_data;
        echo '</div>';
    }


    // Convertissez les données JSON en un tableau PHP.
    $elementor_data = json_decode($elementor_data, true);

    return $elementor_data;
}

function find_images_in_elementor_data($elementor_data)
{
    $images = array();

    // Parcourez les données pour trouver les blocs avec des images.
    foreach ($elementor_data as $element) {
        // Vérifiez si l'élément est un widget et si son type est 'image'.
        if (isset($element['widgetType']) && $element['widgetType'] === 'image') {
            $images[] = array(
                'url' => $element['settings']['image']['url'],
                'id' => $element['id'], // Vous pouvez utiliser cet ID pour créer une ancre.
            );
        }

        // Si l'élément a des enfants, recherchez les images dans ces enfants.
        if (!empty($element['elements'])) {
            $images = array_merge($images, find_images_in_elementor_data($element['elements']));
        }
    }

    return $images;
}

function findParentBlock($elements, $title = "A DEFINIR", $css_id = "A DEFINIR")
{
    foreach ($elements as $element) {
        if ($element['elType'] === 'widget' && isset($element['widgetType']) && $element['widgetType'] === 'heading' && isset($element['settings']['title'])) {
            $title = $element['settings']['title']; // On récupère le titre du bloc si il existe
        }

        if ($element['elType'] === 'section' && isset($element['settings']['_element_id'])) {
            $css_id = $element['settings']['_element_id']; // On récupère l'ID du CSS du bloc si il existe
        }

        if ($element['elType'] === 'widget' && isset($element['widgetType']) && $element['widgetType'] === 'image') {
            $image_id = $element['id'];
            $image_title = isset($element['settings']['image']['alt']) ? $element['settings']['image']['alt'] : "Non défini";
            echo "Pour l'image avec l'ID: $image_id et le titre: $image_title, le bloc est : $title ($css_id)\n"; // Affiche le nom du bloc
        }

        if (!empty($element['elements'])) {
            findParentBlock($element['elements'], $title, $css_id); // Si l'élément a des sous-éléments, on répète la fonction avec les mêmes paramètres
        }
    }
}
