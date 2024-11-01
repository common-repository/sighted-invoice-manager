<?php

if ( have_posts() ){

	//Check Authorization
	$authorized = false;
	if( ( is_user_logged_in() && current_user_can('manage_options') ) ){
		$authorized = true;
	}else{
		if( !post_password_required() ){
			$authorized = true;
		}else{
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/sighted-invoice-manager-public-display-no-password.php';
		}
	}

	//If Authorized then display Invoice
	if( $authorized == true ){
		while ( have_posts() ) : the_post();
			try{
			    //Create Invoice Object
				$invoice = CreateInvoiceObject( $post->ID );

                //Log Remote User Viewing
                $current_user = wp_get_current_user();
                if( !isset( $current_user->user_login ) ){
                	// todo
                	// IP detect from proxy
                    InvoiceLogger("Viewed by ".$_SERVER['REMOTE_ADDR'], $post->ID);
                }

                //Get Font
                $options = get_option( 'sim-invoice-options' );
                if(!isset( $options['font'] ) || empty($options['font']) ){
                    $font = 'Courier';
                }else{
                    $font = $options['font'];
                }



				//Child Class
				class PDF extends FPDF{
                    private $invoice = array();
                    private $font = 'Courier';
					function __construct($a, $b, $c, $invoice, $font) {
                        $this->invoice = $invoice;
                        $this->font = $font;
						parent::__construct($a,$b,$c);
					}
					function Footer(){
						// Position at 1.5 cm from bottom
						$this->SetY(-28);
						// Arial italic 8
						$this->SetFont($this->font,'',10);
						$this->Cell(0,6,'If you have any questions about this invoice, please contact',0,1,'C');
						$array = array(
							$this->invoice['payee']['name'],
							$this->invoice['payee']['phone'],
							$this->invoice['payee']['email']
						);
						$contact = join( ', ', $array);
						$this->Cell(0,6,$contact,0,1,'C');
						// Page number
						$this->Cell(0,8,'Page '.$this->PageNo().' of {nb}',0,0,'R');
					}

					function LineItemTable($header, $data){
						setlocale(LC_MONETARY, 'en_US');
						// Colors, line width and bold font
						$this->SetFillColor(219,219,219);
						$this->SetTextColor(0);
						$this->SetDrawColor(0,0,0);
						$this->SetLineWidth(.3);
						$this->SetFont($this->font,'B',9);
						// Header 196
						$w = array(101, 15, 35, 45);
						for($i=0;$i<count($header);$i++)
						$this->Cell($w[$i],8,$header[$i],1,0,'C',true);
						$this->Ln();
						// Color and font restoration
						$this->SetFillColor(245,245,245);
						$this->SetTextColor(0);
						$this->SetFont($this->font,'',10);
						// Data
						$fill = false;
						for($i=0; $i < 16; $i++){

							//Defaults
							$row[0] = ''; //Description
							$row[1] = ''; //Qty
							$row[2] = ''; //Unit Price
							$row[3] = ''; //Amount

							//Update variables
							if(isset($data[$i])){
								//Description
								if( !is_null($data[$i]['description']) ){
									$row[0] = htmlspecialchars_decode($data[$i]['description']);
								}
                                //Qty
								if( !is_null($data[$i]['qty']) ){
									$row[1] = $data[$i]['qty'];
								}
                                //Unit Price
								if( !is_null($data[$i]['price']) ){
									$row[2] = money_format('%.2n', $data[$i]['price']);
								}
                                //Amount
								if( !is_null($data[$i]['total']) && $data[$i]['total'] != 0 ){
									$row[3] = money_format('%.2n', $data[$i]['total']);
								}
							}

							$this->Cell($w[0],8,$row[0],'LR',0,'L',$fill);
							$this->Cell($w[1],8,$row[1],'LR',0,'C',$fill);
							$this->Cell($w[2],8,$row[2],'LR',0,'R',$fill);
							$this->Cell($w[3],8,$row[3],'LR',0,'R',$fill);
							$this->Ln();
							$fill = !$fill;
						}
					}

				}


				//Create PDF
				$pdf = new PDF('P','mm','Letter', $invoice, $font);
				$pdf->AliasNbPages();
				$pageComplete = false;
				$total = 0.00;
				while( !$pageComplete ){
					$pdf->AddPage();


					//Payee
					$pdf->SetDrawColor(0,0,0);
					$pdf->SetFillColor(219,219,219);
					$pdf->SetTextColor(0,0,0);
					$pdf->SetFont($font,'',16);
					$pdf->Cell(98,12,$invoice['payee']['name'],0,0,'L',false);
					//Status
					$pdf->SetFont($font,'B',28);

					switch ( strtolower($invoice['status']) ) {
						case 'unpaid':
							$status = __( 'Unpaid', 'sighted-invoice-manager' );
							break;
						case 'paid':
							$status = __( 'Paid', 'sighted-invoice-manager' );
							break;
						case 'pending':
							$status = __( 'Payment Pending', 'sighted-invoice-manager' );
							break;
						default:
							$status = __( 'Unknown', 'sighted-invoice-manager' );
							break;
					}
					$pdf->Cell(98,12,$status,0,1,'R',false);
					$pdf->SetFont($font,'',10);
					$pdf->Cell(98,5,$invoice['payee']['address1'],0,1,'L',false);
					$pdf->Cell(98,5,$invoice['payee']['address2'],0,1,'L',false);
					$pdf->Cell(98,5,$invoice['payee']['phone'],0,1,'L',false);
					$pdf->Cell(98,5,$invoice['payee']['email'],0,1,'L',false);
					$pdf->ln(10);

					//Client
					$pdf->SetDrawColor(0,0,0);
					$pdf->SetFillColor(219,219,219);
					$pdf->SetTextColor(0,0,0);
					$pdf->SetFont($font,'B',9);
					$pdf->Cell(98,8,"Bill To: ",1,0,'L',true);	$pdf->Cell(22,8,"",0,0,'L',false);	$pdf->Cell(38,8,"Invoice #",1,0,'C',true);	$pdf->Cell(38,8,"Date",1,1,'C',true);
					$pdf->SetFont($font,'',10);
					$pdf->Cell(98,8,$invoice['client']['name'],0,0,'L',false);	$pdf->Cell(22,8,"",0,0,'L',false);	$pdf->Cell(38,8,$invoice['id'],1,0,'C',false);	$pdf->Cell(38,8,date('m\/d\/y', $invoice['date']),1,1,'C',false);

                    $addBlankLines = 0;
                    //Company
                    if( !empty($invoice['client']['company']) ){
					   $pdf->Cell(98,5,$invoice['client']['company'],0,1,'L',false);
                    }else{
                        $addBlankLines += 1;
                    }
                    //Address1
                    if( !empty($invoice['client']['address1']) ){
                       $pdf->Cell(98,5,$invoice['client']['address1'],0,1,'L',false);
                    }else{
                        $addBlankLines += 1;
                    }
                    //Address2
                    if( !empty($invoice['client']['address2']) ){
                       $pdf->Cell(98,5,$invoice['client']['address2'],0,1,'L',false);
                    }else{
                        $addBlankLines += 1;
                    }
                    //Phone
                    if( !empty($invoice['client']['phone']) ){
                       $pdf->Cell(98,5,$invoice['client']['phone'],0,1,'L',false);
                    }else{
                        $addBlankLines += 1;
                    }
                    //Email
                    if( !empty($invoice['client']['email']) ){
                       $pdf->Cell(98,5,$invoice['client']['email'],0,1,'L',false);
                    }else{
                        $addBlankLines += 1;
                    }

                    //Add up blank lines
                    for ($i=0; $i < $addBlankLines; $i++) {
                       $pdf->Cell(98,5,'',0,1,'L',false);
                    }
					$pdf->ln(10);

					//Line Items
					$header = array('Description', 'Qty', 'Unit Price', 'Amount');
					$rows = array();
					for($i=0; $i < 16; $i++){
						if( !empty($invoice['line-items']) ){
							$row = array_shift($invoice['line-items']);
							array_push($rows, $row);
							$total += $row['total'];
						}
					}
					$pdf->LineItemTable($header, $rows);

					$w = array(101, 15, 35, 45);
					if( empty($invoice['line-items']) ){
						// Closing line
						$pdf->Cell(array_sum($w),0,'',1,1,'C',false);
						$pdf->SetFont($font,'I',11);
						$pdf->Cell($w[0],8,"Thank you for your business!",'LR',0,'C', false);
						$pdf->SetFont($font,'B',12);
						$pdf->Cell(($w[1]+$w[2]),8,"Total",'L',0,'L',false);
						$pdf->Cell($w[3],8,money_format('%.2n', $total),'R',1,'R',false);
						$pdf->Cell(array_sum($w),0,'',1,1,'C',false);
						//Stop Adding Pages
						$pageComplete = true;
					}else{
						// Continued line
						$pdf->Cell(array_sum($w),0,'',1,1,'C',false);
						$pdf->SetFont($font,'I',11);
						$pdf->Cell(array_sum($w),8,'Continued on Next Page',1,1,'C',false);
						$pdf->Cell(array_sum($w),0,'',1,1,'C',false);
					}

				}
				//Output PDF as inline or download
				switch ($options['visit-link-view']) {
					case 'Download':
						$pdf->Output('D', 'Invoice '.$invoice['id'].'.pdf');
						break;
					default: //or View Inline
						$pdf->Output('I', 'Invoice '.$invoice['id'].'.pdf');
						break;
				}

			}catch (Exception $e){
				echo 'Caught exception: '. $e->getMessage(). "\n";
			}
		endwhile;
	}
}else{
	//get_template_part( 'content', 'none' );
	print "Nothing Found";
}





