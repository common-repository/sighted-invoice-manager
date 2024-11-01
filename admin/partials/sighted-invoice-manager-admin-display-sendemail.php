<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.sighted.com/wordpress-plugin
 * @since      1.0.0
 *
 * @package    Sighted_Invoice_Manager
 * @subpackage Sighted_Invoice_Manager/admin/partials
 */


if( !( is_user_logged_in() && current_user_can('manage_options') ) ){ die("You do not have permission to be here."); }


?>

<div class="wrap sighted-invoice-manager" id="how-to">
	<div class="postbox">
		<?php
		//Intializae Variables
		$post_id = NULL;

		//Step 1: Get Invoice
		$step1_passed = false;
		if( isset($_GET['sim-invoice']) && (!is_null($_GET['sim-invoice']) && get_post_type($_GET['sim-invoice']) == 'sim-invoice') ){
			$post_id = $_GET['sim-invoice'];
			$step1_passed = true;
		}else{
			echo '<h2>Step 1: Choose an Invoice</h2>';
			InvoiceSelectForm();
		}
		//Step 2: Choose Template
		$step2_passed = false;
		if($step1_passed === true){
			if( isset($_GET['sim-invoice-template']) && !is_null($_GET['sim-invoice-template']) && !empty($_GET['sim-invoice-template']) ){
				$post_template = $_GET['sim-invoice-template'];
				$step2_passed = true;
			}else{
				echo '<h2>Step 2: Choose a Template</h2>';
				TemplateSelectForm($post_id);
			}
		}
		//Step 3: Preview and Send
		$step3_passed = false;
		if($step2_passed === true){
			$mail_ready = false;
			if($_POST){
				$mail_ready = true;
				foreach( array('to','from','fromEmail','subject','message') as $key ){
					if( !isset($_POST[$key])  ) {$mail_ready = false;}
					if( is_null($_POST[$key]) ) {$mail_ready = false;}
					if( empty($_POST[$key])   ) {$mail_ready = false;}
				}
			}

			if($mail_ready == true){
				$step3_passed = true;
			}else{
				echo '<h2>Step 3: Preview</h2>';
				$invoice = CreateInvoiceObject( $post_id );
				PreviewForm( $invoice, $post_template );
			}
		}
		//Step 4: Give send status or return to post lists
		$step4_passed = false;
		if($step3_passed == true){
			echo '<h2>Step 4: Email</h2>';
			$results = SendEmail($_POST);
			if( $results == true ){
				echo "<p>Your email was sent successfully</p>";
				printf(
					'<p><a href="%s">%s</a></p>',
					esc_url( admin_url('edit.php?post_type=sim-invoice') ),
					__('Return to Invioces', 'sighted-invoice-manager')
				);
                $LOGvar_subject = array_values(get_option( $_GET['sim-invoice-template'] ));
                $this->InvoiceLogger("Successfully sent ".$LOGvar_subject[0]." email", $post_id);
			}else{
				echo "<p>Uh Oh! There was an Error!</p>";
                $LOGvar_subject = array_values(get_option( $_GET['sim-invoice-template'] ));
                $this->InvoiceLogger("Failed to send ".$LOGvar_subject[0]." email", $post_id);
			}

		}

		?>
	</div>

</div>




<?php

