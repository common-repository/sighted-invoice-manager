<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Sighted Invoice_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       Sighted Invoice Manager
 * Plugin URI:        https://www.sighted.com/wordpress-plugin/
 * Description:       Manage invoices on your very own wordpress installation. Supports emailing, PDF viewing, Syncing with sighted.com
 * Version:           1.0.0
 * Author:            Team Sighted
 * Author URI:        https://www.sighted.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	exit();
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sighted-invoice-manager-activator.php
 */
function activate_sighted_invoice_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sighted-invoice-manager-activator.php';
	Sighted_Invoice_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sighted-invoice-manager-deactivator.php
 */
function deactivate_sighted_invoice_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sighted-invoice-manager-deactivator.php';
	Sighted_Invoice_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sighted_invoice_manager' );
register_deactivation_hook( __FILE__, 'deactivate_sighted_invoice_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sighted-invoice-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sighted_invoice_manager() {

	$plugin = new Sighted_Invoice_Manager();
	$plugin->run();

}
run_sighted_invoice_manager();
