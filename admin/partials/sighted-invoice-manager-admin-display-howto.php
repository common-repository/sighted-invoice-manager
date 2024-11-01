<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.sighted.com/wordpress-plugin
 * @since      0.1.0
 *
 * @package    Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/admin/partials
 */

 if( !( is_user_logged_in() && current_user_can('manage_options') ) ){ die("You do not have permission to be here."); }

?>

<div class="wrap sighted-invoice-manager" id="how-to">
	<div class="postbox">
		<h2>How to use Sighted Invoice Manager</h2>
		<ol>
			<li><a href="<?php echo get_admin_url(NULL, 'edit-tags.php?taxonomy=sim-payee&post_type=sim-invoice'); ?>">Create a Payee</a></li>
			<li><a href="<?php echo get_admin_url(NULL, 'edit-tags.php?taxonomy=sim-client&post_type=sim-invoice'); ?>">Create a Client</a></li>
			<li><a href="<?php echo get_admin_url(NULL, 'post-new.php?post_type=sim-invoice'); ?>">Create an Invoice</a></li>
		</ol>
	</div>
	<div class="postbox">
		<h3 class="title">How to backup your invoices</h3>
		<p>Invoices can be backed up by using the <a href="<?php echo get_admin_url(NULL, 'export.php'); ?>">Export</a> feature that comes with WordPress. And if you need to restore them use the <a href="<?php echo get_admin_url(NULL, 'import.php'); ?>">Import</a> feature that comes with Wordpress.</p>
	</div>
</div>