function PreviewForm( $data = NULL, $template = NULL ){
	if( is_null($data) ){ echo "Need some data"; return;}
	if( is_null($template) ){ echo "Need a template"; return;}

	$template = GetTemplateData($template, $data);


	$toString = $data['client']['name'].' <'.$data['client']['email'].'>';
	$fromString = $data['payee']['name'].' <'.$data['payee']['email'].'>';
	$args = array(
		'to' => $toString,
		'from' => $fromString,
		'fromEmail' => $data['payee']['email'],
		'subject' => $template['subject'],
		'message' => $template['message']
	);


	$url = esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sendemail&sim-invoice='.$data['id'].'&sim-invoice-template='.$template['id']) );
	echo '<form action="'.$url.'" method="post">';
	echo '<table class="form-table"><tbody>';
	echo '<tr>';
	echo '<th scope="row"><label>To: </label></th>';
	echo '<td><input name="to" type="text" class="regular-text code" value="'.$args['to'].'" readonly></td>';


	echo '</tr>';

	echo '<tr>';
	echo '<th scope="row"><label>From: </label></th>';
	echo '<td><input name="from" type="text" class="regular-text code" value="'.$args['from'].'" readonly> <label for="bcc_myself"><input name="bcc_myself" type="checkbox" id="bcc_myself" value="1">Bcc</label><input name="fromEmail" type="hidden" value="'.$args['fromEmail'].'"></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<th scope="row"><label>Subject: </label></th>';
	echo '<td><input name="subject" type="text" class="large-text code" value="'.$args['subject'].'"></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<th scope="row"><label>Message: </label></th>';
	echo '<td><textarea name="message" cols="10" rows="20" class="large-text code">'.$args['message'].'</textarea></td>';
	echo '</tr>';

	echo '</tbody></table>';
	echo '<p class="submit">';
	printf(
		'<a href="%s" class="button button-secondary">%s</a>',
		esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sendemail&sim-invoice='.$data['id']) ),
		__('Back', 'sighted-invoice-manager')
	);
	echo ' ';
	echo '<input type="submit" class="button button-primary" value="Send">';
	echo '</p>';
	echo "</form>";
}

function SendEmail( $data = NULL ){
	if( is_null($data) ){ echo "Need some data"; return;}

	//Set Headers
	$headers[] = "MIME-Version: 1.0";
	$headers[] = "Content-type: text/html; charset=iso-8859-1";
	$headers[] = 'From: '.$data['from'];
	if(isset($data['bcc_myself'])){
		$headers[] = 'Bcc: '.$data['from'];
	}
	$headers[] = 'Reply-To: '.$data['from'];
	$headers[] = "Subject: {".$data['subject']."}";
	$headers[] = "X-Mailer: PHP/".phpversion();

	//Create Mail array
	$args = array(
		'to' => $data['to'],
		'subject' => $data['subject'],
		'message' => $data['message'],
		'headers' => implode("\r\n", $headers)
	);

//	echo '<pre>';
//	print_r($args);
//	echo "\n";
//	echo '</pre>';
//	die('Ready to mail');
	return wp_mail( $args['to'], $args['subject'], $args['message'], $args['headers'] );
}

function InvoiceSelectForm( $current_invoice = 0 ){
	$args = array(
		'posts_per_page'   => 5,
		'offset'           => 0,
		'category'         => '',
		'category_name'    => '',
		'orderby'          => 'date',
		'order'            => 'DESC',
		'include'          => '',
		'exclude'          => '',
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'sim-invoice',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'author'	   => '',
		'post_status'      => 'publish',
		'suppress_filters' => true
	);
	$posts_array = get_posts( $args );
	$url = esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sendemail') );
	echo "<form action='".$url."' method='get'>";
	echo '<input name="post_type" type="hidden" value="sim-invoice">';
	echo '<input name="page" type="hidden" value="sighted-invoice-manager-sendemail">';
	echo "<select name='sim-invoice' id='sim-invoice' class='postform'>";
	echo "<option value=''>Invoice</option>";
	foreach ($posts_array as $post) {
		echo '<option ';
		echo 'value="' . $post->ID . '"';
		if( $current_invoice == $post->ID ){
			echo ' selected="selected"';
		}
		echo '>' . $post->ID .' - ' . $post->post_title .'</option>';
	}
	echo "</select>";
	submit_button( 'Next', 'primary', '' );
	echo "</form>";
}

