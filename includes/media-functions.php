<?php

function build_media_data($filter = 'all')
{
    $elementor_pages = get_elementor_posts();
    // var_dump($elementor_pages);
    // die();

    $media_list = [];

    foreach ($elementor_pages as $page) {
        $elementor_page_data = get_elementor_data($page->ID);

        foreach ($elementor_page_data as $section) {

            // bloc
            $bloc_name = build_section_name($section);
            $bloc_url = build_section_url($page->ID  , $section);

            // images
            $images = find_images_in_section($section);

            foreach ($images as $image) {

                // If image is filtered (validated / not_validated) in the admin page, skip it.
                if (is_filtered($image, $filter)) {
                    continue;
                }

                $validation = column_validation($image['id_wp']);

                $page_name = $page->post_title;

                $bloc = column_bloc($bloc_name, $bloc_url);

                $description = column_description($image);

                $title = column_title($image);

                $legend = column_legend($image);

                $atl_text = column_alt_text($image);

                $credit = column_credit($image);

                $thumbnail = column_thumbnail($image);

                $source = column_source($image);

                $media_list[] = [
                    'validation' => $validation,
                    'page' => $page_name,
                    'bloc' => $bloc,
                    'format' => $image['format'],
                    'description' => $description,
                    'alt_text' => $atl_text,
                    'legend' => $legend,
                    'dimentions' => $image['dimensions'],
                    'source' => $source,
                    'credit' => $credit,
                    'thumbnail' => $thumbnail,
                    'file' => $image['file'],
                    'title' => $title,
                ];
            }
        }
    }

    return $media_list;
}

/**
 * Check if the image is filtered 
 *
 * @param [type] $image
 * @param [type] $filter
 * @return boolean
 */
function is_filtered($image, $filter) {
    global $wpdb;
    $image_id = $image['id_wp'];
    $actions_table_name = $wpdb->prefix . 'emvp_media_actions';

    // Obtenez la dernière action de validation pour l'image spécifiée
    $query = $wpdb->prepare(
        "SELECT action_type FROM $actions_table_name 
         WHERE media_id = %d 
         ORDER BY action_date DESC LIMIT 1",
         $image_id
    );

    // Log pour déboguer la requête SQL
    error_log('SQL Query: ' . $query);

    $last_action = $wpdb->get_var($query);

    // Si le filtre est 'validated' et que la dernière action n'est pas 'validate', filtrez-le.
    if ($filter === 'validated' && $last_action !== 'validate') {
        return true;
    }
    // Si le filtre est 'not_validated' et que la dernière action est 'validate', filtrez-le.
    if ($filter === 'not_validated' && $last_action === 'validate') {
        return true;
    }

    // Si aucune des conditions ci-dessus n'est remplie, l'image n'est pas filtrée.
    return false;
}


function column_source($image): string
{
    $source = $image['source_site'];
    if (empty($source)) {
        $source = "No source";
    } else {
        $source = "<a href='{$image['source_url']}'>$source</a>";
    }

    return $source;
}

function column_thumbnail($image): string
{
    $thumbnail =  '';
    if (empty($image['url'])) {
        $thumbnail = $image['thumbnail'];
    } else {
        $thumbnail = "<a href='" . $image['url'] . "'>" . $image['thumbnail'] . "</a>";
    }
    return $thumbnail;
}

function column_bloc($bloc_name, $bloc_url): string
{
    if (empty($bloc_url)) {
        $bloc = $bloc_name;
    } else {
        $bloc = "<a href='$bloc_url'>$bloc_name</a>";
    }
    return $bloc;
}

function column_credit($image): string
{
    $credit = $image['credit'];
    if ($credit == null) {
        $credit = "No credit";
    } else if (mb_strlen($credit) > 14) {
        $credit = mb_substr($credit, 0, 14) . "...";
    }
    return $credit;
}

