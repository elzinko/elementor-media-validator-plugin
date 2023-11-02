<?php

define('ACTION_LOG_TABLE_NAME', 'action_log');

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Action_Log_List_Table extends WP_List_Table {

    public function prepare_items() {
        global $wpdb;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $order_by = (!empty($_GET['orderby'])) ? esc_sql($_GET['orderby']) : 'action_time';
        $order = (!empty($_GET['order'])) ? esc_sql($_GET['order']) : 'desc';
        $search = (!empty($_GET['s'])) ? esc_sql($wpdb->esc_like($_GET['s'])) : '';

        $media_id = $_GET['media_id'] ?? null;
        // Ici, ajoutez votre propre logique pour récupérer les données de l'historique en fonction de la recherche et du tri
        if ($media_id) {
            $data = $this->fetch_log_data($order_by, $order, $search, $media_id);
        } else {
            $data = $this->fetch_log_data($order_by, $order, $search);
        }
    
        usort($data, array($this, 'usort_reorder'));

        $per_page = 5;
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        // [Optionally] Handle pagination
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));

        $this->items = $data;
    }

    public function get_columns() {
        $columns = array(
            'cb'          => '<input type="checkbox" />', // Checkbox for bulk actions
            'thumbnail'   => 'Thumbnail', // Cette colonne affichera la vignette
            'media_id'    => 'ID Média', // Ajoutez cette ligne pour afficher l'ID du média
            'user_name'   => 'Utilisateur',
            'log_key'     => 'Clé',
            'log_value'   => 'Valeur',
            'action_time' => 'Date et Heure'
        );
        return $columns;
    }
    
    public function get_sortable_columns() {
        $sortable_columns = array(
            'media_id'    => array('media_id', false), // Ajoutez cette ligne pour permettre le tri sur l'ID du média
            'user_name'   => array('user_name', false),
            'action_time' => array('action_time', false)
        );
        return $sortable_columns;
    }
    
    
    private function usort_reorder($a, $b) {
        // If no sort, default to action_time
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'action_time';
        // If no order, default to desc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';
    
        // Determine sort order
        if ($orderby == 'action_time') { // Assuming action_time is a date
            $result = strtotime($a[$orderby]) - strtotime($b[$orderby]);
        } else {
            // Case-insensitive string comparison
            $result = strcasecmp($a[$orderby], $b[$orderby]);
        }
    
        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }


    // This method is used to fetch log data from the database
    private function fetch_log_data($order_by, $order, $search, $media_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . ACTION_LOG_TABLE_NAME;
    
        $sql = "SELECT * FROM $table_name";
        $where_clauses = [];
        if ($search) {
            $where_clauses[] = $wpdb->prepare("log_key LIKE %s OR log_value LIKE %s", "%$search%", "%$search%");
        }
        if ($media_id) {
            $where_clauses[] = $wpdb->prepare("media_id = %d", $media_id);
        }
        if ($where_clauses) {
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }
        $sql .= " ORDER BY $order_by $order";
    
        return $wpdb->get_results($sql, ARRAY_A);
    }   


    protected function column_thumbnail($item) {
        // Assurez-vous que $item contient l'ID du média
        $media_id = $item['media_id'];
        
        // Récupérez l'image miniature du média
        $thumbnail = wp_get_attachment_image_src($media_id, 'thumbnail');
        if ($thumbnail) {
            return "<img src='{$thumbnail[0]}' alt='thumbnail' />";
        } else {
            return 'Aucune image'; // ou retourner une image par défaut si vous préférez
        }
    }

    protected function column_user_name($item) {
        // Assurez-vous que $item contient l'ID de l'utilisateur.
        $user_id = $item['user_id'];
        
        // Récupérez les données de l'utilisateur
        $user_info = get_userdata($user_id);
        
        // Retournez le nom d'affichage de l'utilisateur, ou un texte par défaut si l'utilisateur n'existe pas.
        return $user_info ? $user_info->display_name : 'Utilisateur inconnu';
    }
    


    // Default column behavior
    protected function column_default($item, $column_name) {
        switch ($column_name) {
            // Your other columns handling here
            default:
                return $item[$column_name];
        }
    }
}

// Then in your render_history_page function you would use this class
function render_logger_page() {
    $actionLogListTable = new Action_Log_List_Table();
    $actionLogListTable->prepare_items();
    ?>
<div class="wrap">
    <h2>Historique des Actions sur les Médias</h2>
    <form method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <!-- Ajouter un champ pour filtrer par media_id -->
        <input type="text" name="media_id" placeholder="ID Média"
            value="<?php echo esc_attr($_REQUEST['media_id'] ?? '') ?>" />
        <input type="submit" value="Filtrer" />
    </form>
    <?php $actionLogListTable->display(); ?>
</div>

<?php
}

function create_media_log_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . ACTION_LOG_TABLE_NAME;

    // Charset
    $charset_collate = $wpdb->get_charset_collate();

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

        // SQL for creating table
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            media_id mediumint(9),
            log_key varchar(255) NOT NULL,
            log_value varchar(255) NOT NULL,
            action_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Including the upgrade library for creating/updating tables
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Creating the table
        $result = dbDelta($sql);
        error_log(print_r($result, TRUE));
        add_option('media_log_db_version', '1.0');
    }
}

register_activation_hook(__FILE__, 'create_media_log_table');


function log_media_action($media_id, $key, $value)
{
    global $wpdb;
    $table_name = $wpdb->prefix . ACTION_LOG_TABLE_NAME;

    $user_id = get_current_user_id();

    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'media_id' => $media_id,
            'log_key' => $key,
            'log_value' => $value,
        )
    );
}


function add_logger_page()
{
    if (current_user_can('manage_options') || current_user_can('emvp_access_logger')) {
        add_submenu_page(
            'media-validator',
            'Logger',
            'Logger',
            'emvp_access_logger',
            'logger',
            'render_logger_page'
        );
    }
}
add_action('admin_menu', 'add_logger_page');