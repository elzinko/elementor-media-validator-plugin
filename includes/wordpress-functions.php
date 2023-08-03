<?php
function show_all_wp_media()
{
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
