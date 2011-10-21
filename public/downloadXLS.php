<?php
//
// Description
// -----------
// This function will generate an Excel file from the data in subscriptions_excel_data;
//
// Info
// ----
// Status: 				alpha
//
// Arguments
// ---------
// api_key:
// auth_token:		
// excel_id:			The excel ID from the table subscriptions_excel;
//
// Returns
// -------
//
function ciniki_subscriptions_downloadXLS($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'subscription_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No subscription specified'), 
		'_subscription_id'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		'_subscription_name'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		'customer_id'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		'customer_name'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		'first'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		'last'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		'shipping_address'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		'billing_address'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		'mailing_address'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		'primary_email'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		'alternate_email'=>array('required'=>'no', 'default'=>'No', 'blank'=>'yes', 'errmsg'=>'No field specified'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id, and the subscription
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/subscriptions/private/checkAccess.php');
	$ac = ciniki_subscriptions_checkAccess($ciniki, $args['business_id'], 'ciniki.subscriptions.downloadXLS', $args['subscription_id']);
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Load the subscription information
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQuery.php');
	if( $args['mailing_address'] == 'Yes' ) {
		$strsql = "SELECT subscriptions.id, subscriptions.name, subscriptions.description, "
			. "customers.id AS customer_id, CONCAT_WS(' ', prefix, first, middle, last, suffix) AS customer_name, "
			. "customers.first, customers.last, customers.primary_email, customers.alternate_email, "
			. "customer_addresses.address1, customer_addresses.address2, "
			. "customer_addresses.city, customer_addresses.province, "
			. "customer_addresses.postal, customer_addresses.country "
			. "FROM subscriptions, subscription_customers, customers "
			. "LEFT JOIN customer_addresses ON (customers.id = customer_addresses.customer_id "
				. "AND customer_addresses.flags & 0x04 = 0x04 ) "
			. "WHERE subscriptions.id = '" . ciniki_core_dbQuote($ciniki, $args['subscription_id']) . "' "
			. "AND subscriptions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND subscriptions.id = subscription_customers.subscription_id "
			. "AND subscription_customers.status = 1 "
			. "AND subscription_customers.customer_id = customers.id "
			. "AND customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";

	} else {
		$strsql = "SELECT subscriptions.id, subscriptions.name, subscriptions.description, "
			. "customers.id, CONCAT_WS(' ', prefix, first, middle, last, suffix) AS customer_name, "
			. "customers.first, customers.last, customers.primary_email, customers.alternate_email "
			. "FROM subscriptions, subscription_customers, customers "
			. "WHERE subscriptions.id = '" . ciniki_core_dbQuote($ciniki, $args['subscription_id']) . "' "
			. "AND subscriptions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND subscriptions.id = subscription_customers.subscription_id "
			. "AND subscription_customers.status = 1 "
			. "AND subscription_customers.customer_id = customers.id "
			. "AND customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
	}
	//
	// Build array of columns
	//
	$cols = array();
	if( isset($args['_subscription_id']) && $args['_subscription_id'] == 'Yes' ) {
		array_push($cols, array('name'=>'Subscription ID', 'col'=>'id'));
	}
	if( isset($args['_subscription_name']) && $args['_subscription_name'] == 'Yes' ) {
		array_push($cols, array('name'=>'Subscription', 'col'=>'name'));
	}
	if( isset($args['customer_id']) && $args['customer_id'] == 'Yes' ) {
		array_push($cols, array('name'=>'Customer ID', 'col'=>'customer_id'));
	}
	if( isset($args['customer_name']) && $args['customer_name'] == 'Yes' ) {
		array_push($cols, array('name'=>'Customer', 'col'=>'customer_name'));
	}
	if( isset($args['prefix']) && $args['prefix'] == 'Yes' ) {
		array_push($cols, array('name'=>'Prefix', 'col'=>'prefix'));
	}
	if( isset($args['first']) && $args['first'] == 'Yes' ) {
		array_push($cols, array('name'=>'First Name', 'col'=>'first'));
	}
	if( isset($args['middle']) && $args['middle'] == 'Yes' ) {
		array_push($cols, array('name'=>'Middle Name', 'col'=>'middle'));
	}
	if( isset($args['last']) && $args['last'] == 'Yes' ) {
		array_push($cols, array('name'=>'Last Name', 'col'=>'last'));
	}
	if( isset($args['suffix']) && $args['suffix'] == 'Yes' ) {
		array_push($cols, array('name'=>'Suffix', 'col'=>'suffix'));
	}
	if( isset($args['company']) && $args['company'] == 'Yes' ) {
		array_push($cols, array('name'=>'Company', 'col'=>'company'));
	}
	if( isset($args['mailing_address']) && $args['mailing_address'] == 'Yes' ) {
		array_push($cols, array('name'=>'Address 1', 'col'=>'address1'));
		array_push($cols, array('name'=>'Address 2', 'col'=>'address2'));
		array_push($cols, array('name'=>'City', 'col'=>'city'));
		array_push($cols, array('name'=>'Province/State', 'col'=>'province'));
		array_push($cols, array('name'=>'Postal/Zip', 'col'=>'postal'));
		array_push($cols, array('name'=>'Country', 'col'=>'country'));
	}
	if( isset($args['primary_email']) && $args['primary_email'] == 'Yes' ) {
		array_push($cols, array('name'=>'Email', 'col'=>'primary_email'));
	}
	if( isset($args['alternate_email']) && $args['alternate_email'] == 'Yes' ) {
		array_push($cols, array('name'=>'Email', 'col'=>'alternate_email'));
	}

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuery.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbFetchHashRow.php');
	$rc = ciniki_core_dbQuery($ciniki, $strsql, 'subscriptions');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$result_handle = $rc['handle'];

	// Keep track of new row counter, to avoid deleted rows.
	$result = ciniki_core_dbFetchHashRow($ciniki, $result_handle);
	$cur_excel_row = 1;
	$prev_db_row = $result['row']['row'];

	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
	header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT"); 
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="export.xls"');
	header('Cache-Control: max-age=0');

	//
	// Excel streaming code found at: http://px.sklar.com/code.html/id=488
	//

	echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);

	for($i=0;$i<count($cols);$i++) {
		$len = strlen($cols[$i]['name']);
		echo pack("ssssss", 0x204, 8 + $len, $cur_excel_row-1, $i, 0x0, $len);
		echo $cols[$i]['name'];
	}
	$cur_excel_row++;

	while( isset($result['row']) ) {
		for($i=0;$i<count($cols);$i++) {
			if( $cols[$i]['col'] == 'customer_name' ) {
				$customer_name = preg_replace('/(^\s+|\s+$)/', '', $result['row'][$cols[$i]['col']]);
				$customer_name = preg_replace('/\s\s+/', ' ', $customer_name);
				$len = strlen($customer_name);
				echo pack("ssssss", 0x204, 8 + $len, $cur_excel_row-1, $i, 0x0, $len);
				echo $customer_name;
			} else {
				$len = strlen($result['row'][$cols[$i]['col']]);
				echo pack("ssssss", 0x204, 8 + $len, $cur_excel_row-1, $i, 0x0, $len);
				echo $result['row'][$cols[$i]['col']];
			}
		}
		
		$result = ciniki_core_dbFetchHashRow($ciniki, $result_handle);
		$cur_excel_row++;
	}

	//
	// End the excel file
	//
	echo pack("ss", 0x0A, 0x00);

	exit;

	return array('stat'=>'ok');
}
?>
