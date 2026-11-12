<?php
/*
 ****************************************************************
 Copyright (C) 2008-2020 Soft Ventures, Inc. All rights reserved.
 ****************************************************************
 * @package	Appointment Booking Pro - ABPro
 * @copyright	Copyright (C) 2008-2020 Soft Ventures, Inc. All rights reserved.
 * @license	GNU/GPL, see http://www.gnu.org/licenses/gpl-2.0.html
 *
 * ABPro is distributed WITHOUT ANY WARRANTY, or implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header must not be removed. Additional contributions/changes
 * may be added to this header as long as no information is deleted.
 *
 ************************************************************
 The latest version of ABPro is available to subscribers at:
 http://www.appointmentbookingpro.com/
 ************************************************************
*/


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<?php
	if(!isset($cart)){$cart = "no";};

	$sql = 'SELECT * FROM #__sv_apptpro3_authnet_settings;';
	try{
		$database->setQuery($sql);
		$authnet_settings = NULL;
		$authnet_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pay_procs_goto", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}

	if($authnet_settings->authnet_server == "Test"){
		$submit_url = "https://test.authorize.net/gateway/transact.dll";
	} else {
		$submit_url = "https://secure.authorize.net/gateway/transact.dll"; 
	}

	if($cart != "Yes"){		

		// to see what is passed to Authorize.net uncomment line below
		//$submit_url = "https://developer.authorize.net/param_dump.asp"; 
	
		$x_description = JText::_($res_detail->description).": ".$startdate." ".$starttime;
	
	
		require_once JPATH_SITE."/components/com_rsappt_pro3/anet_php_sdk/AuthorizeNet.php"; 
		$api_login_id = $authnet_settings->authnet_api_login_id;
		$transaction_key = $authnet_settings->authnet_transaction_key;
		$fp_timestamp = time();
		$fp_sequence = $request_id;
		$fingerprint = AuthorizeNetSIM_Form::getFingerprint($api_login_id,
		  $transaction_key, $payment_required, $fp_sequence, $fp_timestamp);
	
		if(strpos($name, " ")){
			$fullname = explode(" ", $name);
			$strFirstName = $fullname[0];
			$strLastName = $fullname[1]; 			
		}
	
		$fields = array(  
			'x_fp_sequence'=>$fp_sequence,  
			'x_invoice_num'=>$request_id,
			'x_fp_timestamp'=>$fp_timestamp,  
			'x_fp_hash'=>$fingerprint,  
			'x_description'=>$x_description,  
			'x_login'=>$api_login_id,  
			'x_show_form'=>"PAYMENT_FORM",
			'x_first_name'=>$strFirstName,  
			'x_last_name'=>$strLastName,  
			'x_amount'=>$payment_required,  
			'x_header_html_payment_form'=>$authnet_settings->authnet_header_text,  
			'x_footer_html_payment_form'=>$authnet_settings->authnet_footer_text,  
			'x_receipt_link_method'=>"cc",  
			'x_test_request'=>"false",
			'x_receipt_link_text'=>"Return to site", 
			'x_receipt_link_url'=>JURI::base()."index.php?option=com_rsappt_pro3&view=".$from_screen."&Itemid=".$frompage_item."&task=pp_return&req_id=".$request_id,
			'x_relay_response'=>"true",
			'x_relay_url'=>JURI::base()."index.php?option=com_rsappt_pro3&controller=admin&task=relay_resp&fromscreen=".$from_screen."&Itemid=".$frompage_item
		);  
		//print_r($fields);
		//exit;	
	
		$fields_string = "";
		echo "<html><head></head><body>";
		echo "<form name='myform' action='$submit_url' method='post'>";
		foreach ($fields as $key => $value) {
			print "<input type='hidden' name='".$key."' value=\"".$value."\">";
			$fields_string .= "$key=$value";
		}
		echo "</form>";
	
		echo "<script language='javascript' type='text/javascript'>";
		echo "document.myform.submit();";
		echo "</script>";
		echo "</body></html>";
			exit;	

	} else {
		include_once( JPATH_COMPONENT."/controllers/cart.php" );
		$mycartcontroller = new cartController;
		$session = JFactory::getSession();
		$sid = $session->getId();
		$msg_customer = $mycartcontroller->buildCartMessage($apptpro_config, null, "customer", $sid, "no");
		$session->set('confirmation_message',$msg_customer);
		$msg_cart_in_progress = $mycartcontroller->buildCartMessage($apptpro_config, null, "customer", $sid, "yes");
		$session->set('cart_in_progress_message',$msg_cart_in_progress);
	
		// to see what is passed to Authorize.net uncomment line below
		//$submit_url = "https://developer.authorize.net/param_dump.asp"; 
	
		//$x_description = JText::_($res_detail->description).": ".$startdate." ".$starttime;
		$x_description = JText::_(trim($apptpro_config->cart_paypal_item));
	
		require_once JPATH_SITE."/components/com_rsappt_pro3/anet_php_sdk/AuthorizeNet.php"; 
		$api_login_id = $authnet_settings->authnet_api_login_id;
		$transaction_key = $authnet_settings->authnet_transaction_key;
		$fp_timestamp = time();
		$fp_sequence = "cart|".$cart_row_ids;
		$fingerprint = AuthorizeNetSIM_Form::getFingerprint($api_login_id,
		  $transaction_key, $cart_total, $fp_sequence, $fp_timestamp);
	
		$fields = array(  
			'x_fp_sequence'=>$fp_sequence,  
			'x_invoice_num'=>"cart|".$cart_row_ids,
			'x_fp_timestamp'=>$fp_timestamp,  
			'x_fp_hash'=>$fingerprint,  
			'x_description'=>$x_description,  
			'x_login'=>$api_login_id,  
			'x_show_form'=>"PAYMENT_FORM",
			'x_amount'=>$cart_total,  
			'x_header_html_payment_form'=>$authnet_settings->authnet_header_text,  
			'x_footer_html_payment_form'=>$authnet_settings->authnet_footer_text,  
			'x_receipt_link_method'=>"cc",  
			'x_test_request'=>"false",		
			'x_receipt_link_text'=>"Return to site", 
			'x_receipt_link_url'=>JURI::base()."index.php?option=com_rsappt_pro3&view=".$frompage."&Itemid=".$frompage_item."&task=pp_return",
			'x_relay_response'=>"true",
			'x_relay_url'=>JURI::base()."index.php?option=com_rsappt_pro3&controller=admin&task=relay_resp&fromscreen=".$frompage."&Itemid=".$frompage_item
		);  
		//print_r($fields);
		//exit;	
		$fields_string = "";
		echo "<html><head></head><body>";
		echo "<form name='myform' action='$submit_url' method='post'>";
		foreach ($fields as $key => $value) {
			print "<input type='hidden' name='".$key."' value=\"".$value."\">";
			$fields_string .= "$key=$value";
		}
		echo "</form>";
	
		echo "<script language='javascript' type='text/javascript'>";
		echo "document.myform.submit();";
		echo "</script>";
		echo "</body></html>";
		exit;	
	
	}
	
?>