function CreateInvoiceObject($postID = 0){

	$pm = get_post_meta( $postID );
	$post = get_post($postID);

	//Create Payee
	$term = get_the_terms( $postID, 'sim-payee');
	if(isset($term[0])){
		$term = $term[0];
		$term_meta = get_term_meta($term->term_id);
		//print_r( $term_meta );
		$payee = array(
				'name' => $term->name,
				'address1' => $term_meta["_sim_payee_address1"][0],
				'address2' => $term_meta["_sim_payee_address2"][0],
				'phone' => $term_meta["_sim_payee_phone"][0],
				'email' => $term_meta["_sim_payee_email"][0]
			);
	}else{
		return NULL;
	}

	//Create Client
	$term = get_the_terms( $postID, 'sim-client');
	if(isset($term[0])){
		$term = $term[0];
		$term_meta = get_term_meta($term->term_id);
		//print_r( $term_meta );
		$client = array(
				'name' => $term->name,
				'company' => $term_meta["_sim_client_company"][0],
				'address1' => $term_meta["_sim_client_address1"][0],
				'address2' => $term_meta["_sim_client_address2"][0],
				'phone' => $term_meta["_sim_client_phone"][0],
				'email' => $term_meta["_sim_client_email"][0]
			);
	}else{
		return NULL;
	}

	//Create Line Items
	$line_items = array();
	$entries = get_post_meta( $postID, '_sim_invoice_group_line_item', true );
	foreach ( (array) $entries as $key => $entry ) {
		//print_r($entry);
		$line=array();

		if( !isset($entry['description']) || $entry['description'] == '' ){
			$line['description'] = NULL;
		}else{
			$line['description'] = esc_html( $entry['description'] );
		}

		if( !isset($entry['qty']) || $entry['qty'] == 0 ){
			$line['qty'] = NULL;
		}else{
			$line['qty'] = $entry['qty'];
		}

		if( !isset($entry['price']) || $entry['price'] == 0 ){
			$line['price'] = NULL;
		}else{
			$line['price'] = $entry['price'];
		}

		//Update Totals
		if( ( $line['qty'] != NULL && $line['qty'] != 0 ) && ( $line['price'] != NULL && $line['price'] != 0 ) ){
			$line['total'] = ($line['price'] * $line['qty']);
		}else{
			$line['total'] = 0;
		}

		array_push($line_items, $line);

	}



	$invoice = array(
		'id' => $postID,
		'date' => $pm['_sim_invoice_date'][0],
		'title' => get_the_title($postID),
		'link' => esc_url( get_permalink($postID) ),
		'access-code' => $post->post_password,
		'status' => $pm['_sim_invoice_payment_status'][0],
		'payee' => $payee,
		'client' => $client,
		'line-items' => $line_items
	);

	return $invoice;
}



function TemplateSelectForm( $post_id = 0, $post_template = '' ){
	$url = esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sendemail') );
	echo "<form action='".$url."' method='get'>";
	echo '<input name="post_type" type="hidden" value="sim-invoice">';
	echo '<input name="page" type="hidden" value="sighted-invoice-manager-sendemail">';
	echo '<input name="sim-invoice" type="hidden" value="'.$post_id.'">';
	echo "<select name='sim-invoice-template' id='sim-invoice-template' class='postform'>";
	echo "<option value=''>Template</option>";
	$templates = get_option( 'sim_invoice_templates' );
	foreach($templates as $template=>$default){
		printf(
			'<option value="%s">%s</option>',
			'sim-invoice-template-'.$template,
			$default['title']
		);
	}
	echo "</select>";
	echo '<p class="submit">';
	printf(
		'<a href="%s" class="button button-secondary">%s</a>',
		esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sendemail') ),
		__('Back', 'sighted-invoice-manager')
	);
	echo ' ';
	echo '<input type="submit" class="button button-primary" value="Preview">';
	echo '</p>';
	echo "</form>";
}

