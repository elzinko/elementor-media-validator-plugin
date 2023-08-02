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

    $media_info = get_media_info();

    // Début du tableau
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
