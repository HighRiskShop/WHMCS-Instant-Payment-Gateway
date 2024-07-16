<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function wert_MetaData()
{
    return array(
        'DisplayName' => 'wert',
        'DisableLocalCreditCardInput' => true,
    );
}

function wert_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'wert',
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

function wert_link($params)
{
    $walletAddress = $params['wallet_address'];
    $amount = $params['amount'];
    $invoiceId = $params['invoiceid'];
	$email = $params['clientdetails']['email'];
    $systemUrl = rtrim($params['systemurl'], '/');
    $redirectUrl = $systemUrl . '/modules/gateways/callback/wert.php';
	$invoiceLink = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;
	$hrs_wertio_currency = $params['currency'];
	$callback_URL = $redirectUrl . '?invoice_id=' . $invoiceId;

if ($hrs_wertio_currency === 'USD') {
        $hrs_wertio_final_total = $amount;
		} else {
		
$hrs_wertio_response = file_get_contents('https://api.highriskshop.com/control/convert.php?value=' . $amount . '&from=' . strtolower($hrs_wertio_currency));


$hrs_wertio_conversion_resp = json_decode($hrs_wertio_response, true);

if ($hrs_wertio_conversion_resp && isset($hrs_wertio_conversion_resp['value_coin'])) {
    // Escape output
    $hrs_wertio_final_total	= $hrs_wertio_conversion_resp['value_coin'];      
} else {
	return "Error: Payment could not be processed, please try again (unsupported store currency)";
}	
		}
		
		
		
		
$hrs_wertio_gen_wallet = file_get_contents('https://api.highriskshop.com/control/wallet.php?address=' . $walletAddress .'&callback=' . urlencode($callback_URL));


	$hrs_wertio_wallet_decbody = json_decode($hrs_wertio_gen_wallet, true);

 // Check if decoding was successful
    if ($hrs_wertio_wallet_decbody && isset($hrs_wertio_wallet_decbody['address_in'])) {
        // Store the address_in as a variable
        $hrs_wertio_gen_addressIn = $hrs_wertio_wallet_decbody['address_in'];
        $hrs_wertio_gen_polygon_addressIn = $hrs_wertio_wallet_decbody['polygon_address_in'];
		$hrs_wertio_gen_callback = $hrs_wertio_wallet_decbody['callback_url'];
		
		
		 // Update the invoice description to include address_in
            $invoiceDescription = "Payment reference number: $hrs_wertio_gen_polygon_addressIn";

            // Update the invoice with the new description
            $invoice = localAPI("GetInvoice", array('invoiceid' => $invoiceId), null);
            $invoice['notes'] = $invoiceDescription;
            localAPI("UpdateInvoice", $invoice);

		
		
    } else {
return "Error: Payment could not be processed, please try again (wallet address error)";
    }
	
	
        $paymentUrl = 'https://pay.highriskshop.com/process-payment.php?address=' . $hrs_wertio_gen_addressIn . '&amount=' . $hrs_wertio_final_total . '&provider=wert&email=' . urlencode($email) . '&currency=' . $hrs_wertio_currency;

        // Properly encode attributes for HTML output
        return '<a href="' . $paymentUrl . '" class="btn btn-primary" rel="noreferrer">' . $params['langpaynow'] . '</a>';
}

function wert_activate()
{
    // You can customize activation logic if needed
    return array('status' => 'success', 'description' => 'wert gateway activated successfully.');
}

function wert_deactivate()
{
    // You can customize deactivation logic if needed
    return array('status' => 'success', 'description' => 'wert gateway deactivated successfully.');
}

function wert_upgrade($vars)
{
    // You can customize upgrade logic if needed
}

function wert_output($vars)
{
    // Output additional information if needed
}

function wert_error($vars)
{
    // Handle errors if needed
}
