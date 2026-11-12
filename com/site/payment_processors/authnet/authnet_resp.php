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



defined( '_JEXEC' ) or die( 'Restricted access' );
	include_once( JPATH_SITE."/administrator/components/com_rsappt_pro3/sendmail_pro2.php" );
	include_once( JPATH_SITE."/administrator/components/com_rsappt_pro3/functions_pro2.php" );
	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "authnet_resp", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	$mailer = JFactory::getMailer();
	try{
		$mailer->setSender(array($apptpro_config->mailFROM,null));
	}
	catch (Exception $e){
		logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
		return false;		
	}
	if($apptpro_config->html_email == "Yes"){
		$mailer->IsHTML(true);
	}

	$x_response_code = trim($jinput->getString("x_response_code"));
	//$x_response_subcode = trim($jinput->getString("x_response_subcode"));
	$x_response_reason_code = trim($jinput->getString("x_response_reason_code"));
	$x_response_reason_text = trim($jinput->getString("x_response_reason_text"));
	$x_auth_code = trim($jinput->getString("x_auth_code"));
	$x_avs_code = trim($jinput->getString("x_avs_code"));
	$x_trans_id = trim($jinput->getString("x_trans_id"));
	$x_method = trim($jinput->getString("x_method"));
	$x_card_type = trim($jinput->getString("x_card_type"));
	$x_account_number = trim($jinput->getString("x_account_number"));
	$x_invoice_num = trim($jinput->getString("x_invoice_num"));
	$x_description = trim($jinput->getString("x_description"));
	$x_amount = trim($jinput->getString("x_amount"));
	$x_method = trim($jinput->getString("x_method"));
	$x_type = trim($jinput->getString("x_type"));
	$x_cust_id = trim($jinput->getString("x_cust_id"));
	$x_first_name = trim($jinput->getString("x_first_name"));
	$x_last_name = trim($jinput->getString("x_last_name"));
	$x_company = trim($jinput->getString("x_company"));
	$x_address = trim($jinput->getString("x_address"));
	$x_city = trim($jinput->getString("x_city"));
	$x_state = trim($jinput->getString("x_state"));
	$x_zip = trim($jinput->getString("x_zip"));
	$x_country = trim($jinput->getString("x_country"));
	$x_phone = trim($jinput->getString("x_phone"));
	$x_fax = trim($jinput->getString("x_fax"));
	$x_email = trim($jinput->getString("x_email"));
	$x_tax = trim($jinput->getString("x_tax"));
	$x_duty = trim($jinput->getString("x_duty"));
	$x_freight = trim($jinput->getString("x_freight"));
	$x_tax_exempt = trim($jinput->getString("x_tax_exempt"));
	$x_po_num = trim($jinput->getString("x_po_num"));
	$x_MD5_Hash = trim($jinput->getString("x_MD5_Hash"));
	$x_cavv_response = trim($jinput->getString("x_cavv_response"));
	$x_test_request = trim($jinput->getString("x_test_request"));
	//$x_subscription_id = trim($jinput->getString("x_subscription_id"));
	//$x_subscription_paynum = trim($jinput->getString("x_subscription_paynum"));
	//$x_cim_profile_id = trim($jinput->getString("x_cim_profile_id"));

	$cart_booking = false;
	$mycartcontroller = null;

    if ($x_response_code == "1")
    {
		//This transaction has been approved.	
		// Update bookings data start -------------------------------------------------------- 
		
		// We need to determine if this is a cart return of a single booking
		if(strpos($x_invoice_num, "cart|") === false){
			// single booking, non-cart

			// get request info
			//$sql = 'SELECT * FROM #__sv_apptpro3_requests WHERE id_requests = '.$x_invoice_num;
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

			// we need to set the booking to 'Accepted'
			$request_id = $x_invoice_num; 
			$sql = "select count(*) as requestCount from #__sv_apptpro3_requests where id_requests=".(int)$request_id;
			try{
				$rows = NULL;
				$database->setQuery($sql);
				$rows = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "authnet_resp", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}							
	
			if ($rows->requestCount == 0){
				// oh-oh no request by that number
				$errsql = "insert into #__sv_apptpro3_errorlog (description) values('No outstanding request number: ".$request_id."')";
				$database->setQuery($errsql);
				$database->execute();
			} else {								
				// found request, update it
				
				// first check to see if status = timeout indcating AuthNet Relay_Responce too slow and timeslot is no longer help for this x_invoice_numer
				$sql = "select request_status from #__sv_apptpro3_requests where id_requests=".(int)$request_id;
				$database->setQuery($sql);
				$status = $database -> loadResult();
				if($status == "timeout"){
					$mailer->addRecipient(explode(",",$apptpro_config->mailTO));
					$mailer->setSubject("AuthNet Relay_Responce return on timed-out booking!");
					$mailer->setBody("Booking 'timeout' before AuthNet Relay_Responce. This booking had been paid but NOT accepted in ABPro as the timeslot lock had been released by the timeout, requires admin action! Booking id:".$request_id);
					if($mailer->send() != true){
						logIt("Error sending email");
					}
					$mailer=null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
						return false;		
					}
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
					logIt("Booking timeout before AuthNet Relay_Responce, booking paid but NOT ACCEPTED, requires admin action!",$request_id);
					return;
				}
				
				$payment_adjustment = " payment_status='paid', booking_due=0";
				//logit("booking_due=".$res_request->booking_due);
				//logit("mc_gross=".$mc_gross);
				if(floatval($res_request->booking_due) > floatval($x_amount)){
					$payment_adjustment = " booking_due = booking_due - ".$x_amount." , booking_deposit = ".$x_amount." ";
				}
	
				if($apptpro_config->accept_when_paid == "Yes"){
					$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='AuthNet', txnid='".$x_trans_id."', request_status='accepted' where id_requests=".$request_id;
				} else {
					$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='AuthNet', txnid='".$x_trans_id."' where id_requests=".$request_id;
				}	
				try{					
					$database->setQuery($sql);
					$database->execute();

				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "authnet_resp", "", "");
					echo JText::_('RS1_SQL_ERROR');
	
					$message = "AUTHNET TRANSACTION ERROR: Error on request update for txnid=".$x_trans_id.",".$e->getMessage();
	
					$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
					$mailer->setSubject("AUTHNET TRANSACTION ERROR");
					$mailer->setBody($message);
					if($mailer->send() != true){
						logIt("Error sending email");
					}
					$mailer=null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
						return false;		
					}
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
					if($mailer->send() != true){
						logIt("Error sending email");
					}
					
					exit;
				}
	
				addToCalendar($request_id, $apptpro_config); // will only add if accepted
			}
			
		} else {
			// this is a cart transaction
			// remove 'cart|' from $custom
			$custom = str_replace("cart|", "", $x_invoice_num);
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
				logIt($e->getMessage(), "authnet_resp", "", "");
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
					"booking_deposit=".$booking_deposit.", payment_processor_used='AuthNet', txnid='".$x_trans_id.
					"', request_status='accepted', payment_status='".$payment_status."' ".
					"WHERE id_requests=".$cart_request->request_id;
					try{
						$database->setQuery($sql);
						$database->execute();
	
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "authnet_resp", "", "");
						echo JText::_('RS1_SQL_ERROR');

						$message = "AUTHNET TRANSACTION ERROR: Error on request update for txnid=".$x_trans_id.",".$e->getMessage();

						$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
						$mailer->setSubject("AUTHNET TRANSACTION ERROR");
						$mailer->setBody($message);
						if($mailer->send() != true){
							logIt("Error sending email");
						}
						$mailer=null;
						$mailer = JFactory::getMailer();
						try{
							$mailer->setSender(array($apptpro_config->mailFROM,null));
						}
						catch (Exception $e){
							logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
							return false;		
						}
						if($apptpro_config->html_email == "Yes"){
							$mailer->IsHTML(true);
						}
						if($mailer->send() != true){
							logIt("Error sending email");
						}											
						exit;
					}

					$msg_customer .= buildMessage($cart_request->request_id, "cart_msg_confirm", "No");
					$msg_admin .= buildMessage($cart_request->request_id, "cart_msg_confirm", "No");						
					$msg_customer .= "<br/>";
					$msg_admin .= "<br/>";


					addToCalendar($cart_request->request_id, $apptpro_config); // will only add if accepted									
				}
				$bookings_to_process = rtrim($bookings_to_process, ',');

			}
		}
				
		// Update bookings data end -------------------------------------------------------- 
						
		// Update transactions table start -------------------------------------------------
		
		$strQuery = "insert into #__sv_apptpro3_authnet_transactions(x_response_code,
			x_response_reason_code,	x_response_reason_text,
			x_auth_code, x_avs_code, x_trans_id, x_invoice_num,	x_description, x_amount,
			x_method, x_type, x_cust_id, x_first_name, x_last_name, x_company, x_address,
			x_city, x_state, x_zip,	x_country, x_phone,	x_fax, x_email,	x_tax, x_duty,
			x_freight, x_tax_exempt, x_po_num, x_MD5_Hash, x_cavv_response,	x_test_request)".
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
			"','".$database->escape($x_fax).
			"','".$database->escape($x_email).
			"','".$database->escape($x_tax).
			"','".$database->escape($x_duty).
			"','".$database->escape($x_freight).
			"','".$database->escape($x_tax_exempt).
			"','".$database->escape($x_po_num).
			"','".$database->escape($x_MD5_Hash).
			"','".$database->escape($x_cavv_response).
			"','".$database->escape($x_test_request).
			"')";
		try{	
			$database->setQuery($strQuery);
			$database->execute();

		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "authnet_resp", "", "");
			echo JText::_('RS1_SQL_ERROR');

			$message = "AUTHNET TRANSACTION ERROR: Error on insert into payment info table for txnid=".$x_trans_id.",".$e->getMessage();

			$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
			$mailer->setSubject("AUTHNET TRANSACTION ERROR");
			$mailer->setBody($message);
			if($mailer->send() != true){
				logIt("Error sending email");
			}
			$mailer=null;
			$mailer = JFactory::getMailer();
			try{
				$mailer->setSender(array($apptpro_config->mailFROM,null));
			}
			catch (Exception $e){
				logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
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
			// send confimration email to x_invoice_numer
			$message = buildMessage($x_invoice_num, "confirmation", "Yes");
			$message_admin = buildMessage($x_invoice_num, "confirmation_admin", "Yes");
			$subject = JText::_('RS1_CONFIRMATION_EMAIL_SUBJECT');
	
			if($res_request->email != ""){
				$mailer->addRecipient($res_request->email);
				$mailer->setSubject($subject);
				$mailer->setBody($message);
				if($mailer->send() != true){
					logIt("Error sending email");
				}
				$mailer=null;
				$mailer = JFactory::getMailer();
				try{
					$mailer->setSender(array($apptpro_config->mailFROM,null));
				}
				catch (Exception $e){
					logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
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
					logIt("Error sending email");
				}
				$mailer=null;
				$mailer = JFactory::getMailer();
				try{
					$mailer->setSender(array($apptpro_config->mailFROM,null));
				}
				catch (Exception $e){
					logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
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
					logIt("Error sending email");
				}
				$mailer=null;
				$mailer = JFactory::getMailer();
				try{
					$mailer->setSender(array($apptpro_config->mailFROM,null));
				}
				catch (Exception $e){
					logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
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
				logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
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
					logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
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
					logIt("Error on setting email FROM address: ".$e->getMessage(), "authnet_resp", "", "");
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
					logIt("Error clearing cart after ipn: ".$e->getMessage(), "authnet_resp", "", "");
					echo JText::_('RS1_SQL_ERROR');
					exit;
				}
		}
		// Confirmation email/sms end -----------------------------------------------------
								
    }
    else if ($x_response_code == 2)
    {
		//This transaction has been declined.
		// log and email admin
// moved to receipt view		
//		$errsql = "insert into #__sv_apptpro3_errorlog (description) values('Authorise.net: This transaction has been declined. ".x_invoice_num."')";
//		$database->setQuery($errsql);
//		$database->execute();
//		$mailer=null;
//		$mailer = JFactory::getMailer();
//		$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
//		$mailer->setSubject("This transaction has been declined");
//		$mailer->setBody("Authorise.net: This transaction has been declined. ".x_invoice_num);
//		if($mailer->send() != true){
//			logIt("Error sending email");
//		}
	}
    else if ($x_response_code == 3)
    {
		//There was an error processing this transaction.
		// log and email admin
		$errsql = "insert into #__sv_apptpro3_errorlog (description) values('Authorise.net: There was an error processing this transaction. ".x_invoice_num."')";
		$database->setQuery($errsql);
		$database->execute();
		$mailer=null;
		$mailer = JFactory::getMailer();
		$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
		$mailer->setSubject("There was an error processing this transaction");
		$mailer->setBody("Authorise.net: There was an error processing this transaction. ".x_invoice_num);
		if($mailer->send() != true){
			logIt("Error sending email");
		}
    }

	include_once( JPATH_SITE."/components/com_rsappt_pro3/payment_processors/authnet/authnet_receipt.php" );

?>
