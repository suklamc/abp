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
 // Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * Controller for the cart 
 */
class cartController extends JControllerForm {

	function __construct( $default = array())
	{
		parent::__construct( $default );
		
	}

	function cartController()
	{
		$this->registerTask( 'add_to_cart', 'add_to_cart' );
	}


	/**
	 * Add the product to the cart
	 */
	public function add_to_cart($booking_id, $item_total) {
		$session = JFactory::getSession();
		$jinput = JFactory::getApplication()->input;
		$session_id = $session->getId();
		$database = JFactory::getDBO();
		$sql = "INSERT INTO #__sv_apptpro3_cart (request_id, session_id, item_total) ".
		"VALUES (".$booking_id.",'".$session_id."',".$item_total.");";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_cart", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
		return true;
		
	}


	/**
	 * Delete a product from the cart
	 */
	public function delete() {
		$jinput = JFactory::getApplication()->input;
		$booking = $jinput->getInt('booking', '');
		$database = JFactory::getDBO();
		
		// delete the booking
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'requests_detail.php');
		$model_requests = new requests_detailModelrequests_detail;
 		if($model_requests == null){
			$this->json->msg = "model_requests = null";
			echo json_encode($this->json);
			jExit();
			return true;
		}
		$session = JFactory::getSession();
		$session_id = $session->getId();

		$sql = "SELECT COUNT(*) FROM #__sv_apptpro3_cart WHERE request_id = ".(int)$booking.
		" AND session_id = '".$session_id."'";
		try{
			$database->setQuery($sql);
			if($database->loadResult() == 0){
				jExit();
			}
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_cart", "", "");
			echo JText::_('RS1_SQL_ERROR');
			jExit();
			exit;
		}
		$bookings[0] = $booking; // cart delete is only one at a time
		$model_requests->delete($bookings);

		// if yo do not want ABPro to delete the booking you can comment out the above code 144-153 and un-cooment
		// the code below. Then a cart delete will just set eth booking to 'timeout' status so the slot can be booked by
		// someone else.
				
