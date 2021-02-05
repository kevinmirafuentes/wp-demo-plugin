<?php 
/*
   Plugin Name: Demo Plugin
   Author: Kevin Mirafuentes
   Description: Demo plugin with custom table, shortcode, and admin page for results.
*/

// Installs plugin
register_activation_hook( __FILE__, 'demo_plugin_install' );
function demo_plugin_install() 
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'test_kevin';

    $sql = "CREATE TABLE $table_name (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `key` varchar(255) NOT NULL,
        `value` varchar(255) NOT NULL,
        UNIQUE KEY id (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Form shortcode
add_shortcode('demo_plugin_form', 'demo_plugin_shortcode'); 
function demo_plugin_shortcode() 
{   
    $redirect = esc_url( add_query_arg('success', 1) );
    $action = esc_url( site_url() . '/wp-admin/admin-post.php' );
    ob_start();    
    ?>
    <form action="<?php echo $action ?>" method="post">
        <input type="hidden" name="action" value="demo_plugin_save">
        <input type="hidden" name="redirect" value="<?php echo $redirect ?>">
        <label>
            Key: <input type="text" name="key" />
        </label>
        <label>
            Value: <input type="text" name="value" />
        </label>
        <input type="submit" value="Submit">
    </form>
    <?php
    return ob_get_clean();
}


// Handle form submit
add_action( 'admin_post_demo_plugin_save', 'demo_plugin_save' );
function demo_plugin_save() 
{
    global $wpdb;
    status_header(200);

    $redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : site_url();
    $key = isset($_REQUEST['key']) ? $_REQUEST['key'] : '';
    $value = isset($_REQUEST['value']) ? $_REQUEST['value'] : '';

    $wpdb->insert($wpdb->prefix . 'test_kevin', compact('key', 'value'));
    wp_redirect( $redirect );
    exit;
}

// Displays  data on admin
add_action( 'admin_menu', 'demo_plugin_admin_menu' );
function demo_plugin_admin_menu()
{
    add_menu_page( 
        'Demo Plugin Results', 
        'Demo Plugin Results', 
        'manage_options', 
        'demo-plugin-results', 
        'demo_plugin_results_page'
    );    
}

function demo_plugin_results_page()
{
    global $wpdb;
        $results = $wpdb->get_results( 
                    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}test_kevin") 
                 );
        
    ?>
        <h1>Demo Plugin Submission Results</h1>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <td>ID</td>
                    <td>Key</td>
                    <td>Value</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                <tr>
                    <td><?php echo $result->id; ?></td>
                    <td><?php echo $result->key; ?></td>
                    <td><?php echo $result->value; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php 
}