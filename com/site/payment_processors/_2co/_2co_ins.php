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

 2CheckOut INS processor, well.. not really using INS, just return URL
 
 */



defined( '_JEXEC' ) or die( 'Restricted access' );
	include_once( JPATH_SITE."/administrator/components/com_rsappt_pro3/sendmail_pro2.php" );
	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

	// dev only
	//ini_set ( "SMTP", "shawmail.cg.shawcable.net" ); 

	//print_r($_REQUEST);
	//exit;

echo 'ok';
	//logIt("Start INS");
	$cart_booking = false;

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "2co ins", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	$mailer = JFactory::getMailer();
	try{
		$mailer->setSender(array($apptpro_config->mailFROM,null));
	}
	catch (Exception $e){
		logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
		return false;		
	}
	if($apptpro_config->html_email == "Yes"){
		$mailer->IsHTML(true);
	}

	if($jinput->getString('credit_card_processed') != "Y"){
		logIt("Error encountered - ".$jinput->getString('merchant_order_id'));
		exit;
	}
	
	// assign posted variables to local variables
	$merchant_order_id = $jinput->getString('merchant_order_id'); 
	$order_number = $jinput->getString('order_number');
	$invoice_id = $jinput->getString('invoice_id');
	$li_1_type = $jinput->getString('li_1_type');
	$li_1_name = $jinput->getString('li_1_name');
	$li_1_description = $jinput->getString('li_1_description'); 
	$li_1_price = $jinput->getString('li_1_price');
	$li_1_quantity = $jinput->getString('li_1_quantity');
	$total = $jinput->getString('total');
	$first_name = $jinput->getString('first_name');
	$last_name = $jinput->getString('last_name');
	$phone = $jinput->getString('phone');
	$email = $jinput->getString('email');
	$street_address = $jinput->getString('street_address'); 
	$street_address2 = $jinput->getString('street_address2'); 
	$city = $jinput->getString('city');
	$state = $jinput->getString('state');
	$country = $jinput->getString('country');
	$zip = $jinput->getString('zip');
	$ip_country = $jinput->getString('ip_country'); 
	$lang = $jinput->getString('lang');
	$pay_method = $jinput->getString('pay_method');
	$card_holder_name = $jinput->getString('card_holder_name');
	$credit_card_processed = $jinput->getString('credit_card_processed'); 
	$demo = $jinput->getString('demo');
	$from_screen = $jinput->getString('frompage');
	$from_screen_itemid= $jinput->getString('fromitemid');
	



	// Update bookings data start -------------------------------------------------------- 

	// We need to determine if this is a cart return of a single booking
	if(strpos($merchant_order_id, "cart|") === false){
		// single booking, non-cart
		// we need to set the appting to 'Accepted'
		$request_id = $merchant_order_id; // passed to 2CO, now we get it back
		$sql = "select count(*) as requestCount from #__sv_apptpro3_requests where id_requests=".(int)$request_id;
		try{
			$rows = NULL;
			$database->setQuery($sql);
			$rows = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "2co ins", "", "");
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
			
			// first check to see if status = timeout indcating IPN too slow and timeslot is no longer help for this customer
			$sql = "select request_status from #__sv_apptpro3_requests where id_requests=".(int)$request_id;
			$database->setQuery($sql);
			$status = $database -> loadResult();
			if($status == "timeout"){
				$mailer->addRecipient(explode(",",$apptpro_config->mailTO));
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
					logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
					return false;		
				}
				if($apptpro_config->html_email == "Yes"){
					$mailer->IsHTML(true);
				}
				logIt("Booking timeout before IPN, booking paid but NOT ACCEPTED, requires admin action!",$request_id);
				return;
			}
			
			$payment_adjustment = " payment_status='paid', booking_due=0";
			//logit("booking_due=".$res_request->booking_due);
			//logit("mc_gross=".$mc_gross);
			if(floatval($res_request->booking_due) > floatval($total)){
				$payment_adjustment = " booking_due = booking_due - ".$total." , booking_deposit = ".$total." ";
			}
			
			if($apptpro_config->accept_when_paid == "Yes"){
				$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='2CO', txnid='".$order_number."', request_status='accepted' where id_requests=".$request_id;
			} else {
				$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='2CO', txnid='".$order_number."' where id_requests=".$request_id;
			}	
			try{					
				$database->setQuery($sql);
				$database->execute();
	
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "ins", "", "");
				echo JText::_('RS1_SQL_ERROR');
	
				$message = "2CO TRANSACTION ERROR: Error on request update for order_number=".$order_number.",".$e->getMessage();
	
				$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
				$mailer->setSubject("PAYMENT TRANSACTION ERROR");
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
					logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
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
		// remove 'cart|' from $custom
		$custom = str_replace("cart|", "", $merchant_order_id);
		$merchant_order_id = "cart";
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
			logIt($e->getMessage(), "2co_ins", "", "");
			echo JText::_('RS1_SQL_ERROR');
		}	
		if(sv_count_($cart_requests) == 0 ){
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
				$payment_status = "paid";
				$bookings_to_process .= $cart_request->request_id.",";

				// determine if fully paid or only deposit, payment_due - cart item_total 
				$sql = "SELECT booking_total FROM #__sv_apptpro3_requests WHERE id_requests = ".$cart_request->request_id;
				try{
					$database->setQuery( $sql );
					$booking_total = $database->loadResult();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "2co_ins", "", "");
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
					$payment_status = "pending";																						
				}
				$sql = "update #__sv_apptpro3_requests set booking_due=".$booking_due.", ".
				"booking_deposit=".$booking_deposit.", payment_processor_used='2CO', txnid='".$order_number.
				"', request_status='accepted', payment_status='".$payment_status."' ".
				"WHERE id_requests=".$cart_request->request_id;

				try{
					$database->setQuery($sql);								
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "2co_ins", "", "");
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
						logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
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
	$sql = "insert into #__sv_apptpro3__2co_transactions(merchant_order_id,order_number,invoice_id,li_1_type,li_1_name,li_1_description,
		li_1_price,li_1_quantity,total,first_name,last_name,phone,email,street_address,street_address2,
		city,state,country,zip,ip_country,lang,pay_method,card_holder_name,credit_card_processed,demo) ".
		"values (".
		"'".$database->escape($merchant_order_id).
		"','".$database->escape($order_number).
		"','".$database->escape($invoice_id).
		"','".$database->escape($li_1_type).
		"','".$database->escape($li_1_name).
		"','".$database->escape($li_1_description). 
		"','".$database->escape($li_1_price).
		"','".$database->escape($li_1_quantity).
		"','".$database->escape($total).
		"','".$database->escape($first_name).
		"','".$database->escape($last_name).
		"','".$database->escape($phone).
		"','".$database->escape($email).
		"','".$database->escape($street_address).
		"','".$database->escape($street_address2).
		"','".$database->escape($city).
		"','".$database->escape($state).
		"','".$database->escape($country).
		"','".$database->escape($zip).
		"','".$database->escape($ip_country). 
		"','".$database->escape($lang).
		"','".$database->escape($pay_method).
		"','".$database->escape($card_holder_name).
		"','".$database->escape($credit_card_processed).
		"','".$database->escape($demo)."')";
	
	try{					
		$database->setQuery($sql);
		$database->execute();

	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "ins", "", "");
		echo JText::_('RS1_SQL_ERROR');
		$message = "2CO TRANSACTION ERROR: Error on insert into payment info table for order_number=".$order_number.",".$e->getMessage();

		$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
		$mailer->setSubject("2CO TRANSACTION ERROR");
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
			logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
			return false;		
		}
		if($apptpro_config->html_email == "Yes"){
			$mailer->IsHTML(true);
		}
		exit;
	}
	// Update transactions table end -------------------------------------------------

	// Confirmation emails start -----------------------------------------------------

	// Confirmation emails are different with cart as there are multiple bookings in one cart
	if(!$cart_booking){
		// non-cart
		// get request info
		$database = JFactory::getDBO();
		//$sql = 'SELECT * FROM #__sv_apptpro3_requests WHERE id_requests = '.$custom;
		$sql = 'SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.resource_email'. 
			" FROM #__sv_apptpro3_requests LEFT JOIN #__sv_apptpro3_resources ON ".
			" #__sv_apptpro3_requests.resource =	#__sv_apptpro3_resources.id_resources ".
			" WHERE #__sv_apptpro3_requests.id_requests=".(int)$merchant_order_id;	
		try{						
			$database->setQuery($sql);
			$res_request = NULL;
			$res_request = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "2co ins", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		// send confirmation email to customer
		$message = buildMessage($merchant_order_id, "confirmation", "Yes");
		$message_admin = buildMessage($merchant_order_id, "confirmation_admin", "Yes");
		$subject = JText::_('RS1_PAYPAL_CONFIRMATION_EMAIL_SUBJECT');
	
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
				logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
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
				logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
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
				logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
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
			$reminder_log_time_format = "%H:%M - %b %d";
			$returnCode = "";
			sv_sendSMS($res_request->id_requests, "confirmation", $returnCode, $toResource="Yes");			
			logReminder("New booking (2co): ".$returnCode, $res_request->id_requests, 0, "", $offsetdate->format($reminder_log_time_format));
		}
		
		//echo "index.php?option=com_rsappt_pro3&view=".$from_screen."&Itemid=".$from_screen_itemid."&task=pp_return";
		//exit;
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
			logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
			return false;		
		}
		
		if($apptpro_config->html_email != "Yes"){
			$msg_customer = str_replace("<br>", "\r\n", $msg_customer);
			$msg_admin = str_replace("<br>", "\r\n", $msg_admin);
		}

		// email to customer
		// The customer could change the email for each booking before
		// adding to the cart. 
		include_once( JPATH_COMPONENT."/controllers/cart.php" );
		$mycartcontroller = new cartController;
		
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
				logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
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
				logIt("Error on setting email FROM address: ".$e->getMessage(), "2co ins", "", "");
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
			logIt($e->getMessage(), "2co_ins", "", "");
			echo JText::_('RS1_SQL_ERROR');
		} 
	

	}

	if(!$cart_booking){	
		$this->setRedirect("index.php?option=com_rsappt_pro3&view=".$from_screen."&Itemid=".$from_screen_itemid."&task=twoco_return&tx=".$order_number."&req_id=".$merchant_order_id);
	}else {
		$this->setRedirect("index.php?option=com_rsappt_pro3&view=".$from_screen."&Itemid=".$from_screen_itemid."&task=twoco_return_cart&tx=".$order_number."&req_id=".$merchant_order_id);
	}

?>
