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
setlocale(LC_MONETARY, 'en_US');
//Collect needed data
foreach (array('sim-client', 'sim-payee', 'date') as $key) {
	if(!isset($_GET[$key]) || empty($_GET[$key])){
		$_GET[$key] = NULL;
	}
}
$data = GetReportData($_GET['sim-client'], $_GET['sim-payee'], $_GET['date']);
?>

<div class="wrap sighted-invoice-manager" id="reports">

	<div class="postbox">
		<h2>Income</h2>
        <?php IncomeSelectForm( $data ); ?>
        <pre><?php DisplayReportText( $data ); ?></pre>
	</div>
</div>

<?php
function IncomeSelectForm( $data = NULL ){
	//Create Form
    $url = esc_url( admin_url('edit.php?post_type=sim-invoice&page=sighted-invoice-manager-reports') );
	echo "<form action='".$url."' method='get'>";
	echo '<input name="post_type" type="hidden" value="sim-invoice">';
	echo '<input name="page" type="hidden" value="sighted-invoice-manager-reports">';
	//Display Payee and Client select inputs
    $taxonomies = array('sim-payee', 'sim-client');
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
	//Dates
	echo "<select name='date' id='date' class='postform'>";
	echo "<option value=''>All Dates</option>";
	$TRASH_VAR['GET_date_was_used'] = false;
	foreach ($data['dates'] as $year => $months) {
		echo '<option value="' . $year . '"';
		if(isset($_GET['date']) && $_GET['date'] == $year ){
			echo ' selected="selected"';
			$TRASH_VAR['GET_date_was_used'] = true;
		}
		$count = 0;
		foreach ($months as $month => $num_posts) {
			$count += $num_posts;
		}
		echo '>' . $year . ' (' . $count .')</option>';
		foreach ($months as $month => $num_posts) {
			echo '<option value="' . $year.'-'.$month . '"';
			if(isset($_GET['date']) && $_GET['date'] === $year.'-'.$month ){
				echo ' selected="selected"';
				$TRASH_VAR['GET_date_was_used'] = true;
			}
			echo '>' . date("— m - F", mktime(0, 0, 0, $month, 1, 2000)) . ' (' . $num_posts .')</option>';
		}
	}
	//If using an invalid date...
	if($TRASH_VAR['GET_date_was_used'] === false && !empty($_GET['date'])){
		echo '<option value="" selected="selected">Date not set</option>';
	}
	echo "</select>";
	//Finish Form
	submit_button( 'Filter', 'primary', '', False );
	echo "</form>";
}

function GetReportData( $cslug = NULL, $pslug = NULL, $dslug = NULL ){

	//Setup meta_query
	$data['meta_query'] = array();
	$TRASH_VAR = array();
	if(!is_null($dslug)){
		$TRASH_VAR['start_and_end'] = array();
    	if(strstr($dslug, '-')){
			list($TRASH_VAR['year_str'],$TRASH_VAR['month_str']) = explode("-",$dslug);
			$TRASH_VAR['start'] = date("U", mktime(0, 0, 0, $TRASH_VAR['month_str'], 1, $TRASH_VAR['year_str']));
			$TRASH_VAR['end'] = date("U", mktime(23, 59, 59, $TRASH_VAR['month_str']+1, 0, $TRASH_VAR['year_str']));
		}else{
			$TRASH_VAR['year_str'] = $dslug;
			$TRASH_VAR['month_str'] = NULL;
			$TRASH_VAR['start'] = date("U", mktime(0, 0, 0, 1, 1, $TRASH_VAR['year_str']));
			$TRASH_VAR['end'] = date("U", mktime(23, 59, 59, 12, 31, $TRASH_VAR['year_str']));
		}
		$data['meta_query'] = array(
			array(
				'key' => '_sim_invoice_date',
				'value'   => array( $TRASH_VAR['start'], $TRASH_VAR['end'] ),
				'type'    => 'numeric',
				'compare' => 'BETWEEN',
			),
		);
	}

    //Setup tax_query
    $data['tax_query'] = array();
    //check if both client and payee are set
    if ( !is_null($cslug) && !is_null($pslug) ){
        $data['tax_query']['relation'] = 'AND';
    }
    //set client if not null
    $tax_slug = 'sim-client';
    $data['client']['taxonomy'] = get_taxonomy($tax_slug);
    if (!is_null($cslug)){
        array_push($data['tax_query'],array(
            'taxonomy' => $tax_slug,
            'field'    => 'slug',
            'terms'    => $cslug,
        ));
        $data['client']['slug'] = $cslug;
        $data['client']['term'] = get_term_by( 'slug', $cslug, $tax_slug );
        $data['client']['meta'] = get_term_meta($data['client']['term']->term_id);
    }
    //set payee if not null
    $tax_slug = 'sim-payee';
    $data['payee']['taxonomy'] = get_taxonomy($tax_slug);
    if (!is_null($pslug)){
        array_push($data['tax_query'],array(
            'taxonomy' => $tax_slug,
			'field'    => 'slug',
			'terms'    => $pslug,
        ));
        $data['payee']['slug'] = $pslug;
        $data['payee']['term'] = get_term_by( 'slug', $pslug, $tax_slug );
        $data['payee']['meta'] = get_term_meta($data['payee']['term']->term_id);
    }

    //Setup posts_array
    $args = array(
    	'posts_per_page'   => -1,
    	'offset'           => 0,
    	'orderby'          => 'date',
    	'order'            => 'DESC',
    	'post_type'        => 'sim-invoice',
    	'post_status'      => 'publish',
    );
    if (!empty($data['tax_query'])) {
        $args['tax_query'] = $data['tax_query'];
    }
    $posts_array = get_posts( $args );
	// Get Date Select options using just tax_query
	foreach ($posts_array as $post) {
        $pm = get_post_meta( $post->ID );
        $date = $pm['_sim_invoice_date'][0];
		$date_string = date('Y|m', $date);
		list($year,$month) = explode("|",$date_string);
		if(!isset($data['dates'][$year][$month])){
			$data['dates'][$year][$month] = 1;
		}else{
			$data['dates'][$year][$month] += 1;
		}
	}
	//Sort Dates
	ksort($data['dates']);
	foreach ($data['dates'] as $year => $months) {
		ksort($data['dates'][$year]);
	}

	//Now run get_posts again with meta_query
    if (!empty($data['meta_query'])) {
        $args['meta_query'] = $data['meta_query'];
	    $posts_array = get_posts( $args );
    }

    //Generate Report Data
    $data['invoice_lines'] = array();
    $data['income']['paid']= 0;
    $data['income']['unpaid']= 0;
    foreach ($posts_array as $post) {
        $pm = get_post_meta( $post->ID );
        $date = $pm['_sim_invoice_date'][0];
        $status = $pm['_sim_invoice_payment_status'][0];
        $total = GetLineTotals($pm['_sim_invoice_group_line_item'][0]);
        $invoice = array(
            'date' => $date,
            'url' => $post->guid,
            'ID' => $post->ID,
            'post_title' => $post->post_title,
            'total' => $total,
        );
        array_push($data['invoice_lines'], $invoice);

		//sort paid and unpaid
        if ($status == 'paid') {
            $data['income']['paid'] += $total;
        }else{
            $data['income']['unpaid'] += $total;
        }
    }
    return $data;
}

