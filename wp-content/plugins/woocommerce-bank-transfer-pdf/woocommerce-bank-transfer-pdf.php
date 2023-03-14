<?php
	
/**
 * Plugin Name: WooCommerce Bank Transfer PDF
 * Plugin URI:	https://digitalgarden.co
 * Description: Adds the bank transfer (bacs) details to the PDF invoice generated by WooCommerce PDF Invoices & Packing Slips plugin.
 * Author:		DigitalGarden
 * Author URI:	https://digitalgarden.co
 * Version:		1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Display the bank info when the payment method is bank transfer (bacs) or the status is on-hold or pending
function woocommerce_bank_transfer_pdf( $template_type, $order ) {
    if ( $template_type == 'invoice' ) {
        if ( $order->get_payment_method() == 'bacs' || in_array( $order->get_status(), array( 'on-hold', 'pending' ) ) ) {
	        
            $invoice = wcpdf_get_invoice( $order, true );
		    $invoice_number = $invoice->get_number();
		    $formatted_invoice_number = $invoice_number->get_formatted();
	        
	        $bacs_details = get_option( 'woocommerce_bacs_accounts');
	        echo '<div class="bank-info">';
	        echo '<p><strong>Payment Details</strong></p>';
	        foreach ( $bacs_details as $account ) {
				echo '<p>';
                echo /*'<b>Account:</b> ' .*/ esc_attr( wp_unslash( $account['account_name'] ) ) . '<br>';
				echo '<b>Sort Code:</b> ' . esc_attr( $account['sort_code'] ) . '<br>';
				echo '<b>Account Number:</b> ' . esc_attr( $account['account_number'] ) . '<br>';
				echo '<b>Payment Reference:</b> ' . $formatted_invoice_number . '<br>';
				echo '<b>IBAN:</b> ' . esc_attr( $account['iban'] ) . '<br>';
				echo '<b>BIC / Swift:</b> ' . esc_attr( $account['bic'] ) . '<br>';
				echo '<b>Bank:</b> ' . esc_attr( wp_unslash( $account['bank_name'] ) );
		        echo '</p>';
		    }
		    echo '</div>';
        }
    }
}
add_action( 'wpo_wcpdf_after_order_details', 'woocommerce_bank_transfer_pdf', 10, 2 );

// If an order is manually created in the backend, payment method is empty. Display 'Bank transfer' instead of nothing.
function woocommerce_bank_transfer_pdf_default_payment_method( $payment_method, $order_document ) {
	if ( empty( $payment_method ) ) {
		$payment_method = 'Bank transfer';
	}
	return $payment_method;
}
add_action( 'wpo_wcpdf_payment_method', 'woocommerce_bank_transfer_pdf_default_payment_method', 10, 2 );

// If an order is manually created in the backend, payment method is empty. Display 'Bank transfer' instead of nothing.
function woocommerce_bank_transfer_pdf_styles( $document_type, $document ) {
	?>
	.invoice .customer-notes { padding-top: 30px !important; }
	<?php
}
add_action( 'wpo_wcpdf_custom_styles', 'woocommerce_bank_transfer_pdf_styles', 10, 2 );

// Due date support
/*function woocommerce_bank_transfer_pdf_due_date ($template_type, $order) {
    if ($template_type == 'invoice') {
        $invoice = wcpdf_get_invoice( $order );
        if ( $invoice_date = $invoice->get_date() ) {
            $due_date = date_i18n( get_option( 'date_format' ), strtotime( $invoice_date->date_i18n('Y-m-d H:i:s') . ' + 30 days') );
            ?>
            <tr class="due-date">
                <th>Payment Due:</th>
                <td><?php echo $due_date; ?></td>
            </tr>
            <?php
        }
    }
}
add_action( 'wpo_wcpdf_after_order_data', 'woocommerce_bank_transfer_pdf_due_date', 10, 2 );*/
