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

    $elementor_posts = get_elementor_posts();

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
    foreach ($elementor_posts as $post) {
        $elementor_data = get_elementor_data($post->ID);
        $images = find_images_in_elementor_data($elementor_data);
        foreach ($images as $image) {
            $media_info = array(
                'page' => $post->post_title,
                'bloc' => $image['id'],
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
    echo '</tbody>';

    // Fin du tableau
    echo '</table>';


    $media_info = get_media_info();


    echo '<h1>All media</h1>';
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
    foreach ($media_info as $info) {
        echo '<tr>';
        echo '<td>' . esc_html($info['page']) . '</td>';
        echo '<td>' . esc_html($info['bloc']) . '</td>';
        echo '<td><a href="' . esc_url($info['url']) . '">' . esc_html($info['url']) . '</a></td>';
        echo '<td>' . esc_html($info['provenance']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';

    // Fin du tableau
    echo '</table>';
}
