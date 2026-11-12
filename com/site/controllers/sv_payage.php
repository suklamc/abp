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

	include_once( JPATH_SITE."/administrator/components/com_rsappt_pro3/sendmail_pro2.php" );

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


 
class sv_payageController extends JControllerForm
{

	function __construct( $default = array())
	{
		parent::__construct( $default );

		$this->registerTask( 'payment_complete', 'payment_complete' );
		$this->registerTask( 'payment_update', 'payment_update' );
	}


	function payment_complete()	{

		include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

		// get the details of the payment that just completed
	
		$jinput = JFactory::getApplication()->input;
		$tid = $jinput->get('tid','', 'STRING');

		require_once JPATH_ADMINISTRATOR.'/components/com_payage/api.php';

		
		$payment_data = PayageApi::Get_Payment_Data($tid);
		if ($payment_data === false)
			{
			$message = JText::sprintf('COM_PAYAGE_TEST_INVALID_TRANSACTION',$tid);
			logIt($message, "ctrl sv_payage", "", "");
			//return;
			}

		$ok_or_fail = "fail";
		$request_id = $this->payment_update($tid);
		PayageApi::Set_Payment_Processed($tid, 1); // set as processed so it cannot be processed again
		
		$url = "";
		if($request_id == -1){
			//$msg = JText::_('SV_PAYAGE_FAILED'.$tid);
			//$url = JRoute::_('index.php?option=com_rsappt_pro3&view=payfail&tid='.$tid);
			// can't get router to work ;-(
			$url = 'index.php?option=com_rsappt_pro3&view=payfail&tid='.$tid;

		} else {
			//$url = JRoute::_( 'index.php?option=com_rsappt_pro3&view=paysuccess&tid='.$tid);
			$url = 'index.php?option=com_rsappt_pro3&view=paysuccess&tid='.$tid;
		}
			
		$this->setRedirect($url);
	}
	
	
	function payment_update($tid){
		$cart_booking = false;
		$message = "";
		$message_admin = "";
		$message_attachment = "";
		$mailer = JFactory::getMailer();

		// get config info
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_sv_payage", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		// IPN from PayPal - payage style
		$payment_data = PayageApi::Get_Payment_Data($tid);
		$payment_status = PayageApi::Get_Status_Description($payment_data->pg_status_code);
			
		$custom = $payment_data->app_transaction_id;
		$mc_gross = $payment_data->gross_amount;
		$gateway_name = "Payage-".$payment_data->gateway_name;
		$txn_id = $payment_data->pg_transaction_id;
		if($payment_status == "Success"){
			
			// Update bookings data start -------------------------------------------------------- 
			// We need to determine if this is a cart return of a single booking
			if(strpos($custom, "cart|") === false){
				// single booking, non-cart
				// get request info
				$database = JFactory::getDBO();
				//$sql = 'SELECT * FROM #__sv_apptpro3_requests WHERE id_requests = '.$custom;
				$sql = 'SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.*'. 
					" FROM #__sv_apptpro3_requests LEFT JOIN #__sv_apptpro3_resources ON ".
					" #__sv_apptpro3_requests.resource =	#__sv_apptpro3_resources.id_resources ".
					" WHERE #__sv_apptpro3_requests.id_requests=".(int)$custom;						
				try{
					$database->setQuery($sql);
					$res_request = NULL;
					$res_request = $database -> loadObject();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_sv_payage", "", "");
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
					logIt($e->getMessage(), "ctrl_sv_payage", "", "");
					echo JText::_('RS1_SQL_ERROR');
				}	

				if ($rows->requestCount == 0){
					// oh-oh no request by that number
					logIt("No outstanding request number: ".$request_id, "ctrl_sv_payage", "", "");
				} else {								
					// found request, update it
					
					// first check to see if status = timeout indcating IPN too slow and timeslot is no longer help for this customer
					$sql = "select request_status from #__sv_apptpro3_requests where id_requests=".(int)$request_id;
					try{
						$database->setQuery($sql);
						$status = $database -> loadResult();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "ctrl_sv_payage", "", "");
						echo JText::_('RS1_SQL_ERROR');
					}	
					if($status == "timeout"){
						try{
							$mailer->setSender(array($apptpro_config->mailFROM,null));
						}
						catch (Exception $e){
							logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_payage", "", "");
							return false;		
						}
						try{
							$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
						}
						catch (Exception $e){
							logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_payage", "", "");
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
							logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_payage", "", "");
							return false;		
						}
						$mailer->setSender(array($apptpro_config->mailFROM,null));
						if($apptpro_config->html_email == "Yes"){
							$mailer->IsHTML(true);
						}
						logIt("Booking timeout before IPN, booking paid but NOT ACCEPTED, requires admin action!",$request_id);
						return;
					}
					
					$payment_adjustment = " payment_status='paid', booking_due=0";
					//logit("booking_due=".$res_request->booking_due);
					//logit("mc_gross=".$mc_gross);
					if(floatval($res_request->booking_due) > floatval($mc_gross)){
						$payment_adjustment = " booking_due = booking_due - ".$mc_gross." , booking_deposit = ".$mc_gross." ";
					}
					
					if($apptpro_config->accept_when_paid == "Yes"){
						$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='$gateway_name', txnid='".$txn_id."', request_status='accepted' where id_requests=".$request_id;
					} else {
						$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='$gateway_name', txnid='".$txn_id."' where id_requests=".$request_id;
					}		
					try{				
						$database->setQuery($sql);
						$database->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "ctrl_sv_payage", "", "");
						echo JText::_('RS1_SQL_ERROR');	
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
					logIt($e->getMessage(), "ctrl_sv_payage", "", "");
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
							logIt($e->getMessage(), "ctrl_sv_payage", "", "");
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
						"booking_deposit=".$booking_deposit.", payment_processor_used='PayPal', txnid='".$txn_id.
						"', request_status='accepted', payment_status='".$payment_status."' ".
						"WHERE id_requests=".$cart_request->request_id;

						try{
							$database->setQuery($sql);								
							$database->execute();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "ctrl_sv_payage", "", "");
							echo JText::_('RS1_SQL_ERROR');
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
			// not done for Payage.
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
					logIt($e->getMessage(), "sv_payage", "", "");
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
				if($apptpro_config->html_email == "Yes"){
					$mailer->IsHTML(true);
				}

				if($messages_to_use->attach_ics_customer == "Yes"){
					$mailer->AddStringAttachment($ics, "appointment_".strval($request_id).".ics");
				}

				if($message_attachment != ""){
					$mailer->addAttachment($message_attachment);
				}

				if($res_request->email != ""){
					try{
						$mailer->addRecipient(explode(",", $res_request->email));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_payage", "", "");
						return false;		
					}
					$mailer->setSubject($subject);
					$mailer->setBody($message);
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_payage", "", "");
						return false;		
					}
					if($mailer->send() != true){
						logIt("Error sending email");
					}
					$mailer=null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_payage", "", "");
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
					try{
						$mailer->addRecipient(explode(",", $res_request->resource_email));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_payage", "", "");
						return false;		
					}
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
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_payage", "", "");
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
					try{
						$mailer->addRecipient(explode(",", $jv_to));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_payage", "", "");
						return false;		
					}
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
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_payage", "", "");
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
					logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_payage", "", "");
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
						if($cart_email_address->email != ""){
							try{
								$mailer->addRecipient($cart_email_address->email);
							}
							catch (Exception $e){
								logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_payage", "", "");
								return false;		
							}
						}
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
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_payage", "", "");
						return false;		
					}
				}
				
				// email to admin
				if($apptpro_config->mailTO != ""){
					$to = $apptpro_config->mailTO;
	
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
	
					try{
						$mailer->addRecipient(explode(",", $to));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_payage", "", "");
						return false;		
					}
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
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_payage", "", "");
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
							try{
								$mailer->addRecipient(explode(",", $cart_resource_address->resource_email));
							}
							catch (Exception $e){
								logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_payage", "", "");
								return false;		
							}
						}
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

				$session = JFactory::getSession();
				$sid = $session->getId();
				$session->set('confirmation_message',$msg_customer);

				// clear cart
				$sql = "DELETE FROM #__sv_apptpro3_cart WHERE request_id IN(".$bookings_to_process.")";
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_sv_payage", "", "");
					echo JText::_('RS1_SQL_ERROR');
				} 
			}

			// Confirmation emails end -----------------------------------------------------

		} else {
			$status = PayageApi::Get_Status_Description($payment_data->pg_status_code);
			$message = JText::sprintf('COM_PAYAGE_TEST_RETURN_STATUS',$tid, $status).' ('.$payment_data->gateway_name.')';
			$request_id = $payment_data->app_transaction_id;
			logIt($message."[".$request_id."]", "ctrl_sv_payage", "", "");
			// timeout the booking associated with this failed payment if not a cart
			if(strpos($request_id, "cart|") === false){
				$sql = "update #__sv_apptpro3_requests set request_status='timeout' where id_requests=".$request_id;
				try{
					$database->setQuery($sql);
					$database -> execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_sv_payage", "", "");
					echo JText::_('RS1_SQL_ERROR');
					return false;
				}		
			}
			return -1;
		}

		$request_id = str_replace("cart|", "", $request_id);
		return $request_id;
	}
		
		
		
}
?>