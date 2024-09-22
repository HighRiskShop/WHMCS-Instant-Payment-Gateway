<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function upi_MetaData()
{
    return array(
        'DisplayName' => 'upi',
        'DisableLocalCreditCardInput' => true,
    );
}

function upi_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'upi',
        ),
        'description' => array(
            'FriendlyName' => 'Description',
            'Type' => 'textarea',
            'Rows' => '3',
            'Cols' => '25',
            'Default' => 'Pay using Credit/debit card (including MasterCard, Visa, and Apple Pay).',
            'Description' => 'This controls the description which the user sees during checkout.',
        ),
        'wallet_address' => array(
            'FriendlyName' => 'USDC Polygon Wallet Address',
            'Type' => 'text',
            'Description' => 'Insert your USDC Polygon Wallet address.',
        ),
    );
}

function upi_link($params)
{
    $walletAddress = $params['wallet_address'];
    $amount = $params['amount'];
    $invoiceId = $params['invoiceid'];
	$email = $params['clientdetails']['email'];
    $systemUrl = rtrim($params['systemurl'], '/');
    $redirectUrl = $systemUrl . '/modules/gateways/callback/upi.php';
	$invoiceLink = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;
	$hrs_upiimps_currency = $params['currency'];
	$callback_URL = $redirectUrl . '?invoice_id=' . $invoiceId;

if ($hrs_upiimps_currency !== 'INR') {
        return "Error: Invoice currency must be INR";
		} else {
		$hrs_upiimps_final_total = $amount;
		}
		
		
		
		
$hrs_upiimps_gen_wallet = file_get_contents('https://api.highriskshop.com/control/wallet.php?address=' . $walletAddress .'&callback=' . urlencode($callback_URL));


	$hrs_upiimps_wallet_decbody = json_decode($hrs_upiimps_gen_wallet, true);

 // Check if decoding was successful
    if ($hrs_upiimps_wallet_decbody && isset($hrs_upiimps_wallet_decbody['address_in'])) {
        // Store the address_in as a variable
        $hrs_upiimps_gen_addressIn = $hrs_upiimps_wallet_decbody['address_in'];
        $hrs_upiimps_gen_polygon_addressIn = $hrs_upiimps_wallet_decbody['polygon_address_in'];
		$hrs_upiimps_gen_callback = $hrs_upiimps_wallet_decbody['callback_url'];
		
		
		 // Update the invoice description to include address_in
            $invoiceDescription = "Payment reference number: $hrs_upiimps_gen_polygon_addressIn";

            // Update the invoice with the new description
            $invoice = localAPI("GetInvoice", array('invoiceid' => $invoiceId), null);
            $invoice['notes'] = $invoiceDescription;
            localAPI("UpdateInvoice", $invoice);

		
		
    } else {
return "Error: Payment could not be processed, please try again (wallet address error)";
    }
	
	
        $paymentUrl = 'https://pay.highriskshop.com/process-payment.php?address=' . $hrs_upiimps_gen_addressIn . '&amount=' . $hrs_upiimps_final_total . '&provider=upi&email=' . urlencode($email) . '&currency=' . $hrs_upiimps_currency;

        // Properly encode attributes for HTML output
        return '<a href="' . $paymentUrl . '" class="btn btn-primary" rel="noreferrer">' . $params['langpaynow'] . '</a>';
}

function upi_activate()
{
    // You can customize activation logic if needed
    return array('status' => 'success', 'description' => 'upi gateway activated successfully.');
}

function upi_deactivate()
{
    // You can customize deactivation logic if needed
    return array('status' => 'success', 'description' => 'upi gateway deactivated successfully.');
}

function upi_upgrade($vars)
{
    // You can customize upgrade logic if needed
}

function upi_output($vars)
{
    // Output additional information if needed
}

function upi_error($vars)
{
    // Handle errors if needed
}
