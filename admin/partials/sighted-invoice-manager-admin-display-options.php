<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.sighted.com/wordpress-plugin
 * @since      1.0.0
 *
 * @package    Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/admin/partials
 */

 if( !( is_user_logged_in() && current_user_can('manage_options') ) ){ die("You do not have permission to be here."); }

?>

<div class="wrap sighted-invoice-manager" id="options">
	<h1>Invoice Manager</h1>
		<?php
		if(!isset($_GET['tab'])){ $_GET['tab'] = 'options'; }
		DisplayAdminTabs($_GET['tab']);

		switch($_GET['tab']){

			case 'api_settings':
				echo '<form action="options.php" method="post">';
				settings_fields( 'sim-invoice-api-settings' );
				do_settings_sections( 'sim-invoice-api-settings' );
				submit_button();
				echo '</form>';
				break;
			case 'mail':
				echo '<h2>Templates</h2>';

				$tags = array(
					'[id]', '[date]', '[overdue]', '[status]', '[payee-name]', '[payee-address1]', '[payee-address2]',
					'[payee-phone]', '[payee-email]', '[client-name]', '[client-company]', '[client-address1]',
					'[client-address2]', '[client-phone]', '[client-email]'
				);
				for($i=0; $i<sizeof($tags); $i++){
					$str = '<code>';
					$str .= $tags[$i];
					$str .= '</code>';
					$tags[$i] = $str;
				}
				echo "<p>Available Tags: ".join(', ',$tags)."</p>";

				$templates = get_option( 'sim_invoice_templates' );
				foreach($templates as $template=>$default){
					echo '<form action="options.php" method="post">';
					echo '<div class="postbox">';
					settings_fields( 'sim-invoice-template-'.$template );
					do_settings_sections( 'sim-invoice-template-'.$template );
					submit_button();
					echo '</div>';
					echo '</form>';
				}
				break;
			default:
				echo '<form action="options.php" method="post">';
				settings_fields( 'sim-invoice-options' );
				do_settings_sections( 'sim-invoice-options' );
				submit_button();
				echo '</form>';
				break;
		}
		?>
</div>


<?php

function DisplayAdminTabs( $current = 'options' ) {
    $tabs = array( 'options' => 'Options', 'mail' => 'Mail Templates', 'api_settings' => 'API Settings' );
    //echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?post_type=sim-invoice&page=sighted-invoice-manager-options&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}

?>
