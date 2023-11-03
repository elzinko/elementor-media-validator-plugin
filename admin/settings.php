<?php

/**
 * Add settings page under the main plugin menu
 *
 * @return void
 */
function add_menu_settings()
{
    if (current_user_can('manage_options') || current_user_can('emvp_access_settings')) {
        add_submenu_page(
            'media-validator',
            'Settings', // Page title
            'Settings', // Menu title
            'emvp_access_settings', // Capability
            'settings', // Menu slug
            'render_page_settings' // Render function
        );
    }
}
add_action('admin_menu', 'add_menu_settings');

/**
 * Render the settings page
 */
function render_page_settings()
{
?>
<div class="wrap">
    <h2>Settings</h2>

    No settings for now

    <!-- <form method="post" action="options.php">
        <?php
            settings_fields('shutterstock-api-settings');
            do_settings_sections('shutterstock-api-settings');
            ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Client key</th>
                <td><input type="text" name="consumer_key"
                        value="<?php echo esc_attr(get_option('consumer_key')); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">client secret code</th>
                <td><input type="text" name="consumer_secret"
                        value="<?php echo esc_attr(get_option('consumer_secret')); ?>" /></td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form> -->
</div>
<?php
}

/**
 * Register settings for the plugin
 *
 * @return void
 */
function register_settings()
{
    register_setting('shutterstock-api-settings', 'consumer_key');
    register_setting('shutterstock-api-settings', 'consumer_secret');
}
add_action('admin_init', 'register_settings');