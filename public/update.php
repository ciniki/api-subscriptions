<?php
//
// Description
// -----------
//
// Info
// ----
// Status: 			defined
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_subscriptions_update(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'subscription_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Subscription'), 
        'name'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Name'), 
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Flags'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'subscriptions', 'private', 'checkAccess');
    $rc = ciniki_subscriptions_checkAccess($ciniki, $args['business_id'], 'ciniki.subscriptions.update', $args['subscription_id']); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
	$modules = $rc['modules'];

	//
	// Update the subscription
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	$rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.subscriptions.subscription', $args['subscription_id'], $args, 0x07);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	// 
	// Update the web settings
	//
	if( isset($modules['ciniki.web']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'settingsUpdateSubscriptions');
		$rc = ciniki_web_settingsUpdateSubscriptions($ciniki, $modules, $args['business_id']);
		if( $rc['stat'] != 'ok' ) {
			// Don't return error code to user, they successfully updated the record
			error_log("ERR: " . $rc['code'] . ' - ' . $rc['msg']);
		}
	}

	return array('stat'=>'ok');
}
?>