function column_legend($image): string
{
    $legend = $image['legend'];
    if (empty($legend)) {
        return "<a href='{$image['edit_url']}'>add legend</a>";
    } else {
        return "<a href='#' title='{$legend}'><input type='checkbox' checked disabled /></a>";
    }
}

function column_alt_text($image): string
{
    $alt_text = $image['alt_text'];
    if (empty($alt_text)) {
        return "<a href='{$image['edit_url']}'>add alt text</a>";
    } else {
        return "<a href='{$image['edit_url']}' title='{$alt_text}'><input type='checkbox' checked disabled /></a>";
    }
}

function column_validation($image_id): string
{
    global $wpdb;
    $media_actions_table = $wpdb->prefix . 'emvp_media_actions';

    // Query to get the most recent 'validate' or 'unvalidate' action
    $query = $wpdb->prepare(
        "SELECT action_type FROM $media_actions_table WHERE media_id = %d AND (action_type = 'validate' OR action_type = 'invalidate') ORDER BY action_date DESC LIMIT 1",
        $image_id
    );
    $last_action_type = $wpdb->get_var($query);

    // Determine the checkbox state based on the last action
    $checked = $last_action_type === 'validate' ? "checked" : "";
    
    // Determine if the checkbox should be disabled
    $current_user = wp_get_current_user();
    $disabled = ($last_action_type === 'validate' && !isAdminOrAgency()) ? "disabled" : "";
    
    return sprintf(
        '<input type="checkbox" name="validation" data-id="%s" %s %s />',
        $image_id,
        $checked,
        $disabled
    );
}


function isAdminOrAgency() {
    return current_user_can('manage_options') || current_user_can('emvp_agency');
}


function column_description($image): string
{
    $description = $image['description'];
    if (empty($description)) {
        return "<a href='{$image['edit_url']}'>add description</a>";
    } else {
        return "<a href='{$image['edit_url']}' title='{$description}'><input type='checkbox' checked disabled /></a>";
    }
}

function column_title($image): string
{
    $title = $image['description'];
    if (empty($title)) {
        return "<a href='{$image['edit_url']}'>add title</a>";
    } else {
        return "<a href='{$image['edit_url']}' title='{$title}'><input type='checkbox' checked disabled /></a>";
    }
}

/**
 * Get all wordpress pages media data
 *
 * @return array All wordpress pages media data
 */
function get_wp_media_info(): array
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
        );
    }

    return $media_info;
}


/**
 * Retrieve image source from filename
 *
 * @param string $filename image filename
 * @return array ['site' => 'sitename', 'url' => 'site_image_url',]
 */
function get_image_source($filename): array
{

    if (!empty($filename)) {
        // istockphoto
        if (strpos($filename, 'istockphoto') !== false) {
            if (preg_match('/istockphoto-(\d+)-/', $filename, $matches)) {
                return array(
                    'site' => 'istockphoto',
                    'url' => build_istockphoto_url($matches[1]),
                );
            }
        }
        // gettyimages
        else if (strpos($filename, 'gettyimages') !== false) {

            if (preg_match('/gettyimages-(\d+)-/', $filename, $matches)) {
                return array(
                    'site' => 'gettyimages',
                    'url' => build_gettyimage_url($matches[1]),
                );
            }
        }
    }
    return array(
        'site' => null,
        'url' => null,
    );
}


function get_image_credit($metadata)
{
    return $metadata['image_meta']['credit'] ?? null;
}

/**
 * Build istockphoto url from image id
 *
 * @param [type] $image_id
 * @return string istockphoto url
 */
function build_istockphoto_url($image_id): string
{
    return "https://www.istockphoto.com/fr/search/2/image?phrase=$image_id";
}

/**
 * Build Getty Images url from image id
 *
 * @param [type] $image_id
 * @return string gettyimages url
 */
function build_gettyimage_url($image_id): string
{
    return "https://www.gettyimages.fr/search/2/image?phrase=$image_id";
}