<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function mercuryo_MetaData()
{
    return array(
        'DisplayName' => 'mercuryo',
        'DisableLocalCreditCardInput' => true,
    );
}

function mercuryo_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'mercuryo',
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

function mercuryo_link($params)
{
    $walletAddress = $params['wallet_address'];
    $amount = $params['amount'];
    $invoiceId = $params['invoiceid'];
	$email = $params['clientdetails']['email'];
    $systemUrl = rtrim($params['systemurl'], '/');
    $redirectUrl = $systemUrl . '/modules/gateways/callback/mercuryo.php';
	$invoiceLink = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;
	$hrs_mercuryoio_currency = $params['currency'];
	$callback_URL = $redirectUrl . '?invoice_id=' . $invoiceId;
	$hrs_mercuryoio_final_total = $amount;
				
$hrs_mercuryoio_gen_wallet = file_get_contents('https://api.highriskshop.com/control/wallet.php?address=' . $walletAddress .'&callback=' . urlencode($callback_URL));


	$hrs_mercuryoio_wallet_decbody = json_decode($hrs_mercuryoio_gen_wallet, true);

 // Check if decoding was successful
    if ($hrs_mercuryoio_wallet_decbody && isset($hrs_mercuryoio_wallet_decbody['address_in'])) {
        // Store the address_in as a variable
        $hrs_mercuryoio_gen_addressIn = $hrs_mercuryoio_wallet_decbody['address_in'];
        $hrs_mercuryoio_gen_polygon_addressIn = $hrs_mercuryoio_wallet_decbody['polygon_address_in'];
		$hrs_mercuryoio_gen_callback = $hrs_mercuryoio_wallet_decbody['callback_url'];
		
		
		 // Update the invoice description to include address_in
            $invoiceDescription = "Payment reference number: $hrs_mercuryoio_gen_polygon_addressIn";

            // Update the invoice with the new description
            $invoice = localAPI("GetInvoice", array('invoiceid' => $invoiceId), null);
            $invoice['notes'] = $invoiceDescription;
            localAPI("UpdateInvoice", $invoice);

		
		
    } else {
return "Error: Payment could not be processed, please try again (wallet address error)";
    }
	
	
        $paymentUrl = 'https://pay.highriskshop.com/process-payment.php?address=' . $hrs_mercuryoio_gen_addressIn . '&amount=' . $hrs_mercuryoio_final_total . '&provider=mercuryo&email=' . urlencode($email) . '&currency=' . $hrs_mercuryoio_currency;

        // Properly encode attributes for HTML output
        return '<a href="' . $paymentUrl . '" class="btn btn-primary" rel="noreferrer">' . $params['langpaynow'] . '</a>';
}

function mercuryo_activate()
{
    // You can customize activation logic if needed
    return array('status' => 'success', 'description' => 'mercuryo gateway activated successfully.');
}

function mercuryo_deactivate()
{
    // You can customize deactivation logic if needed
    return array('status' => 'success', 'description' => 'mercuryo gateway deactivated successfully.');
}

function mercuryo_upgrade($vars)
{
    // You can customize upgrade logic if needed
}

function mercuryo_output($vars)
{
    // Output additional information if needed
}

function mercuryo_error($vars)
{
    // Handle errors if needed
}
