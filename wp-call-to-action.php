<?php
/**
 * The plugin bootstrap file.
 *
 * @package WP_Call_To_Action
 *
 * Plugin Name: WordPress Call To Action
 * Plugin URI: 
 * Description: Easily create call to action for your WordPress site.
 * Version: 1.0.0
 * Author: Pea
 * Author URI:
 * License: GPLv3
 * Text Domain: wp-call-to-action
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define.
define( 'WP_CALL_TO_ACTION_NAME', 'WEN Call To Action' );
define( 'WP_CALL_TO_ACTION_SLUG', 'wp-call-to-action' );
define( 'WP_CALL_TO_ACTION_VERSION', '1.1' );
define( 'WP_CALL_TO_ACTION_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'WP_CALL_TO_ACTION_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'WP_CALL_TO_ACTION_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
define( 'WP_CALL_TO_ACTION_POST_TYPE_CTA', 'wp_cta' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-call-to-action-activator.php
 */
function activate_wp_call_to_action() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-call-to-action-activator.php';
	WP_Call_To_Action_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-call-to-action-deactivator.php
 */
function deactivate_wp_call_to_action() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-call-to-action-deactivator.php';
	WP_Call_To_Action_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_call_to_action' );
register_deactivation_hook( __FILE__, 'deactivate_wp_call_to_action' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-call-to-action.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_call_to_action() {

	$plugin = new WP_Call_To_Action();
	$plugin->run();

}
run_wp_call_to_action();
