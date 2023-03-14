<?php
	
/**
 * Plugin Name: WooCommerce Sage Export
 * Plugin URI:	https://digitalgarden.co
 * Description: Produces a CSV export that meets Sage accounting import requirements.
 * Author:		DigitalGarden
 * Author URI:	https://digitalgarden.co
 * Version:		1.0.2
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function wc_sage_export_js() {
	$button_href = $_SERVER['REQUEST_URI'] . '&action=wc_sage_export' . '&_wpnonce=' . wp_create_nonce( 'wc_sage_export' );
    ?>
    <script>
    jQuery(function(){
	    console.log(jQuery( 'h1.wp-heading-inline' ).text())
	    if ( jQuery( 'h1.wp-heading-inline' ).text().trim() == 'Orders') {
        	jQuery("body.post-type-shop_order .wp-header-end").before('<a href="<?php echo $button_href ; ?>" class="page-title-action">Sage Export</a>');
        }
    });
    </script>
    <?php
}
add_action('admin_head', 'wc_sage_export_js');


if ( isset($_GET['action'] ) && $_GET['action'] == 'wc_sage_export' )  {
	add_action( 'admin_init', 'wc_sage_export' ) ;
}

/*	
	Sage Transaction Types:
	--------------------------------
	BR = Bank Receipt
	BP = Bank Payment
	CP = Cash Payment
	CR = Cash Receipt
	JD = Journal Debit
	JC = Journal Credit
	SI = Sales Invoice
	SC = Sales Credit Note
	SA = Sales Receipt on Account
	SP = Sales Payment
	PI = Purchase Invoice
	PC = Purchase Credit Note
	PA = Purchase Payment on Account
	PR = Purchase Receipt
	VP = Credit Card Payment
	VR = Credit Card Receipt
	
	To do:
	--------------------------------
	1. Make some values configurable on settings page
*/

function wc_sage_export() {

	if ( !current_user_can( 'manage_options' ) || !is_admin() ) {
		return false;
	}

	$nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';
	if ( ! wp_verify_nonce( $nonce, 'wc_sage_export' ) ) {
		die( 'Security check error' );
	}
	
	ob_start();

	$filename = 'wc_sage_export_' . time() . '.csv';
	
	$header_row = array(
		'Type',
		'Account Reference',
		'Nominal A/C Ref',
		'Department Code',
		'Date',
		'Reference',
		'Details',
		'Net Amount',
		'Tax Code',
		'Tax Amount',
		'Exchange Rate',
		'Extra Reference',
		'User Name',
		'Project Refn',
		'Cost Code Refn',
	);
	$data_rows = array();
	
	$args = array(
	    'limit' => -1,
	);
	$orders = wc_get_orders( $args );
	foreach ( $orders as $order ) {
		
		$is_refunded = is_a( $order, 'WC_Order_Refund' );
		
		if ( $is_refunded ) {
			$refund_order = $order;
		    $order = wc_get_order( $order->get_parent_id() );
		} else {
			$refund_order = null;
		}
		
		if ( $order->get_payment_method() == 'stripe' && in_array( $order->get_status(), array( 'completed', 'refunded' ) ) ) {
		
			// Type
			$order_row[0] = 'VR'; // Credit Card Receipt
			if ( $is_refunded ) {
				$order_row[0] = 'VP'; // Credit Card Payment;
			}
			
			// Account Reference
			$order_row[1] = '1202'; // Sage Account Reference Number (1201 Bank Account, 1202 Stripe Account)
			
			// Nominal A/C Ref
			$order_row[2] = $order->get_id();
			
			// Department Code
			$order_row[3] = ''; // Leave blank
			
			// Date
			$date = $order->get_date_created();
			if ( $is_refunded ) {
				$date = $refund_order->get_date_created();
			}
			$order_row[4] = date( 'd\/m\/Y', strtotime( $date ) ) ; // Date in format DD/MM/YYYY
			
			// Reference
			$order_row[5] = 'Online order payment';
			if ( $is_refunded ) {
				if ( $refund_order ) {
					$order_row[5] = 'Online order refund';
				}
			}
			
			// Details
			$items = $order->get_items();
			$order_row[6] = '';
			foreach ( $items as $item ) {
				$order_row[6] .= $item['name'] . ', ';
			}
			$order_row[6] = rtrim ( $item['name'], ', ' );
			
			// Net Amount
			$order_row[7] = $order->get_total();
			if ( $is_refunded ) {
				$order_row[7] = number_format( -$order->get_total(), 2 );
			}
			
			// Tax Code
			$order_row[8] = 'T9'; // Always T9
			
			// Tax Amount
			$order_row[9] = '0.00';
			
			// Exchange Rate
			$order_row[10] = ''; // Leave blank
			
			// Extra Reference
			$order_row[11] = 'Billing Name: ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
			//if ( !$is_refunded ) {
				$order_row[11] = 'WP User ID: ' . $order->get_customer_id() . ', ' . $order_row[11];
			//}
			
			// User Name
			$admin_user = wp_get_current_user();
			$order_row[12] = $admin_user->user_login; // Username of the WP admin downloading report
			
			// Project Refn
			$order_row[13] = ''; // Leave blank
			
			// Cost Code Refn
			$order_row[14] = ''; // Leave blank
			
			if ( !$is_refunded && $order->get_payment_method() == 'stripe' ) {
				$fee_row = $order_row; // Clone the order row as a starting point
	
				// Type
				$fee_row[0] = 'VP'; // Credit Card Payment
				
				// Reference
				$fee_row[5] = 'Stripe fee';
				
				// Net amount
				$fee_row[7] = number_format ( -$order->get_meta("_stripe_fee"), 2 );
				
				// Extra Reference
				$fee_row[11] = 'Stripe Transaction ' . $order->get_transaction_id();
				
				// Add the fee row to the data rows
				$data_rows[] = $fee_row;
			}
			
			// Add the order to the data rows
			$data_rows[] = $order_row;
		}
	}

	$fh = @fopen( 'php://output', 'w' );
	fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Content-Description: File Transfer' );
	header( 'Content-type: text/csv' );
	header( "Content-Disposition: attachment; filename={$filename}" );
	header( 'Expires: 0' );
	header( 'Pragma: public' );
	fputcsv( $fh, $header_row );
	foreach ( $data_rows as $data_row ) {
		fputcsv( $fh, $data_row );
	}
	fclose( $fh );
	
	ob_end_flush();
	
	die();
}