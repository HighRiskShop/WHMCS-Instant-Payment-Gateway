<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function robinhood_MetaData()
{
    return array(
        'DisplayName' => 'robinhood',
        'DisableLocalCreditCardInput' => true,
    );
}

function robinhood_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'robinhood',
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

function robinhood_link($params)
{
    $walletAddress = $params['wallet_address'];
    $amount = $params['amount'];
    $invoiceId = $params['invoiceid'];
	$email = $params['clientdetails']['email'];
    $systemUrl = rtrim($params['systemurl'], '/');
    $redirectUrl = $systemUrl . '/modules/gateways/callback/robinhood.php';
	$invoiceLink = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;
	$hrs_robinhoodcom_currency = $params['currency'];
	$callback_URL = $redirectUrl . '?invoice_id=' . $invoiceId;

if ($hrs_robinhoodcom_currency === 'USD') {
        $hrs_robinhoodcom_final_total = $amount;
		} else {
		
$hrs_robinhoodcom_response = file_get_contents('https://api.highriskshop.com/control/convert.php?value=' . $amount . '&from=' . strtolower($hrs_robinhoodcom_currency));


$hrs_robinhoodcom_conversion_resp = json_decode($hrs_robinhoodcom_response, true);

if ($hrs_robinhoodcom_conversion_resp && isset($hrs_robinhoodcom_conversion_resp['value_coin'])) {
    // Escape output
    $hrs_robinhoodcom_final_total	= $hrs_robinhoodcom_conversion_resp['value_coin'];      
} else {
	return "Error: Payment could not be processed, please try again (unsupported store currency)";
}	
		}
		
		
		
		
$hrs_robinhoodcom_gen_wallet = file_get_contents('https://api.highriskshop.com/control/wallet.php?address=' . $walletAddress .'&callback=' . urlencode($callback_URL));


	$hrs_robinhoodcom_wallet_decbody = json_decode($hrs_robinhoodcom_gen_wallet, true);

 // Check if decoding was successful
    if ($hrs_robinhoodcom_wallet_decbody && isset($hrs_robinhoodcom_wallet_decbody['address_in'])) {
        // Store the address_in as a variable
        $hrs_robinhoodcom_gen_addressIn = $hrs_robinhoodcom_wallet_decbody['address_in'];
        $hrs_robinhoodcom_gen_polygon_addressIn = $hrs_robinhoodcom_wallet_decbody['polygon_address_in'];
		$hrs_robinhoodcom_gen_callback = $hrs_robinhoodcom_wallet_decbody['callback_url'];
		
		
		 // Update the invoice description to include address_in
            $invoiceDescription = "Payment reference number: $hrs_robinhoodcom_gen_polygon_addressIn";

            // Update the invoice with the new description
            $invoice = localAPI("GetInvoice", array('invoiceid' => $invoiceId), null);
            $invoice['notes'] = $invoiceDescription;
            localAPI("UpdateInvoice", $invoice);

		
		
    } else {
return "Error: Payment could not be processed, please try again (wallet address error)";
    }
	
	
        $paymentUrl = 'https://pay.highriskshop.com/process-payment.php?address=' . $hrs_robinhoodcom_gen_addressIn . '&amount=' . $hrs_robinhoodcom_final_total . '&provider=robinhood&email=' . urlencode($email) . '&currency=' . $hrs_robinhoodcom_currency;

        // Properly encode attributes for HTML output
        return '<a href="' . $paymentUrl . '" class="btn btn-primary" rel="noreferrer">' . $params['langpaynow'] . '</a>';
}

function robinhood_activate()
{
    // You can customize activation logic if needed
    return array('status' => 'success', 'description' => 'robinhood gateway activated successfully.');
}

function robinhood_deactivate()
{
    // You can customize deactivation logic if needed
    return array('status' => 'success', 'description' => 'robinhood gateway deactivated successfully.');
}

function robinhood_upgrade($vars)
{
    // You can customize upgrade logic if needed
}

function robinhood_output($vars)
{
    // Output additional information if needed
}

function robinhood_error($vars)
{
    // Handle errors if needed
}
