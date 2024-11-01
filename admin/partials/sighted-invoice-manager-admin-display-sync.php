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

 function message($message, $type) {
    switch ($type) {
        case 'error':
            echo '<div class="error notice"><p>'.$message.'</p></div>';
            break;
        case 'updated':
            echo '<div class="updated notice"><p>'.$message.'</p></div>';
            break;
        case 'update-nag':
                echo '<div class="update-nag" notice"><p>'.$message.'</p></div>';
        default:
            echo '<div class="notice"><p>'.$message.'</p></div>';
            break;
    }
 }

 function fetchClients() {
 	 	$api_credentials = get_option('sim-invoice-api-settings');
 	 	$remote_url = sprintf('http://sighted.dev/api/clients/?email=%s&password=%s&tenantID=%s',$api_credentials['email'], $api_credentials['password'], $api_credentials['tenantID']);
 	 	$remote_response = wp_remote_get($remote_url);
 		if (wp_remote_retrieve_response_code($remote_response) == 200) {
 			return json_decode(wp_remote_retrieve_body($remote_response));
 		}
 		return NULL;
 }
 function  fetchInvoices() {
        $api_credentials = get_option('sim-invoice-api-settings');
        $remote_url = sprintf('http://sighted.dev/api/invoices/?email=%s&password=%s&tenantID=%s',$api_credentials['email'], $api_credentials['password'], $api_credentials['tenantID']);
        $remote_response = wp_remote_get($remote_url);
        if (wp_remote_retrieve_response_code($remote_response) == 200) {
            return json_decode(wp_remote_retrieve_body($remote_response));
        }
        return NULL;
 }
 function syncClients() {
    if (!$clients = fetchClients()) {
        echo "No client data received!";
        return NULL;
    }
    $clients_data = (array) $clients->data;
    $skipping_client = [];
    foreach ($clients_data as $client) {
        $term_name = sprintf("%s %s", $client->firstname, $client->lastname);
        if($existing_term_data = term_exists($term_name, 'sim-client')) {
           $skipping_client[] = $term_name;
           continue;
        }
        $term_insert_response = wp_insert_term(
            $term_name,
            'sim-client',
            array(
                'slug' => strtolower($term_name)
            )
        );
        if($term_insert_response instanceof WP_Error) {
            continue;
        }

        $term_id = $term_insert_response['term_id'];
        add_term_meta($term_id, '_sim_client_company', $client->company);
        add_term_meta($term_id, '_sim_client_email', $client->email);
        add_term_meta($term_id, '_sim_client_phone', $client->phone);
        add_term_meta($term_id, '_sim_client_address1', sprintf("%s %s %s", $client->add_1, $client->city, $client->state, $client->country));
        add_term_meta($term_id, '_sim_client_address2', sprintf("%s %s %s", $client->add_2, $client->city, $client->state, $client->country));
        add_term_meta($term_id, '_sighted_sourced', 1);
        }
        foreach ($skipping_client as $skipped) {
            message('import of '. $skipped .' was skipped because it already exists!' , 'error');
        }
        return TRUE;
 }

 function syncInvoices() {
    if (!$invoices = fetchInvoices()) {
        echo "No invoice data received!";
        return NULL;
    }
    $invoices_data = (array) $invoices->data;
    $skipping_invoice = [];
    foreach ($invoices_data as $invoice) {
        // d(get_page_by_title($invoice->subject, 'OBJECT', 'sim-invoice'));
        // continue;
        // subject is title for imported invoices

        if( null !== get_page_by_title($invoice->subject, 'OBJECT', 'sim-invoice') ) {
            $skipping_invoice[] = $invoice->subject;
            continue;
        }
        $invoice_post = array(
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_author' => 1,
            'slug' => $invoice->subject . '-' .$invoice->tenant_invoice_id,
            'post_title' => $invoice->subject,
            'post_status' => 'draft',
            'post_type' => 'sim-invoice'
        );
        $invoice_post_id = wp_insert_post($invoice_post);
        d($invoice_post_id);
        // update invoice items
        $lineitems = explode("|", $invoice->items);

        $line_item_total = count($lineitems);
        $items = array();

        for($i=0; $i < $line_item_total; $i++){

         //${'item_' . $i}[] = explode(',',$lineitems[$i]);
         $items[] = explode(',',$lineitems[$i]);
         // remove empty value
         ($items[$i][0] == ' ') ? array_shift($items[$i]) : "";
          (count($items[$i]) == 1) ? array_pop($items) : "";
        }
        // replacement for comma
         $find ="/__/";
         $replace =",";

        $invoice_items = array_map(function($item) use($find, $replace) {
            return array(
                'description' => preg_replace($find, $replace, $item[1]),
                'price' => $item[2],
                'quantity' => $item[3]
            );
        }, $items);


        // if($term_insert_response instanceof WP_Error) {
        //     continue;
        // }

        // $term_id = $term_insert_response['term_id'];
        update_post_meta($invoice_post_id, '_sim_invoice_date', strtotime($invoice->due_date));
        $payment_status = ($invoice->payment == 0 ) ? "unpaid" : ($invoice->payment == 1 ? "partial" : "paid");
        update_post_meta($invoice_post_id, '_sim_invoice_payment_status',  $payment_status);
        update_post_meta($invoice_post_id, '_sim_invoice_group_line_item', $invoice_items);
        // update_post_meta($invoice_post_id, '_sim_client_phone', $client->phone);
        // update_post_meta($invoice_post_id, '_sim_client_address1', sprintf("%s %s %s", $client->add_1, $client->city, $client->state, $client->country));
        // update_post_meta($invoice_post_id, '_sim_client_address2', sprintf("%s %s %s", $client->add_2, $client->city, $client->state, $client->country));
        update_post_meta($invoice_post_id, '_sighted_sourced', 1);
        }
        foreach ($skipping_invoice as $skipped) {
            message('import of '. $skipped .' was skipped because it already exists!' , 'error');
        }
        return TRUE;
 }

 if( !( is_user_logged_in() && current_user_can('manage_options') ) ){ die("You do not have permission to be here."); }
 if(isset($_POST['sync']) && $_POST['sync'] == 1) {
 	// run request to fetch data from sighted
 	// show progress bar

    if (!$invoices = fetchInvoices()) {
        echo "No invoce data received!";
    }
 	// d($clients);
    syncClients();
    syncInvoices();
 	echo "Sync complete";
 }
?>

<div class="wrap sighted-invoice-manager" id="sync">
	<h1>Invoice Manager</h1>
		<?php
		if(!isset($_GET['tab'])){ $_GET['tab'] = 'sync'; }
		DisplayAdminSyncTabs($_GET['tab']);

		switch($_GET['tab']){
			case 'sync':
				$url = esc_url(admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sync&tab=sync'));
				echo "<form action=".$url." method='POST'>";
				echo '<input name="sync" value="1" type="hidden" />';
				submit_button("Sync", "primary large");
				echo '</form>';
				break;

			case 'list_client':
				echo "List of clients";
				break;
			default:
				// echo '<form action="options.php" method="post">';
				// settings_fields( 'sim-invoice-options' );
				// do_settings_sections( 'sim-invoice-options' );
				// submit_button();
				// echo '</form>';
				// break;
				echo "<h1> Unhandled tab </h1>";
		}
		?>
</div>


<?php

function DisplayAdminSyncTabs( $current = 'sync' ) {
    $tabs = array( 'sync' => 'Sync', 'list_client' => 'Clients' );
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?post_type=sim-invoice&page=sighted-invoice-manager-sync&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}

?>