function GetTemplateData( $template = NULL, $data = NULL ){
	if( is_null($template) ){ die("Need a template for GetTemplateData"); return;}
	if( is_null($data) ){ die("Need Data for GetTemplateData"); return;}


	$templates = get_option( 'sim_invoice_templates' );
	foreach($templates as $k=>$v){
		$a[] = 'sim-invoice-template-'.$k;
	}
	$templates = $a;

	$templateObject = NULL;

	if(in_array( $template, $templates )){
		$options = array_values(get_option( $template ));
		//set id
		$templateObject['id'] = $template;
		//set subject
		if(!isset($options[0])){
			$templateObject['subject'] = 'This fields default is not set in options yet.';
		}else{
			$templateObject['subject'] = ReplaceTags($options[0], $data);
		}
		//set message
		if(!isset($options[1])){
			$templateObject['message'] = 'This fields default is not set in options yet.';
		}else{
			$templateObject['message'] = ReplaceTags($options[1], $data);
		}
	}else{
		die( "<p>Template <code>$template</code> does not exists</p>" );
	}


//	switch($template){
//		case 'sim-invoice-template-custom':
//			$templateObject['id'] = $template;
//			$options = get_option( $template );
//
//			//set subject
//			if(!isset($options['template-custom-subject'])){
//				$templateObject['subject'] = 'This fields default is not set in options yet.';
//			}else{
//				$templateObject['subject'] = ReplaceTags($options['template-custom-subject'], $data);
//			}
//
//			//set message
//			if(!isset($options['template-custom-message'])){
//				$templateObject['message'] = 'This fields default is not set in options yet.';
//			}else{
//				$templateObject['message'] = ReplaceTags($options['template-custom-message'], $data);
//			}
//
//			break;
//		case 'sim-invoice-template-invoiceservicesrendered':
//			$templateObject['id'] = $template;
//			$options = get_option( $template );
//
//			//set subject
//			if(!isset($options['template-invoiceservicesrendered-subject'])){
//				$templateObject['subject'] = 'This fields default is not set in options yet.';
//			}else{
//				$templateObject['subject'] = ReplaceTags($options['template-invoiceservicesrendered-subject'], $data);
//			}
//
//			//set message
//			if(!isset($options['template-invoiceservicesrendered-message'])){
//				$templateObject['message'] = 'This fields default is not set in options yet.';
//			}else{
//				$templateObject['message'] = ReplaceTags($options['template-invoiceservicesrendered-message'], $data);
//			}
//
//			break;
//		default:
//			die( "Template \"$template\" does not exists" );
//			$templateObject = NULL;
//			break;
//	}
	return $templateObject;

}

function ReplaceTags($oldString = NULL, $data = NULL){
	if( is_null($oldString) ){ die("Need a string for ReplaceTags"); return;}
	if( is_null($data) ){ echo "Need Data for ReplaceTags"; return;}

	$newString = $oldString;

	$current_date = date('U', strtotime(date("Y-m-d 00:00:00", strtotime("today"))));
	$invoice_date = date('U', strtotime(date("Y-m-d 00:00:00", $data['date'])));
	$difference = $current_date - $invoice_date;
	$overdue = intval( ($difference) / (60 * 60 * 24) );

	$tags = array(
		'[id]' => $data['id'],
		'[date]' => date('m\/d\/y', $data['date']),
		'[overdue]' => $overdue.' days',
		'[title]' => $data['title'],
		'[link]' => $data['link'],
		'[access-code]' => $data['access-code'],
		'[status]' => $data['status'],
		'[payee-name]' => $data['payee']['name'],
		'[payee-address1]' => $data['payee']['address1'],
		'[payee-address2]' => $data['payee']['address2'],
		'[payee-phone]' => $data['payee']['phone'],
		'[payee-email]' => $data['payee']['email'],
		'[client-name]' => $data['client']['name'],
		'[client-company]' => $data['client']['company'],
		'[client-address1]' => $data['client']['address1'],
		'[client-address2]' => $data['client']['address2'],
		'[client-phone]' => $data['client']['phone'],
		'[client-email]' => $data['client']['email'],
	);
	foreach($tags as $tag=>$value){
		$pos = strpos($newString, $tag);
		if ($pos !== false) {
			//Found in string
			$newString = str_replace($tag,$value,$newString);
		}
	}
	return $newString;

}

?>





