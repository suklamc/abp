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

	$mailer = JFactory::getMailer();
	try{
		$mailer->setSender(array($apptpro_config->mailFROM,null));
	}
	catch (Exception $e){
		logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_aim_proc_pay", "", "");
		return false;		
	}
	if($apptpro_config->html_email == "Yes"){
		$mailer->IsHTML(true);
	}

	$x_response_code = $response->response_code;
	$x_response_reason_code = $response->response_reason_code;
	$x_response_reason_text = $response->response_reason_text;
	$x_auth_code = $response->authorization_code;
	$x_avs_code = $response->avs_response;
	$x_trans_id = $response->transaction_id;
	$x_method = $response->method;
	$x_card_type = $response->card_type;
	$x_account_number = $response->account_number;
	$x_invoice_num = $request_id;
	$x_description = $response->description;
	$x_amount = $response->amount;
	$x_type = $response->transaction_type;
	$x_cust_id = $response->customer_id;
	$x_first_name = $response->first_name;
	$x_last_name = $response->last_name;
	$x_company = $response->company;
	$x_address = $response->address;
	$x_city = $response->city;
	$x_state = $response->state;
	$x_zip = $response->zip_code;
	$x_country = $response->country;
	$x_phone = $response->phone;
	$x_email = $response->email_address;
	$x_tax = $response->tax;
	$x_duty = $response->duty;
	$x_tax_exempt = $response->tax_exempt;
	$x_po_num = $response->purchase_order_number;
	$x_cavv_response = $response->card_code_response;

	$cart_booking = false;
	$mycartcontroller = null;

	//This transaction has been approved.	
	// Update bookings data start -------------------------------------------------------- 
