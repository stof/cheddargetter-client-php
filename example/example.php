<?php
	require('../../CheddarGetter/Client.php');
	require('../../CheddarGetter/Client/Exception.php');
	require('../../CheddarGetter/Response.php');
	require('../../CheddarGetter/Response/Exception.php');
	
	$client = new CheddarGetter_Client(
		'https://cheddargetter.com',
		'your.username@example.com',
		'your.password',
		'YOUR_PRODUCT_CODE'
	);
	
	echo "\n";
	echo "****************************************************\n";
	echo "** DELETING PREVIOUSLY CREATED EXAMPLE CUSTOMERS  **\n";
	echo "****************************************************\n";
	
	// delete customers if they're already there
	try {
		$response = $client->deleteCustomer('MILTON_WADDAMS');
		echo "\n\tDeleted Milton Waddams\n";
	} catch (Exception $e) {}
	try {
		$response = $client->deleteCustomer('BILL_LUMBERG');
		echo "\n\tDeleted Bill Lumberg\n";
	} catch (Exception $e) {}
	
	echo "\n";
	echo "****************************************************\n";
	echo "** CREATE CUSTOMER ON THE FREE PLAN               **\n";
	echo "****************************************************\n";
	
	// create a customer on a free plan
	$data = array(
		'code' 			=> 'MILTON_WADDAMS',
		'firstName' 		=> 'Milton',
		'lastName' 		=> 'Waddams',
		'email' 			=> 'milt@initech.com',
		'subscription' 	=> array(
			'planCode' 		=> 'FREE'
		)
	);
	try {
		$response = $client->newCustomer($data); 
		echo "\n\tCreated Milton Waddams with code=MILTON_WADDAMS\n";
	} catch (Exception $e) {
		echo "\n\t" . 'ERROR: (' . $e->getCode() . ') ' . $e->getMessage() . "\n"; 
	}
	
	echo "\n";
	echo "****************************************************\n";
	echo "** SIMULATE ERROR CREATING CUSTOMER ON PAID PLAN  **\n";
	echo "****************************************************\n";
	
	// try to create a customer on a paid plan (simulated error)
	$data = array(
		'code' 			=> 'BILL_LUMBERG',
		'firstName' 		=> 'Bill',
		'lastName' 		=> 'Lumberg',
		'email' 			=> 'bill@initech.com',
		'subscription' 	=> array(
			'planCode' 		=> 'PREMIUM',
			'ccNumber' 		=> '4111111111111111',
			'ccExpiration' 	=> '10/2014',
			'ccCardCode' 	=> '123',
			'ccFirstName' 	=> 'Bill',
			'ccLastName'		=> 'Lumberg',
			'ccZip'			=> '05003' // simulates an error of "Credit card type is not accepted"
		)
	);
	try {
		$response = $client->newCustomer($data); 
	} catch (CheddarGetter_Response_Exception $re) {
		if ($re->getCode() == 412) { // missing fields or field format errors
			echo "\n\tERROR - MISSING OR INVALID FIELDS:\n";
			echo "\n\t(" . $re->getCode() . ':' . $re->getAuxCode() . ') ' . $re->getMessage() . "\n";
		} else if ($re->getCode() == 422) { // payment processing errors
			echo "\n\tAN ERROR OCCURED DURING PAYMENT PROCESSING:\n";
			echo "\n\t(" . $re->getCode() . ':' . $re->getAuxCode() . ') ' . $re->getMessage() . "\n";
		} else {
			echo "\n\t" . 'ERROR: (' . $re->getCode() . ':' . $re->getAuxCode() . ') ' . $re->getMessage() . "\n";
		}
	} catch (Exception $e) {
		echo "\n\t" . 'ERROR: (' . $e->getCode() . ') ' . $e->getMessage() . "\n"; 
	}
	
	echo "\n";
	echo "****************************************************\n";
	echo "** CREATE CUSTOMER ON PAID PLAN AND GET CURRENT   **\n";
	echo "** INVOICE INFORMATION                            **\n";
	echo "****************************************************\n";
	
	$data = array(
		'code' 			=> 'BILL_LUMBERG',
		'firstName' 		=> 'Bill',
		'lastName' 		=> 'Lumberg',
		'email' 			=> 'bill@initech.com',
		'subscription' 	=> array(
			'planCode' 		=> 'PREMIUM',
			'ccNumber' 		=> '4111111111111111',
			'ccExpiration' 	=> '10/2014',
			'ccCardCode' 	=> '123',
			'ccFirstName' 	=> 'Bill',
			'ccLastName'		=> 'Lumberg',
			'ccZip'			=> '90210'
		)
	);
	try {
		$response = $client->newCustomer($data); 
	} catch (Exception $e) {
		echo "\n\t" . 'ERROR: (' . $e->getCode() . ') ' . $e->getMessage() . "\n"; 
	}
	
	// get lumberg and display current details
	try {
		$response = $client->getCustomer('BILL_LUMBERG');
		$customer = $response->getCustomer();
		$subscription = $response->getCustomerSubscription();
		$plan = $response->getCustomerPlan();
		$invoice = $response->getCustomerInvoice();
		
		echo "\n\t{$customer['firstName']} {$customer['lastName']}\n";
		echo "\tPricing Plan: {$plan['name']}\n";
		echo "\tPending Invoice Scheduled: " . date('m/d/Y') . "\n";
		foreach ($invoice['charges'] as $chargeCode=>$charge) {
			echo "\t\t({$charge['quantity']}) $chargeCode " . number_format($charge['eachAmount']*$charge['quantity'], 2) . "\n\n";
		}
		
	} catch (Exception $e) {
		echo "\n\t" . 'ERROR: (' . $e->getCode() . ') ' . $e->getMessage() . "\n"; 
	}
	
