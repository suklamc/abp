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
				if($charge->paid){
					
					// Update bookings data start -------------------------------------------------------- 
					if($isCart == false){
						// single booking, non-cart
						$sql = "select count(*) as requestCount from #__sv_apptpro3_requests where id_requests=".(int)$request_id;
						$rows = NULL;
						try{
							$database->setQuery($sql);
							$rows = $database -> loadObject();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "pay_proc_stripe_process_payment", "", "");
							echo JText::_('RS1_SQL_ERROR');
						}	
	
						if ($rows->requestCount == 0){
							// oh-oh no request by that number
							logIt("No outstanding request number: ".$request_id, "pay_proc_stripe_process_payment", "", "");
						} else {								
							// found request, update it
							
							// first check to see if status = timeout indcating too slow and timeslot is no longer help for this customer
							$sql = "select request_status from #__sv_apptpro3_requests where id_requests=".(int)$request_id;
							try{
								$database->setQuery($sql);
								$status = $database -> loadResult();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "pay_proc_stripe_process_payment", "", "");
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
								$mailer->setSubject("Time-out booking!");
								$mailer->setBody("Booking 'timeout' before payment processing completed. This booking had been paid but NOT accepted in ABPro as the timeslot lock had been released by the timeout, requires admin action! Booking id:".$request_id);
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
								logIt("Booking timeout before Strpe payment complete, booking paid but NOT ACCEPTED, requires admin action!",$request_id);
								return;
							}
										
							$payment_adjustment = " payment_status='paid', booking_due=0";
							if(floatval($grand_total) > floatval($charge->amount)/100){
								$calculated_due = floatval($grand_total) - floatval($charge->amount)/100;
								$payment_adjustment = " booking_due = ".strval($calculated_due).", booking_deposit = ".strval(floatval($charge->amount)/100)." ";
							}
										
							if($apptpro_config->accept_when_paid == "Yes"){
								$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='Stripe', txnid='".$charge->id."', request_status='accepted' where id_requests=".$request_id;
								$request_status = "accepted";
							} else {
								$sql = "update #__sv_apptpro3_requests set ".$payment_adjustment.", payment_processor_used='Stripe', txnid='".$charge->id."' where id_requests=".$request_id;
							}		
							try{				
								$database->setQuery($sql);
								$database->execute();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "pay_proc_stripe_process_payment", "", "");
								echo JText::_('RS1_SQL_ERROR');
		
								$message = "STRIPE TRANSACTION ERROR: Error on request update for txnid=".$charge->id.",".$database -> stderr();
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
									logIt("Error on setting email FROM address: ".$e->getMessage(), "pay_proc_stripe_process_payment", "", "");
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
						// cart booking updates of status, emails and such is done in the cart controller chcekout process
						// should not get here as exception would have been caught in process_booking_request 
					}
				
				
				} else {
					// not paid message back from Stripe
					
				}
				// Update bookings data end -------------------------------------------------------- 
			
				// Update transactions table start -------------------------------------------------
				$sql = "insert into #__sv_apptpro3_stripe_transactions(stripe_txn_id,request_id,cart,amount,currency,description,seller_message,".
					"status,card_brand,card_country,card_exp_month,card_exp_year,card_last4)".
					"values (".
					"'".$charge->id."',".
					$request_id.",".
					"'".($isCart?JText::_('RS1_ADMIN_SCRN_YES'):JText::_('RS1_ADMIN_SCRN_NO'))."',".
					$charge->amount.",".
					"'".$charge->currency."',".
					"'".$charge->description."',".
					"'".$charge->outcome->seller_message."',".
					"'".$charge->status."',".
					"'".$charge->source->brand."',".
					"'".$charge->source->country."',".
					"".$charge->source->exp_month.",".
					"".$charge->source->exp_year.",".
					"'".$charge->source->last4."'".
					")";
				try{	
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "pay_proc_stripe_process_payment", "", "");
					echo JText::_('RS1_SQL_ERROR');
	
					$message = "STRIPE TRANSACTION ERROR: Error on insert into payment info table for txnid=".$charge->id.",".$database -> stderr();
	
					$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
					$mailer->setSubject("STRIPE TRANSACTION ERROR");
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
//				// Update transactions table end -------------------------------------------------

?>