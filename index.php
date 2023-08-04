<?php

/**
 * Plugin Name: Elementor Media Validation Plugin
 * Description: Ce plugin récupère les url des médias pour chaque page et crée un tableau avec la liste des médias, leur url et un indicateur pour savoir s'ils ont été validés.
 * Version: 1.0
 * Author: Thomas Couderc
 */


include(plugin_dir_path(__FILE__) . 'includes/media-functions.php');
include(plugin_dir_path(__FILE__) . 'includes/elementor-functions.php');
include(plugin_dir_path(__FILE__) . 'admin/admin-page.php');
include(plugin_dir_path(__FILE__) . 'includes/functions.php');
include(plugin_dir_path(__FILE__) . 'admin/export.php');


// Définissez la constante PLUGIN_DEBUG sur true pour activer le débogage.
define('PLUGIN_DEBUG', false);


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

/**
 * Register js script that allow validation of media
 *
 * @return void
 */
function enqueue_my_script()
{
    wp_register_script('my-script', plugin_dir_url(__FILE__) . 'assets/js/custom.js', array('jquery'), '1.0', true);
    wp_enqueue_script('my-script');
    wp_localize_script('my-script', 'MyAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('my-special-string'),
    ));
}

add_action('admin_enqueue_scripts', 'enqueue_my_script');