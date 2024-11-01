<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/public
 * @author     Team Sighted <team@sighted.com>
 */
class Sighted_Invoice_Manager_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $Sighted_Invoice_Manager    The ID of this plugin.
	 */
	private $Sighted_Invoice_Manager;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $Sighted_Invoice_Manager       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $Sighted_Invoice_Manager, $version ) {

		$this->Sighted_Invoice_Manager = $Sighted_Invoice_Manager;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->Sighted_Invoice_Manager, plugin_dir_url( __FILE__ ) . 'css/sighted-invoice-manager-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->Sighted_Invoice_Manager, plugin_dir_url( __FILE__ ) . 'js/sighted-invoice-manager-public.js', array( 'jquery' ), $this->version, false );
	}




	/**
	 * Custom Templates for the Custom Post Type.
	 *
	 * @since     1.0.0
	 */
	public function Sighted_Invoice_Custom_Template( $template ){
		global $post;
		if (isset($post->post_type) && $post->post_type == 'sim-invoice') {
			if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/sighted-invoice-manager-public-display.php' ) ) {
				//Load PDF Library
				if ( !file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/fpdf18/fpdf.php' ) ) {
					die( "ERROR: Can't find PDF library ( ". plugin_dir_path( dirname( __FILE__ ) ) . 'includes/fpdf18/fpdf.php'." )" );
				}
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/fpdf18/fpdf.php';
				$template =  plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/sighted-invoice-manager-public-display.php';
			}
		}
		return $template;
	}


}
