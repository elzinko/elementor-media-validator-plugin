<?php

// Loading table class
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// Extending class
class Media_List_Table extends WP_List_Table
{
    private $media_data;

    private function get_media_data()
    {
        // use the function from previous question
        return build_media_data();
    }

    // Define table columns
    function get_columns()
    {
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'page' => 'Page',
            'bloc' => 'Bloc',
            'bloc_url'    => 'Bloc URL',
            'format'      => 'Format',
            'description' => 'Description',
            'source_url' => 'Source URL',
            'source_site' => 'Source Site',
            'thumbnail' => 'Thumbnail',
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
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="media[]" value="%s" />',
            $item['page'] // you may want to change the value depending on your needs
        );
    }

    // Add sorting to columns
    protected function get_sortable_columns()
    {
        $sortable_columns = array(
            'page'  => array('page', false),
            'bloc' => array('bloc', false),
            'format'   => array('format', true)
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