		// set pending booking to timeout
//		$sql = "UPDATE #__sv_apptpro3_requests SET request_status = 'timeout' WHERE id_requests = ".$booking;
//		try{
//			$database->setQuery($sql);
//			$database->execute();
//		} catch (RuntimeException $e) {
//			logIt($e->getMessage(), "ctrl_cart", "", "");
//			$err = $e->getMessage();
//			echo JText::_('RS1_SQL_ERROR');
//			$this->json->msg = $err;
//			echo json_encode($this->json);
//			jExit();
//			exit;
//		}

		
		// remove from cart
		$sql = "DELETE FROM #__sv_apptpro3_cart WHERE request_id = ".$booking;
		$msg = "";
		try{
			$database->setQuery($sql);
			$database->execute();
			$msg = JText::_('RS1_REMOVED_FROM_CART');
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "ctrl_cart", "", "");
			$msg = JText::_('RS1_SQL_ERROR');
		}
		
		echo $msg;
		jExit();
		return true;
	}


	function cart_exists() {
		$session = JFactory::getSession();
		$session_id = $session->getId();
		$jinput = JFactory::getApplication()->input;

		$database = JFactory::getDBO();
		$sql = "SELECT count(id_row_cart) as count FROM #__sv_apptpro3_cart WHERE session_id = '".$session_id."'";
		try{
			$database->setQuery($sql);
			$cartItems = NULL;
			$cartItems = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "controllers/cart", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		if($cartItems->count > 0){
			return true;
		}
		return false;
		
	}

	function checkout(){
		// If PayPal, send customer there, else
		// - set bookings to accpted (or new)
		// - send confirmation email
		include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );
		$jinput = JFactory::getApplication()->input;

		$session_id = $jinput->getString('sid', '');
		$cart_total = $jinput->getFloat('cart_total', '0');
		$pay_proc_submit = $jinput->getString('pp', '0');
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$update_status = "new";
		$stripe_token = $jinput->getString('st', '0');
		
		// get config info
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "controllers/cart", "", "");
			$err = JText::_('RS1_SQL_ERROR');
			$this->json->msg = $err;
			echo json_encode($this->json);
			jExit();
			return true;
		}		

		// get cart rows for this sid
		$sql = "SELECT *, #__sv_apptpro3_requests.name as CustomerName FROM #__sv_apptpro3_cart INNER JOIN #__sv_apptpro3_requests ".
		"ON #__sv_apptpro3_cart.request_id = #__sv_apptpro3_requests.id_requests ".
		"INNER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
		"WHERE session_id = '".$session_id."' ".
		" AND #__sv_apptpro3_requests.request_status = 'pending' ".
		" ORDER BY startdate, starttime";
		try{
			$database->setQuery($sql);
			$rows = NULL;
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "controllers/cart", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		

		$msg_customer = "";
		$msg_admin = "";
		$cart_row_ids = "";
		if(sv_count_($rows) > 0){
			if($pay_proc_submit != "0" && $pay_proc_submit != "5" && floatval($cart_total) > 0){
				// reset timers once more before going to PayPal.
				if(sv_count_($rows) > 0){
					$sql = "UPDATE #__sv_apptpro3_cart set created = NOW() WHERE session_id = '".$session_id."'";
					try{
						$database->setQuery($sql);
						$database->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "ctrl_cart", "", "");
						echo JText::_('RS1_SQL_ERROR');
						exit;
					}
				}
				
				// get id_row_cart values into a string to pass through PayPal
				foreach($rows as $row){
					$cart_row_ids .= $row->id_row_cart;
					$cart_row_ids .= "|";
				}
				$cart_row_ids = substr($cart_row_ids, 0, strlen($cart_row_ids)-1);
				
				// go to payment gateway
				$mobile_order = "No";
				$cart = "Yes";
				// drop in the appropriate goto code..
				$payment_required = $cart_total;

				$pay_proc = $pay_proc_submit;
				if(strpos($pay_proc_submit, "~") >-1){
					// this is payage so we need to strip of the ~account_id
					$pay_proc = substr($pay_proc_submit, 0, strpos($pay_proc_submit, "~"));
				}

				include JPATH_COMPONENT.DIRECTORY_SEPARATOR."payment_processors".DIRECTORY_SEPARATOR.$pay_proc.DIRECTORY_SEPARATOR.$pay_proc."_goto.php";
				
			} else {
				
				// If $pay_proc_submit = "5" this is a Stripe transaction and we have a token so we can charge the card
				// and set the booking to paid, then fall through to the normal confirmation stuff below.
				if($pay_proc_submit == "5" && floatval($cart_total) > 0){
		
					$request_id = $last_id->last_id;		
					// get stripe settings
					$sql = 'SELECT * FROM #__sv_apptpro3_stripe_settings;';
					try{
						$database->setQuery($sql);
						$stripe_settings = NULL;
						$stripe_settings = $database -> loadObject();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "ctrl_process_booking_request", "", "");
						echo JText::_('RS1_SQL_ERROR');
						exit;
					}			

					// In here we will charge the card and fall through to confimrations or abort on a failed transaction
					require_once(JPATH_SITE."/components/com_rsappt_pro3/stripe/stripe-php/lib/Stripe.php");
					// See keys here: https://dashboard.stripe.com/account/apikeys

					try{
						Stripe::setApiKey($stripe_settings->stripe_sk);	
					
						// Token is created using Stripe.js or Checkout!
						// Get the payment token submitted by the form:
						$token = $stripe_token;
						// Charge the user's card:
						$charge = Stripe_Charge::create(array(
						  "amount" => $cart_total*100,
						  "currency" => $stripe_settings->stripe_currency,
						  "description" => $stripe_settings->stripe_billing_description,
						  "source" => $token,
						));
														
					}
			
					catch(Stripe_CardError $e) {
					}
					catch (Stripe_InvalidRequestError $e) {
					// Invalid parameters were supplied to Stripe's API
						echo $e->getMessage();
						exit;
					} catch (Stripe_AuthenticationError $e) {
					// Authentication with Stripe's API failed
					// (maybe you changed API keys recently)
						echo $e->getMessage();
						exit;
					} catch (Stripe_ApiConnectionError $e) {
					// Network communication with Stripe failed
						echo $e->getMessage();
						exit;
					} catch (Stripe_Error $e) {
						echo $e->getMessage();
						exit;
					// Display a very generic error to the user, and maybe send
					// yourself an email
					} catch (Exception $e) {
						echo $e->getMessage();
						exit;
					}	
									
				}
					
				// no payment gateway, just update booking status, and send confirmation emails
				$msg_customer = $this->buildCartMessage($apptpro_config, $rows, "customer"); // rows = cart join with request
				$msg_admin = $this->buildCartMessage($apptpro_config, $rows, "admin");		
				 
				$bookings_to_process = "";
				foreach($rows as $row){
					$update_status = "new";
					if($row->auto_accept == "Global"){
						$auto_accept = $apptpro_config->auto_accept;
					} else {
						$auto_accept = $row->auto_accept;
					}

					if( $auto_accept == "Yes" ) {
						$update_status = "accepted";
					}
					
					$sql = "UPDATE #__sv_apptpro3_requests SET request_status = '".$update_status."' ";
					
					if($pay_proc_submit == "5" && floatval($cart_total) > 0){
						$payment_adjustment = " payment_status='paid', booking_due=0";
						//logit("charge->paid=".$charge->paid);
						if(floatval($res_request->booking_due) > floatval($charge->paid)){
							$payment_adjustment = " booking_due = ".$grand_total." - ".$charge->paid.", booking_deposit = ".$charge->paid." ";
						}							
						$sql .= ",".$payment_adjustment.", payment_processor_used='Stripe', txnid='".$charge->id."'";
					}
					$sql .= " WHERE id_requests = ".$row->id_requests;
					
					try{
						$database->setQuery($sql);
						$database->execute();						
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "ctrl_cart", "", "");
						$err = $database->$e->getMessage();
						$this->json->msg = $err;
						echo json_encode($this->json);
						jExit();
						return true;
					} 
					
					if($pay_proc_submit == "5" && floatval($cart_total) > 0){
						// card has been charged, we need to add transaction record 
						$isCart = true;
						$request_id = $row->id_requests;
						include JPATH_COMPONENT.DIRECTORY_SEPARATOR."payment_processors".DIRECTORY_SEPARATOR."stripe".DIRECTORY_SEPARATOR."stripe_process_payment.php";
					}
					
					$bookings_to_process .= $row->id_requests.",";
					
					addToCalendar($row->id_requests, $apptpro_config); // will only add if accepted									
					addToEmailMarketing($row->id_requests, $apptpro_config);
				}				
				$bookings_to_process = rtrim($bookings_to_process, ',');

				// dev only
				//ini_set ( "SMTP", "shawmail.cg.shawcable.net" ); 
				
				// send confirmation emails
				$mailer = JFactory::getMailer();
				try{
					$mailer->setSender(array($apptpro_config->mailFROM,null));
				}
				catch (Exception $e){
					logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_cart", "", "");
					return false;		
				}
				if($apptpro_config->html_email != "Yes"){
					$msg_customer = str_replace("<br>", "\r\n", $msg_customer);
					$msg_admin = str_replace("<br>", "\r\n", $msg_admin);
				}
	
				// email to customer
				// The customer could change the email for each booking before
				// adding to the cart. 
				$cart_email_addresses = $this->get_cart_email("customer", $bookings_to_process);
				if(sv_count_($cart_email_addresses)>0){
					foreach($cart_email_addresses as $cart_email_address){
						if($cart_email_address->email != ""){
							try{
								$mailer->addRecipient($cart_email_address->email);
							}
							catch (Exception $e){
								logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_cart", "", "");
								return false;		
							}
						}
					}
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
	
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($msg_customer);

					try{
						if($mailer->send() != true){
							logIt("Error sending email: ".$mailer->ErrorInfo, "ctrl_cart", "", "");
						}
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_cart", "", "");
						return false;		
					}
					// reset for next
					$mailer = null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_cart", "", "");
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
						logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_cart", "", "");
						return false;		
					}
					$mailer->setSubject(JText::_($apptpro_config->mailSubject));
					$mailer->setBody($msg_admin);
					if($mailer->send() != true){
						logIt("Error sending email: ".$mailer->ErrorInfo, "ctrl_cart", "", "");
					}
	
					// reset for next
					$mailer = null;
					$mailer = JFactory::getMailer();
					try{
						$mailer->setSender(array($apptpro_config->mailFROM,null));
					}
					catch (Exception $e){
						logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_cart", "", "");
						return false;		
					}
				}
				
				// email to resource
				// each resource can have diffeent email and the cart can have multiple resoucres
				$cart_resource_addresses = $this->get_cart_email("resource", $bookings_to_process);
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
								logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_cart", "", "");
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
							logIt("Error sending email: ".$mailer->ErrorInfo, "ctrl_cart", "", "");
						}
					}
				}

				// clear cart
				$sql = "DELETE FROM #__sv_apptpro3_cart WHERE request_id IN(".$bookings_to_process.")";
				$database->setQuery($sql);
				try{
					$database->setQuery($sql);
					$database->execute();						
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_cart", "", "");
					$err = $database->$e->getMessage();
					$this->json->msg = $err;
					echo json_encode($this->json);
					jExit();
					return true;
				} 
				
				// display confirmation							
				JFactory::getDocument()->setMimeEncoding( 'application/json' );
				$data = array(
        			'msg' => $msg_customer
			    );
			    echo json_encode( $data );				
				jExit();
				return true;
				
				
			}
		} else {
			
			JFactory::getDocument()->setMimeEncoding( 'application/json' );
			$data = array(
				'msg' =>JText::_('RS1_CART_EMPTY')
			);
			echo json_encode( $data );				
			jExit();		
			return true;
			
		}
	}

	function get_cart_total($sid="") {
		// Problem, theoretically the customer could chaneg the email for each booking before
		// adding to the cart. We will just fetch the email from the first entry.
		$jinput = JFactory::getApplication()->input;
		$session_id = "";
		if($sid == ""){
			$session = JFactory::getSession();
			$session_id = $session->getId();
		} else {
			$session_id = $sid;
		}

		$database = JFactory::getDBO();
		$sql = "SELECT sum(#__sv_apptpro3_cart.item_total) as total FROM #__sv_apptpro3_cart INNER JOIN #__sv_apptpro3_requests ".
		"ON #__sv_apptpro3_cart.request_id = #__sv_apptpro3_requests.id_requests ".
		" WHERE #__sv_apptpro3_cart.session_id = '".$session_id."' AND #__sv_apptpro3_requests.request_status = 'pending'";
	
		try{
			$database->setQuery($sql);
			$cartTotal = NULL;
			$cartTotal = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "controllers/cart", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return -1;
		}		
		if($cartTotal->total == NULL){
			return -1;
		}
		return $cartTotal->total;		
	}

	function get_cart_email($which, $bookings_to_process) {
		$jinput = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		$session_id = $session->getId();

		$database = JFactory::getDBO();
		if($which == "customer"){
			$sql = "SELECT DISTINCT email FROM #__sv_apptpro3_requests ".
			" WHERE id_requests IN('".$database->escape($bookings_to_process)."')";
		} else {
			$sql = "SELECT DISTINCT resource_email FROM #__sv_apptpro3_requests ".
			"INNER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
			" WHERE #__sv_apptpro3_requests.id_requests IN('".$database->escape($bookings_to_process)."')";
		}
//		if($which == "customer"){
//			$sql = "SELECT DISTINCT #__sv_apptpro3_requests.email FROM #__sv_apptpro3_cart INNER JOIN #__sv_apptpro3_requests ".
//			"ON #__sv_apptpro3_cart.request_id = #__sv_apptpro3_requests.id_requests ".
//			" WHERE #__sv_apptpro3_cart.session_id = '".$session_id."' AND #__sv_apptpro3_requests.request_status = 'pending'";
//		} else {
//			$sql = "SELECT DISTINCT #__sv_apptpro3_resources.resource_email FROM #__sv_apptpro3_cart INNER JOIN #__sv_apptpro3_requests ".
//			"ON #__sv_apptpro3_cart.request_id = #__sv_apptpro3_requests.id_requests ".
//			"INNER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
//			" WHERE #__sv_apptpro3_cart.session_id = '".$session_id."' AND #__sv_apptpro3_requests.request_status = 'pending'";
//		}
		try{
			$database->setQuery($sql);
			$cartEmail = NULL;
			$cartEmail = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "controllers/cart", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		return $cartEmail;		
	}

	function buildCartMessage($apptpro_config, $cart_join_request_rows, $which, $sid="", $cart_in_progress=""){
		$jinput = JFactory::getApplication()->input;
		// if no $cart_join_request_rows passed in build it now, will need session_id to fecth cart rows
		if($cart_join_request_rows == null){
			$database = JFactory::getDBO();
			$sql = "SELECT * FROM #__sv_apptpro3_cart INNER JOIN #__sv_apptpro3_requests ".
			"ON #__sv_apptpro3_cart.request_id = #__sv_apptpro3_requests.id_requests ".
			"INNER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
			"WHERE session_id = '".$sid."' ".
			" AND #__sv_apptpro3_requests.request_status = 'pending' ".
			" ORDER BY id_row_cart";
			try{	
				$database->setQuery($sql);
				$cart_join_request_rows = NULL;
				$cart_join_request_rows = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "controllers/cart", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}		

		}
		$msg = JText::_(clean_svkey($apptpro_config->cart_msg_header));
		$msg .= "<br/>";
		//$bookings_to_process = "";
		$cart_total = $this->get_cart_total();
		foreach($cart_join_request_rows as $row){
			$update_status = "new";
			if($row->auto_accept == "Global"){
				$auto_accept = $apptpro_config->auto_accept;
			} else {
				$auto_accept = $row->auto_accept;
			}

			if( $auto_accept == "Yes" ) {
				$update_status = "accepted";
			}
			//$bookings_to_process .= $row->id_requests.",";
			
			if($cart_in_progress == "yes"){
				// If customer retrunes before PayPal is done we need to show an in-progress message
				$update_status = "new";
			}
			// To create a confimration message we will need each booking id and what it's status is 
			// because some may be 'accepted' and others 'new'
			if($update_status == "new"){
				if($which == "customer"){
					$msg .= buildMessage($row->id_requests, "cart_msg_inprogress", "No");
				} else {
					// same msg for admin and customer for now may be different in future
					$msg .= buildMessage($row->id_requests, "cart_msg_inprogress", "No");						
				}
			} else {
				if($which == "customer"){
					$msg .= buildMessage($row->id_requests, "cart_msg_confirm", "No");
				} else {
					// same msg for admin and customer for now may be different in future
					$msg .= buildMessage($row->id_requests, "cart_msg_confirm", "No");
				}
			}
			$msg .= "<br/>";
		}
		
		$msg .= JText::_(clean_svkey($apptpro_config->cart_msg_footer));
		// swap in cart total is token is found
		$msg = str_replace("[cart_total]", $cart_total, $msg);
		
		return $msg;
	}
	
}

?>