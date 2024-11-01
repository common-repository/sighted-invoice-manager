<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.sighted.com/wordpress-plugin
 * @since      1.0.0
 *
 * @package    Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/includes
 * @author     Team Sighted <team@sighted.com>
 */
class Sighted_Invoice_Manager_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		//Templates Option
		$templates = get_option( 'sim_invoice_templates' );
		if( $templates ){
			delete_option( 'sim_invoice_templates' );
		}

		//remove settings
		unregister_setting( 'sim-invoice-options', 'sim-invoice-options' );
		$templates = get_option( 'sim_invoice_templates' );
		foreach($templates as $template=>$default){
			$option_name = 'sim-invoice-template-'.$template;
			unregister_setting( $option_name, $option_name );
		}

		//Force Rewrite Flush
		$need_flush = get_option( 'sim_invoice_need_flush' );
		if( !$need_flush ){
			add_option( 'sim_invoice_need_flush', 'true' );
		}else{
			update_option( 'sim_invoice_need_flush', 'true' );
		}
		flush_rewrite_rules( false );


		// register sighted sync deactivate hook

		register_deactivation_hook(__FILE__, function() {
			wp_clear_scheduled_hook('sighted_sync_event');
		});

	}

}