//	
//	// We need to determine if this is a cart return of a single booking
	if($cart != "Yes"){
		// single booking, non-cart
			
			$payment_adjustment = " payment_status='paid', booking_due=0";
			//logit("booking_due=".$res_request->booking_due);
			//logit("mc_gross=".$mc_gross);
			if(floatval($res_request->booking_due) > floatval($x_amount)){
				$payment_adjustment = " booking_due = booking_due - ".$x_amount." , booking_deposit = ".$x_amount." ";
			}

			if($apptpro_config->accept_when_paid == "Yes"){
				$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='AuthNetAIM', txnid='".$x_trans_id."', request_status='accepted' where id_requests=".$request_id;
			} else {
				$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='AuthNetAIM', txnid='".$x_trans_id."' where id_requests=".$request_id;
			}	
			try{					
				$database->setQuery($sql);
				$database->execute();

			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "authnet_aim_proc_payment", "", "");
				echo JText::_('RS1_SQL_ERROR');

				$message = "AUTHNET TRANSACTION ERROR: Error on request update for txnid=".$x_trans_id.",".$e->getMessage();

				$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
				$mailer->setSubject("AUTHNET TRANSACTION ERROR");
				$mailer->setBody($message);
				if($mailer->send() != true){
					logIt("Error sending email", "authnet_aim_proc_payment");
				}
				$mailer=null;
				$mailer = JFactory::getMailer();
				try{
					$mailer->setSender(array($apptpro_config->mailFROM,null));
				}
				catch (Exception $e){
					logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_aim_proc_pay", "", "");
					return false;		
				}
				if($apptpro_config->html_email == "Yes"){
					$mailer->IsHTML(true);
				}
				if($mailer->send() != true){
					logIt("Error sending email", "authnet_aim_proc_payment");
				}
				
				exit;
			}

			addToCalendar($request_id, $apptpro_config); // will only add if accepted
		
		
	} else {
		// this is a cart transaction
		$custom = $cart_row_ids;
		$x_invoice_num = "cart|".$cart_row_ids;
		// cart booking, need to process multiple bookings in a cart
		include_once( JPATH_COMPONENT."/controllers/cart.php" );
		$mycartcontroller = new cartController;
		$cart_booking = true;	
		$cart_total = 0;							
		// new status must be accepted as they have paid their money
		$update_status = "accepted";
		
		// Need request ids 
		// First get cart row ids from $custom passed through PayPal
		$cart_row_ids = str_replace("|", ",", $custom); // now we can use this as the IN clause								
		
		$database = JFactory::getDBO();
		$sql = "SELECT request_id, session_id, item_total FROM #__sv_apptpro3_cart ".
			" WHERE id_row_cart IN (".$database->escape($cart_row_ids).")";						
		try{
			$database->setQuery($sql);
			$cart_requests = NULL;
			$cart_requests = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "authnet_aim_proc_payment", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if(sv_count_($cart_requests) == 0 ){
			// oh-oh, cart has been emptied before we got here, big problem, log error
			logIt("Error, cart empty when processing ipn for x_trans_id ".$x_trans_id);
		} else {
			// get cart total
			$cart_total = $mycartcontroller->get_cart_total($cart_requests[0]->session_id);

			// for each booking we need to update request_status and payment_status
			// we will also build the confimratoin message as we g through each cart item

			$msg_customer = JText::_(clean_svkey($apptpro_config->cart_msg_header));
			$msg_admin = JText::_(clean_svkey($apptpro_config->cart_msg_header));
			$msg_customer .= "<br/>";
			$msg_admin .= "<br/>";
			$bookings_to_process = "";

			foreach($cart_requests as $cart_request){	
				$booking_total = 0;
				$booking_due = 0;
				$booking_deposit = 0;
				$payment_status = "paid";
				$bookings_to_process .= $cart_request->request_id.",";

				// determine if fully paid or only deposit, payment_due - cart item_total 
				$sql = "SELECT booking_total FROM #__sv_apptpro3_requests WHERE id_requests = ".$cart_request->request_id;
				try{
					$database->setQuery( $sql );
					$booking_total = $database->loadResult();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "", "", "");
					echo JText::_('RS1_SQL_ERROR');
					return false;
				}		
				if($booking_total == $cart_request->item_total){
					// paid in full
					$booking_due = 0;
					$booking_deposit = 0;											
				} else {
					// deposit only
					$booking_due = $booking_total - $cart_request->item_total;
					$booking_deposit = $cart_request->item_total;
					$payment_status = "pending";																						
				}
				$sql = "update #__sv_apptpro3_requests set booking_due=".$booking_due.", ".
				"booking_deposit=".$booking_deposit.", payment_processor_used='AuthNetAIM', txnid='".$x_trans_id.
				"', request_status='accepted', payment_status='".$payment_status."' ".
				"WHERE id_requests=".$cart_request->request_id;
				try{
					$database->setQuery($sql);
					$database->execute();

				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "authnet_aim_proc_payment", "", "");
					echo JText::_('RS1_SQL_ERROR');

					$message = "AUTHNET TRANSACTION ERROR: Error on request update for txnid=".$x_trans_id.",".$e->getMessage();

					$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
					$mailer->setSubject("AUTHNET TRANSACTION ERROR");
					$mailer->setBody($message);
					if($mailer->send() != true){
						logIt("Error sending email", "authnet_aim_proc_payment");
					}
					$mailer=null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_aim_proc_pay", "", "");
						return false;		
					}
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
					if($mailer->send() != true){
						logIt("Error sending email", "authnet_aim_proc_payment");
					}											
					exit;
				}

				$msg_customer .= buildMessage($cart_request->request_id, "cart_msg_confirm", "No");
				$msg_admin .= buildMessage($cart_request->request_id, "cart_msg_confirm", "No");						
				$msg_customer .= "<br/>";
				$msg_admin .= "<br/>";
		
				$session = JFactory::getSession();
				$session->set('confirmation_message', $msg_customer);


				addToCalendar($cart_request->request_id, $apptpro_config); // will only add if accepted									
			}
			$bookings_to_process = substr($bookings_to_process, 0, strlen($bookings_to_process)-1);
		}
	}
			
	// Update bookings data end -------------------------------------------------------- 
					
	// Update transactions table start -------------------------------------------------
	
	$sql = "insert into #__sv_apptpro3_authnet_aim_transactions(x_response_code,
		x_response_reason_code,	x_response_reason_text,
		x_auth_code, x_avs_code, x_trans_id, x_invoice_num,	x_description, x_amount,
		x_method, x_type, x_cust_id, x_first_name, x_last_name, x_company, x_address,
		x_city, x_state, x_zip,	x_country, x_phone, x_email,	x_tax, x_duty,
		x_tax_exempt, x_po_num, x_cavv_response)".
		"values (".
		"'".$database->escape($x_response_code).
		"','".$database->escape($x_response_reason_code).
		"','".$database->escape($x_response_reason_text).
		"','".$database->escape($x_auth_code).
		"','".$database->escape($x_avs_code).
		"','".$database->escape($x_trans_id).
		"','".$database->escape($x_invoice_num).
		"','".$database->escape($x_description).
		"','".$database->escape($x_amount).
		"','".$database->escape($x_method).
		"','".$database->escape($x_type).
		"','".$database->escape($x_cust_id).
		"','".$database->escape($x_first_name).
		"','".$database->escape($x_last_name).
		"','".$database->escape($x_company).
		"','".$database->escape($x_address).
		"','".$database->escape($x_city).
		"','".$database->escape($x_state).
		"','".$database->escape($x_zip).
		"','".$database->escape($x_country).
		"','".$database->escape($x_phone).
		"','".$database->escape($x_email).
		"','".$database->escape($x_tax).
		"','".$database->escape($x_duty).
		"','".$database->escape($x_tax_exempt).
		"','".$database->escape($x_po_num).
		"','".$database->escape($x_cavv_response).
		"')";

	try{	
		$database->setQuery($sql);
		$database->execute();

	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "authnet_aim_proc_payment", "", "");
		echo JText::_('RS1_SQL_ERROR');

		$message = "AUTHNET TRANSACTION ERROR: Error on insert into payment info table for txnid=".$x_trans_id.",".$e->getMessage();

		$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
		$mailer->setSubject("AUTHNET TRANSACTION ERROR");
		$mailer->setBody($message);
		if($mailer->send() != true){
			logIt("Error sending email", "authnet_aim_proc_payment");
		}
		$mailer=null;
		$mailer = JFactory::getMailer();
		try{
			$mailer->setSender(array($apptpro_config->mailFROM,null));
		}
		catch (Exception $e){
			logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_aim_proc_pay", "", "");
			return false;		
		}
		if($apptpro_config->html_email == "Yes"){
			$mailer->IsHTML(true);
		}
		exit;
	}

	// Update transactions table end -------------------------------------------------

	// Confirmation email/sms start -----------------------------------------------------

	if(!$cart_booking){
		// non-cart
		// get request info for sending confirmations
		$sql = 'SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.resource_email'. 
			" FROM #__sv_apptpro3_requests LEFT JOIN #__sv_apptpro3_resources ON ".
			" #__sv_apptpro3_requests.resource =	#__sv_apptpro3_resources.id_resources ".
			" WHERE #__sv_apptpro3_requests.id_requests=".(int)$x_invoice_num;	
		try{					
			$database->setQuery($sql);
			$res_request = NULL;
			$res_request = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "authnet_resp", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
		// send confimration email to x_invoice_numer
		$message = buildMessage($x_invoice_num, "confirmation", "Yes");
		$message_admin = buildMessage($x_invoice_num, "confirmation_admin", "Yes");
		$subject = JText::_('RS1_CONFIRMATION_EMAIL_SUBJECT');

		if($res_request->email != ""){
			$mailer->addRecipient($res_request->email);
			$mailer->setSubject($subject);
			$mailer->setBody($message);
			if($mailer->send() != true){
				logIt("Error sending email", "authnet_aim_proc_payment");
			}
			$mailer=null;
			$mailer = JFactory::getMailer();
			try{
				$mailer->setSender(array($apptpro_config->mailFROM,null));
			}
			catch (Exception $e){
				logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_aim_proc_pay", "", "");
				return false;		
			}
			if($apptpro_config->html_email == "Yes"){
				$mailer->IsHTML(true);
			}
		}
		
		if($res_request->resource_email != ""){
			$mailer->addRecipient(explode(",", $res_request->resource_email));
			$mailer->setSubject($subject);
			$mailer->setBody($message_admin);
			if($mailer->send() != true){
				logIt("Error sending email", "authnet_aim_proc_payment");
			}
			$mailer=null;
			$mailer = JFactory::getMailer();
			try{
				$mailer->setSender(array($apptpro_config->mailFROM,null));
			}
			catch (Exception $e){
				logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_aim_proc_pay", "", "");
				return false;		
			}
			if($apptpro_config->html_email == "Yes"){
				$mailer->IsHTML(true);
			}
		}
		
		if($apptpro_config->mailTO != ""){
			$jv_to = $apptpro_config->mailTO;
			$mailer->addRecipient(explode(",", $jv_to));
			$mailer->setSubject($subject);
			$mailer->setBody($message_admin);
			if($mailer->send() != true){
				logIt("Error sending email", "authnet_aim_proc_payment");
			}
			$mailer=null;
			$mailer = JFactory::getMailer();
			try{
				$mailer->setSender(array($apptpro_config->mailFROM,null));
			}
			catch (Exception $e){
				logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_aim_proc_pay", "", "");
				return false;		
			}
			if($apptpro_config->html_email == "Yes"){
				$mailer->IsHTML(true);
			}
		}
		
		if($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes"){
			// SMS to resource
			$config = JFactory::getConfig();
			$tzoffset = $config->get('config.offset');      
			$offsetdate = JFactory::getDate();
			$offsetdate->setOffset($tzoffset);
			$reminder_log_time_format = "Y-m-d H:i:s";
			$returnCode = "";
			sv_sendSMS($res_request->id_requests, "confirmation", $returnCode, $toResource="Yes");			
			logReminder("New booking (authorize.net): ".$returnCode, $res_request->id_requests, 0, "", $offsetdate->format($reminder_log_time_format));
		}
	} else {
		// cart confirmation message
		$msg_customer .= JText::_(clean_svkey($apptpro_config->cart_msg_footer));
		// swap in cart total is token is found
		$msg_customer = str_replace("[cart_total]", $cart_total, $msg_customer);
		$msg_admin .= JText::_(clean_svkey($apptpro_config->cart_msg_footer));
		$msg_admin = str_replace("[cart_total]", $cart_total, $msg_admin);
		
		// dev only
		//ini_set ( "SMTP", "shawmail.cg.shawcable.net" ); 
		
		// send confirmation emails
		$mailer = JFactory::getMailer();
		try{
			$mailer->setSender(array($apptpro_config->mailFROM,null));
		}
		catch (Exception $e){
			logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_aim_proc_pay", "", "");
			return false;		
		}
		
		if($apptpro_config->html_email != "Yes"){
			$msg_customer = str_replace("<br>", "\r\n", $msg_customer);
			$msg_admin = str_replace("<br>", "\r\n", $msg_admin);
		}

		// email to customer
		// The customer could change the email for each booking before
		// adding to the cart. 
		$cart_email_addresses = $mycartcontroller->get_cart_email("customer", $bookings_to_process);
			
		if(sv_count_($cart_email_addresses)>0){
			foreach($cart_email_addresses as $cart_email_address){
				$mailer->addRecipient($cart_email_address->email);
			}
			if($apptpro_config->html_email == "Yes"){
				$mailer->IsHTML(true);
			}

			$mailer->setSubject(JText::_($apptpro_config->mailSubject));
			$mailer->setBody($msg_customer);
			if($mailer->send() != true){
				logIt("Error sending email: ".$mailer->ErrorInfo);
			}
			// reset for next
			$mailer = null;
			$mailer = JFactory::getMailer();
			try{
				$mailer->setSender(array($apptpro_config->mailFROM,null));
			}
			catch (Exception $e){
				logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_aim_proc_pay", "", "");
				return false;		
			}
		}
		
		// email to admin
		if($apptpro_config->mailTO != ""){
			$to = $apptpro_config->mailTO;

			if($apptpro_config->html_email == "Yes"){
				$mailer->IsHTML(true);
			}

			$mailer->addRecipient(explode(",", $to));
			$mailer->setSubject(JText::_($apptpro_config->mailSubject));
			$mailer->setBody($msg_admin);
			if($mailer->send() != true){
				logIt("Error sending email: ".$mailer->ErrorInfo);
			}

			// reset for next
			$mailer = null;
			$mailer = JFactory::getMailer();
			try{
				$mailer->setSender(array($apptpro_config->mailFROM,null));
			}
			catch (Exception $e){
				logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_aim_proc_pay", "", "");
				return false;		
			}
		}
		
		// email to resource
		// each resource can have diffeent email and the cart can have multiple resoucres
		$cart_resource_addresses = $mycartcontroller->get_cart_email("resource", $bookings_to_process);
		if(sv_count_($cart_resource_addresses)>0){
			$recip_count = 0;
			foreach($cart_resource_addresses as $cart_resource_address){
				// a single resource can have multiple email notification addresses specified.
				if($cart_resource_address->resource_email != ""){
					$recip_count ++;
				}
				$mailer->addRecipient(explode(",", $cart_resource_address->resource_email));
			}

			if($apptpro_config->html_email == "Yes"){
				$mailer->IsHTML(true);
			}

			$mailer->setSubject(JText::_($apptpro_config->mailSubject));
			$mailer->setBody($msg_admin);
			if($recip_count > 0){ // is no cart items had a resource email to, don't send
				if($mailer->send() != true){
					logIt("Error sending email: ".$mailer->ErrorInfo);
				}
			}
		}

		// clear cart
		$sql = "DELETE FROM #__sv_apptpro3_cart WHERE request_id IN(".$bookings_to_process.")";
		try{
			$database->setQuery($sql);
			$database->execute();
			} catch (RuntimeException $e) {
				logIt("Error clearing cart after ipn: ".$e->getMessage(), "authnet_aim_proc_payment", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}
		$cancel_code = "cart";

	}
	// Confirmation email/sms end -----------------------------------------------------
							
	$next_view="show_confirmation&cc=".$cancel_code;
	$jinput = JFactory::getApplication()->input;

	$frompage = $jinput->getString('frompage');
	$frompage_item = $jinput->getString('frompage_item');
	$config = JFactory::getConfig();
	$seo = $config->get( 'sef' );
	$tmpl = $jinput->getString('tmpl', "");
	if($frompage_item == ""){
		// from popup caller, no item id
		$frompage_item = 1; // need a place holder for router parsing
	}
	if($seo == "1"){		
		$url = JRoute::_( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&task='.$next_view.'&req_id='.$last_id->last_id.($tmpl!=""?"&tmpl=".$tmpl:""));
	} else {
		$url = 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item.'&task='.$next_view.'&req_id='.$last_id->last_id.($tmpl!=""?"&tmpl=".$tmpl:"");
	}
	
	$this->setRedirect($url);


?>