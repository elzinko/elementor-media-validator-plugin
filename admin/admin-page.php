<?php

include(plugin_dir_path(__FILE__) . './admin-list.php');


// Plugin menu callback function
function media_list_init()
{
    // Creating an instance
    $empTable = new Media_List_Table();

    echo '<div class="wrap"><h2>Media List Table</h2>';
    // Prepare table
    $empTable->prepare_items();
    // Display table
    $empTable->display();
    echo '</div>';
}


// Adding menu
function my_add_menu_items()
{
    add_menu_page('Media List Table', 'Media List Table', 'activate_plugins', 'media-info-tracker', 'media_list_init');
}
add_action('admin_menu', 'my_add_menu_items');