?>


<?php
	//FUNCTIONS
	function CreateInvoiceObject($postID = 0){

		$pm = get_post_meta( $postID );

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
			$payee = NULL;
		}

		//Create Client
		$term = get_the_terms( $postID, 'sim-client');
		if(isset($term[0])){
			$term = $term[0];
			$term_meta = get_term_meta($term->term_id);
			//print_r( $term_meta );
            $client = array(
                    'name' => $term->name,
                    'company' => '',
                    'address1' => '',
                    'address2' => '',
                    'phone' => '',
                    'email' => ''
            );
            if( isset($term_meta["_sim_client_company"][0]) ){
                $client['company'] = $term_meta["_sim_client_company"][0];
            }
            if( isset($term_meta["_sim_client_address1"][0]) ){
                $client['address1'] = $term_meta["_sim_client_address1"][0];
            }
            if( isset($term_meta["_sim_client_address2"][0]) ){
                $client['address2'] = $term_meta["_sim_client_address2"][0];
            }
            if( isset($term_meta["_sim_client_phone"][0]) ){
                $client['phone'] = $term_meta["_sim_client_phone"][0];
            }
            if( isset($term_meta["_sim_client_email"][0]) ){
                $client['email'] = $term_meta["_sim_client_email"][0];
            }
		}else{
			$client = NULL;
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
			'status' => $pm['_sim_invoice_payment_status'][0],
			'payee' => $payee,
			'client' => $client,
			'line-items' => $line_items
		);

		return $invoice;
	}

    /**
     * Logger
     */
     function InvoiceLogger($title = NULL, $invoice_id = 1){
        if( is_null($title) ){ return; }
        // Gather post data.
        $current_user = wp_get_current_user();
        $user_login  = $current_user->user_login;
        if( !isset( $user_login ) || empty( $user_login ) ){
            $user_login = $_SERVER['REMOTE_ADDR'];
        }
        $my_post = array(
            'post_title'    => $title,
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

?>
