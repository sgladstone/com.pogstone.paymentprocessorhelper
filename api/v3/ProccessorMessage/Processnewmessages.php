<?php

/**
 * ProccessorMessage.Processnewmessages API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_proccessor_message_processnewmessages_spec(&$spec) {
  
}

/**
 * ProccessorMessage.Processnewmessages API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_proccessor_message_processnewmessages($params) {
 
 /*
  if (array_key_exists('magicword', $params) && $params['magicword'] == 'sesame') {
    $returnValues = array( // OK, return several data rows
      12 => array('id' => 12, 'name' => 'Twelve'),
      34 => array('id' => 34, 'name' => 'Thirty four'),
      56 => array('id' => 56, 'name' => 'Fifty six'),
    );
    */
    // ALTERNATIVE: $returnValues = array(); // OK, success
    // ALTERNATIVE: $returnValues = array("Some value"); // OK, return a single value
	handle_the_messages(); 
    // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
    return civicrm_api3_create_success($returnValues, $params, 'ProcessorMessage', 'processnewmessages');
 // } else {
  //  throw new API_Exception(/*errorMessage*/ 'Everyone knows that the magicword is "sesame"', /*errorCode*/ 1234);
 // }
}



 function handle_the_messages(){
   
   	//init();

	$pay_pal_type = "PayPal"; 
	$authnet_type = "AuthNet"; 
	$ewayemailrecur_type = "eWay_Recurring";
	
	
	$params = array(
	  'version' => 3,
	  'sequential' => 1,
	  'vendor_type' => $pay_pal_type,
	);
	$result = civicrm_api('PaymentProcessorTypeHelper', 'get', $params);
	
	$tmp  = $result['values'][0];
	if($tmp['id'] == $pay_pal_type){
		$bool_str = $tmp['name'];
		$pay_pal_enabled = $bool_str === 'true'? true: false;
	
	}
	//  ( [is_error] => 0 [version] => 3 [count] => 1 [id] => PayPal [values] => Array ( [0] => Array ( [id] => PayPal [name] => false ) ) )
	//print "<br><br>result:";
	//print_r( $result); 
	$params = array(
	  'version' => 3,
	  'sequential' => 1,
	  'vendor_type' => $authnet_type,
	);
	$result = civicrm_api('PaymentProcessorTypeHelper', 'get', $params);
	
	$tmp  = $result['values'][0];
	if($tmp['id'] == $authnet_type){
		$bool_str = $tmp['name'];
		$authnet_enabled = $bool_str === 'true'? true: false;
	
	}
	// Now check for eWay recurring (email notifications)
	$params = array(
	  'version' => 3,
	  'sequential' => 1,
	  'vendor_type' => $ewayemailrecur_type ,
	);
	$result = civicrm_api('PaymentProcessorTypeHelper', 'get', $params);
	
	$tmp  = $result['values'][0];
	if($tmp['id'] == $ewayemailrecur_type ){
		$bool_str = $tmp['name'];
		$ewayrecur_enabled = $bool_str === 'true'? true: false;
	
	}
	
	
	if( $pay_pal_enabled  ){
   //   print "<br><br><br> PayPal enabled"; 
	    
		$sql = 	"SELECT msgs.amount, msgs.txn_id,  msgs.message_date, concat(msgs.last_name, ',' , msgs.first_name) as sort_name , `civicrm_recur_id` , c.id as crm_contrib_id, c.contact_id as crm_contact_id, con.sort_name as crm_contact_name, recur.id as crm_recur_id, ct.name as contrib_type_name, recur_ct.id as recur_contribution_type , recur_ct.name as recur_contrib_type_name, recur.contact_id as recur_contact_id, recur_contact.id as recur_contact_id, recur_contact.sort_name as recur_contact_name, `rec_type` , date_format(message_date, '%Y%m%d'  ) as message_date , `payment_status` ,
			rp_invoice_id, recur.amount  as crm_amount
      FROM pogstone_paypal_messages as msgs LEFT JOIN civicrm_contribution c ON msgs.txn_id = c.trxn_id LEFT JOIN civicrm_contact con ON c.contact_id = con.id LEFT JOIN civicrm_contribution_recur recur ON recur.id = (  substr( rp_invoice_id, LOCATE( '&r=' , rp_invoice_id) + 3,   ( LOCATE( 
    '&b=', rp_invoice_id ) - 3 -  (LOCATE( '&r=' , rp_invoice_id)  ) ) ) )  LEFT JOIN civicrm_financial_type recur_ct ON recur.financial_type_id = recur_ct.id LEFT JOIN civicrm_contact recur_contact ON recur.contact_id = recur_contact.id LEFT JOIN civicrm_financial_type ct ON c.financial_type_id = ct.id WHERE msgs.payment_status = 'Completed' AND length(msgs.recurring_payment_id) > 0 AND c.id IS NULL
       AND msgs.message_date >= '2013-03-01'
       GROUP by msgs.ipn_track_id ";
		
       
       	    }else if(  $authnet_enabled ){
       	    // print "<br><br><br> Authorize.net enabled"; 
       		
       		$sql = "SELECT concat(x_last_name, ',' , x_first_name) as sort_name , `civicrm_recur_id` , c.id as crm_contrib_id, c.contact_id as crm_contact_id, con.sort_name as crm_contact_name, recur.id as crm_recur_id, ct.name as contrib_type_name, recur_ct.id as recur_contribution_type , recur_ct.name as recur_contrib_type_name, recur.contact_id as recur_contact_id, recur_contact.id as recur_contact_id, recur_contact.sort_name as recur_contact_name, `rec_type` , date_format(message_date, '%Y%m%d'  ) as message_date ,
       		x_amount as message_amount, 
       		`x_response_code` , `x_response_reason_code` , `x_response_reason_text` , `x_avs_code` , `x_auth_code` , `x_trans_id` , 
     `x_method` , `x_card_type` , `x_account_number` , `x_first_name` , `x_last_name` , `x_company` , `x_address` , `x_city` , `x_state` , `x_zip` ,
      `x_country` , `x_phone` , `x_fax` , `x_email` , `x_invoice_num` , `x_description` , `x_type` , `x_cust_id` , `x_ship_to_first_name` , `x_ship_to_last_name` , `x_ship_to_company` , `x_ship_to_address` , `x_ship_to_city` , `x_ship_to_state` , `x_ship_to_zip` , `x_ship_to_country` , `x_amount` , `x_tax` , `x_duty` , `x_freight` , `x_tax_exempt` , `x_po_num` , `x_MD5_Hash` , `x_cvv2_resp_code` , `x_cavv_response` , `x_test_request` , `x_subscription_id` , `x_subscription_paynum` , recur.amount  as crm_amount
      FROM pogstone_authnet_messages as msgs LEFT JOIN civicrm_contribution c ON msgs.x_trans_id = c.trxn_id LEFT JOIN civicrm_contact con ON c.contact_id = con.id LEFT JOIN civicrm_contribution_recur recur ON recur.processor_id = msgs.x_subscription_id LEFT JOIN civicrm_financial_type recur_ct ON recur.financial_type_id = recur_ct.id LEFT JOIN civicrm_contact recur_contact ON recur.contact_id = recur_contact.id LEFT JOIN civicrm_financial_type ct ON c.financial_type_id = ct.id WHERE msgs.x_response_code = '1' AND length(msgs.x_subscription_id) > 0 AND c.id IS NULL
       AND msgs.message_date >= '2013-03-01'  " ; 
       		
       		
      }else if( $ewayrecur_enabled){
       		   // Currently only completed eWay transactions have an amount > 0. 
       		   // Raw 'eway_email_date' is always in America/New York time zone. Need to adjust to the time zone of the client, such as Sydney in Australia
       		   $hours_to_add = ""; 
				
				$org_timezone = variable_get('pogstone_local_timezone', NULL);
				if( $org_timezone == 'Australia/Sydney'){
					$num_sec  = 60 * 60 * 14; 
					$hours_to_add = '14 HOUR' ;
				}else if( $org_timezone ==   'Australia/Melbourne'){ 
					$num_sec  = 60 * 60 * 14; 
					$hours_to_add = '14 HOUR' ;
				
				}else if( $org_timezone ==  'Australia/Perth' ){
					$num_sec  = 60 * 60 * 12;  
					$hours_to_add = '12 HOUR' ;
				}else{
					print "<br>org time zone not recognized: ".$org_timezone;
					$num_sec  = 60 * 60 * 14; 
					$hours_to_add = '14 HOUR' ;	
				}
				
				// field 'eway_email_date' is of type datetime
				
				
				//$tmp_a =  $email_timestamp + $num_sec; 	
				// 	print "<br><br>email timestamp: ".$email_timestamp."<br> Adjusted ts: ".$tmp_a;
				// $paymentDate = date('Ymd H:i:s', $tmp_a) ;
       		
       		    $sql = 	"SELECT recur.id as crm_recur_id,  msgs.eway_transaction_id,  
       		    DATE_ADD( `eway_email_date`,  INTERVAL ".$hours_to_add." ) as adj_eway_email_date , `eway_currency`, `eway_amount`,
       		     `eway_transaction_id`, `eway_name`, `eway_address`,
       		     `eway_invoice_reference_number`, `eway_email_subject`, `eway_email_body`, 
       		     c.id as crm_contrib_id, c.contact_id as contact_id, contact_a.sort_name as crm_contact_name, ct.name as contrib_type_name,
       		     recur.id as crm_recur_id, recur_ct.name as recur_contrib_type_name, recur.contact_id as recur_contact_id,
       		      recur_contact.id as recur_contact_id,
       		     recur_contact.sort_name as recur_contact_name , recur.amount  as crm_amount
       		    FROM pogstone_eway_messages as msgs LEFT JOIN civicrm_contribution c ON msgs.eway_transaction_id = c.trxn_id 
       		    LEFT JOIN civicrm_contribution_recur recur ON recur.processor_id = msgs.eway_invoice_reference_number 
       		    LEFT JOIN civicrm_financial_type recur_ct ON recur.financial_type_id = recur_ct.id 
       		    LEFT JOIN civicrm_contact recur_contact ON recur.contact_id = recur_contact.id 
       		    LEFT JOIN civicrm_contact contact_a ON c.contact_id = contact_a.id 
       		    LEFT JOIN civicrm_financial_type ct ON c.financial_type_id = ct.id WHERE msgs.eway_amount > 0 
       		    AND msgs.eway_invoice_reference_number LIKE '%(r)' AND c.id IS NULL ";
       		
       		
       		
       		
       		}
	
	
     
       $tmp_server_path =   realpath($_SERVER['DOCUMENT_ROOT'].'/../') ;   
    	  
    	  $filename_prefix = date('Y-m-d');
    	  
    	  
	 // $logfile = $tmp_server_path."/".$filename_prefix."__pogstone_createContributionsFromMessgae_log.txt";
	 // print "<br>Log file path: ".$logfile;
	  
	//     $log_handle =   fopen(  $logfile , "a+") ;
	$logfile = 0; 
	     
	     $now = date('Y-m-d  H:i:s');
	     
	     // Store the posted values in an associative array
		$fields = array();
		
	     if($log_handle){
	     
		  fwrite($log_handle, "\n Special Debug version -------------------------------------------------------\n\n");
		  fwrite ( $log_handle, "\n Now it is: ".$now); 
		  fwrite(  $log_handle , '  ');
	     	  fwrite(  $log_handle , '\n');
	     	  
	     	  fwrite(  $log_handle , $sql);
	     	  fwrite(  $log_handle , '\n');
     	  }
     	
     	 
   //	print "<br><hr>";
 //  print "<h1>Pogstone script for recurring contributions</h1>";
   
   fixRecurringWithNoContribs();
   
  // print "<h2>Section: Find new payment processor messages and attempt to create contribution records</h2>"; 
  // print "<br><br>SQL: ".$sql;
   if(strlen( $sql) > 0 ){
	 $dao  =  & CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
        $rec_count = 0;
	while ( $dao->fetch( ) ) {
	
		$message_valid_to_process = true; 
		
		$cid = $dao->recur_contact_id; 
		$contrib_type_id = $dao->recur_contribution_type ; 
		$recur_id = $dao->crm_recur_id;
		$crm_contact_name = $dao->crm_contact_name; 
		$card_billingname = $dao->sort_name;
		$crm_amount = $dao->crm_amount;
			
		if(  $creditCardUtils->isPayPalEnabled() ){
		     
		        $receive_date = $dao->message_date;
			$amount = $dao->amount;
			$trxn_id = $dao->txn_id ; 
			$processor_subscription_id = $dao->recurring_payment_id;
			// print "<br>Inside paypal section: amt: ".$amount; 
		
		}else if( $creditCardUtils->isAuthorizeNetEnabled()  ){
		
		        $receive_date = $dao->message_date;
			$amount = $dao->x_amount;
			$trxn_id = $dao->x_trans_id ; 
			$processor_subscription_id = $dao->x_subscription_id;
			
			$tmp_trans_amount = number_format($amount, 2); 
			$tmp_crm_amount = number_format($crm_amount, 2); 
			if( $tmp_crm_amount  <> $tmp_trans_amount ){
				$message_valid_to_process = false; 
				$message_error_text = "Transaction amount ($tmp_trans_amount) does NOT match CRM amount ($tmp_crm_amount) for this subscription";
			
			}
		
		}else if($creditCardUtils->isEWayEnabled() ){
			// recur.id as crm_recur_id,  msgs.eway_transaction_id,   `eway_email_date`, 
			
			$receive_date = $dao->adj_eway_email_date;  
			$amount = $dao->eway_amount;
			 
			$trxn_id = $dao->eway_transaction_id ; 
			$processor_subscription_id = $dao->crm_recur_id; 
		       
		}
		
		
		
		// this is the fancy new way introduced for version 4.3.x or better
		if(strlen( $recur_id) > 0 ){
		     if( strlen( $trxn_id ) == 0 ){
		           print "<br><br>Cannot process this message, trxn_id is empty. "; 
		           print "<br> \n Error on contact id: ".$cid." -- Name on Card: ".$card_billingname." -- CRM Name: ".$crm_contact_name." crm_recur_id: ".$recur_id; 
		     
		     }else if($message_valid_to_process <> true ){
		     	 print "<br><br>Cannot process this message: ".$message_error_text; 
		     	 print "<br> \n Error on contact id: ".$cid." -- Name on Card: ".$card_billingname." -- CRM Name: ".$crm_contact_name." crm_recur_id: ".$recur_id; 
		     
		     
		     }else{
		  //	print "<h2>Process for contact id: ".$cid." -- Name on Card: ".$card_billingname." -- CRM Name: ".$crm_contact_name." crm_recur_id: ".$recur_id."</h2>";
		  if( $log_handle){
			   fwrite($log_handle, "\n Process for contact id: ".$cid." -- Name on Card: ".$card_billingname." -- CRM Name: ".$crm_contact_name." crm_recur_id: ".$recur_id." trxn id: ".$trxn_id."  ----------------------------------------------------\n\n");
			   }
			$rtn_code = UpdateRecurringContributionSubscription($log_handle, $recur_id , $trxn_id, $receive_date  ); 
		
			// TODO: Check rtn_code to see if there was an error. 
			$rec_count++;
			
		      }
		}else{
			// print "<br>Error: Could not find crm_recur_id for x_subscription_id: ".$processor_subscription_id;
		
		}
		
		
		
		
	}

	$dao->free();
	
	}
	
	
	handleCancelledSubscriptions(); 
	
	 
	 
	
	 
	 
	 
		
	}
	
	
	  //  run();
	   
				
	  
	  
	    // Test with hard-coded message
	     // Next 3 values should come from the payment processor message. 
	     /*
	        $crm_recur_id = "3";
	 	$trxn_id = "41122";
		$trxn_receive_date = "20130703"; 
		
		UpdateRecurringContributionSubscription($log_handle, $crm_recur_id , $trxn_id, $trxn_receive_date  ); 
		*/
		
	function handleCancelledSubscriptions(){
		// print "<h2>Section: If recurring contribution is cancelled, then update the pending contribution to cancelled status as well. </h2>"; 
	// If recurring subscription is cancelled, make sure the pending contribution is also cancelled. 
	$cancelled_status_id = "3"; 
	
	$pending_status_id = "2"; 
	
	$tmp_sql = "select c.id as contrib_id 
	FROM civicrm_contribution_recur r join civicrm_contribution c ON r.id = c.contribution_recur_id 
	WHERE  r.contribution_status_id  = $cancelled_status_id
	AND c.contribution_status_id = $pending_status_id "; 
	
	 $dao  =  & CRM_Core_DAO::executeQuery( $tmp_sql,   CRM_Core_DAO::$_nullArray ) ;
	 while($dao->fetch()){
	 	$contrib_id = $dao->contrib_id; 
	 
	 	if( strlen( $contrib_id ) > 0){
		 	$params = array(
			  'version' => 3,
			  'sequential' => 1,
			  'id' => $contrib_id ,
			  'contribution_status_id' => $cancelled_status_id,
			);
			$result = civicrm_api('Contribution', 'create', $params);
			print "<br>API update contrib. status result:<br>";
			print_r( $result); 
		}
	 
	 }
	 
	 
	 $dao->free();
	
	}	
		
	function fixRecurringWithNoContribs(){
	
		 // Check for recurring contributions with NO associated contributions. 
	// print "<h2>Section: Look for contribution_recur records with NO associated contributions, as this prevents messages from being processed. </h2>";
	 
	 // check payment processor type. This is only needed for Auth.net, PayPalPro, and eWAY
	  // Ignore subscriptions that are cancelled(3) or completed(1).
	 $tmp_sql = "select r.id, r.contact_id,  r.amount, r.financial_type_id as financial_type_id ,
	  r.contribution_status_id , r.campaign_id, r.start_date
	FROM civicrm_contribution_recur r 
	JOIN civicrm_payment_processor p ON r.payment_processor_id = p.id
	JOIN civicrm_payment_processor_type pt ON pt.id = p.payment_processor_type_id 
	LEFT JOIN civicrm_contribution c ON r.id = c.contribution_recur_id 
	WHERE c.id IS NULL
	AND r.start_date > '2014-01-01'
	AND pt.name IN ('PayPal', 'AuthNet', 'eWay_recurring') 
	AND r.contribution_status_id NOT IN ( 1, 3)
	GROUP BY r.id "; 
	
	 $dao  =  & CRM_Core_DAO::executeQuery( $tmp_sql,   CRM_Core_DAO::$_nullArray ) ;
	  while($dao->fetch()){
	  	
	  	$recur_id = $dao->id;
	  	$contact_id = $dao->contact_id ; 
	  	$amount = $dao->amount;
	  	$financial_type_id= $dao->financial_type_id; 
	  //	$contribution_status_id = $dao->contribution_status_id; 
	  	$start_date = $dao->start_date; 
	  	$campaign_id = $dao->campaign_id; 
	  	
	  	
	  		//$params = array(
			//  'version' => 3,
			//  'sequential' => 1,
			//  'contribution_status_id' => $cancelled_status_id,
		//	);
			//$result = civicrm_api('Contribution', 'create', $params);
			//print "<br>API update contrib. status result:<br>";
			//print_r( $result); 
	  
	  
	  
	  }
	   $dao->free();
	
	}	
	     
	 function create_needed_line_item_db_records( $line_item_id, $line_item_data, $contrib_data ){
	 
	 
	      if( strlen($contrib_data['trxn_id']) == 0 ){
	           print "<h2>Error: Transaction ID cannot be empty!</h2>";
	           
	           exit(); 
	      }
	     // print "<br>About to create needed line item records.<br>";
	     // print_r(  $line_item) ; 
	      
	      $description_cleaned = str_replace( "'", "\'", $line_item_data['label'] ); 
	      
	      	$insert_sql_financial_item = "INSERT INTO 
	      					civicrm_financial_item (  created_date, transaction_date, contact_id, description, 
	      					amount, 
	      					currency, 
	      					financial_account_id, status_id , entity_table , entity_id ) 
	      					VALUES ( '".$contrib_data['receive_date']."' , '".$contrib_data['receive_date']."' , ".$contrib_data['contact_id'].",
	      					 '".$description_cleaned."' , ".$line_item_data['line_total'].
	      					", '".$contrib_data['currency'] ."' , 
	      					".$line_item_data['financial_type_id'].", '1' , 'civicrm_line_item' , ".$line_item_id."  ) "; 
	      					
	     // 	print "<br>Part 1: Insert SQL: ".$insert_sql_financial_item; 				
	       	$dao_fi  =  & CRM_Core_DAO::executeQuery($insert_sql_financial_item,   CRM_Core_DAO::$_nullArray ) ;
	 	$dao_fi->free();
	 	
	 	// Now get ID from new record
	 	$financial_item_id = "";
	 	$get_id_sql = "SELECT * FROM civicrm_financial_item WHERE 
	 			entity_table = 'civicrm_line_item' AND  entity_id = ".$line_item_id;
	 	
	 	$dao_get_id =  & CRM_Core_DAO::executeQuery($get_id_sql,   CRM_Core_DAO::$_nullArray ) ;
	 	while ( $dao_get_id->fetch( ) ) {
	 	   $financial_item_id = $dao_get_id->id ; 	 	
	 	}
	 	
	 	$dao_get_id->free(); 
	 	
	 	
	 	// civicrm_financial_trxn.id is needed for financial_trxn_id field. Go get it. 
	 	$crm_trxn_id = ""; 
	 	$get_trxn_id_sql = "SELECT id 
				   FROM  civicrm_financial_trxn where trxn_id = '".$contrib_data['trxn_id']."'"; 
				   
				
		$dao_get_trxn_id =  & CRM_Core_DAO::executeQuery($get_trxn_id_sql,   CRM_Core_DAO::$_nullArray ) ;
	 	while ( $dao_get_trxn_id->fetch( ) ) {
	 	   $crm_trxn_id = $dao_get_trxn_id->id ; 	 	
	 	}
	 	
	 	$dao_get_trxn_id->free(); 
				   
				   
	 	
	 	$insert_sql_ft = "INSERT INTO civicrm_entity_financial_trxn ( entity_table, entity_id, financial_trxn_id, amount ) 
	 			  VALUES( 'civicrm_financial_item', ".$financial_item_id.", ".$crm_trxn_id." , ".$line_item_data['line_total']." )  ";
	 			  
	 	// print "<br>Part 2: Insert SQL: ".$insert_sql_ft; 
	 	$dao_ft  =  & CRM_Core_DAO::executeQuery($insert_sql_ft,   CRM_Core_DAO::$_nullArray ) ;
	 	$dao_ft->free();
	 
	 
	 
	 }   
	    
	 function UpdateRecurringContributionSubscription($log_handle, $crm_recur_id , $trxn_id, $trxn_receive_date  ){
	 
	   $contribution_completed = false; 
	   
	    
	    $params = array(
		  'version' => 3,
		  'sequential' => 1,
		  'id' => $crm_recur_id,
		);
	   $result = civicrm_api('ContributionRecur', 'get', $params);
	   if($result['is_error'] <> 0 ){
	   	//print "<br><br>Error calling ContributionRecur Get API: <br>";
	   	//print_r( $result) ;
	   	 fwrite(  $log_handle , '\n');
	   	 fwrite( $log_handle, "Error calling ContributionRecur Get API: \n") ; 
	   	return; 
	   } 
	    
	    	if($result['count'] <> "1" ){
	    		// print "<br><br>Error: Could not retrieve Recurring Contribution id: ".$crm_recur_id; 
	    		 fwrite(  $log_handle , '\n');
	   	 fwrite( $log_handle, "\nError: Could not retrieve Recurring Contribution id: ".$crm_recur_id) ; 
	    		return; 
	    	
	    	}
	    $first_contrib_status = "";
	    $first_contrib_id = "";
	    
	  //   print "<br>About to check for first contrib in the subscription<br>"; 
	   //  print_r($result); 	
	    // get contrib. id of starting contrib.
	    findFirstContributionInSubscription($log_handle,  $crm_recur_id,  $first_contrib_id,  $first_contrib_status);
	    
	   // print "<br>Already checked for first contrib in the subscription"; 
		
	
	 if(  $first_contrib_status == "1" ){
	 	if( strlen( $first_contrib_id ) > 0 ){
			// Create a new contribution record based on data from the first contribution record. 
			
	  		$rtn_code = createContributionBasedOnExistingContribution($first_contrib_id,  $trxn_id, $trxn_receive_date);
	  		$contribution_completed = $rtn_code; 
	  		
	  	}else{
	  		// print "<Br><br>Error: For crm_recur_id: ".$crm_recur_id."   First contribution id (for completed contribution) is blank"; 
	  		 fwrite(  $log_handle , '\n');
	   		 fwrite( $log_handle, "Error: For crm_recur_id: ".$crm_recur_id." First contribution id (for completed contribution) is blank \n") ; 
	  	
	  	}
	  }else if( $first_contrib_status == "2" ){
	  	// update existing first contribution record staus from pending to complete
	  	// print "<br><br>Need to update first contribution record (id: ".$first_contrib_id.") ."; 
	  	// print "<br>Because API issues, will create brand new contribution based on first, then will delete the first pending";
	  	 fwrite(  $log_handle , '\n');
	   		 fwrite( $log_handle, "\nNeed to update first contribution record (id: ".$first_contrib_id.") . \n") ;
	   		  fwrite( $log_handle, "\nBecause API issues, will create brand new contribution based on first, then will delete the first pending \n") ;
	  	
	  	if( strlen( $first_contrib_id ) > 0 ){
			// Create a new contribution record based on data from the first contribution record. 
	  		$rtn_code = createContributionBasedOnExistingContribution($first_contrib_id,  $trxn_id, $trxn_receive_date);
	  		$contribution_completed = $rtn_code; 
	  		
	  		if($rtn_code == true ){
	  			// delete original pending contribution
	  			// $first_contrib_id
	  			 $params = array(
				  'version' => 3,
				  'sequential' => 1,
				  'id' => $first_contrib_id,
				);
				$result = civicrm_api('Contribution', 'delete', $params ) ; 
				// print "<br>Result from deleting the pending contribution:<br>";
				// print_r($result); 
	  		
	  		}
	  		
	  	}else{
	  	//	print "<Br><br>Error: For crm_recur_id: ".$crm_recur_id." First contribution id (for pending contribution) is blank"; 
	  		 fwrite(  $log_handle , '\n');
	   		 fwrite( $log_handle, "<Br><br>Error: For crm_recur_id: ".$crm_recur_id." First contribution id (for pending contribution) is blank\n") ;
	   		 
	  	
	  	}
	         
	        
	         
	  	
	  }else{
	  	// print "<br><br>ERROR: Unrecognized contribution status for the first contribution record in the subscription"; 
	  
	  }
	  
	 
	 if( $contribution_completed){
	      update_recurring_subscription_details( $crm_recur_id ,  $trxn_receive_date  );
	      
	 
	 }
	 
	 }   
	    
	    
	    
	 function update_recurring_subscription_details( $crm_recur_id ,  $trxn_receive_date  ){
	 	if(strlen( $crm_recur_id) == 0){
	 		// print "<br>ERROR: crm_recur_id is a required parameter";
	 		return; 
	 	
	 	}
	 
	 	// Figure out what new recurring status should be. Either "in progress" or "completed"
	 	 $recur_completed_contribution_count = 0; 
	 	 $recur_expected_contribution_count = 0; 
	 	// Step 1: Find out how many completed payments have occured.
	 	$params = array(
		  'version' => 3,
		  'sequential' => 1,
		  'contribution_recur_id' => $crm_recur_id,
		  'contribution_status_id' => 1,
		);
		$result = civicrm_api('Contribution', 'getcount', $params);
		
		if($result['is_error'] <> 0 ){
			// print "<br>ERROR: issue calling Contribution Get API";
			// print_r ( $result );
			return;
		
		}else{
			// print "<br>Successfully called Contribution...getcount API";  
			//print_r($result);
		   $recur_completed_contribution_count = $result;
		      // print "<br>Completed Contributions for this recuring subscription: ".$recur_completed_contribution_count;
		   
		
		}
		
		// Step 2: Find out how many payments wer expected
		$params = array(
		  'version' => 3,
		  'sequential' => 1,
		  'id' => $crm_recur_id,
		);
		$result = civicrm_api('ContributionRecur', 'getsingle', $params);
		
		if($result['is_error'] <> 0 ){
			// print "<br>ERROR: issue calling ContributionRecur GetSingle API";
			// print_r ( $result );
			return;
		
		}else{
		   $recur_expected_contribution_count  = $result['installments'];
		     // print "<br>Expected Contributions for this recuring subscription: ".$recur_expected_contribution_count;
		   
		
		}


		$new_recur_status = "";
		//if( is_numeric( $recur_completed_contribution_count )) {
		if( is_numeric( $recur_completed_contribution_count ) && is_numeric( $recur_expected_contribution_count) ){
			$recur_completed_num  = intval( $recur_completed_contribution_count) ;
	 		$recur_expected_num = intval( $recur_expected_contribution_count );
	 		
	 		if(  $recur_expected_num <> 0 && $recur_completed_num == $recur_expected_num ){
	 			$new_recur_status = "1" ; // completed.
	 		}else if( $recur_completed_num > 0 ){
	 			$new_recur_status = "5" ; // In progress
	 		
	 		}
		
		}else if( is_numeric($recur_completed_contribution_count) ) {
			$recur_completed_num  = intval ( $recur_completed_contribution_count ) ;
			if( $recur_completed_num > 0 ){
	 			$new_recur_status = "5" ; // In progress
	 		
	 		}
		
		
		}
		if( strlen( $new_recur_status) > 0 ){
			$status_sql = " , contribution_status_id = ".$new_recur_status;
		}else{
			$status_sql = "";
		}	
	 
	 	$update_sql = "UPDATE civicrm_contribution_recur 
	 			SET modified_date = '".$trxn_receive_date."' ".$status_sql."
	 			 WHERE id = ".$crm_recur_id ; 
	 	 // print "<br><br>Update recur sql: <br>".$update_sql; 
	 	$dao  =  & CRM_Core_DAO::executeQuery($update_sql,   CRM_Core_DAO::$_nullArray ) ;
	 	$dao->free();
	 
	 
	 }
	    
	 function findFirstContributionInSubscription( $log_handle,  $crm_recur_id,  &$first_contrib_id,  &$first_contrib_status){
	 	
	 	// Find the 'pending' contribution record for this subscription. (Should only be one or zero) 
	 	$pending_status_id = "2"; 
	 	$completed_status_id = "1"; 
	 	$params = array(
			  'version' => 3,
			  'sequential' => 1,
			  'contribution_recur_id' => $crm_recur_id,
			  'contribution_status_id' => $pending_status_id,
			);
		$result = civicrm_api('Contribution', 'get', $params);
		if( $result['is_error'] <> 0 ){
			// print "<br>ERROR: issue calling Contribution Get API";
			// print_r ( $result );
		}else{
			fwrite(  $log_handle , '\n');
	   		 fwrite( $log_handle, "Inside FindFirst: For crm_recur_id: ".$crm_recur_id." first contrib array :") ; 
	  		foreach( $result as $key => $cur_tmp){
	  			fwrite( $log_handle, "\n".$key." : ".$cur_tmp);
	  			
	  			if($key == 'values' ){
	  				foreach( $cur_tmp as $key_j => $cur_j){
	  					foreach($cur_j as $key_k => $cur_k){
	  						fwrite( $log_handle, "\n".$key_k." : ".$cur_k);
	  					}
	  				}
	  			
	  			}	
	  		
	  		}
		  // print "<Br><br>for crm_recur_id: ".$crm_recur_id." first contrib:<br> ";
		  // print_r( $result  ); 
		    if( $result['count'] == "1" ){
		    	 $first_contrib_id = $result['id'] ; 
		    	 $first_contrib_status = $pending_status_id ; 
		    
		    }else if(  $result['count'] == "0" ){
		       //  print "<br><br>There is no pending contribution. So create so get the oldest contribution on this subscription: ".$crm_recur_id; 
		        
		        fwrite(  $log_handle , '\n');
	   		 fwrite( $log_handle, "There is no pending contribution. So create so get the oldest contribution on this subscription: ".$crm_recur_id) ; 
		        $params = array(
			  'version' => 3,
			  'sequential' => 1,
			  'contribution_recur_id' => $crm_recur_id,
			  'contribution_status_id' => $completed_status_id ,
			);
			$result = civicrm_api('Contribution', 'get', $params);
		
		       // print_r( $result ) ;
		        if($result['is_error'] <> 0 ){
		        	// print "<br>ERROR: issue calling Contribution Get API";
				// print_r ( $result );
				 fwrite(  $log_handle , "\n");
	   			 fwrite( $log_handle, "ERROR: issue calling Contribution Get API: \n") ; 
	   			 foreach($result as $key => $cur){
	  					fwrite( $log_handle, "\n".$key." : ".$cur);
	  			}
		        
		        }else{
		        	fwrite( $log_handle, "Call to contrib API was successful.");
		        	foreach($result as $key => $cur){
	  					fwrite( $log_handle, "\n".$key." : ".$cur);
	  			}
		           // print_r( $result ) ;
		        	if( $result['count'] <> 0){
		        		$tmp_contrib_id = $result['values'][0]['contribution_id'] ;
		        		$first_contrib_id = $tmp_contrib_id; 
		        	}
		        
		        
		        }
		        
		        
		        
		        $first_contrib_status = $completed_status_id ; 
		    }else{
		    	// print "<br><br>Error: More than one pending contribution found. This is invalid. ";
		    
		    }
			
			
		
		}
			
	 	
	 
	 }  
	    
	 function createContributionBasedOnExistingContribution($base_contrib_id, $trxn_id, $trxn_receive_date  ){
	 	
	 	$rtn_code = false; 
	 	
		
		// Get the first completed contribution ID from the subscription. Will use the details
		// to create the lastest contribution. Only difference should be date, and transaction ID. 
		
		$base_result = civicrm_api('Contribution', 'get', array( 'version' => 3, 'sequential' => 1, 'id' => $base_contrib_id ) );
		
		//print "<br>base contrib: ";
		//print_r($base_result ) ;
		
		if($base_result['is_error'] <> 0){
			// print "<br>Error calling contribution get API:<br>";
			// print_r($base_result ) ;
			
			 return $rtn_code; 
			
		}
		
		
		
		
		// need to get all the line items
		$lineitem_result = civicrm_api('LineItem', 'get', array( 'version' => 3, 'sequential' => 1, 
		'entity_table' => 'civicrm_contribution',   'entity_id' => $base_contrib_id ) );
		
		
		if($lineitem_result['is_error'] <> 0){
			// print "<br>Error calling LineItem get API:<br>";
			// print_r( $lineitem_result ) ;
			 return $rtn_code; 
		
		}
				
		$new_contrib_tmp = $base_result['values'][0]; 
		
		// Need to get custom data values from contribution.
		require_once( 'utils/util_custom_fields.php');
		$customFieldsUtils = new util_custom_fields();
		$tmp_custom_data_api_names = $customFieldsUtils->getContributionAPINames();
		
		
		
	//	print "<br>Contribution parms from Base:<br>";
	//	print_r( $new_contrib_tmp ) ;
		$source_tmp = 'automated payment'; 
		 $skipLineItem_parm = "1"; 
		 
		
		$new_contrib_params = array( 'version' => 3,
			  'sequential' => 1,
			  'financial_type_id' =>  $new_contrib_tmp['financial_type_id'],
			  'contact_id' => $new_contrib_tmp['contact_id'], 
			  'skipLineItem' => $skipLineItem_parm, 
			  'payment_instrument_id' => 1,
			  'total_amount' => $new_contrib_tmp['total_amount'] ,
			  'trxn_id' => $trxn_id ,
			  'contribution_recur_id' => $new_contrib_tmp['contribution_recur_id'] ,
			  'currency' => $new_contrib_tmp['currency'] , 
			  //'fee_amount' => $new_contrib_tmp['fee_amount'],
			  //'net_amount' => $new_contrib_tmp['net_amount'],
			  'contribution_campaign_id' => $new_contrib_tmp['contribution_campaign_id'], 
			  'non_deductible_amount' => $new_contrib_tmp['non_deductible_amount'],
			  'contribution_page_id' => $new_contrib_tmp['contribution_page_id'],
			  'source' => $source_tmp ,  
			  'honor_contact_id' => $new_contrib_tmp['honor_contact_id'],
			  'honor_type_id' => $new_contrib_tmp['honor_type_id'], 
			  'contribution_status_id' => 1,
			  'receive_date' => $trxn_receive_date  ) ; 
			  
			  // Deal with custom data values
			  if(is_array($tmp_custom_data_api_names ) ){
			  	foreach($tmp_custom_data_api_names as $cur_api_name){
			  		$new_contrib_params[$cur_api_name] = $new_contrib_tmp[$cur_api_name] ;
			  	
			  	}
			  
			  }
		
		if( strlen( $new_contrib_params['non_deductible_amount'])  == 0  ){
		
			unset( $new_contrib_params['non_deductible_amount'] );
		
		}
		
		 if( strlen($trxn_id ) == 0){
		 	print "<h2>Error: trxn id CANNOT be empty, will not create contribution.</h2>";
		 	print_r( $new_contrib_params ); 
		 	exit();
		 	
		 }	  
			  
		//$new_contrib_params['total_amount'] = $gateway_amount; 
			$new_contrib_result = civicrm_api('Contribution', 'create', $new_contrib_params ) ; 
			if($new_contrib_result['is_error'] <> 0 ){
					print "<br>Error calling Contribution Create API: <br>";
					print_r( $new_contrib_result); 
					return $rtn_code; 
			
			}
			
			//print "<hr><br>Called Contribution Create API: <br>";
			//print_r( $new_contrib_result); 
		
			$new_contrib_id = $new_contrib_result['id']; 
			//print "<br><br> new contrib id: ".$new_contrib_id; 
			// process each line item
			
			$all_line_items = $lineitem_result['values'];
			$line_item_count = $lineitem_result['count']; 
			
			foreach( $all_line_items as $original_line_item){
						//print "<hr><br><br>Original line item: ";
						//print_r( $original_line_item ); 
						
						
						// create line items:
						$params = array(
						  'version' => 3,
						  'sequential' => 1,
						  'entity_table' => 'civicrm_contribution',
						  'entity_id' => $new_contrib_id , 
						  'price_field_id' => $original_line_item['price_field_id'],
						  'label' => $original_line_item['label'],
						  'qty' => $original_line_item['qty'],
						  'unit_price' =>  $original_line_item['unit_price'],
						  'line_total' => $original_line_item['line_total'],
						  'participant_count' => $original_line_item['participant_count'],
						  'price_field_value_id' => $original_line_item['price_field_value_id'],
						  'financial_type_id' => $original_line_item['financial_type_id'],
						  'deductible_amount' => $original_line_item['deductible_amount'],
						  
						);
						
						//print "<br><br>New line item:<br> ";
						//print_r( $params ) ; 
						$li_result = civicrm_api('LineItem', 'create', $params);
						if($li_result['is_error'] <> 0 ){
							print "<br>Error calling Line Item API: <br>";
							print_r( $li_result); 
					
						}else{
							// print "<br>Called line item API: <br>";
							// print_r( $li_result); 
							// This is needed because of bug in line item API. 
							// 
							// print_r( $new_contrib_params ) ; 
							create_needed_line_item_db_records($li_result['id'] , $li_result['values'][0], $new_contrib_params );
							$rtn_code = true; 
						}
									
					
			}  // end of loop on each line item.
					
				  
			
			

		
		
		
				
			
	  return $rtn_code; 
	 
	 
	 }
	 