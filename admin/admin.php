<?php

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


    // To show checkbox with each row
    // function column_cb($item)
    // {
    //     return sprintf(
    //         '<input type="checkbox" name="media[]" value="%s" />',
    //         $item['page']
    //     );
    // }

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


/**
 * Plugin menu callback function
 *
 * @return void
 */
function media_list_init()
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


/**
 * Adding menu item
 *
 * @return void
 */
function my_add_menu_items()
{
    add_menu_page('Media validator', 'Media Validator', 'activate_plugins', 'media-validator', 'media_list_init');
}
add_action('admin_menu', 'my_add_menu_items');
