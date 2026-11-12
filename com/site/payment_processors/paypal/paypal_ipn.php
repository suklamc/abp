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
	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
	include_once( JPATH_SITE."/components/com_rsappt_pro3/payment_processors/paypal/PaypalIPN.php" );
	$jinput = JFactory::getApplication()->input;

	// dev only
	//ini_set ( "SMTP", "shawmail.cg.shawcable.net" ); 

	// PayPal IPN requires SSL
//logIt("Starting new IPN", "pp_ipn", "", "");

	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_paypal_settings;';
	try{
		$database->setQuery($sql);
		$paypal_settings = NULL;
		$paypal_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pay_procs_goto", "", "");
		echo JText::_('RS1_SQL_ERROR');
	}
	
	$cart_booking = false;
	
	// get config stuff
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pp_ipn", "", "");
		echo JText::_('RS1_SQL_ERROR');
	}		

	$mailer = JFactory::getMailer();
	try{
		$mailer->setSender(array($apptpro_config->mailFROM,null));
	}
	catch (Exception $e){
		logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
		return false;		
	}
	if($apptpro_config->html_email == "Yes"){
		$mailer->IsHTML(true);
	}
	
	
	$ipn = new PaypalIPN();

	if($paypal_settings->paypal_use_sandbox == "Yes"){
		$ipn->useSandbox();
	}

	$ipn->usePHPCerts();

	$verified = $ipn->verifyIPN();
	if ($verified) {
		/*
		 * Process IPN
		 */
//logIt("IPN Verified", "pp_ipn", "", "");

		// assign posted variables to local variables
		$item_name = $jinput->getString('item_name');
		$business = $jinput->getString('business');
		$item_number = $jinput->getString('item_number');
		$payment_status = $jinput->getString('payment_status');
		$mc_gross = $jinput->getString('mc_gross');
		$payment_currency = $jinput->getString('mc_currency');
		$txn_id = $jinput->getString('txn_id');
		$receiver_email = $jinput->getString('receiver_email');
		$receiver_id = $jinput->getString('receiver_id');
		$quantity = $jinput->getString('quantity');
		$num_cart_items = $jinput->getString('num_cart_items');
		$payment_date = $jinput->getString('payment_date');
		$first_name = $jinput->getString('first_name');
		$last_name = $jinput->getString('last_name');
		$payment_type = $jinput->getString('payment_type');
		$payment_status = $jinput->getString('payment_status');
		$payment_gross = $jinput->getString('payment_gross');
		$payment_fee = $jinput->getString('payment_fee');
		$settle_amount = $jinput->getString('settle_amount');
		$memo = $jinput->getString('memo');
		$payer_email = $jinput->getString('payer_email');
		$txn_type = $jinput->getString('txn_type');
		$payer_status = $jinput->getString('payer_status');
		$address_street = $jinput->getString('address_street');
		$address_city = $jinput->getString('address_city');
		$address_state = $jinput->getString('address_state');
		$address_zip = $jinput->getString('address_zip');
		$address_country = $jinput->getString('address_country');
		$address_status = $jinput->getString('address_status');
		$item_number = $jinput->getString('item_number');
		$tax = $jinput->getString('tax');
		$option_name1 = $jinput->getString('option_name1');
		$option_selection1 = $jinput->getString('option_selection1');;
		$option_name2 = $jinput->getString('option_name2');
		$option_selection2 = $jinput->getString('option_selection2');
		$for_auction = $jinput->getString('for_auction');
		$invoice = $jinput->getString('invoice');
		$custom = $jinput->getString('custom');
		$notify_version =$jinput->getString('notify_version');
		$verify_sign = $jinput->getString('verify_sign');
		$payer_business_name = $jinput->getString('payer_business_name');
		$payer_id =$jinput->getString('payer_id');
		$mc_currency = $jinput->getString('mc_currency');
		$mc_fee = $jinput->getString('mc_fee');
		$exchange_rate = $jinput->getString('exchange_rate');
		$settle_currency  = $jinput->getString('settle_currency');
		$parent_txn_id  = $jinput->getString('parent_txn_id');
		$pending_reason = $jinput->getString('pending_reason');
		$reason_code = $jinput->getString('reason_code');


		$database = JFactory::getDBO();
		
		$fecha = date("m")."/".date("d")."/".date("Y");
		$fecha = date("Y").date("m").date("d");
		
		//check if transaction ID has been processed before
		$sql = "select count(*) as txnCount from #__sv_apptpro3_paypal_transactions where txnid='".$database->escape($txn_id)."'";
		$rows = NULL;
		try{
			$database->setQuery($sql);
			$rows = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "pp_ipn", "", "");
			echo JText::_('RS1_SQL_ERROR');
		}	

		if ($rows->txnCount == 0){
			// no dupe carry on..

			// goodie we got paid
			if($payment_status == "Completed"){

				// Update bookings data start -------------------------------------------------------- 
				// We need to determine if this is a cart return of a single booking
				if(strpos($custom, "cart|") === false){
					// single booking, non-cart
					// get request info
					$database = JFactory::getDBO();
					//$sql = 'SELECT * FROM #__sv_apptpro3_requests WHERE id_requests = '.$custom;
					$sql = 'SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.resource_email'. 
						" FROM #__sv_apptpro3_requests LEFT JOIN #__sv_apptpro3_resources ON ".
						" #__sv_apptpro3_requests.resource =	#__sv_apptpro3_resources.id_resources ".
						" WHERE #__sv_apptpro3_requests.id_requests=".(int)$custom;						
					try{
						$database->setQuery($sql);
						$res_request = NULL;
						$res_request = $database -> loadObject();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "pp_ipn", "", "");
						echo JText::_('RS1_SQL_ERROR');
					}	

					// we need to set the appting to 'Accepted'
					$request_id = $custom; // passed to PayPal, now we get it back
					$sql = "select count(*) as requestCount from #__sv_apptpro3_requests where id_requests=".(int)$request_id;
					$rows = NULL;
					try{
						$database->setQuery($sql);
						$rows = $database -> loadObject();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "pp_ipn", "", "");
						echo JText::_('RS1_SQL_ERROR');
					}	

					if ($rows->requestCount == 0){
						// oh-oh no request by that number
						logIt("No outstanding request number: ".$request_id, "pp_ipn", "", "");
					} else {								
						// found request, update it
						
						// first check to see if status = timeout indcating IPN too slow and timeslot is no longer help for this customer
						$sql = "select request_status from #__sv_apptpro3_requests where id_requests=".(int)$request_id;
						try{
							$database->setQuery($sql);
							$status = $database -> loadResult();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "pp_ipn", "", "");
							echo JText::_('RS1_SQL_ERROR');
						}	
						if($status == "timeout"){
							try{
								$mailer->addRecipient(explode(",",$apptpro_config->mailTO));
							}
							catch (Exception $e){
								logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_front_desk", "", "");
								return false;		
							}
							$mailer->setSubject("IPN return on timed-out booking!");
							$mailer->setBody("Booking 'timeout' before IPN. This booking had been paid but NOT accepted in ABPro as the timeslot lock had been released by the timeout, requires admin action! Booking id:".$request_id);
							if($mailer->send() != true){
								logIt("Error sending email");
							}
							$mailer=null;
								$mailer = JFactory::getMailer();
							try{
								$mailer->setSender(array($apptpro_config->mailFROM,null));
							}
							catch (Exception $e){
								logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
								return false;		
							}
							if($apptpro_config->html_email == "Yes"){

								$mailer->IsHTML(true);
							}
							logIt("Booking timeout before IPN, booking paid but NOT ACCEPTED, requires admin action!",$request_id);
							return;
						}
						
						$payment_adjustment = " payment_status='paid', booking_due=0";
//		logIt("booking_due=".$res_request->booking_due, "pp_ipn", "", "");
//		logIt("booking_total=".$res_request->booking_total, "pp_ipn", "", "");
//		logIt("mc_gross=".$mc_gross, "pp_ipn", "", "");
						if(floatval($res_request->booking_total) > floatval($mc_gross)){
							$payment_adjustment = " booking_due = booking_total - ".$mc_gross." , booking_deposit = ".$mc_gross." ";
						}
						
						if($apptpro_config->accept_when_paid == "Yes"){
							$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='PayPal', txnid='".$txn_id."', request_status='accepted' where id_requests=".$request_id;
						} else {
							$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='PayPal', txnid='".$txn_id."' where id_requests=".$request_id;
						}		

						try{				
							$database->setQuery($sql);
							$database->execute();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "pp_ipn", "", "");
							echo JText::_('RS1_SQL_ERROR');

							$message = "PAYPAL TRANSACTION ERROR: Error on request update for txnid=".$txn_id.",".$database -> stderr();
							$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
							$mailer->setSubject("PAYPAL TRANSACTION ERROR");
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
								logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
								return false;		
							}
							if($apptpro_config->html_email == "Yes"){
								$mailer->IsHTML(true);
							}
							if($mailer->send() != true){
								logIt("Error sending email");
							}										
						}

						addToCalendar($request_id, $apptpro_config); // will only add if accepted
					}								
				} else {
					// remove 'cart|' from $custom
					$custom = str_replace("cart|", "", $custom);
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
						logIt($e->getMessage(), "pp_ipn", "", "");
						echo JText::_('RS1_SQL_ERROR');
					}	
					if(count($cart_requests) == 0 ){
						// oh-oh, cart has been emptied before we got here, big problem, log error
						logIt("Error, cart empty when processing ipn for txnid ".$txn_id);
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
							$abpro_payment_status = "paid";
							$bookings_to_process .= $cart_request->request_id.",";

							// determine if fully paid or only deposit, payment_due - cart item_total 
							$sql = "SELECT booking_total FROM #__sv_apptpro3_requests WHERE id_requests = ".$cart_request->request_id;
							try{
								$database->setQuery( $sql );
								$booking_total = $database->loadResult();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "pp_ipn", "", "");
								echo JText::_('RS1_SQL_ERROR');
							}	
							if($booking_total == $cart_request->item_total){
								// paid in full
								$booking_due = 0;
								$booking_deposit = 0;											
							} else {
								// deposit only
								$booking_due = $booking_total - $cart_request->item_total;
								$booking_deposit = $cart_request->item_total;
								$abpro_payment_status = "pending";																						
							}
							$sql = "update #__sv_apptpro3_requests set booking_due=".$booking_due.", ".
							"booking_deposit=".$booking_deposit.", payment_processor_used='PayPal', txnid='".$txn_id.
							"', request_status='accepted', payment_status='".$abpro_payment_status."' ".
							"WHERE id_requests=".$cart_request->request_id;

							try{
								$database->setQuery($sql);								
								$database->execute();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "pp_ipn", "", "");
								echo JText::_('RS1_SQL_ERROR');

								$message = "PAYPAL TRANSACTION ERROR: Error on request update for txnid=".$txn_id.",".$database -> stderr();

								$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
								$mailer->setSubject("PAYPAL TRANSACTION ERROR");
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
									logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
									return false;		
								}
								if($apptpro_config->html_email == "Yes"){
									$mailer->IsHTML(true);
								}
								if($mailer->send() != true){
									logIt("Error sending email");
								}											
							}

							$msg_customer .= buildMessage($cart_request->request_id, "cart_msg_confirm", "No");
							$msg_admin .= buildMessage($cart_request->request_id, "cart_msg_confirm", "No");						
							$msg_customer .= "<br/>";
							$msg_admin .= "<br/>";
							
							addToCalendar($cart_request->request_id, $apptpro_config); // will only add if accepted									
						}									
					}
				}													
				// Update bookings data end -------------------------------------------------------- 
			
				// Update transactions table start -------------------------------------------------
				$strQuery = "insert into #__sv_apptpro3_paypal_transactions(paymentstatus,buyer_email,firstname,lastname,street,city,".
					"state,zipcode,country,mc_gross,mc_fee,itemnumber,itemname,os0,on0,os1,on1,quantity,custom,memo,paymenttype,".
					"paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) ".
					"values (".
					"'".$database->escape($payment_status).
					"','".$database->escape($payer_email).
					"','".$database->escape($first_name).
					"','".$database->escape($last_name).
					"','".$database->escape($address_street).
					"','".$database->escape($address_city).
					"','".$database->escape($address_state).
					"','".$database->escape($address_zip).
					"','".$database->escape($address_country).
					"','".$database->escape($mc_gross).
					"','".$database->escape($mc_fee).
					"','".$database->escape($item_number).
					"','".$database->escape($item_name).
					"','".$database->escape($option_name1).
					"','".$database->escape($option_selection1).
					"','".$database->escape($option_name2).
					"','".$database->escape($option_selection2).
					"','".$database->escape($quantity).
					"','".($cart_booking?'cart':$database->escape($custom)).
					"','".$database->escape($memo).
					"','".$database->escape($payment_type).
					"','".$database->escape($payment_date).
					"','".$database->escape($txn_id).
					"','".$database->escape($pending_reason).
					"','".$database->escape($reason_code).
					"','".$database->escape($tax).
					"','".$fecha."')";
				try{	
					$database->setQuery($strQuery);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "pp_ipn", "", "");
					echo JText::_('RS1_SQL_ERROR');

					$message = "PAYPAL TRANSACTION ERROR: Error on insert into payment info table for txnid=".$txn_id.",".$database -> stderr();

					$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
					$mailer->setSubject("PAYPAL TRANSACTION ERROR");
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
						logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
						return false;		
					}
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}								
				}
				// Update transactions table end -------------------------------------------------
			
				// Confirmation emails start -----------------------------------------------------

				// Confirmation emails are different with cart as there are multiple bookings in one cart
				
				if(!$cart_booking){
					// non-cart
					$array = array($request_id);
					$ics = buildICSfile($array);
			
					$sql = 'SELECT * FROM #__sv_apptpro3_mail WHERE id_mail = '.($res_request->mail_id ==1 ||$res_request->mail_id == null?"1":$res_request->mail_id);
					try{
						$database->setQuery($sql);
						$messages_to_use = $database -> loadObject();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "be_sendmail", "", "");
						echo JText::_('RS1_SQL_ERROR').$e->getMessage();
						exit;
					}
					
					// send confirmation email to customer
					//$message = buildMessage($custom, "confirmation", "Yes");
					$temp = buildMessage($custom, "confirmation", "Yes", "", "", "Yes");
					$message .= $temp[0];
					if($temp[1] != ""){
						$message_attachment = JPATH_BASE.$temp[1];
					}				
					$message_admin = buildMessage($custom, "confirmation_admin", "Yes");
					$subject = JText::_('RS1_PAYPAL_CONFIRMATION_EMAIL_SUBJECT');

					if($messages_to_use->attach_ics_customer == "Yes"){
						$mailer->AddStringAttachment($ics, "appointment_".strval($request_id).".ics");
					}

					if($message_attachment != ""){
						$mailer->addAttachment($message_attachment);
					}

					if($res_request->email != ""){
						$mailer->addRecipient(explode(",", $res_request->email));
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
							logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
							return false;		
						}
						if($apptpro_config->html_email == "Yes"){
							$mailer->IsHTML(true);
						}
					}

					// send confirmation email to resource
					if($messages_to_use->attach_ics_resource == "Yes"){
						$mailer->AddStringAttachment($ics, "appointment_".strval($request_id).".ics");
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
							logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
							return false;		
						}
						if($apptpro_config->html_email == "Yes"){
							$mailer->IsHTML(true);
						}
					}
					
					// send confirmation email to admin
					if($messages_to_use->attach_ics_admin == "Yes"){
						$mailer->AddStringAttachment($ics, "appointment_".strval($request_id).".ics");
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
							logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
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
						logReminder("New booking (ipn): ".$returnCode, $res_request->id_requests, 0, "", $offsetdate->format($reminder_log_time_format));
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
						logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
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
						
					if(count($cart_email_addresses)>0){
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
							logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
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
							logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_procs_goto", "", "");
							return false;		
						}
					}
					
					// email to resource
					// each resource can have diffeent email and the cart can have multiple resoucres
					$cart_resource_addresses = $mycartcontroller->get_cart_email("resource", $bookings_to_process);
					if(count($cart_resource_addresses)>0){
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
						logIt($e->getMessage(), "pp_ipn", "", "");
						echo JText::_('RS1_SQL_ERROR');
					} 
				}

				// Confirmation emails end -----------------------------------------------------

			} else {
				// payment_status not complete??
				$sql = "insert into #__sv_apptpro3_errorlog (description) values('Payment Status, not `completed`, payment_status=".$payment_status.", txnid=".$txn_id.", request=".$custom."')";
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "pp_ipn", "", "");
					echo JText::_('RS1_SQL_ERROR');
				} 
		
				// send an email
				$message = "Payment Status, not `Completed`, payment_status=".$payment_status.", txnid=".$txn_id.", request=".$custom;
				try{
					$mailer->addRecipient(explode(",",$apptpro_config->mailTO));
				}
				catch (Exception $e){
					logIt("Error on setting email FROM address: ".$e->getMessage(), "paypal_ipn", "", "");
					return false;		
				}
				$mailer->setSubject("PAYMENT STATUS NOT COMPLETE");
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
					logIt("Error on setting email FROM address: ".$e->getMessage(), "paypal_ipn", "", "");
					return false;		
				}
				if($apptpro_config->html_email == "Yes"){
					$mailer->IsHTML(true);
				}
				
			}
		}
				
	}
	
	// Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
	header("HTTP/1.1 200 OK");	
	
	
?>
