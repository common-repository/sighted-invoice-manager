<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.sighted.com
 * @since      1.0.0
 *
 * @package    Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/includes
 * @author     Team sighted <team@sighted.com>
 */
class Sighted_Invoice_Manager_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		//Force Rewrite Flush
		$need_flush = get_option( 'sim_invoice_need_flush' );
		if( !$need_flush ){
			add_option( 'sim_invoice_need_flush', 'true' );
		}else{
			update_option( 'sim_invoice_need_flush', 'true' );
		}

		// register sync hook for sighted sync
		// register_activation_hook(__FILE__, function() {
		//     if (! wp_next_scheduled ( 'sighted_sync_event' )) {
		// 		wp_schedule_event(time(), 'hourly', 'sighted_sync_event');
		//     }
		// });

		// add_action('sighted_sync_event', self::sighted_sync());

	}

	public static function sighted_sync() {
		if(empty(get_option('sim-invoice-api-settings'))) {
			return NULL;
		}
		// Sighted_Invoice_Manager_sync_page();
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/sighted-invoice-manager-admin-display-sync.php';
		return (syncClients() && syncInvoices());
	}

}
