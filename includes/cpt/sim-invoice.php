<?php

/**
 * Custom Post type needed for both public and admin.
 *
 * @link       https://www.sighted.com/wordpress-plugin
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
class Sighted_Invoice_Manager_CPT_sim_invoice {

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
    public function __construct( $Sighted_Invoice_Manager, $version ) {

        $this->Sighted_Invoice_Manager = $Sighted_Invoice_Manager;
        $this->version = $version;

    }









    /**
     * Registers the post type.
     *
     * @since     1.0.0
     */
    public function Register_Post_Type() {
        //Invoice Post Type
        $labels = array(
            'name'                => _x( 'Invoices', 'Post Type General Name', 'sighted-invoice-manager' ),
            'singular_name'       => _x( 'Invoice', 'Post Type Singular Name', 'sighted-invoice-manager' ),
            'menu_name'           => __( 'Sighted Invoice Manager', 'sighted-invoice-manager' ),
            'name_admin_bar'      => __( 'Invoice', 'sighted-invoice-manager' ),
            'parent_item_colon'   => __( 'Parent Invoice:', 'sighted-invoice-manager' ),
            'all_items'           => __( 'Invoices', 'sighted-invoice-manager' ),
            'add_new_item'        => __( 'Add New Invoice', 'sighted-invoice-manager' ),
            'add_new'             => __( 'Add New', 'sighted-invoice-manager' ),
            'new_item'            => __( 'New Invoice', 'sighted-invoice-manager' ),
            'edit_item'           => __( 'Edit Invoice', 'sighted-invoice-manager' ),
            'update_item'         => __( 'Update Invoice', 'sighted-invoice-manager' ),
            'view_item'           => __( 'View Invoice', 'sighted-invoice-manager' ),
            'search_items'        => __( 'Search Invoices', 'sighted-invoice-manager' ),
            'not_found'           => __( 'Not found', 'sighted-invoice-manager' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'sighted-invoice-manager' ),
        );
        $args = array(
            'label'               => __( 'invoice', 'sighted-invoice-manager' ),
            'description'         => __( 'Invoice', 'sighted-invoice-manager' ),
            'labels'              => $labels,
            'supports'            => array( 'title' ),
            'taxonomies'          => array( 'sim-client', 'sim-payee' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 101,
            // 'menu_icon'           => 'dashicons-groups',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
        );
        register_post_type( 'sim-invoice', $args );
    }



    /**
     * Registers taxonomies.
     *
     * @since     1.0.0
     */
    public function Register_Taxonomies() {
        //Client
        $labels = array(
            'name'                       => _x( 'Client', 'Taxonomy General Name', 'sighted-invoice-manager' ),
            'singular_name'              => _x( 'Client', 'Taxonomy Singular Name', 'sighted-invoice-manager' ),
            'menu_name'                  => __( 'Clients', 'sighted-invoice-manager' ),
            'all_items'                  => __( 'All Clients', 'sighted-invoice-manager' ),
            'parent_item'                => __( 'Parent Client', 'sighted-invoice-manager' ),
            'parent_item_colon'          => __( 'Parent Client:', 'sighted-invoice-manager' ),
            'new_item_name'              => __( 'New Client Name', 'sighted-invoice-manager' ),
            'add_new_item'               => __( 'Add New Client', 'sighted-invoice-manager' ),
            'edit_item'                  => __( 'Edit Client', 'sighted-invoice-manager' ),
            'update_item'                => __( 'Update Client', 'sighted-invoice-manager' ),
            'view_item'                  => __( 'View Client', 'sighted-invoice-manager' ),
            'separate_items_with_commas' => __( 'Separate menus with commas', 'sighted-invoice-manager' ),
            'add_or_remove_items'        => __( 'Add or remove menus', 'sighted-invoice-manager' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'sighted-invoice-manager' ),
            'popular_items'              => __( 'Popular Clients', 'sighted-invoice-manager' ),
            'search_items'               => __( 'Search Clients', 'sighted-invoice-manager' ),
            'not_found'                  => __( 'Not Found', 'sighted-invoice-manager' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => false,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => false,
            'show_tagcloud'              => false,
            'rewrite' => array('slug' => 'invoice-client'),
        );
        register_taxonomy( 'sim-client', array( 'sim-invoice' ), $args );

        //Payee
        $labels = array(
            'name'                       => _x( 'Payee', 'Taxonomy General Name', 'sighted-invoice-manager' ),
            'singular_name'              => _x( 'Payee', 'Taxonomy Singular Name', 'sighted-invoice-manager' ),
            'menu_name'                  => __( 'Payees', 'sighted-invoice-manager' ),
            'all_items'                  => __( 'All Payees', 'sighted-invoice-manager' ),
            'parent_item'                => __( 'Parent Payee', 'sighted-invoice-manager' ),
            'parent_item_colon'          => __( 'Parent Payee:', 'sighted-invoice-manager' ),
            'new_item_name'              => __( 'New Payee Name', 'sighted-invoice-manager' ),
            'add_new_item'               => __( 'Add New Payee', 'sighted-invoice-manager' ),
            'edit_item'                  => __( 'Edit Payee', 'sighted-invoice-manager' ),
            'update_item'                => __( 'Update Payee', 'sighted-invoice-manager' ),
            'view_item'                  => __( 'View Payee', 'sighted-invoice-manager' ),
            'separate_items_with_commas' => __( 'Separate menus with commas', 'sighted-invoice-manager' ),
            'add_or_remove_items'        => __( 'Add or remove menus', 'sighted-invoice-manager' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'sighted-invoice-manager' ),
            'popular_items'              => __( 'Popular Payees', 'sighted-invoice-manager' ),
            'search_items'               => __( 'Search Payees', 'sighted-invoice-manager' ),
            'not_found'                  => __( 'Not Found', 'sighted-invoice-manager' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => false,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => false,
            'show_tagcloud'              => false,
            'rewrite' => array('slug' => 'invoice-payee'),
        );
        register_taxonomy( 'sim-payee', array( 'sim-invoice' ), $args );
    }


    /**
     * Custom rewrites and permalinks.
     *
     * @since     1.0.0
     */
    function Custom_Rewrites( ){
        $queryarg = 'post_type=sim-invoice&p=';
        add_rewrite_tag('%cpt_id%', '([^/]+)', $queryarg);
        add_permastruct('invoice', '/invoice/%cpt_id%/', false);
    }
    function Custom_Permalinks( $link, $id=NULL ){
        if( is_null($id) ){ return $link; }
        $post = get_post($id);
        if ( $post->post_type == 'sim-invoice' ){
            global $wp_rewrite;
            if ( is_wp_error( $post ) )
                return $post;
            $newlink = $wp_rewrite->get_extra_permastruct('invoice');
            $newlink = str_replace("%cpt_id%", $post->ID, $newlink);
            $newlink = home_url(user_trailingslashit($newlink));
            return $newlink;
        } else {
            return $link;
        }
    }


}



?>
