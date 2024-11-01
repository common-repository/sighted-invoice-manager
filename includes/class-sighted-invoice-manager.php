<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link			 https://www.sighted.com/wordpress-plugin
 * @since			1.0.0
 *
 * @package		Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since			1.0.0
 * @package		Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/includes
 * @author		 Team Sighted <team@sighted.com>
 */
class Sighted_Invoice_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since		1.0.0
	 * @access	 protected
	 * @var			Sighted_Invoice_Manager_Loader		$loader		Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since		1.0.0
	 * @access	 protected
	 * @var			string		$Sighted_Invoice_Manager		The string used to uniquely identify this plugin.
	 */
	protected $Sighted_Invoice_Manager;

	/**
	 * The current version of the plugin.
	 *
	 * @since		1.0.0
	 * @access	 protected
	 * @var			string		$version		The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since		1.0.0
	 */
	public function __construct() {

		$this->Sighted_Invoice_Manager = 'sighted-invoice-manager';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$gofs = get_option( 'gmt_offset' );
		date_default_timezone_set('Etc/GMT'.(($gofs < 0)?'+':'').-$gofs);

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sighted_Invoice_Manager_Loader. Orchestrates the hooks of the plugin.
	 * - Sighted_Invoice_Manager_i18n. Defines internationalization functionality.
	 * - Sighted_Invoice_Manager_Admin. Defines all hooks for the admin area.
	 * - Sighted_Invoice_Manager_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since		1.0.0
	 * @access	 private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sighted-invoice-manager-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sighted-invoice-manager-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sighted-invoice-manager-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sighted-invoice-manager-public.php';

		/**
		 * Include Metabox Library.
		 * https://github.com/WebDevStudios/CMB2
		 * https://github.com/origgami/CMB2-grid
		 */
		if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/CMB2/init.php' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/CMB2/init.php';
		}else{
			die("Cant find " . plugin_dir_path( dirname( __FILE__ ) ) . 'includes/CMB2/init.php');
		}

		if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/CMB2-grid/Cmb2GridPlugin.php' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/CMB2-grid/Cmb2GridPlugin.php';
		}else{
			die("Cant find " . plugin_dir_path( dirname( __FILE__ ) ) . 'includes/CMB2-grid/Cmb2GridPlugin.php');
		}
		/**
		 * Include Custom Post Types
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cpt/sim-invoice.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cpt/sim-invoice-logs.php';

		$this->loader = new Sighted_Invoice_Manager_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sighted_Invoice_Manager_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since		1.0.0
	 * @access	 private
	 */
	private function set_locale() {

		$plugin_i18n = new Sighted_Invoice_Manager_i18n();
		$plugin_i18n->set_domain( $this->get_Sighted_Invoice_Manager() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since		1.0.0
	 * @access	 private
	 */
	private function define_admin_hooks() {

		// $this->loader->add_action( 'init', $plugin_admin, 'Register_Custom_Metabox_Library' );

		//CPT sim-invoice
		$cpt_sim_invoice = new Sighted_Invoice_Manager_CPT_sim_invoice( $this->get_Sighted_Invoice_Manager(), $this->get_version() );
		$this->loader->add_action( 'init', $cpt_sim_invoice, 'Register_Post_Type' );
		$this->loader->add_action( 'init', $cpt_sim_invoice, 'Register_Taxonomies' );
		$this->loader->add_action( 'init', $cpt_sim_invoice, 'Custom_Rewrites' );
		$this->loader->add_filter( 'post_type_link', $cpt_sim_invoice, 'Custom_Permalinks', 1, 3 );

		//CPT sim-invoice-logs
		$cpt_sim_invoice_logs = new Sighted_Invoice_Manager_CPT_sim_invoice_logs( $this->get_Sighted_Invoice_Manager(), $this->get_version() );
		$this->loader->add_action( 'init', $cpt_sim_invoice_logs, 'Register_Post_Type' );
		$this->loader->add_action( 'init', $cpt_sim_invoice_logs, 'Register_Taxonomies' );
		$this->loader->add_action( 'init', $cpt_sim_invoice_logs, 'Custom_Rewrites' );
		$this->loader->add_filter( 'post_type_link', $cpt_sim_invoice_logs, 'Custom_Permalinks', 1, 3 );
		$plugin_admin = new Sighted_Invoice_Manager_Admin( $this->get_Sighted_Invoice_Manager(), $this->get_version() );
		$this->loader->add_action( 'init', $this, 'Check_Sighted_Invoice_Needs_Flush' );

		//Handle Post Transitions
		$this->loader->add_action( 'transition_post_status', $plugin_admin, 'Sighted_Invoice_Post_Transition', 10, 3 );
		$this->loader->add_action( 'post_submitbox_misc_actions', $plugin_admin, 'Sighted_Invoice_Post_Misc_Actions' );

		//Custom Meta Boxes
		$this->loader->add_filter( 'cmb2_admin_init', $plugin_admin, 'Register_Custom_Metaboxes' );
		$this->loader->add_filter( 'cmb2_admin_init', $plugin_admin, 'Register_Custom_Client_Metaboxes' );
		$this->loader->add_filter( 'cmb2_admin_init', $plugin_admin, 'Register_Custom_Payee_Metaboxes' );
		// $this->loader->add_action( 'admin_menu', $plugin_admin, 'Remove_Sighted_Invoice_Metaboxes' );

		//Custom Post Columns
		$this->loader->add_filter( 'restrict_manage_posts', $plugin_admin, 'Make_Sighted_Invoice_Columns_Filterable' );
		$this->loader->add_filter( 'manage_edit-sim-invoice_columns', $plugin_admin, 'Change_Sighted_Invoice_Columns' );
		$this->loader->add_action( 'manage_sim-invoice_posts_custom_column', $plugin_admin, 'Change_Sighted_Invoice_Columns_Display', 10, 2 );
		$this->loader->add_filter( 'manage_edit-sim-invoice_sortable_columns', $plugin_admin, 'Make_Sighted_Invoice_Columns_Sortable' );
		$this->loader->add_filter( 'request', $plugin_admin, 'Sighted_Invoice_Requests' );
		$this->loader->add_filter( 'months_dropdown_results', $plugin_admin, 'Remove_Sighted_Invoice_Date_Filter' );
		$this->loader->add_filter( 'views_edit-sim-invoice', $plugin_admin, 'Change_Sighted_Invoice_Quicklinks' );
		//Invoice Logs
		$this->loader->add_filter( 'manage_edit-sim-invoice-logs_columns', $plugin_admin, 'Change_Sighted_Invoice_Logs_Columns' );
		$this->loader->add_action( 'manage_sim-invoice-logs_posts_custom_column', $plugin_admin, 'Change_Sighted_Invoice_Logs_Columns_Display', 10, 2 );
		$this->loader->add_filter( 'manage_edit-sim-invoice-logs_sortable_columns', $plugin_admin, 'Make_Sighted_Invoice_Logs_Columns_Sortable' );
		$this->loader->add_filter( 'request', $plugin_admin, 'Sighted_Invoice_Logs_Requests' );

		$this->loader->add_filter( 'list_table_primary_column', $plugin_admin, 'Set_Sighted_Invoice_Primary_Columns', 10, 2 );
		$this->loader->add_filter( 'post_row_actions', $plugin_admin, 'Set_Sighted_Invoice_Row_Action_Buttons', 10, 2 );
		$this->loader->add_filter( 'page_row_actions', $plugin_admin, 'Set_Sighted_Invoice_Row_Action_Buttons', 10, 2 );

		//Custom Taxonomy Options
		$this->loader->add_action( 'admin_footer-edit-tags.php', $plugin_admin, 'Hide_Sighted_Invoice_Tax_Meta' );
		$this->loader->add_filter( 'manage_edit-sim-client_columns', $plugin_admin, 'Change_Sighted_Invoice_Client_Columns' );
		$this->loader->add_action( 'manage_sim-client_custom_column', $plugin_admin, 'Change_Sighted_Invoice_Client_Columns_Display', 10, 3 );
		$this->loader->add_filter( 'manage_edit-sim-payee_columns', $plugin_admin, 'Change_Sighted_Invoice_Payee_Columns' );

		//Settings Page
		$this->loader->add_action( 'admin_init', $plugin_admin, 'Sighted_Invoice_Manager_settings_init' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'Sighted_Invoice_Manager_Admin_Menu' );
		//		$this->loader->add_filter( "plugin_action_links_".$this->basename, $plugin_admin, 'Sighted_Invoice_Manager_plugin_settings_link' );

		//Enqueue Scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since		1.0.0
	 * @access	 private
	 */
	private function define_public_hooks() {

		//CPT sim-invoice
		$cpt_sim_invoice = new Sighted_Invoice_Manager_CPT_sim_invoice( $this->get_Sighted_Invoice_Manager(), $this->get_version() );
		$this->loader->add_action( 'init', $cpt_sim_invoice, 'Register_Post_Type' );
		$this->loader->add_action( 'init', $cpt_sim_invoice, 'Register_Taxonomies' );

		//CPT sim-invoice-logs
		$cpt_sim_invoice_logs = new Sighted_Invoice_Manager_CPT_sim_invoice_logs( $this->get_Sighted_Invoice_Manager(), $this->get_version() );
		$this->loader->add_action( 'init', $cpt_sim_invoice_logs, 'Register_Post_Type' );
		$this->loader->add_action( 'init', $cpt_sim_invoice_logs, 'Register_Taxonomies' );


		$this->loader->add_action( 'init', $this, 'Check_Sighted_Invoice_Needs_Flush' );

		$plugin_public = new Sighted_Invoice_Manager_Public( $this->get_Sighted_Invoice_Manager(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		//Custom Templates for Custom Post Types
		$this->loader->add_filter( 'index_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( '404_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'archive_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'author_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'category_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'tag_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'taxonomy_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'date_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'home_template', $plugin_public, 'Sighted_Invoice_Custom_Template' ); //seems all it needs is this one but left in the rest for good measure
		$this->loader->add_filter( 'front_page_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'page_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'paged_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'search_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'single_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'text_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'attachment_template', $plugin_public, 'Sighted_Invoice_Custom_Template' );
		$this->loader->add_filter( 'comments_popup', $plugin_public, 'Sighted_Invoice_Custom_Template' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since		1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since		 1.0.0
	 * @return		string		The name of the plugin.
	 */
	public function get_Sighted_Invoice_Manager() {
		return $this->Sighted_Invoice_Manager;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since		 1.0.0
	 * @return		Sighted_Invoice_Manager_Loader		Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since		 1.0.0
	 * @return		string		The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**********************************************************************/
	/* ONLY ADD STUFF THAT NEEDS TO BE USED IN BOTH PUBLIC AND ADMIN AREA */
	/* REFERENCE WITH $this->functionname()								  */
	/**********************************************************************/


	public function Check_Sighted_Invoice_Needs_Flush() {
		$need_flush = get_option( 'sim_invoice_need_flush');
		//Add option
		if( !$need_flush ){
			add_option( 'sim_invoice_need_flush', 'true' );
			$need_flush = get_option( 'sim_invoice_need_flush' );
		}
		//Update option after performing flush
		if( $need_flush != 'false' ){
			flush_rewrite_rules( false );
			update_option( 'sim_invoice_need_flush', 'false' );
		}
	}




}
