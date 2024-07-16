<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function stripe_MetaData()
{
    return array(
        'DisplayName' => 'stripe',
        'DisableLocalCreditCardInput' => true,
    );
}

function stripe_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'stripe',
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

function stripe_link($params)
{
    $walletAddress = $params['wallet_address'];
    $amount = $params['amount'];
    $invoiceId = $params['invoiceid'];
	$email = $params['clientdetails']['email'];
    $systemUrl = rtrim($params['systemurl'], '/');
    $redirectUrl = $systemUrl . '/modules/gateways/callback/stripe.php';
	$invoiceLink = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;
	$hrs_stripecom_currency = $params['currency'];
	$callback_URL = $redirectUrl . '?invoice_id=' . $invoiceId;

if ($hrs_stripecom_currency === 'USD') {
        $hrs_stripecom_final_total = $amount;
		} else {
		
$hrs_stripecom_response = file_get_contents('https://api.highriskshop.com/control/convert.php?value=' . $amount . '&from=' . strtolower($hrs_stripecom_currency));


$hrs_stripecom_conversion_resp = json_decode($hrs_stripecom_response, true);

if ($hrs_stripecom_conversion_resp && isset($hrs_stripecom_conversion_resp['value_coin'])) {
    // Escape output
    $hrs_stripecom_final_total	= $hrs_stripecom_conversion_resp['value_coin'];      
} else {
	return "Error: Payment could not be processed, please try again (unsupported store currency)";
}	
		}
		
		
		
		
$hrs_stripecom_gen_wallet = file_get_contents('https://api.highriskshop.com/control/wallet.php?address=' . $walletAddress .'&callback=' . urlencode($callback_URL));


	$hrs_stripecom_wallet_decbody = json_decode($hrs_stripecom_gen_wallet, true);

 // Check if decoding was successful
    if ($hrs_stripecom_wallet_decbody && isset($hrs_stripecom_wallet_decbody['address_in'])) {
        // Store the address_in as a variable
        $hrs_stripecom_gen_addressIn = $hrs_stripecom_wallet_decbody['address_in'];
        $hrs_stripecom_gen_polygon_addressIn = $hrs_stripecom_wallet_decbody['polygon_address_in'];
		$hrs_stripecom_gen_callback = $hrs_stripecom_wallet_decbody['callback_url'];
		
		
		 // Update the invoice description to include address_in
            $invoiceDescription = "Payment reference number: $hrs_stripecom_gen_polygon_addressIn";

            // Update the invoice with the new description
            $invoice = localAPI("GetInvoice", array('invoiceid' => $invoiceId), null);
            $invoice['notes'] = $invoiceDescription;
            localAPI("UpdateInvoice", $invoice);

		
		
    } else {
return "Error: Payment could not be processed, please try again (wallet address error)";
    }
	
	
        $paymentUrl = 'https://pay.highriskshop.com/process-payment.php?address=' . $hrs_stripecom_gen_addressIn . '&amount=' . $hrs_stripecom_final_total . '&provider=stripe&email=' . urlencode($email) . '&currency=' . $hrs_stripecom_currency;

        // Properly encode attributes for HTML output
        return '<a href="' . $paymentUrl . '" class="btn btn-primary" rel="noreferrer">' . $params['langpaynow'] . '</a>';
}

function stripe_activate()
{
    // You can customize activation logic if needed
    return array('status' => 'success', 'description' => 'stripe gateway activated successfully.');
}

function stripe_deactivate()
{
    // You can customize deactivation logic if needed
    return array('status' => 'success', 'description' => 'stripe gateway deactivated successfully.');
}

function stripe_upgrade($vars)
{
    // You can customize upgrade logic if needed
}

function stripe_output($vars)
{
    // Output additional information if needed
}

function stripe_error($vars)
{
    // Handle errors if needed
}
