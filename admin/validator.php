<?php

define('MEDIA_ACTIONS_TABLE_NAME','emvp_media_actions');

/**
 * add validator menu page under the main plugin menu
 *
 * @return void
 */
function add_menu_validator()
{
    if (current_user_can('manage_options') || current_user_can('emvp_access_validator')) {
        add_menu_page(
            'Media validator', 
            'Media Validator', 
            'emvp_access_validator', 
            'media-validator', 
            'render_page_validator'
        );
    }
}
add_action('admin_menu', 'add_menu_validator');

// Loading table class
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// Extending wp table class
class Media_List_Table extends WP_List_Table
{
    private $media_data;

    private function get_media_data()
    {
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        return build_media_data($filter);
    }

    // Define table columns
    function get_columns()
    {
        $columns = array(
            'thumbnail' => 'Thumbnail',
            'page' => 'Page',
            'bloc' => 'Bloc',
            'title' => 'Title',
            'description' => 'Description',
            'alt_text' => 'Alt',
            'legend' => 'Legend',
            'source' => 'Source',
            'credit' => 'Credit',
            'dimentions' => 'Size',
            // 'file' => 'Filename',
            // 'format' => 'Format',
            'validation' => 'Valid',
        );
        return $columns;
    }

    // Bind table with columns, data and all
    function prepare_items()
    {
        $this->media_data = $this->get_media_data();

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        usort($this->media_data, array(&$this, 'usort_reorder'));

        $this->items = $this->media_data;
    }

    // bind data with column
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    // Add sorting to columns
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'page'  => array('page', false),
            'bloc' => array('bloc', false),
            'format'   => array('format', false),
            'source' => array('source', false),
            'credit' => array('credit', false),
        );
        return $sortable_columns;
    }

    // Sorting function
    function usort_reorder($a, $b)
    {
        // If no sort, default to page
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'page';
        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);
        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }
}

function render_page_validator()
{
    // Creating an instance
    $empTable = new Media_List_Table();

    // Get filter value
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';


    echo '<div class="wrap"><h2>Elementor Media List</h2>';

    // Add filter
    echo '<select id="media-filter">';
    echo '<option value="all"' . ($filter === 'all' ? ' selected' : '') . '>All</option>';
    echo '<option value="validated"' . ($filter === 'validated' ? ' selected' : '') . '>Validated</option>';
    echo '<option value="not_validated"' . ($filter === 'not_validated' ? ' selected' : '') . '>Not Validated</option>';
    echo '</select>';

    // Prepare table
    $empTable->prepare_items();
    // Display table
    $empTable->display();
    echo '</div>';

    echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            $("#media-filter").change(function() {
                var selectedFilter = $(this).children("option:selected").val();
                window.location.search += "&filter=" + selectedFilter;
            });
        });
    </script>';
}

function insert_media_action($media_id, $user_id, $action_type, $comment) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'emvp_media_actions';

    $result = $wpdb->insert(
        $table_name,
        array(
            'media_id' => $media_id,
            'user_id' => $user_id,
            'action_type' => $action_type,
            'action_comment' => $comment,
            'action_date' => current_time('mysql', 1)
        ),
        array(
            '%d',
            '%d',
            '%s',
            '%s',
            '%s'
        )
    );
    if($result === false) {
        // Handle error, log it or return false
        // e.g., error_log($wpdb->last_error);
        return false;
    }

    return $wpdb->insert_id;
}

function media_action_validate() {
    // Vérifier la nonce pour la sécurité
    check_ajax_referer('emvp_plugin_security_string', 'security');

    // Vérifier que l'utilisateur courant a l'autorisation nécessaire pour effectuer cette action
    if ( !current_user_can('emvp_access_validator') ) {
        wp_send_json_error('Vous n\'avez pas l\'autorisation de faire cela.');
    }

    // Récupérer les données de la requête
    $user_id = get_current_user_id(); // L'ID de l'utilisateur actuellement connecté
    $media_id = isset($_POST['media_id']) ? intval($_POST['media_id']) : 0;
    $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
    $comment = isset($_POST['comment']) ? sanitize_textarea_field($_POST['comment']) : '';

    // Insérer les données dans la base de données
    $action_id = insert_media_action($media_id, $user_id, $action_type, $comment);

    if ($action_id === FALSE) {
        wp_send_json_error('Une erreur est survenue lors de l\'enregistrement de l\'action.');
    }

    $log_id = insert_log($action_id, $user_id, $action_type);
    if ($log_id === FALSE) {
        wp_send_json_error('Une erreur est survenue lors de l\'enregistrement du log.');
    }

    wp_send_json_success('L\'action a été enregistrée avec succès.');
}
add_action('wp_ajax_media_action_validate', 'media_action_validate');