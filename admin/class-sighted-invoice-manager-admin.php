<?php

class Sighted_Invoice_Manager_Admin {
	private $Sighted_Invoice_Manager;
	private $version;
	public function __construct( $Sighted_Invoice_Manager, $version ) {

		$this->Sighted_Invoice_Manager = $Sighted_Invoice_Manager;
		$this->version = $version;

	}
	public function enqueue_styles() {
		wp_enqueue_style( $this->Sighted_Invoice_Manager, plugin_dir_url( __FILE__ ) . 'css/sighted-invoice-manager-admin.css', array(), $this->version, 'all' );
	}
	public function enqueue_scripts() {
		wp_enqueue_script( $this->Sighted_Invoice_Manager, plugin_dir_url( __FILE__ ) . 'js/sighted-invoice-manager-admin.js', array( 'jquery' ), $this->version, false );
	}


	/**
	 * Create Custom Meta.
	 * https://github.com/WebDevStudios/cmb2
	 *
	 */
	public function Register_Custom_Metaboxes() {
		//Invoice Details
		$prefix = '_sim_invoice_';
		$cmb = new_cmb2_box( array(
			'id'			=> $prefix . 'details',
			'title'		 => __( 'Invoice', 'sighted-invoice-manager' ),
			'object_types'  => array( 'sim-invoice', ), // Post type
			'context'	   => 'normal',
			'priority'	  => 'high',
		) );
		$field_date = $cmb->add_field( array(
			'name'	   => __( 'Date', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'date',
			'type'	   => 'text_date_timestamp',
			'timezone_meta_key' => $prefix . 'timezone',
			'date_format' => 'm\/d\/y',
			'default'	 => date("m\/d\/y"),
		) );
		$field_payment = $cmb->add_field( array(
			'name'			 => __( 'Status', 'sighted-invoice-manager' ),
			'id'			   => $prefix . 'payment_status',
			'type'			 => 'select',
			'show_option_none' => false,
			'default'		  => 'unpaid',
			'options'		  => array(
				'unpaid'   => __( 'Unpaid', 'sighted-invoice-manager' ),
				'paid'	 => __( 'Paid', 'sighted-invoice-manager' ),
				'pending'  => __( 'Payment Pending', 'sighted-invoice-manager' ),
			),
		) );
		$cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($cmb);
		$row = $cmb2Grid->addRow();
		$row->addColumns(array(
		   array($field_date, 'class' => 'col-md-4'),
		   array($field_payment, 'class' => 'col-md-8')
		));
		$field_payee = $cmb->add_field( array(
			'name'	 => __( 'Payee', 'sighted-invoice-manager' ),
			'desc'	 => __( 'Who is getting paid?', 'sighted-invoice-manager' ),
			'id'	   => $prefix . 'payee',
			'type'	 => 'taxonomy_select',
			'taxonomy' => 'sim-payee', // Taxonomy Slug
		) );
		$field_client = $cmb->add_field( array(
			'name'	 => __( 'Client', 'sighted-invoice-manager' ),
			'desc'	 => __( 'Who is paying?', 'sighted-invoice-manager' ),
			'id'	   => $prefix . 'client',
			'type'	 => 'taxonomy_select',
			'taxonomy' => 'sim-client', // Taxonomy Slug
		) );
		$cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($cmb);
		$row = $cmb2Grid->addRow();
		$row->addColumns(array(
		   array($field_payee, 'class' => 'col-md-6'),
		   array($field_client, 'class' => 'col-md-6')
		));


		//Line Items
		$prefix = '_sim_invoice_group_';
		$cmb_group = new_cmb2_box( array(
			'id'		   => $prefix . 'line_items',
			'title'		=> __( 'Line Items', 'sighted-invoice-manager' ),
			'object_types' => array( 'sim-invoice', ),
		) );
		$group_field_id = $cmb_group->add_field( array(
			'id'		  => $prefix . 'line_item',
			'type'		=> 'group',
			'options'	 => array(
				'group_title'   => __( 'Item {#}', 'sighted-invoice-manager' ), // {#} gets replaced by row number
				'add_button'	=> __( 'Add Another Item', 'sighted-invoice-manager' ),
				'remove_button' => __( 'Remove Item', 'sighted-invoice-manager' ),
				'sortable'	  => true, // beta
				'closed'		=> true, // true to have the groups closed by default
			),
		) );
		$gField1 = $cmb_group->add_group_field( $group_field_id, array(
			'name'	   => __( 'Description', 'sighted-invoice-manager' ),
			'id'		 => 'description',
			'type'	   => 'text',
			'attributes'  => array(
				'placeholder'	=> 'Labor',
			),
		) );
		$gField2 = $cmb_group->add_group_field( $group_field_id, array(
			'name'	   => __( 'Qty', 'sighted-invoice-manager' ),
			'id'		 => 'qty',
			'type'	   => 'text',
			'attributes'  => array(
				'placeholder'	=> '0',
			),
		) );
		$gField3 = $cmb_group->add_group_field( $group_field_id, array(
			'name'	   => __( 'Price per Qty', 'sighted-invoice-manager' ),
			'id'		 => 'price',
			'type'	   => 'text_money',
			'before_field' => ' ', // override '$' symbol if needed
			'attributes'  => array(
				'placeholder'	=> '0.00',
			),
		) );
		$cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($cmb_group);
		$cmb2GroupGrid   = $cmb2Grid->addCmb2GroupGrid($group_field_id);
		$row			 = $cmb2GroupGrid->addRow();
		$row->addColumns(array(
			$gField1, $gField2, $gField3
		));


		//Invoice Logs
		$prefix = '_sim_invoice_logs_';
		$fields = array();
		$cmb = new_cmb2_box( array(
			'id'			=> $prefix . 'invoice',
			'title'		 => __( 'Invoice', 'sighted-invoice-manager' ),
			'object_types'  => array( 'sim-invoice-logs', ), // Post type
			'context'	   => 'normal',
			'priority'	  => 'high',
		) );
		$fields[] = $cmb->add_field( array(
			'name'	   => __( 'Invoice ID', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'invoice_id',
			'type'	   => 'text'
		) );
		$fields[] = $cmb->add_field( array(
			'name'	   => __( 'User', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'user',
			'type'	   => 'text'
		) );
		$cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($cmb);
		$row = $cmb2Grid->addRow();
		$row->addColumns(array(
		   array($fields[0], 'class' => 'col-md-12')
		));
		$fields = array();
	}


	/**
	 * Custom Post Type Quicklinks.
	 *
	 */
	function Change_Sighted_Invoice_Quicklinks($views) {
		unset($views['publish']);
		return $views;
	}

	/**
	 * Custom Post Type Columns.
	 *
	 */
	 //Invoices
	public function Change_Sighted_Invoice_Columns( $columns ) {
		$new_columns = array();
		$new_columns['cb'] = $columns['cb'];
		$new_columns['id'] = __( 'ID', 'sighted-invoice-manager' );
		// $new_columns['taxonomy-sim-client'] = $columns['taxonomy-sim-client'];
		$new_columns['custom-taxonomy-sim-client'] = __( 'Client', 'sighted-invoice-manager');
		$new_columns['invoice_date'] = __( 'Date', 'sighted-invoice-manager' );
		$new_columns['total'] = __( 'Total', 'sighted-invoice-manager' );
		$new_columns['payment_status'] = __( 'Payment', 'sighted-invoice-manager' );
		// $new_columns['access_code'] = __( 'Access Code', 'sighted-invoice-manager' );
//		$new_columns['actions'] = __( 'Actions', 'sighted-invoice-manager' );
//		$new_columns['title'] = $columns['title'];
//		$new_columns['custom_title'] = __( 'Title', 'sighted-invoice-manager' );
//		$new_columns['taxonomy-sim-payee'] = $columns['taxonomy-sim-payee'];
		return $new_columns;
	}
	// Display the column content
	public function Change_Sighted_Invoice_Columns_Display( $column, $post_id ) {
		$post = get_post($post_id);
		global $wp;
		$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
		switch ( $column ) {
			case 'custom_title' :
				edit_post_link( get_the_title( $post_id ), '', '', $post_id );
				break;
			case 'id' :
				$meta = '';
				$meta .= sprintf(
					'<strong><a href="%s" title="%s">%s</a></strong>',
					esc_url( get_edit_post_link( $post_id, '' ) ),
					__( 'Edit this item', 'sighted-invoice-manager' ),
					$post_id.' - '.get_the_title( $post_id )
				);
				$links = array();
				array_push($links, sprintf(
					'<span class="edit"><a href="%s" title="%s">%s</a></span>',
					esc_url( get_edit_post_link( $post_id, '' ) ),
					__( 'Edit', 'sighted-invoice-manager' ),
					__( 'Edit', 'sighted-invoice-manager' )
				));
				array_push($links, sprintf(
					'<span class="trash"><a class="submitdelete" href="%s" title="%s">%s</a></span>',
					esc_url( get_delete_post_link( $post_id, '' ) ),
					__( 'Move this item to the Trash', 'sighted-invoice-manager' ),
					__( 'Trash', 'sighted-invoice-manager' )
				));
				array_push($links, sprintf(
					'<span class="view"><a href="%s" title="%s">%s</a></span>',
					esc_url( get_permalink($post_id) ),
					__( 'View', 'sighted-invoice-manager' ),
					__( 'View', 'sighted-invoice-manager' )
				));
				array_push($links, sprintf(
					'<span class="send-email"><a href="%s" title="%s">%s</a></span>',
					esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sendemail&sim-invoice='.$post_id) ),
					__( 'Send Email', 'sighted-invoice-manager' ),
					__( 'Send Email', 'sighted-invoice-manager' )
				));
				$meta .= '<div class="row-actions">' . join(' | ', $links) . '</div>';
				$meta .= '<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>';
				echo $meta;
				break;
			case 'invoice_date' :
				$date = get_post_meta($post_id, '_sim_invoice_date', true);
				if ( !$date ){
					$meta = '—';
				}else{
					$date_formatted = date('m\/d\/y', $date);
					$meta = $date_formatted;
				}
				echo $meta;
				break;
			case 'payment_status' :
				$meta = get_post_meta($post_id, '_sim_invoice_payment_status', true);
				if ( !$meta ){
					$meta = '—';
				}else{
					$meta = ucwords($meta);
					if( in_array( strtolower($meta), array('unpaid', 'pending') ) ){
						$date = get_post_meta($post_id, '_sim_invoice_date', true);
						$current_date = date('U', strtotime(date("Y-m-d 00:00:00", strtotime("today"))));
						$invoice_date = date('U', strtotime(date("Y-m-d 00:00:00", $date)));
						$difference = $current_date - $invoice_date;
						$numdays = intval( ($difference) / (60 * 60 * 24) );

						if($numdays < 30){
							$color = '2ECC40'; //Green
						}elseif($numdays < 90){
							$color = 'FFDC00'; //Yellow
						}else{
							$color = 'FF4136'; //Red
						}

						if($numdays > 1){
							//Overdue
							$string = "Overdue " . $numdays . " days";
						}elseif($numdays < -1){
							//Not due yet
							$string = "Due in " . ($numdays*-1) . " days";
						}elseif($numdays == 0){
							//Due today
							$string = "Due Today";
						}elseif($numdays == 1){
							//Due today
							$string = "Due Yesterday";
						}elseif($numdays == -1){
							//Due today
							$string = "Due Tomorrow";
						}else{
							//Whats Left?!
							$string = "What options could be left?";
						}

						$links = array();
						if( in_array( get_post_meta($post_id, '_sim_invoice_payment_status', true), array('unpaid', 'pending')) ){
							array_push($links, sprintf(
								'<span class="mark-paid"><a href="%s" title="%s">%s</a></span>',
								//esc_url( admin_url('edit.php?post_type=sim-invoice&mark-paid='.$post_id) ),
								esc_url($current_url.'&mark-paid='.$post_id),
								__( 'Mark this item Paid', 'sighted-invoice-manager' ),
								__( 'Mark Paid', 'sighted-invoice-manager' )
							));
						}
						//$meta .= print_r($wp);
						$meta .= '<div class="row-actions" style="display: inline; padding-left: 8px;">' . join(' | ', $links) . '</div>';

						$meta .= "<br><span style='color:#".$color."'>".$string."</span>";

						$links = array();
						array_push($links, sprintf(
							'<span class="send-email"><a href="%s" title="%s">%s</a></span>',
							esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sendemail&sim-invoice='.$post_id.'&sim-invoice-template=sim-invoice-template-paymentreminder') ),
							__( 'Send Payment Reminder', 'sighted-invoice-manager' ),
							'<span class="dashicons dashicons-email-alt"></span>'
						));
						$meta .= '<div class="row-actions" style="display: inline; padding-left: 8px;">' . join(' | ', $links) . '</div>';

					}
				}
				echo $meta;
				break;
			case 'access_code' :
				echo $post->post_password;
				printf(
					'<div class="row-actions inline"><span class="send-email"><a href="%s" title="%s">%s</a></span></div>',
					esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sendemail&sim-invoice='.$post_id.'&sim-invoice-template=sim-invoice-template-accesscode') ),
					__( 'Send Access Code', 'sighted-invoice-manager' ),
					'<span class="dashicons dashicons-email-alt"></span>'
				);
				break;
			case 'actions' :
				printf(
					'<a href="%s">%s</a>',
					esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sendemail&sim-invoice='.$post_id) ),
					__( 'Send Email', 'sighted-invoice-manager' )
				);
				break;
			case 'total' :
				$meta = get_post_meta( $post_id, '_sim_invoice_group_line_item', true );
				$total = 0;

				foreach ( (array) $meta as $line_item => $key ) {
					$qty = $price = $subprice = 0;
					if ( isset( $key['qty'] ) ){ $qty = $key['qty']; }
					if ( isset( $key['price'] ) ){ $price = $key['price']; }
					$subprice = $qty * $price;
					$total += $subprice;
				}

				setlocale(LC_MONETARY, 'en_US');
				echo money_format('%.2n', $total);
				break;
			case 'custom-taxonomy-sim-client':
				$client = get_the_terms($post_id, 'sim-client');
				if ( !$client ){
					$meta = '—';
				}else{
					$meta = sprintf(
						'<a href="%s">%s</a><br>%s',
						esc_url( admin_url('edit.php?post_type=sim-invoice&sim-client='.$client[0]->slug) ),
						$client[0]->name,
						get_term_meta($client[0]->term_id, '_sim_client_company', true)
					);
				}
				echo $meta;
				break;
		}
	}
	function Set_Sighted_Invoice_Primary_Columns( $column, $screen ) {
		switch ($screen) {
			case 'edit-sim-invoice':
				$column = 'id';
				break;
			case 'edit-sim-invoice-logs':
				$column = 'custom_title';
				break;
		}
		return $column;
	}
	// Set custom row action buttons, shown on hover
	function Set_Sighted_Invoice_Row_Action_Buttons($actions, $post){
		switch ($post->post_type) {
			case 'sim-invoice':
					$actions['send_email'] = sprintf(
						'<span class="send-email"><a href="%s" title="%s">%s</a></span>',
						esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-sendemail&sim-invoice='.$post->ID) ),
						__( 'Send Email', 'sighted-invoice-manager' ),
						__( 'Send Email', 'sighted-invoice-manager' )
					);
				break;
		}
		return $actions;
	}


	// Register the column as sortable
	public function Make_Sighted_Invoice_Columns_Sortable( $columns ) {
		$columns['id'] = 'id';
		$columns['custom_title'] = 'title';
		$columns['invoice_date'] = 'invoice_date';
		$columns['payment_status'] = 'payment_status';
		//$columns['total'] = 'total'; //DOESNT WORK, NO WAY TO GET TOTAL FOR ORDER
		return $columns;
	}

	//Invoice Logs
	public function Change_Sighted_Invoice_Logs_Columns( $columns ) {
		$new_columns = array();
		$new_columns['cb'] = $columns['cb'];
		$new_columns['custom_title'] = $columns['title'];
		$new_columns['invoice_id'] = __( 'Invoice ID', 'sighted-invoice-manager' );
		$new_columns['user'] = __( 'User', 'sighted-invoice-manager' );
		$new_columns['custom_date'] = $columns['date'];
		return $new_columns;
	}
	// Display the column content
	public function Change_Sighted_Invoice_Logs_Columns_Display( $column, $post_id ) {
		switch ( $column ) {
			case 'invoice_id':
				$value = get_post_meta($post_id, '_sim_invoice_logs_invoice_id', true);
				if ( !$value ){
					$meta = '—';
				}else{
					$meta = $value;
				}
				echo $meta;
				break;
			case 'user':
				$value = get_post_meta($post_id, '_sim_invoice_logs_user', true);
				if ( !$value ){
					$meta = '—';
				}else{
					$meta = $value;
				}
				echo $meta;
				break;
			case 'custom_date':
				$value = get_the_date( 'Y/m/d g:i:s A', $post_id );
				if ( !$value ){
					$meta = '—';
				}else{
					$meta = $value;
				}
				echo $meta;
				break;
			case 'custom_title':
				$value = get_the_title($post_id);
				if ( !$value ){
					$meta = '—';
				}else{
					$meta = $value;
				}
				echo $meta;
				break;
		}
	}
	// Register the column as sortable
	public function Make_Sighted_Invoice_Logs_Columns_Sortable( $columns ) {
		$columns['custom_title'] = 'title';
		$columns['invoice_id'] = 'invoice_id';
		$columns['custom_date'] = 'date';
		return $columns;
	}
	/**
	 * Custom Post Type Filterable Taxonomy.
	 *
	 */
	public function Make_Sighted_Invoice_Columns_Filterable() {
		global $typenow;

		//Invoice
		if( $typenow == 'sim-invoice' ){
			$taxonomies = array('sim-client', 'sim-payee');
			foreach ($taxonomies as $tax_slug) {
				$tax_obj = get_taxonomy($tax_slug);
				$tax_name = $tax_obj->labels->all_items;
				$terms = get_terms($tax_slug);
				if(count($terms) > 0) {
					echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
					echo "<option value=''>$tax_name</option>";
					foreach ($terms as $term) {
						if($tax_slug == 'sim-client'){
							$value = $term->name . ' - ' . get_term_meta($term->term_id, '_sim_client_company', true);
						}else{
							$value = $term->name;
						}
						echo '<option ';
						echo 'value="' . $term->slug . '"';
						if(isset($_GET[$tax_slug]) && $_GET[$tax_slug] == $term->slug ){
							echo ' selected="selected"';
						}
						echo '>' . $value . ' (' . $term->count .')</option>';
					}
					echo "</select>";
				}
			}

			//add Payment Status filter
			echo "<select name='sim-invoice-payment-status' id='sim-invoice-payment-status' class='postform'>";
			echo "<option value=''>All Payment Statuses</option>";
			$terms = array('paid', 'unpaid', 'pending');
			foreach ($terms as $term) {
				$query = new WP_Query( array(
					'post_type'  => 'sim-invoice',
					'meta_key'   => '_sim_invoice_payment_status',
					'meta_value' => $term
				) );
				//print_r($query);
				$count = $query->found_posts;
				echo '<option ';
				echo 'value="' . $term . '"';
				if(isset($_GET['sim-invoice-payment-status']) && $_GET['sim-invoice-payment-status'] == $term ){
					echo ' selected="selected"';
				}
				echo '>' . ucwords($term) .' (' . $count .')</option>';
			}
			echo "</select>";

		//Invoice Logs
		}elseif($typenow == 'sim-invoice-logs'){

			//add Invoice ID filter
			echo "<select name='sim-invoice-id' id='sim-invoice-id' class='postform'>";
			echo "<option value=''>All Invoices</option>";
			//get all logs, extract id into array key and count as value
			$query = new WP_Query( array(
				'post_type'  => 'sim-invoice-logs',
				'post_status' => 'publish',
				'posts_per_page' => -1
			) );
			$options = array();
			if( $query->have_posts() ) {
				while ($query->have_posts()) : $query->the_post();
					$invoice_id = get_post_meta(get_the_ID(), '_sim_invoice_logs_invoice_id', true);
					if( ! isset( $options[$invoice_id] ) ){
						$options[$invoice_id] = 1;
					}else{
						$options[$invoice_id] += 1;
					}
				endwhile;
			}
			wp_reset_query();

			//foreach in array create option
			foreach ($options as $option => $count) {
				echo '<option ';
				echo 'value="' . $option . '"';
				if(isset($_GET['sim-invoice-id']) && $_GET['sim-invoice-id'] == $option ){
					echo ' selected="selected"';
				}
				echo '>' . ucwords($option) . ' - ' . get_the_title($option) .' (' . $count .')</option>';
			}
			echo "</select>";

		}
	}

	/**
	 * Remove date filter from cpt.
	 *
	 */
	public function Remove_Sighted_Invoice_Date_Filter() {
		global $typenow;
		if( $typenow == 'sim-invoice' ){
			return array();
		}
		if( $typenow == 'sim-invoice-logs' ){
			return array();
		}
	}



	/**
	 * Custom Taxonomy Options.
	 *
	 */
	public function Register_Custom_Client_Metaboxes() {
		// Start with an underscore to hide fields from custom fields list
		$prefix = '_sim_client_';
		//Taxonomy Metadata
		$cmb_term = new_cmb2_box( array(
			'id'			=> $prefix . 'details',
			'object_types'  => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'	=> array( 'sim-client' ), // Tells CMB2 which taxonomies should have these fields
			'context'	   => 'normal',
			'priority'	  => 'high',
		) );
		$field_company = $cmb_term->add_field( array(
			'name'	   => __( 'Company', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'company',
			'type'	   => 'text'
		) );
		$field_address1 = $cmb_term->add_field( array(
			'name'	   => __( 'Address Line 1', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'address1',
			'type'	   => 'text'
		) );
		$field_address2 = $cmb_term->add_field( array(
			'name'	   => __( 'Address Line 2', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'address2',
			'type'	   => 'text'
		) );
		$field_phone = $cmb_term->add_field( array(
			'name'	   => __( 'Phone', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'phone',
			'type'	   => 'text'
		) );
		$field_email = $cmb_term->add_field( array(
			'name'	   => __( 'Email', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'email',
			'type'	   => 'text_email'
		) );
	}

	public function Register_Custom_Payee_Metaboxes() {
		// Start with an underscore to hide fields from custom fields list
		$prefix = '_sim_payee_';
		//Taxonomy Metadata
		$cmb_term = new_cmb2_box( array(
			'id'			=> $prefix . 'details',
			'object_types'  => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'	=> array( 'sim-payee' ), // Tells CMB2 which taxonomies should have these fields
			'context'	   => 'normal',
			'priority'	  => 'high',
		) );
		$field_address1 = $cmb_term->add_field( array(
			'name'	   => __( 'Address Line 1', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'address1',
			'type'	   => 'text'
		) );
		$field_address2 = $cmb_term->add_field( array(
			'name'	   => __( 'Address Line 2', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'address2',
			'type'	   => 'text'
		) );
		$field_phone = $cmb_term->add_field( array(
			'name'	   => __( 'Phone', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'phone',
			'type'	   => 'text'
		) );
		$field_email = $cmb_term->add_field( array(
			'name'	   => __( 'Email', 'sighted-invoice-manager' ),
			'id'		 => $prefix . 'email',
			'type'	   => 'text_email'
		) );
	}


	public function Change_Sighted_Invoice_Client_Columns( $columns ) {
		$new_columns['cb'] = $columns['cb'];
		$new_columns['name'] = $columns['name'];
		$new_columns['company'] = 'Company';
		$new_columns['posts'] = $columns['posts'];
		return $new_columns;
	}
	public function Change_Sighted_Invoice_Client_Columns_Display( $deprecated, $column, $term_id ) {
		switch ( $column ) {
			case 'company' :
				$company = get_term_meta($term_id, '_sim_client_company', true);
				echo $company;
				break;
		}
	}

	public function Change_Sighted_Invoice_Payee_Columns( $columns ) {
		unset($columns['description']);
		unset($columns['slug']);
		return $columns;
	}

	public function Hide_Sighted_Invoice_Tax_Meta(){
		global $post_type;
		if( 'sim-invoice' != $post_type ){return;}

		global $current_screen;
		switch ( $current_screen->id )
		{
			case 'edit-category':
				// WE ARE AT /wp-admin/edit-tags.php?taxonomy=category
				// OR AT /wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID=1&post_type=post
				break;
			case 'edit-post_tag':
				// WE ARE AT /wp-admin/edit-tags.php?taxonomy=post_tag
				// OR AT /wp-admin/edit-tags.php?action=edit&taxonomy=post_tag&tag_ID=3&post_type=post
				break;
		}
		?>
		<script type="text/javascript">
		jQuery(document).ready( function($) {
			$('#tag-description').parent().remove();
			$('.term-description-wrap').remove();
			$('#tag-slug').parent().remove();
			$('.term-slug-wrap').remove();
			$('.term-name-wrap p').remove();
		});
		</script>
		<?php
	}

	/**
	 * Set default visibility to private.
	 *
	 */
	function Sighted_Invoice_Post_Transition( $new_status, $old_status, $post ) {
		if ( $post->post_type == 'sim-invoice'){
			$update = false;
			//Create new
			if( $old_status == 'new' && $new_status == 'auto-draft'){
				$this->InvoiceLogger("Created new Invoice", $post->ID);
			}
			//Published
			if( ($old_status == 'auto-draft' || $old_status == 'pending' || $old_status == 'draft') && $new_status == 'publish'){
				$this->InvoiceLogger("Published", $post->ID);
			}
			//Unpublished
			if( $old_status == 'publish' && ($new_status == 'auto-draft' || $new_status == 'pending' || $new_status == 'draft')){
				$this->InvoiceLogger("Unpublished: ".$new_status, $post->ID);
			}
			//Trashed
			if( $new_status == 'trash' ){
				$this->InvoiceLogger("Trashed", $post->ID);
			}
			//Restored
			if( $old_status == 'trash' ){
				$this->InvoiceLogger("Restored from trash", $post->ID);
			}
			if( $post->post_status == 'private' ){
				$post->post_status = 'publish';
				$update = true;
			}
			if( !$post->post_password || $post->post_password == ''){
				$post->post_password = wp_generate_password( 8, false );
				$update = true;
			}
			if( $update ){
				wp_update_post( $post );
				$this->InvoiceLogger("Updated password to ".$post->post_password, $post->ID);
			}
		}
	}

	function Sighted_Invoice_Post_Misc_Actions(){
		global $post;
		if ($post->post_type != 'sim-invoice'){ return; }

		$message = __('Visibility must be <strong>password protected</strong>.');
		?>
		<style type="text/css">
		.Sighted_visibiltiy_warning {
			background-color: lightred;
			border: 1px solid red;
			border-radius: 2px;
			margin: 4px;
			padding: 4px;
		}
		</style>
		<script type="text/javascript">
		(function($){
			try {
				var s = $('#post-visibility-display').text();
				console.log(s);
				$('#post-visibility-select').find('br').hide();
				$("input[id='visibility-radio-public']").hide();
				$("label[for='visibility-radio-public']").hide();
				$("input[id='visibility-radio-private']").hide();
				$("label[for='visibility-radio-private']").hide();
				$("input[id='visibility-radio-password']").hide();
				$("label[for='visibility-radio-password']").hide();
				$("#visibility-radio-password").prop("checked", true)
			} catch(err){}
		}) (jQuery);
		</script>
		<?php
	}





	/**
	 * Admin Requests.
	 *
	 */
	public function Sighted_Invoice_Requests( $vars ) {
		if( isset($vars['post_type']) && $vars['post_type'] == 'sim-invoice' ){

			//Check orderby or set default
			if ( isset( $vars['orderby'] ) ) {

				switch ( $vars['orderby'] ) {
					case 'title' :
						$vars = array_merge( $vars, array(
							'orderby' => 'title'
						) );
						break;
					case 'id' :
						$vars = array_merge( $vars, array(
							'orderby' => 'ID'
						) );
						break;
					case 'payment_status' :
						$vars = array_merge( $vars, array(
							'meta_key' => '_sim_invoice_payment_status',
							'orderby' => 'meta_value'
						) );
						break;
					case 'invoice_date' :
						$vars = array_merge( $vars, array(
							'meta_key' => '_sim_invoice_date',
							'orderby' => 'meta_value_num',
						) );
						break;
					case 'meta_value' : //has to be kept here for new urls, which change orderyby to meta_value
						$vars = array_merge( $vars, array(
							'meta_key' => $_GET['meta_key'],
						) );
						break;
					case 'meta_value_num' : //has to be kept here for new urls, which change orderyby to meta_value_num
						$vars = array_merge( $vars, array(
							'meta_key' => $_GET['meta_key'],
						) );
						break;
					default :
						break;
				}
			}else{
				//default is invoice date
				$vars = array_merge( $vars, array(
					'meta_key' => '_sim_invoice_date',
					'orderby' => 'meta_value_num',
					'order' => 'DESC'
				) );
			}


			//Check GET
			if ( isset($_GET['mark-paid']) ) {
				$post_id = $_GET['mark-paid'];
				if(is_string( get_post_status( $post_id ) )){
					$current_status = get_post_meta($post_id, '_sim_invoice_payment_status', true);
					if( $current_status != 'paid' ){
						update_post_meta($post_id, '_sim_invoice_payment_status', 'paid');
						$this->InvoiceLogger("Marked Paid", $post_id);
					}
					unset($_GET['mark-paid']);
				}else{
					die( "Can't find " . $post_id );
				}
			}

			if ( isset( $_GET['filter_action'] ) && isset( $_GET['sim-invoice-payment-status'] ) ) {
				if($_GET['sim-invoice-payment-status'] == 'paid'){
					$vars = array_merge( $vars, array(
						'meta_key' => '_sim_invoice_payment_status',
						'meta_value' => 'paid'
					) );
				}elseif($_GET['sim-invoice-payment-status'] == 'unpaid'){
					$vars = array_merge( $vars, array(
						'meta_key' => '_sim_invoice_payment_status',
						'meta_value' => 'unpaid'
					) );
				}elseif($_GET['sim-invoice-payment-status'] == 'pending'){
					$vars = array_merge( $vars, array(
						'meta_key' => '_sim_invoice_payment_status',
						'meta_value' => 'pending'
					) );
				}
			}
			if ( isset( $_GET['filter_action'] ) && isset( $_GET['sim-invoice-id'] ) ) {
				$vars = array_merge( $vars, array(
					'meta_key' => '_sim_invoice_logs_invoice_id',
					'meta_value' => $_GET['sim-invoice-id']
				) );
			}
		}

		return $vars;
	}
	public function Sighted_Invoice_Logs_Requests( $vars ) {
		if( isset($vars['post_type']) && $vars['post_type'] == 'sim-invoice-logs' ){

			//Check orderby or set default
			if ( isset( $vars['orderby'] ) ) {
				switch ( $vars['orderby'] ) {
					case 'title' :
						$vars = array_merge( $vars, array(
							'orderby' => 'title'
						) );
						break;
					case 'date' :
						$vars = array_merge( $vars, array(
							'orderby' => 'date'
						) );
						break;
					case 'invoice_id' :
						$vars = array_merge( $vars, array(
							'meta_key' => '_sim_invoice_logs_invoice_id',
							'orderby' => 'meta_value_num'
						) );
						break;
					default :
						break;
								}
			}

			//Check GET
			if ( isset( $_GET['filter_action'] ) && isset( $_GET['sim-invoice-id'] ) ) {
				$vars = array_merge( $vars, array(
					'meta_key' => '_sim_invoice_logs_invoice_id',
					'meta_value' => $_GET['sim-invoice-id']
				) );
			}

		}

		return $vars;
	}




	/**
	 * Custom Admin Pages.
	 *
	 */
	function Sighted_Invoice_Manager_Admin_Menu(  ) {
		global $submenu;

		//remove add new from menu
		unset($submenu['edit.php?post_type=sim-invoice'][10]);

		//add reports page
		// add_submenu_page(
		// 	'edit.php?post_type=sim-invoice',
		// 	__( 'Invoice Manager', 'sighted-invoice-manager' ),
		// 	__( 'Reports', 'sighted-invoice-manager' ),
		// 	'manage_options',
		// 	$this->Sighted_Invoice_Manager.'-reports',
		// 	array( $this, 'Sighted_Invoice_Manager_reports_page' )
		// );
		//add send email page
		add_submenu_page(
			'edit.php?post_type=sim-invoice',
			__( 'Invoice Manager', 'sighted-invoice-manager' ),
			__( 'Send Email', 'sighted-invoice-manager' ),
			'manage_options',
			$this->Sighted_Invoice_Manager.'-sendemail',
			array( $this, 'Sighted_Invoice_Manager_sendemail_page' )
		);
		//add how to use to menu
		// add_submenu_page(
		// 	'edit.php?post_type=sim-invoice',
		// 	__( 'How to use Invoice Manager', 'sighted-invoice-manager' ),
		// 	__( 'How To Use', 'sighted-invoice-manager' ),
		// 	'manage_options',
		// 	$this->Sighted_Invoice_Manager.'-howto',
		// 	array( $this, 'Sighted_Invoice_Manager_howto_page' )
		// );
		//add options page
		add_submenu_page(
			'edit.php?post_type=sim-invoice',
			__( 'Sighted Invoice Manager', 'sighted-invoice-manager' ),
			__( 'Options', 'sighted-invoice-manager' ),
			'manage_options',
			$this->Sighted_Invoice_Manager.'-options',
			array( $this, 'Sighted_Invoice_Manager_options_page' )
		);

		add_submenu_page(
			'edit.php?post_type=sim-invoice',
			__( 'Sighted Invoice Manager', 'sighted-invoice-manager' ),
			__( 'Sync', 'sighted-invoice-manager' ),
			'manage_options',
			$this->Sighted_Invoice_Manager.'-sync',
			array( $this, 'Sighted_Invoice_Manager_sync_page' )
		);
	}
	function Sighted_Invoice_Manager_howto_page(  ) {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/sighted-invoice-manager-admin-display-howto.php';
	}
	function Sighted_Invoice_Manager_options_page(  ) {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/sighted-invoice-manager-admin-display-options.php';
	}
	function Sighted_Invoice_Manager_reports_page(  ) {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/sighted-invoice-manager-admin-display-reports.php';
	}
	function Sighted_Invoice_Manager_sendemail_page(  ) {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/sighted-invoice-manager-admin-display-sendemail.php';
	}

	function Sighted_Invoice_Manager_sync_page() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/sighted-invoice-manager-admin-display-sync.php';
	}


	 /**
	 * Settings for Media Options.
	 *
	 * @since	1.0.0
	 */
	function Sighted_Invoice_Manager_settings_init(  ) {
		//Options Tab
		register_setting( 'sim-invoice-options', 'sim-invoice-options' );
		add_settings_section(
			'invoice-display-section', //section ID
			__( 'Invoice Display', 'sighted-invoice-manager' ),
			array( $this, 'Sighted_Invoice_Manager_settings_render_section_invoice_display' ),
			'sim-invoice-options'
		);
		add_settings_field(
			'font', //field ID
			__( 'Font', 'sighted-invoice-manager' ),
			array( $this, 'Sighted_Invoice_Manager_settings_render_field_font' ),
			'sim-invoice-options',
			'invoice-display-section' //belongs to section ID?
		);
		add_settings_field(
			'visit-link-view', //field ID
			__( 'When visiting the link', 'sighted-invoice-manager' ),
			array( $this, 'Sighted_Invoice_Manager_settings_render_field_visit_link_view' ),
			'sim-invoice-options',
			'invoice-display-section' //belongs to section ID?
		);

		//Mail Tab
		//Templates Option
		$default = array(
			'custom' => array(
				'title' => 'Custom',
				'subject' => 'Custom Subject',
				'message' => "Custom Message"
				),
			'invoiceservicesrendered' => array(
				'title' => 'Invoice for Services Rendered',
				'subject' => 'Invoice for Services Rendered',
				'message' => "[client-name]\n[client-company]\n\nYour invoice for [date] is ready. You can view your copy by using the link and access code provided below.\n\nWork Performed: [title]\nInvoice ID: [id]\nInvoice Date: [date]\nLink: [link]\nAccess Code: [access-code]\n\nThank you for your business\n\n\n[payee-name]\n[payee-email]\n[payee-phone]"
				),
			'invoicepaid' => array(
				'title' => 'Invoice Paid',
				'subject' => 'Invoice Paid',
				'message' => "[client-name]\n[client-company]\n\nYour invoice for [date] is [status]. You can view your copy by using the link and access code provided below.\n\nWork Performed: [title]\nInvoice ID: [id]\nInvoice Date: [date]\nLink: [link]\nAccess Code: [access-code]\n\nThank you for your business\n\n\n[payee-name]\n[payee-email]\n[payee-phone]"
				),
			'accesscode' => array(
				'title' => 'Access Code',
				'subject' => 'Access Code',
				'message' => "[client-name]\n[client-company]\n\nHere is the access code you requested.\n\nLink: [link]\nAccess Code: [access-code]\n\nThank you\n\n\n[payee-name]\n[payee-email]\n[payee-phone]"
				),
			'paymentreminder' => array(
				'title' => 'Payment Reminder',
				'subject' => 'Payment Reminder',
				'message' => "[client-name]\n[client-company]\n\nThis is a reminder your invoice for [date] is currently overdue by [overdue]. You can view your copy by using the link and access code provided below.\n\nWork Performed: [title]\nInvoice ID: [id]\nInvoice Date: [date]\nLink: [link]\nAccess Code: [access-code]\n\nThank you for your business\n\n\n[payee-name]\n[payee-email]\n[payee-phone]"
				),
		);
		$templates = get_option( 'sim_invoice_templates' );
		//if templates doesnt exist add it
		if( !$templates ){
			echo '<!-- Template does not exist -->';
			add_option( 'sim_invoice_templates', $default );
			$templates = get_option( 'sim_invoice_templates' );
		}
		//if it does exist but does not match new defaults then replace
		$originalTemplates = $templates;
		$newTemplates = array_merge($default, $templates);
		$arraysAreEqual = ($originalTemplates == $newTemplates); // TRUE if $a and $b have the same key/value pairs.
		if( ! $arraysAreEqual ){
			update_option( 'sim_invoice_templates', $newTemplates );
			$templates = get_option( 'sim_invoice_templates' );
		}
		//create individual options for each template now
		foreach($templates as $template=>$default){
			$option_name = 'sim-invoice-template-'.$template;
			$section_id = 'template-'.$template.'-section';
			register_setting( $option_name, $option_name );
			add_settings_section(
				$section_id, //section ID
				__( $default['title'], 'sighted-invoice-manager' ),
				array( $this, 'Sighted_Invoice_Manager_settings_render_section_template' ),
				$option_name
			);
			add_settings_field(
				'template-'.$template.'-subject', //field ID
				__( 'Subject', 'sighted-invoice-manager' ),
				array( $this, 'Sighted_Invoice_Manager_settings_render_field_template_subject' ),
				$option_name, //belongs to option name?
				$section_id, //belongs to section ID?
				array($template, $default['subject']) //args
			);
			add_settings_field(
				'template-'.$template.'-message', //field ID
				__( 'Message', 'sighted-invoice-manager' ),
				array( $this, 'Sighted_Invoice_Manager_settings_render_field_template_message' ),
				$option_name, //belongs to option name?
				$section_id, //belongs to section ID?
				array($template, $default['message']) //args
			);
		}


		//API Tab
		register_setting( 'sim-invoice-api-settings', 'sim-invoice-api-settings' );
		add_settings_section(
			'invoice-display-section', //section ID
			__( 'Sighted API', 'sighted-invoice-manager' ),
			array( $this, 'Sighted_Invoice_Manager_settings_render_section_invoice_display' ),
			'sim-invoice-api-settings'
		);

		add_settings_field(
			'email', //field ID
			__( 'Email', 'sighted-invoice-manager' ),
			array( $this, 'Sighted_Invoice_Manager_settings_render_field_email' ),
			'sim-invoice-api-settings',
			'invoice-display-section' //belongs to section ID?
		);
		add_settings_field(
			'password', //field ID
			__( 'Password', 'sighted-invoice-manager' ),
			array( $this, 'Sighted_Invoice_Manager_settings_render_field_password' ),
			'sim-invoice-api-settings',
			'invoice-display-section' //belongs to section ID?
		);

		add_settings_field(
			'tenantID', //field ID
			__( 'APP ID', 'sighted-invoice-manager' ),
			array( $this, 'Sighted_Invoice_Manager_settings_render_field_tenantid' ),
			'sim-invoice-api-settings',
			'invoice-display-section' //belongs to section ID?
		);



	}



	/**
	 * Email field
	 */

	function Sighted_Invoice_Manager_settings_render_field_email() {

		$options = get_option( 'sim-invoice-api-settings' );

		if(!isset( $options['email'] )) {
			$new_options = $options;
			$new_options['email'] = '';
			update_option( 'sim-invoice-api-settings', $new_options);
			$options = get_option('sim-invoice-api-settings');
		}

		echo sprintf('<input type="email" name="sim-invoice-api-settings[email]" value="%s" class="large-text">', $options['email'] );
	}


	function Sighted_Invoice_Manager_settings_render_field_password() {

		$options = get_option( 'sim-invoice-api-settings' );

		if(!isset( $options['password'] )) {
			$new_options = $options;
			$new_options['password'] = '';
			update_option( 'sim-invoice-api-settings', $new_options);
			$options = get_option('sim-invoice-api-settings');
		}

		echo sprintf('<input type="password" name="sim-invoice-api-settings[password]" value="%s" class="large-text">', $options['password'] );
	}

	function Sighted_Invoice_Manager_settings_render_field_tenantid() {

		$options = get_option( 'sim-invoice-api-settings' );

		if(!isset( $options['tenantID'] )) {
			$new_options = $options;
			$new_options['tenantID'] = '';
			update_option( 'sim-invoice-api-settings', $new_options);
			$options = get_option('sim-invoice-api-settings');
		}

		echo sprintf('<input type="text" name="sim-invoice-api-settings[tenantID]" value="%s" class="large-text">', $options['tenantID'] );
	}


	//Invoice Display
	function Sighted_Invoice_Manager_settings_render_section_invoice_display(  ) {
		return;
	}
	function Sighted_Invoice_Manager_settings_render_field_font(  ) {
		$options = get_option( 'sim-invoice-options' );
		$fonts = array(
			'Arial',
			'Courier',
			'Times'
		);

		if(!isset( $options['font'] ) || empty($options['font']) ){
			$new_options = $options;
			$new_options['font'] = 'Courier';
			update_option( 'sim-invoice-options', $new_options );
			$options = get_option( 'sim-invoice-options' );
		}

		echo "<select name='sim-invoice-options[font]' id='sim-invoice-options-font'>";
			foreach ($fonts as $font) {
				printf(
					'<option value="%s"%s>%s</option>',
					$font, //value
					selected( $options['font'], $font, false), //selected
					ucwords($font) //label
				);
			}
		echo "</select>";
	}
	function Sighted_Invoice_Manager_settings_render_field_visit_link_view(  ) {
		$options = get_option( 'sim-invoice-options' );
		$views = array(
			'View Inline',
			'Download'
		);

		if(!isset( $options['visit-link-view'] ) || empty($options['visit-link-view']) ){
			$new_options = $options;
			$new_options['visit-link-view'] = 'View Inline';
			update_option( 'sim-invoice-options', $new_options );
			$options = get_option( 'sim-invoice-options' );
		}

		echo "<select name='sim-invoice-options[visit-link-view]' id='sim-invoice-options-visit-link-view'>";
			foreach ($views as $view) {
				printf(
					'<option value="%s"%s>%s</option>',
					$view, //value
					selected( $options['visit-link-view'], $view, false), //selected
					ucwords($view) //label
				);
			}
		echo "</select>";
	}



	//Mail Template Section
	function Sighted_Invoice_Manager_settings_render_section_template( $template = NULL  ) { return; }
	function Sighted_Invoice_Manager_settings_render_field_template_subject( $data = NULL ) {
		if( is_null( $data )){ die("Need data for Sighted_Invoice_Manager_settings_render_field_template_subject"); }
		$template = $data[0]; //template id
		$default = $data[1]; //template default value
		$meta_key = 'template-'.$template.'-subject';
		$options_name = 'sim-invoice-template-'.$template;
		$options = get_option( $options_name );
		if(!isset($options[$meta_key]) || empty($options[$meta_key]) ){
			$new_options = $options;
			$new_options[$meta_key] = $default;
			update_option($options_name, $new_options);
			$options = get_option( $options_name );
			$value = $options[$meta_key];
		}else{
			$value = $options[$meta_key];
		}
		$input = sprintf(
			'<input type="text" name="%s" value="%s" class="large-text code">',
			$options_name . '[' . $meta_key . ']',
			$value
		);
		echo $input;
	}
	function Sighted_Invoice_Manager_settings_render_field_template_message( $data = NULL ) {
		if( is_null( $data )){ die("Need data for Sighted_Invoice_Manager_settings_render_field_template_message"); }
		$template = $data[0]; //template id
		$default = $data[1]; //template default value
		$meta_key = 'template-'.$template.'-message';
		$options_name = 'sim-invoice-template-'.$template;
		$options = get_option( $options_name );
		if(!isset($options[$meta_key]) || empty($options[$meta_key]) ){
			$new_options = $options;
			$new_options[$meta_key] = $default;
			update_option($options_name, $new_options);
			$options = get_option( $options_name );
			$value = $options[$meta_key];
		}else{
			$value = $options[$meta_key];
		}
		$input = sprintf(
			'<textarea name="%s" cols="10" rows="10" class="large-text code">%s</textarea>',
			$options_name . '[' . $meta_key . ']',
			$value
		);
		echo $input;
	}

	//Remove builtin metaboxes from the sim invoice cpt
	public function Remove_Sighted_Invoice_Metaboxes(){
		remove_meta_box( 'tagsdiv-sim-client', 'sim-invoice', 'side' );
		remove_meta_box( 'tagsdiv-sim-payee', 'sim-invoice', 'side' );
	}

	// Logger
	private function InvoiceLogger($title = NULL, $invoice_id = 1){
		if( is_null($title) ){ return; }
		// Gather post data.
		$current_user = wp_get_current_user();
		$user_login  = $current_user->user_login;
		if( !isset( $user_login ) || empty( $user_login ) ){
			$user_login = $_SERVER['REMOTE_ADDR'];
		}
		$my_post = array(
			'post_title'	=> $title,
			'post_type'  => "sim-invoice-logs",
			'post_status'   => 'publish',
			'meta_input' => array(
				'_sim_invoice_logs_invoice_id' => $invoice_id,
				'_sim_invoice_logs_user' => $user_login
			)
		);
		// Insert the post into the database.
		return wp_insert_post( $my_post );
	}

}
