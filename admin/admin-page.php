<?php

/** 
 * Ajouter la page d'administration au menu 
 * @return void
 */

function media_info_admin_page()
{
    add_menu_page('Media Validator', 'Media Validator', 'manage_options', 'media-info-tracker', 'display_media_info_page');
}

add_action('admin_menu', 'media_info_admin_page');

// Afficher la page d'administration
function display_media_info_page()
{

    $elementor_pages = get_elementor_posts();

    echo '
    <style>
        .info-table {
            border-collapse: collapse;
            width: 100%;
        }
        
        .info-table th, .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .info-table th {
            padding-top: 12px;
            padding-bottom: 12px;
            background-color: #4CAF50;
            color: white;
        }
    </style>';

    echo '<h1>Elementor Media Validation Plugin</h1>';
    echo '<table class="info-table">';

    echo '<thead>';
    echo '<tr>';
    echo '<th>Page</th>';
    echo '<th>Bloc</th>';
    echo '<th>Format</th>';
    echo '<th>Description</th>';
    echo '<th>Provider</th>';
    echo '<th>Image</th>';
    echo '</tr>';
    echo '</thead>';

    echo '<tbody>';
    foreach ($elementor_pages as $page) {

        $elementor_page_data = get_elementor_data($page->ID);

        foreach ($elementor_page_data as $section) {

            // bloc
            $bloc_name = get_bloc_name_from_section($section);
            $section_url = get_url_for_section($section);

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

                // print
                echo '<tr>';
                echo '<td>' . esc_html($media_info['page']) . '</td>';
                // echo '<td>' . esc_html($media_info['bloc']) . '</td>';
                echo "<td><a href='{$section_url}'>" . esc_html($media_info['bloc']) . "</a></td>";
                echo '<td>' . esc_html($image['format']) . '</td>';
                echo "<td>$description</td>";
                echo "<td><a href='{$source_url}'>{$source_site}</a></td>";
                echo "<td>{$thumbnail}</td>";
                echo '</tr>';
            }
        }
    }
    echo '</tbody>';

    echo '</table>';


    // show_all_wp_media();
}
