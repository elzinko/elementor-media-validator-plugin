<?php

function build_media_data()
{
    $elementor_pages = get_elementor_posts();

    $media_list = [];

    foreach ($elementor_pages as $page) {
        $elementor_page_data = get_elementor_data($page->ID);

        foreach ($elementor_page_data as $section) {

            // bloc
            $bloc_name = build_section_name($section);
            $bloc_url = build_section_url($section);

            // images
            $images = find_images_in_section($section);

            foreach ($images as $image) {

                // valid
                $validation = column_validation($image['id_wp']);

                // page
                $page_name = $page->post_title;

                // bloc
                if (empty($bloc_url)) {
                    $bloc = $bloc_name;
                } else {
                    $bloc = "<a href='$bloc_url'>$bloc_name</a>";
                }

                // description
                $description = column_description($image);

                // Title
                $title = column_title($image);

                // credit
                $credit = $image['credit'];
                if ($credit == null) {
                    $credit = "No credit";
                } else if (mb_strlen($credit) > 14) {
                    $credit = mb_substr($credit, 0, 14) . "...";
                }

                // thumbnail
                $thumbnail = '';
                if (empty($image['url'])) {
                    $thumbnail = $image['thumbnail'];
                } else {
                    $thumbnail = "<a href='" . $image['url'] . "'>" . $image['thumbnail'] . "</a>";
                }

                // source
                $source = $image['source_site'];
                if (empty($source)) {
                    $source = "No source";
                } else {
                    $source = "<a href='{$image['source_url']}'>$source</a>";
                }

                $media_list[] = [
                    'validation' => $validation,
                    'page' => $page_name,
                    'bloc' => $bloc,
                    'format' => $image['format'],
                    'description' => $description,
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
 * Generates the validation column checkbox for each row
 *
 * @param $image
 * @return string
 */
function column_validation($image_id): string
{
    // Retrieve the meta_data value
    $media_validation = get_post_meta($image_id, 'media_validation', true);

    // Check the box if the value is "1"
    $checked = $media_validation == "1" ? "checked" : "";

    return sprintf(
        '<input type="checkbox" name="validation" %s data-id="%s" />',
        $checked,
        $image_id
    );
}


function column_description($image): string
{
    $description = $image['description'];
    if (empty($description)) {
        $description = "<a href='{$image['edit_url']}'>add description</a>";
    } else {
        if (mb_strlen($description) > 30) {
            $description = mb_substr($description, 0, 30) . "...";
        }
        $description = "<a href='{$image['edit_url']}'>$description</a>";
    }
    return $description;
}

function column_title($image): string
{
    $title = $image['title'];
    if (empty($title)) {
        $title = "<a href='{$image['edit_url']}'>add title</a>";
    } else if (mb_strlen($title) > 14) {
        $title = mb_substr($title, 0, 14) . "...";
    }
    return $title;
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
            'provenance' => 'à déterminer',
            'validation' => false,
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
    return $metadata['image_meta']['credit'];
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