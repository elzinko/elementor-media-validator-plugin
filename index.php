<?php

/**
 * Plugin Name: Media Info Tracker
 * Description: Ce plugin récupère les url des médias pour chaque page et crée un tableau avec la liste des médias, leur url et un indicateur pour savoir s'ils ont été validés.
 * Version: 1.0
 * Author: Thomas Couderc
 */


include(plugin_dir_path(__FILE__) . 'includes/media-functions.php');
include(plugin_dir_path(__FILE__) . 'includes/elementor-functions.php');
include(plugin_dir_path(__FILE__) . 'admin/admin-page.php');


// Elementor plugin installation check 

register_activation_hook(__FILE__, 'media_info_tracker_activation');

function media_info_tracker_activation()
{
    if (!is_plugin_active('elementor/elementor.php')) {
        // Désactivez votre plugin.
        deactivate_plugins(plugin_basename(__FILE__));

        // Créez un message d'erreur pour informer l'utilisateur.
        wp_die(__('Ce plugin nécessite Elementor pour fonctionner. Veuillez installer et activer Elementor, puis réessayez.', 'media-info-tracker'));
    }
}
