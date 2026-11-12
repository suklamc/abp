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
	$jinput = JFactory::getApplication()->input;

	// dev only
	//ini_set ( "SMTP", "shawmail.cg.shawcable.net" ); 

echo 'ok';
	//logIt("Starting IPN");

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
		logIt($e->getMessage(), "pp_purchase_uc_ipn", "", "");
		echo JText::_('RS1_SQL_ERROR');
	}		

	$mailer = JFactory::getMailer();
	try{
		$mailer->setSender(array($apptpro_config->mailFROM,null));
	}
	catch (Exception $e){
		logIt("Error on setting email FROM address: ".$e->getMessage(), "pp_purchase_uc_ipn", "", "");
		return false;		
	}
	if($apptpro_config->html_email == "Yes"){
		$mailer->IsHTML(true);
	}

	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';
	$formData = $jinput->post->getArray();
	foreach($formData as $key => $value) {	
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}
	// post back to PayPal system to validate
    $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

    $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
    $header.= "User-Agent: PHP/".phpversion()."\r\n";
    $header.= "Referer: ".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].@$_SERVER['QUERY_STRING']."\r\n";
    $header.= "Server: ".$_SERVER['SERVER_SOFTWARE']."\r\n";
	if($paypal_settings->paypal_use_sandbox == "Yes"){
		$header.= "Host: www.sandbox.paypal.com:80\r\n";
	} else {
		$header.= "Host: www.paypal.com:80\r\n";
	}
    $header.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header.= "Content-Length: ".strlen($req)."\r\n";
    $header.= "Accept: */*\r\n\r\n";
	
	if($paypal_settings->paypal_use_sandbox == "Yes"){
		$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
	} else {
		//$fp = fsockopen ("www.paypal.com", 80, $errno, $errstr, 30);
		$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
	}
	
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
	if (!$fp) {
		// HTTP ERROR
		echo "error";
	} else {
		fwrite ($fp, $header . $req);
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0) {
	
					//logit("VERIFIED");
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
						logIt($e->getMessage(), "pp_purchase_uc_ipn", "", "");
						echo JText::_('RS1_SQL_ERROR');
					}	

					if ($rows->txnCount == 0){
						// no dupe carry on..

						// goodie we got paid
						if($payment_status == "Completed"){

							// Update bookings credit start -------------------------------------------------------- 
							$database = JFactory::getDBO();
							$table_name_user_credit = '#__sv_apptpro3_user_credit';
							$table_name_user_credit_activity = "#__sv_apptpro3_user_credit_activity";

							// get product details
							$sql = "SELECT * FROM #__sv_apptpro3_products WHERE id_products = ".$pid;
							try{
								$database->setQuery($sql);
								$product = NULL;
								$product = $database -> loadObject();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "pp_purchase_uc_ipn", "", "");
								echo JText::_('RS1_SQL_ERROR');
								return false;
							}		
							// does the user have an ABPro User Credits account?
							$table_name = '#__sv_apptpro2_user_credit';
							$sql = "SELECT Count(*) FROM ".$table_name_user_credit." WHERE user_id = ".$uid;
							$database->setQuery($sql);
							$has_account = $database->loadResult();
							if ($database -> getErrorNum()) {
								echo $database -> stderr();
								logIt("Error checking for user credit account: ".$database->getErrorMsg(), "pp_purchase_uc_ipn");
								return;
							}
							if($has_account > 0){
								// update
								$sql = "UPDATE ".$table_name_user_credit." SET balance = (balance + ".($product->product_value).") ".
								" WHERE user_id = ".$uid;
								$database->setQuery($sql);
								$database->query();
								if ($database -> getErrorNum()) {
									echo $database -> stderr();
									logIt("Error setting user credit: ".$database->getErrorMsg(), "pp_purchase_uc_ipn");
									return;
								}
								// now credit activity
								$sql = "INSERT INTO ".$table_name_user_credit_activity." (user_id, comment, operator_id, increase, balance) ".
								"VALUES (".$uid.",".
								"'".JText::_('RS1_UC_PURCHASE_COMMENT')." - ".$txn_id."',".
								$uid.",".
								($product->product_value).",".
								"(SELECT balance from ".$table_name_user_credit." WHERE user_id = ".$uid."))";
								$database->setQuery($sql);
								$database->query();
								if ($database -> getErrorNum()) {
									echo $database -> stderr();
									logIt("Error setting user credit activity: ".$database->getErrorMsg(), "pp_purchase_uc_ipn");
									return;
								}
							} else {
								// add new
								$sql = "INSERT INTO ".$table_name_user_credit." (user_id, balance) VALUES (".$uid.",".$product->product_value.") ";
								$database->setQuery($sql);
								$database->query();
								if ($database -> getErrorNum()) {
									echo $database -> stderr();
									logIt("Error adding user credit: ".$database->getErrorMsg(), "pp_purchase_uc_ipn");
									return;
								}
								// now credit activity
								$sql = "INSERT INTO ".$table_name_user_credit_activity." (user_id, comment, operator_id, increase, balance) ".
								"VALUES (".$uid.",".
								"'".JText::_('RS1_UC_PURCHASE_COMMENT')." - ".$txn_id."',".
								$uid.",".
								$product->product_value.",".
								"(SELECT balance from ".$table_name_user_credit." WHERE user_id = ".$uid."))";
								$database->setQuery($sql);
								$database->query();
								if ($database -> getErrorNum()) {
									echo $database -> stderr();
									logIt("Error setting user credit activity: ".$database->getErrorMsg(), "pp_purchase_uc_ipn");
									return;
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
								logIt($e->getMessage(), "pp_purchase_uc_ipn", "", "");
								echo JText::_('RS1_SQL_ERROR');

								$message = "PAYPAL TRANSACTION ERROR: Error on insert into payment info table for txnid=".$txn_id.",".$database -> stderr();

								try{
									$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
								}
								catch (Exception $e){
									logIt("Error on setting email TO address: ".$e->getMessage(), "pp_purchase_uc_ipn", "", "");
									return false;		
								}
							
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
									logIt("Error on setting email FROM address: ".$e->getMessage(), "pp_purchase_uc_ipn", "", "");
									return false;		
								}
								if($apptpro_config->html_email == "Yes"){
									$mailer->IsHTML(true);
								}								
							}
							// Update transactions table end -------------------------------------------------
						
							// Confirmation emails start -----------------------------------------------------

							// Confirmation emails are different with cart as there are multiple bookings in one cart
								// to be added at a later date						

							// Confirmation emails end -----------------------------------------------------

						} else {
							// payment_status not complete??
							$sql = "insert into #__sv_apptpro3_errorlog (description) values('Payment Status, not `completed`, payment_status=".$payment_status.", txnid=".$txn_id.", request=".$custom."')";
							try{
								$database->setQuery($sql);
								$database->execute();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "pp_purchase_uc_ipn", "", "");
								echo JText::_('RS1_SQL_ERROR');
							} 
					
							// send an email
							$message = "Payment Status, not `Completed`, payment_status=".$payment_status.", txnid=".$txn_id.", request=".$custom;
							try{
								$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
							}
							catch (Exception $e){
								logIt("Error on setting email TO address: ".$e->getMessage(), "pp_purchase_uc_ipn", "", "");
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
								logIt("Error on setting email FROM address: ".$e->getMessage(), "pp_purchase_uc_ipn", "", "");
								return false;		
							}
							if($apptpro_config->html_email == "Yes"){
								$mailer->IsHTML(true);
							}
							
						}
					} else {
						$sql = "insert into #__sv_apptpro3_errorlog (description) values('Duplicate transaction, txnid=".$txn_id.", request=".$custom."')";
						try{
							$database->setQuery($sql);
							$database->execute();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "pp_purchase_uc_ipn", "", "");
							echo JText::_('RS1_SQL_ERROR');
						} 

						// send an email
						$message = "Duplicate transaction, txnid=".$txn_id.", request=".$custom;
						try{
							$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
						}
						catch (Exception $e){
							logIt("Error on setting email TO address: ".$e->getMessage(), "pp_purchase_uc_ipn", "", "");
							return false;		
						}
						$mailer->setSubject("VERIFIED DUPLICATED TRANSACTION");
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
							logIt("Error on setting email FROM address: ".$e->getMessage(), "pp_purchase_uc_ipn", "", "");
							return false;		
						}
						if($apptpro_config->html_email == "Yes"){
							$mailer->IsHTML(true);
						}
					}
					
				
				} else if (strcmp ($res, "INVALID") == 0) {
					// if the IPN POST was 'INVALID'...do this
					// log for manual investigation			

					$sql = "insert into #__sv_apptpro3_errorlog (description) values('INVALID IPN, txnid=".$txn_id.", user=".$custom."')";
					try{
						$database->setQuery($sql);
						$database->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "pp_purchase_uc_ipn", "", "");
						echo JText::_('RS1_SQL_ERROR');
					} 

					$message = "INVALID IPN, txnid=".$txn_id.", request=".$custom;
					try{
						$mailer->addRecipient(explode(",", $apptpro_config->mailTO));
					}
					catch (Exception $e){
						logIt("Error on setting email TO address: ".$e->getMessage(), "pp_purchase_uc_ipn", "", "");
						return false;		
					}
					$mailer->setSubject("INVALID IPN");
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
						logIt("Error on setting email FROM address: ".$e->getMessage(), "pp_purchase_uc_ipn", "", "");
						return false;		
					}
					if($apptpro_config->html_email == "Yes"){
						$mailer->IsHTML(true);
					}
				} else {
					//logit($res);
				}
		}
		fclose ($fp);
	}
	exit;
?>
