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
	include_once( JPATH_SITE."/administrator/components/com_rsappt_pro3/sendmail_pro2.php" );
	include_once( JPATH_SITE."/administrator/components/com_rsappt_pro3/functions_pro2.php" );
	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

	// dev only
	//ini_set ( "SMTP", "shawmail.cg.shawcable.net" ); 
	$jinput = JFactory::getApplication()->input;

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "gw_wrapit", "", "");
		echo json_encode(JText::_('RS1_SQL_ERROR'));
		jExit();
	}		
	$mailer = JFactory::getMailer();
	$mailer->setSender(array($apptpro_config->mailFROM,null));
	if($apptpro_config->html_email == "Yes"){
		$mailer->IsHTML(true);
	}
	$gw_order_id = $jinput->getString( 'gw_order_id', '-1' );
	$request_id = $jinput->getString( 'req_id', '-1' );
	$gw_item = $jinput->getString( 'gw_name', 'Appointment' );
	$gw_description = $jinput->getString( 'gw_description', 'Appointment Payment' );
	$jinput = JFactory::getApplication()->input;
	$gw_price = $jinput->getFloat( 'gw_price', '-1' );

	$cart_booking = false;
	$mycartcontroller = null;

	if(strpos($request_id, "cart") != false){
		$cart = "Yes";
	} else {
		$cart = "No";
	}
	
	//This transaction has been approved.	
	// Update bookings data start -------------------------------------------------------- 
	
	// We need to determine if this is a cart return of a single booking
	if($cart != "Yes"){
		// single booking, non-cart
			$payment_adjustment = " payment_status='paid', booking_due=0";
			//logit("booking_due=".$res_request->booking_due);
			//logit("mc_gross=".$mc_gross);

			$sql = 'SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.resource_email'. 
				" FROM #__sv_apptpro3_requests LEFT JOIN #__sv_apptpro3_resources ON ".
				" #__sv_apptpro3_requests.resource =	#__sv_apptpro3_resources.id_resources ".
				" WHERE #__sv_apptpro3_requests.id_requests=".(int)$request_id;	
			try{					
				$database->setQuery($sql);
				$res_request = NULL;
				$res_request = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "authnet_resp", "", "");
				echo json_encode(JText::_('RS1_SQL_ERROR'));
				jExit();
			}		
			
			if(floatval($res_request->booking_due) > floatval($gw_price)){
				$payment_adjustment = " booking_due = booking_due - ".$gw_price." , booking_deposit = ".$gw_price." ";
			}

			if($apptpro_config->accept_when_paid == "Yes"){
				$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='GoogleWallet', txnid='".$gw_order_id."', request_status='accepted' where id_requests=".$request_id;
			} else {
				$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='GoogleWallet', txnid='".$gw_order_id."' where id_requests=".$request_id;
			}	
			try{					
				$database->setQuery($sql);
				$database->execute();

			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "google_wallet_proc_payment", "", "");
				echo json_encode(JText::_('RS1_SQL_ERROR'));

				$message = "TRANSACTION ERROR: Error on request update for id=".$request_id;

				$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
				$mailer->setSubject("TRANSACTION ERROR");
				$mailer->setBody($message);
				if($mailer->send() != true){
					logIt("Error sending email", "google_wallet_proc_payment");
				}
				$mailer=null;
				$mailer = JFactory::getMailer();
				$mailer->setSender(array($apptpro_config->mailFROM,null));
				if($apptpro_config->html_email == "Yes"){
					$mailer->IsHTML(true);
				}
				if($mailer->send() != true){
					logIt("Error sending email", "google_wallet_proc_payment");
				}
				
				jExit();
			}

			addToCalendar($request_id, $apptpro_config); // will only add if accepted
		
		
	} else {
		// this is a cart transaction
		$cart_row_ids = "";
		$session = JFactory::getSession();
		$session_id = $session->getId();

		$sql = "SELECT * FROM #__sv_apptpro3_cart ".
			"WHERE session_id = '".$session_id."';";
			try{	
				$database->setQuery($sql);
				$rows = NULL;
				$rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "google_wallet_proc_payment", "", "");
				echo json_encode(JText::_('RS1_SQL_ERROR'));
				jExit();
			}		
		foreach($rows as $row){
			$cart_row_ids .= $row->id_row_cart;
			$cart_row_ids .= "|";
		}
		$cart_row_ids = substr($cart_row_ids, 0, strlen($cart_row_ids)-1);

		$custom = $cart_row_ids;
		$request_id = "cart|".$cart_row_ids;
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
			logIt($e->getMessage(), "google_wallet_proc_payment", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR'));
			jExit();
		}		
		if(sv_count_($cart_requests) == 0 ){
			// oh-oh, cart has been emptied before we got here, big problem, log error
			logIt("Error, cart empty when processing ipn for gw_order_id ".$gw_order_id);
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
					echo json_encode(JText::_('RS1_SQL_ERROR'));
					jExit();
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
				"booking_deposit=".$booking_deposit.", payment_processor_used='GoogleWallet', txnid='".$gw_order_id.
				"', request_status='accepted', payment_status='".$payment_status."' ".
				"WHERE id_requests=".$cart_request->request_id;
				try{
					$database->setQuery($sql);
					$database->execute();

				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "google_wallet_proc_payment", "", "");
					echo json_encode(JText::_('RS1_SQL_ERROR'));

					$message = "TRANSACTION ERROR: Error on request update for txnid=".$gw_order_id.",".$e->getMessage();

					$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
					$mailer->setSubject("TRANSACTION ERROR");
					$mailer->setBody($message);
					if($mailer->send() != true){
						logIt("Error sending email", "google_wallet_proc_payment");
					}
					$mailer=null;
					$mailer = JFactory::getMailer();
					$mailer->setSender(array($apptpro_config->mailFROM,null));
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
					if($mailer->send() != true){
						logIt("Error sending email", "google_wallet_proc_payment");
					}											
					jExit();
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
	
	$sql = "insert into #__sv_apptpro3_google_wallet_transactions(gw_order_id, request_id, gw_item, gw_description, gw_price)".
		"values (".
		"'".$database->escape($gw_order_id).
		"','".$database->escape($request_id).
		"','".$database->escape($gw_item).
		"','".$database->escape($gw_description).
		"','".$database->escape(number_format($gw_price,2)).
		"')";

	try{	
		$database->setQuery($sql);
		$database->execute();

	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "google_wallet_proc_payment", "", "");
		echo json_encode(JText::_('RS1_SQL_ERROR'));

		$message = "TRANSACTION ERROR: Error on insert into payment info table for txnid=".$gw_order_id.",".$e->getMessage();

		$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
		$mailer->setSubject("TRANSACTION ERROR");
		$mailer->setBody($message);
		if($mailer->send() != true){
			logIt("Error sending email", "google_wallet_proc_payment");
		}
		$mailer=null;
		$mailer = JFactory::getMailer();
		$mailer->setSender(array($apptpro_config->mailFROM,null));
		if($apptpro_config->html_email == "Yes"){
			$mailer->IsHTML(true);
		}
		jExit();
	}
	// Update transactions table end -------------------------------------------------

	// Confirmation email/sms start -----------------------------------------------------

	if($cart != "Yes"){
		// non-cart
		// get request info for sending confirmations
		$sql = 'SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.resource_email'. 
			" FROM #__sv_apptpro3_requests LEFT JOIN #__sv_apptpro3_resources ON ".
			" #__sv_apptpro3_requests.resource =	#__sv_apptpro3_resources.id_resources ".
			" WHERE #__sv_apptpro3_requests.id_requests=".(int)$request_id;	
		try{					
			$database->setQuery($sql);
			$res_request = NULL;
			$res_request = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "authnet_resp", "", "");
			echo json_encode(JText::_('RS1_SQL_ERROR'));
			jExit();
		}		
		
		// send confimration email to x_invoice_numer
		$message = buildMessage($request_id, "confirmation", "Yes");
		$message_admin = buildMessage($request_id, "confirmation_admin", "Yes");
		$subject = JText::_('RS1_CONFIRMATION_EMAIL_SUBJECT');

		if($res_request->email != ""){
			$mailer->addRecipient($res_request->email);
			$mailer->setSubject($subject);
			$mailer->setBody($message);
			if($mailer->send() != true){
				logIt("Error sending email", "google_wallet_proc_payment");
			}
			$mailer=null;
			$mailer = JFactory::getMailer();
			$mailer->setSender(array($apptpro_config->mailFROM,null));
			if($apptpro_config->html_email == "Yes"){
				$mailer->IsHTML(true);
			}
		}
		
		if($res_request->resource_email != ""){
			$mailer->addRecipient(explode(",", $res_request->resource_email));
			$mailer->setSubject($subject);
			$mailer->setBody($message_admin);
			if($mailer->send() != true){
				logIt("Error sending email", "google_wallet_proc_payment");
			}
			$mailer=null;
			$mailer = JFactory::getMailer();
			$mailer->setSender(array($apptpro_config->mailFROM,null));
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
				logIt("Error sending email", "google_wallet_proc_payment");
			}
			$mailer=null;
			$mailer = JFactory::getMailer();
			$mailer->setSender(array($apptpro_config->mailFROM,null));
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
		$mailer->setSender(array($apptpro_config->mailFROM,null));
		
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
			$mailer->setSender(array($apptpro_config->mailFROM,null));
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
			$mailer->setSender(array($apptpro_config->mailFROM,null));
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
				logIt("Error clearing cart after ipn: ".$e->getMessage(), "google_wallet_proc_payment", "", "");
				echo json_encode(JText::_('RS1_SQL_ERROR'));
				jExit();
			}
		$cancel_code = "cart";

	}
	// Confirmation email/sms end -----------------------------------------------------

	echo json_encode("Complete");
	jExit();
							

?>