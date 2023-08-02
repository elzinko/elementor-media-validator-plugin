<?php

/** 
 * Ajouter la page d'administration au menu 
 * @return void
 */

function media_info_admin_page()
{
    add_menu_page('Media Info Tracker', 'Media Info', 'manage_options', 'media-info-tracker', 'display_media_info_page');
}

add_action('admin_menu', 'media_info_admin_page');

// Afficher la page d'administration
function display_media_info_page()
{

    $elementor_pages = get_elementor_posts();

    echo '<h1>Elementor media</h1>';
    echo '<table>';

    // En-têtes du tableau
    echo '<thead>';
    echo '<tr>';
    echo '<th>Page</th>';
    echo '<th>Bloc</th>';
    echo '<th>URL</th>';
    echo '<th>Provenance</th>';
    echo '</tr>';
    echo '</thead>';

    // Corps du tableau
    echo '<tbody>';
    foreach ($elementor_pages as $page) {

        $elementor_page_data = get_elementor_data($page->ID);



        foreach ($elementor_page_data as $section) {


            $bloc_name = get_bloc_name_from_section($section);

            $images = find_images_in_section($section);

            foreach ($images as $image) {

                // $bloc = find_bloc_name_around_image($elementor_page_data, $image);

                $media_info = array(
                    'page' => $page->post_title,
                    'bloc' => $bloc_name,
                    'url' => $image['url'],
                    'provenance' => 'Elementor'
                );

                echo '<tr>';
                echo '<td>' . esc_html($media_info['page']) . '</td>';
                echo '<td>' . esc_html($media_info['bloc']) . '</td>';
                echo '<td><a href="' . esc_url($media_info['url']) . '">' . esc_html($media_info['url']) . '</a></td>';
                echo '<td>' . esc_html($media_info['provenance']) . '</td>';
                echo '</tr>';
            }
        }
    }
    echo '</tbody>';

    // Fin du tableau
    echo '</table>';


    // show_all_wp_media();
}