function DisplayReportText( $data = NULL ){

    //Display Payee and Client
    echo "<pre>"; //start report
    if (isset($data['payee']['term'])) {
        echo str_pad("Payee: ", 8, ' ').$data['payee']['term']->name."\n";
    }else {
        echo str_pad("Payee: ", 8, ' ').$data['payee']['taxonomy']->labels->all_items."\n";
    }
    if (isset($data['client']['term'])) {
        echo str_pad("Client: ", 8, ' ').$data['client']['term']->name;
        if (isset($data['client']['meta']['_sim_client_company'][0])) {
            echo " - ".$data['client']['meta']['_sim_client_company'][0];
        }
        echo "\n";
    }else {
        echo str_pad("Client: ", 8, ' ').$data['client']['taxonomy']->labels->all_items."\n";
    }

	//Display Date
	if(isset($data['meta_query'][0]['value'])){
		echo str_pad("Date: ", 8, ' ').date("M jS, Y",$data['meta_query'][0]['value'][0]);
		echo ' - ';
		echo date("M jS, Y",$data['meta_query'][0]['value'][1])."\n";
	}else{
		echo str_pad("Date: ", 8, ' ')."All of time"."\n";
	}


	//Extra spacer line
    echo "\n";

	//Display Invoices
    $max_string_length = 40;
    echo str_pad('Date', 10, "-").str_pad('Invoice', $max_string_length, "-").str_pad('Amount', 10, "-", STR_PAD_LEFT)."\n";
    foreach ($data['invoice_lines'] as $line) {
        $string_length = strlen($line['ID'].' - '.$line['post_title']);
        if ($string_length > $max_string_length-3) {
            $id_and_post = substr($line['ID'].' - '.$line['post_title'], 0, $max_string_length-3).'...';
            $spaces = '';
        }else {
            $id_and_post = $line['ID'].' - '.$line['post_title'];
            $spaces = str_repeat(" ", $max_string_length-$string_length);
        }
        $invoice_url = sprintf(
            '<a href="%s">%s</a>%s',
            $line['url'],
            $id_and_post,
            $spaces
        );
        $date = date('m\/d\/y', $line['date']);
        $amount = money_format('%.2n', $line['total']);
        echo str_pad($date, 10, " ").$invoice_url.str_pad($amount, 10, " ", STR_PAD_LEFT)."\n";
    }
    echo str_pad('', 10, "-").str_pad('', $max_string_length, "-").str_pad('', 10, "-", STR_PAD_LEFT)."\n";
    echo str_pad('', $max_string_length, " ").str_pad('Paid: ', 10, " ", STR_PAD_LEFT).str_pad(money_format('%.2n', $data['income']['paid']), 10, " ", STR_PAD_LEFT)."\n";
    echo str_pad('', $max_string_length, " ").str_pad('Unpaid: ', 10, " ", STR_PAD_LEFT).str_pad(money_format('%.2n', $data['income']['unpaid']), 10, " ", STR_PAD_LEFT)."\n";
    echo str_pad('', $max_string_length, " ").str_pad('', 10, " ", STR_PAD_LEFT).str_pad('', 10, "-")."\n";
    echo str_pad('', $max_string_length, " ").str_pad('Total: ', 10, " ", STR_PAD_LEFT).str_pad(money_format('%.2n', $data['income']['unpaid']+$data['income']['paid']), 10, " ", STR_PAD_LEFT)."\n";
    echo "\n";
    echo "Report Generated on ".current_time( 'mysql' )."\n";
    echo "</pre>"; //end report
}

function GetLineTotals($e){
    $entries = unserialize($e);
    $total = 0;
    foreach ( (array) $entries as $key => $entry ) {
        $line=array();
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
        if( ( $line['qty'] != NULL && $line['qty'] != 0 ) && ( $line['price'] != NULL && $line['price'] != 0 ) ){
            $line['total'] = ($line['price'] * $line['qty']);
        }else{
            $line['total'] = 0;
        }
        $total += $line['total'];
    }
    return $total;
}

?>
