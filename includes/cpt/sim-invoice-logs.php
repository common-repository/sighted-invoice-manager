<?php

/**
 * Custom Post type needed for both public and admin.
 *
 * @link       http://www.sighted.com/wordpress-plugin
 * @since      1.0.0
 *
 * @package    Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/includes/cpt
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/includes/cpt
 * @author     Team Sighted <team@sighted.com>
 */
class Sighted_Invoice_Manager_CPT_sim_invoice_logs {

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
     * @since    1.0.3
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.3
     * @param      string    $Sighted_Invoice_Manager       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($Sighted_Invoice_Manager, $version) {

        $this -> Sighted_Invoice_Manager = $Sighted_Invoice_Manager;
        $this -> version = $version;

    }

    /**
     * Registers the post type.
     *
     * @since     1.0.0
     */
    public function Register_Post_Type() {
        //Logs Post Type
        $labels = array(
            'name' => _x('Invoice Logs', 'Post Type General Name', 'sighted-invoice-manager'),
            'singular_name' => _x('Invoice Log', 'Post Type Singular Name', 'sighted-invoice-manager'),
            'menu_name' => __('Logs', 'sighted-invoice-manager'),
            'name_admin_bar' => __('Invoice Log', 'sighted-invoice-manager'),
            'parent_item_colon' => __('Parent Invoice Log:', 'sighted-invoice-manager'),
            'all_items' => __('Logs', 'sighted-invoice-manager'),
            'add_new_item' => __('Add New Invoice Log', 'sighted-invoice-manager'),
            'add_new' => __('Add New', 'sighted-invoice-manager'),
            'new_item' => __('New Invoice Log', 'sighted-invoice-manager'),
            'edit_item' => __('Edit Invoice Log', 'sighted-invoice-manager'),
            'update_item' => __('Update Invoice Log', 'sighted-invoice-manager'),
            'view_item' => __('View Invoice Log', 'sighted-invoice-manager'),
            'search_items' => __('Search Invoice Logs', 'sighted-invoice-manager'),
            'not_found' => __('Not found', 'sighted-invoice-manager'),
            'not_found_in_trash' => __('Not found in Trash', 'sighted-invoice-manager'),
        );
        $args = array('label' => __('invoice-log', 'sighted-invoice-manager'), 'description' => __('Invoice Log', 'sighted-invoice-manager'), 'labels' => $labels, 'supports' => array('title'), 'taxonomies' => array(), 'hierarchical' => false, 'public' => false, 'show_ui' => true, 'show_in_menu' => 'edit.php?post_type=sim-invoice', 'menu_position' => 102, 'menu_icon' => 'dashicons-groups', 'show_in_admin_bar' => false, 'show_in_nav_menus' => false, 'can_export' => true, 'has_archive' => false, 'exclude_from_search' => true, 'publicly_queryable' => false, 'capability_type' => 'post', );
        register_post_type('sim-invoice-logs', $args);
    }

    /**
     * Registers taxonomies.
     *
     * @since     1.0.0
     */
    public function Register_Taxonomies() {
    }

    /**
     * Custom rewrites and permalinks.
     *
     * @since     1.0.0
     */
    public function Custom_Rewrites() {
    }

    public function Custom_Permalinks($link, $id = NULL) {
        return $link;
    }

}
?>