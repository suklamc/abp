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

		$sql = 'SELECT * FROM #__sv_apptpro3_paypal_settings;';
		try{
			$database->setQuery($sql);
			$paypal_settings = NULL;
			$paypal_settings = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "pay_procs_goto", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}

		if($paypal_settings->paypal_use_sandbox == "Yes"){
			$paypal_url = $paypal_settings->paypal_sandbox_url; 
		} else {
			$paypal_url = $paypal_settings->paypal_production_url; 
		}
		$mobile_url = "";

		if($cart != "Yes"){		
			// check for request specific PayPal account 
			$database = JFactory::getDBO();
			$sql = "SELECT #__sv_apptpro3_resources.paypal_account FROM #__sv_apptpro3_requests ".
			"  INNER JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
			" WHERE #__sv_apptpro3_requests.id_requests = ".(int)$request_id;
			//echo $sql;
			//exit;
			try{
				$database->setQuery($sql);
				$res_paypal_account = $database->loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "functions2", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}				
			if($res_paypal_account == ""){
				$paypal_account = $paypal_settings->paypal_account;
			} else {
				$paypal_account = $res_paypal_account;
			}
			
			$paypal_url = $paypal_url.'?cmd=_xclick&currency_code='.$paypal_settings->paypal_currency_code.
			"&business=" .$paypal_account;
			$mobile_url = $paypal_url;

			$paypal_url .= "&return=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&view=".$from_screen."&Itemid=".$frompage_item."&task=pp_return&req_id=".$request_id);
			
			$paypal_url .= "&notify_url=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&view=admin&task=ipn"). 
			"&charset=UTF-8";
			
			// mobile does not need 'return' as that comes from the mobile app.
			$mobile_url .= "&notify_url=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&view=admin&task=ipn"). 
			"&charset=UTF-8";
	
	
			//PayPal will display in the language you have your account set to. 
			//If you want to switch PayPal language in the call you can un-comment the following lines and set the langauge appropriately.
			//The follwon show changing to Japanese
			//$paypal_url .= "&locale.x=ja_JP";
			//$paypal_url .= "&lc=JP";
			if($paypal_settings->paypal_itemname ==""){
				$paypal_url .= "&item_name=".JText::_($res_detail->description).": ".$startdate." ".$starttime;
				$mobile_url .= "&item_name=".JText::_($res_detail->description).": ".$startdate." ".$starttime;
			} else {
				$itemname = processTokens($request_id, JText::_($paypal_settings->paypal_itemname));
				$paypal_url .= "&item_name=".$itemname;
				$mobile_url .= "&item_name=".$itemname;
			}
			if($paypal_settings->paypal_on0 !="" && $paypal_settings->paypal_os0 !=""){
				$on0 = processTokens($request_id, JText::_($paypal_settings->paypal_on0));
				$os0 = processTokens($request_id, JText::_($paypal_settings->paypal_os0));
				$paypal_url .= "&on0=".$on0.
				"&os0=".$os0;
			}
			if($paypal_settings->paypal_on1 !="" && $paypal_settings->paypal_os1 !=""){
				$on1 = processTokens($request_id, JText::_($paypal_settings->paypal_on1));
				$os1 = processTokens($request_id, JText::_($paypal_settings->paypal_os1));
				$paypal_url .= "&on1=".$on1.
				"&os1=".$os1;
			}
			if($paypal_settings->paypal_on2 !="" && $paypal_settings->paypal_os2 !=""){
				$on2 = processTokens($request_id, JText::_($paypal_settings->paypal_on2));
				$os2 = processTokens($request_id, JText::_($paypal_settings->paypal_os2));
				$paypal_url .= "&on2=".$on2.
				"&os2=".$os2;
			}
			if($paypal_settings->paypal_on3 !="" && $paypal_settings->paypal_os3 !=""){
				$on3 = processTokens($request_id, JText::_($paypal_settings->paypal_on3));
				$os3 = processTokens($request_id, JText::_($paypal_settings->paypal_os3));
				$paypal_url .= "&on3=".$on3.
				"&os3=".$os3;
			}
			$paypal_url .= "&amount=".$payment_required.
			"&custom=".strval($request_id);
	
			$mobile_url .= "&amount=".$payment_required.
			"&custom=".strval($request_id);
	
			/* The locale of the login or sign-up page, which may have the specific country's language available, depending on localization. 
			If unspecified, PayPal determines the locale by using a cookie in the subscriber's browser. 
			If there is no PayPal cookie, the default locale is US. */
			//$paypal_url .= "&lc=US”
			//$mobile_url .= "&lc=US”
			
			if($paypal_settings->paypal_logo_url != ""){
				$paypal_url .= "&image_url=".$paypal_settings->paypal_logo_url;
			}
			
			// Templates can mess with the $this->baseurl property causing JURI::base() to return an empty string and PayPal return URLs to 
			// loose the site's domain. You can easily override the $this->baseurl inside your template by re-defining it again at the top 
			// of the index.php file of your template, like: $this->baseurl = JURI::base();
			// At the top of your template's index.php file, add the following
			//		$this->baseurl = JUri::base();

			
			//echo $paypal_url;
			//exit;		
	
			if($mobile_order == "Yes"){
				return $mobile_url;
			} else {
				header("Location: ".$paypal_url);
				exit;	
			}
		} else {		
			// cannot use resoucre specific PayPal account as there could by multiple different resoucres in a cart
			$paypal_account = $paypal_settings->paypal_account;
					
			// When we return from PayPal the cart will be empty so we need to build the confirmatin message now and store in session
			include_once( JPATH_COMPONENT."/controllers/cart.php" );
			$mycartcontroller = new cartController;
			$session = JFactory::getSession();
			$sid = $session->getId();
			$msg_customer = $mycartcontroller->buildCartMessage($apptpro_config, null, "customer", $sid, "no");
			$session->set('confirmation_message',$msg_customer);		
			$msg_cart_in_progress = $mycartcontroller->buildCartMessage($apptpro_config, null, "customer", $sid, "yes");
			$session->set('cart_in_progress_message',$msg_cart_in_progress);
	
			$paypal_url = $paypal_url.'?cmd=_xclick&currency_code='.$paypal_settings->paypal_currency_code.
			"&business=" .$paypal_account.
			"&return=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&view=".$frompage."&Itemid=".$frompage_item."&task=pp_return_cart"). 
			"&notify_url=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&controller=admin&task=ipn"). 
			"&charset=UTF-8";
			//PayPal will display in the language you have your account set to. 
			//If you want to switch PayPal language in the call you can un-comment the following lines and set the langauge appropriately.
			//The following shows changing to Japanese
			//$paypal_url .= "&locale.x=ja_JP";
			//$paypal_url .= "&lc=JP";
			if($paypal_settings->paypal_itemname ==""){
				$paypal_url .= "&item_name=ABPro booking(s)";
			} else {
				$paypal_url .= "&item_name=".JText::_(trim($apptpro_config->cart_paypal_item));
			}
	// Cart covers multipl bookings, cannot use tokens		
	//		if($apptpro_config->paypal_on0 !="" && $apptpro_config->paypal_os0 !=""){
	//			$on0 = processTokens($request_id, JText::_($apptpro_config->paypal_on0));
	//			$os0 = processTokens($request_id, JText::_($apptpro_config->paypal_os0));
	//			$paypal_url .= "&on0=".$on0.
	//			"&os0=".$os0;
	//		}
	//		if($apptpro_config->paypal_on1 !="" && $apptpro_config->paypal_os1 !=""){
	//			$on1 = processTokens($request_id, JText::_($apptpro_config->paypal_on1));
	//			$os1 = processTokens($request_id, JText::_($apptpro_config->paypal_os1));
	//			$paypal_url .= "&on1=".$on1.
	//			"&os1=".$os1;
	//		}
	//		if($apptpro_config->paypal_on2 !="" && $apptpro_config->paypal_os2 !=""){
	//			$on2 = processTokens($request_id, JText::_($apptpro_config->paypal_on2));
	//			$os2 = processTokens($request_id, JText::_($apptpro_config->paypal_os2));
	//			$paypal_url .= "&on2=".$on2.
	//			"&os2=".$os2;
	//		}
	//		if($apptpro_config->paypal_on3 !="" && $apptpro_config->paypal_os3 !=""){
	//			$on3 = processTokens($request_id, JText::_($apptpro_config->paypal_on3));
	//			$os3 = processTokens($request_id, JText::_($apptpro_config->paypal_os3));
	//			$paypal_url .= "&on3=".$on3.
	//			"&os3=".$os3;
	//		}
			$paypal_url .= "&amount=".$cart_total.
			"&custom=cart|".$cart_row_ids."";
			if($paypal_settings->paypal_logo_url != ""){
				$paypal_url .= "&image_url=".$paypal_settings->paypal_logo_url;
			}
			//echo $paypal_url;
			//exit;				
			header("Location: ".$paypal_url);
			exit;	
		}
?>