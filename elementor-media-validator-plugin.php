<?php

/**
 * Plugin Name: Elementor Media Validator Plugin
 * Description: Ce plugin récupère les url des médias pour chaque page et crée un tableau avec la liste des médias, leur url et un indicateur pour savoir s'ils ont été validés.
 * Version: 1.0
 * Author: Thomas Couderc
 */

define('PLUGIN_DEBUG', true);
define('PLUGIN_VERSION', '1.0.0');
define('PLUGIN_NAME', 'elementor-media-validator-plugin');

define('PLUGIN_PATH', plugin_dir_path(__FILE__));
include( PLUGIN_PATH . 'includes/Base/Activate.php' );
use EMVP\Base\Activate;

include(plugin_dir_path(__FILE__) . 'includes/roles.php');
include(plugin_dir_path(__FILE__) . 'includes/media-functions.php');
include(plugin_dir_path(__FILE__) . 'includes/elementor-functions.php');
include(plugin_dir_path(__FILE__) . 'includes/functions.php');
include(plugin_dir_path(__FILE__) . 'admin/validator.php');
include(plugin_dir_path(__FILE__) . 'admin/export.php');
include(plugin_dir_path(__FILE__) . 'admin/logger.php');
include(plugin_dir_path(__FILE__) . 'admin/settings.php');



/**
 * Register plugin activation hook.
 * Check if Elementor is active, if not, deactivate plugin.
 */
function emvp_activation()
{
    if (!is_plugin_active('elementor/elementor.php')) {
        // Disable your plugin.
        deactivate_plugins(plugin_basename(__FILE__));

        // Create an error message to let the user know.
        wp_die(__('Ce plugin nécessite Elementor pour fonctionner. Veuillez installer et activer Elementor, puis réessayez.', 'media-info-tracker'));
    }

    // Assurez-vous que le rôle administrateur a également cette capacité
    $role = get_role('administrator');
    if ($role) {
        $role->add_cap('emvp_access_validator');
        $role->add_cap('emvp_access_logger');
        $role->add_cap('emvp_access_export');
        $role->add_cap('emvp_access_settings');
    }

    Activate::activate();

}
register_activation_hook(__FILE__, 'emvp_activation');

function emvp_deactivation() {

    register_deactivation_hook(__FILE__, 'emvp_remove_client_role');
    register_deactivation_hook(__FILE__, 'emvp_remove_agency_role');

    // Retirer les capacités personnalisées du rôle administrateur
    $role = get_role('administrator');
    if ($role) {
        $role->remove_cap('emvp_access_validator');
        $role->remove_cap('emvp_access_logger');
        $role->remove_cap('emvp_access_export');
        $role->remove_cap('emvp_access_settings');
    }
}
register_deactivation_hook(__FILE__, 'emvp_deactivation');

/**
 * Register js script that allow media validation
 *
 * @return void
 */
function handle_admin_scripts()
{
    wp_register_script('my-script', plugin_dir_url(__FILE__) . 'assets/js/custom.js', array('jquery'), '1.0', true);
    wp_enqueue_script('my-script');
    wp_localize_script('my-script', 'MyAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('emvp_plugin_security_string'),
        'isAdminOrAgency' => isAdminOrAgency(), // Assuming 'agency' is a role you have set up
        'reloadOnSuccess' => true, // Set this to true if you want to reload the page on successful AJAX call
    ));
}
add_action('admin_enqueue_scripts', 'handle_admin_scripts');

/**
 * Register css style for admin page
 *
 * @return void
 */
function handle_admin_styles()
{
    wp_enqueue_style('my_custom_styles', plugin_dir_url(__FILE__) . 'assets/css/admin.css');
}
add_action('admin_enqueue_scripts', 'handle_admin_styles');