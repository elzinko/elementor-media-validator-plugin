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
            $id = $element['id'];
            $image_id = $element['settings']['image']['id'];
            $url = $element['settings']['image']['url'];
            $metadata = wp_get_attachment_metadata($image_id);
            $filename = $metadata['file'] ?? 'no filename';
            $title = get_post_field('post_title', $image_id);
            $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            $legend = get_post_field('post_excerpt', $image_id);
            $description = get_post_field('post_content', $image_id);
            $edit_url = "https://media-plugin.local/wp-admin/upload.php?item={$image_id}";
            $credit = get_image_credit($metadata);
            $source = get_image_source($filename);
            $thumbnail = wp_get_attachment_image($image_id, 'thumbnail');
            $dimensions = build_dimensions($metadata, $filename, $url);

            // Add the image to the array.
            $images[] = array(
                'id_wp' => $image_id,
                'id' => $id,
                'url' => $url,
                'file' => $filename,
                'description' => $description,
                'edit_url' => $edit_url,
                'credit' => $credit,
                'source_url' => $source['url'],
                'source_site' => $source['site'],
                'thumbnail' => $thumbnail,
                'format' => pathinfo($element['settings']['image']['url'], PATHINFO_EXTENSION),
                'dimensions' => $dimensions,
                'title' => $title,
                'alt_text' => $alt_text,
                'legend' => $legend,
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
 * Build dimensions. If metadata is empty, then get dimensions from filename. If no dimensions is found in filename, then get dimensions from url. If no dimensions is found in url, then set dimensions to "A DEFINIR"
 *
 * @param [type] $metadata
 * @param [type] $filename
 * @return string
 */
function build_dimensions($metadata, $filename, $url): string
{
    if (empty($metadata) || empty($metadata['width']) || empty($metadata['height'])) {
        $dimensions = get_dimensions_from_string($filename);
    } else {
        $dimensions = "{$metadata['width']}x{$metadata['height']}";
    }

    if ($dimensions == null) {
        $dimensions = get_dimensions_from_string($url);
    }

    if ($dimensions == null) {
        $dimensions = 'A DEFINIR';
    }

    return $dimensions;
}

function get_dimensions_from_filename($filename)
{
    $dimensions = explode('x', $filename);
    if (count($dimensions) === 2) {
        return "{$dimensions[0]}x{$dimensions[1]}";
    }

    return null;
}

function get_dimensions_from_url($url)
{
    $dimensions = explode('x', $url);
    if (count($dimensions) === 2) {
        return "{$dimensions[0]}x{$dimensions[1]}";
    }

    return null;
}

function get_dimensions_from_string($string)
{
    if (preg_match('/(\d+)x(\d+)/', $string, $matches)) {
        return "{$matches[1]}x{$matches[2]}";
    }

    return null;
}

/**
 * Build name of a section
 * 1 - get H1 of section if name and css id not found
 * 2 - get css id of section if name not found
 * 3 - if anything found set name to "A DEFINIR" 
 *
 * @param [type] $section
 * @return string name of section
 */
function build_section_name($section): string|null
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

/**
 * Find anchor recursively in section.
 *
 * @param [type] $array
 * @return string
 */
function find_anchor_recursive($array): string|null
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

/**
 * Get url for section. The url is build using the first anchor found in the section
 *
 * @param [type] $section
 * @return string|null
 */
function build_section_url($section): string|null
{
    $base_url = get_site_url();

    // Find the anchor recursively
    $anchor = find_anchor_recursive($section);

    if ($anchor) {
        // Build the anchor link
        $anchor_link = $base_url . '#' . $anchor;
        return $anchor_link;
    }

    // Otherwise, return null
    return null;
}