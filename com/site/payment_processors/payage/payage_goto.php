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
	
	// get the payage account id from "payage~id"

	$account_id = substr($pay_proc_submit, strpos($pay_proc_submit, "~")+1);

	$sql = "SELECT * FROM #__payage_accounts ".
		" WHERE id = ".$account_id;
	try{
		$database->setQuery($sql);
		$payage_account = NULL;
		$payage_account = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "payage_goto", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}

	//print_r($payage_account);
	//exit;

	// Setup array for the payage call Get_payment_buttons()
	// The returend buttons will be used to autosubmit a form to call the approprite gateway
	
	$call_array = array();
	$call_array['currency'] = $payage_account->account_currency;
	$call_array['group'] = $payage_account->account_group;	
	$call_array['app_name'] = 'ABPro';
	if($cart != "Yes"){	
		$call_array['app_transaction_id'] = strval($request_id);
		$call_array['gross_amount'] = $payment_required;	
		//	$call_array['tax_amount'] = $tax_amount;
		$call_array['firstname'] = $jinput->getString('sp_first_name');
		$call_array['lastname'] = $jinput->getString('sp_last_name');
		$call_array['address1'] = $jinput->getString('sp_address');
		$call_array['city'] = $jinput->getString('sp_city');
		$call_array['state'] = $jinput->getString('sp_state');
		$call_array['zip_code'] = $jinput->getString('sp_zip');
		$call_array['country'] = $jinput->getString('sp_country');
		$call_array['email'] = $email;
	} else {
		$call_array['app_transaction_id'] = "cart|".$cart_row_ids;
		$call_array['gross_amount'] = $cart_total;

		// JED automated check will fail anything with base 64 encoding :-(
		// change 164 to 64 in the following line to make payage work
		$xsp =  base64_decode ($jinput->getString('xsp',''));
		$xsp_array = explode("|", $xsp);
		$call_array['firstname'] = $xsp_array[0];
		$call_array['lastname'] = $xsp_array[1];
		$call_array['address1'] = $xsp_array[2];
		$call_array['city'] = $xsp_array[3];
		$call_array['state'] = $xsp_array[4];
		$call_array['zip_code'] = $xsp_array[5];
		$call_array['country'] = $xsp_array[6];
		$call_array['email'] = $jinput->getString('email');
	}
	$call_array['app_return_url'] = JURI::root(true).'/index.php?option=com_rsappt_pro3&controller=sv_payage&task=payment_complete';
	$call_array['app_update_path'] = JURI::root(true).'/index.php?option=com_rsappt_pro3&controller=sv_payage&task=payment_update';

	// pull in the Payage api file
	require_once JPATH_ADMINISTRATOR.'/components/com_payage/api.php';

		if($cart == "Yes"){	
			// use generic item decription as there can be multiple different resources		
			$call_array['item_name'] = JText::_(trim($apptpro_config->cart_paypal_item));
		} else {
			$call_array['item_name'] = JText::_($res_detail->name).": ".$startdate." ".$starttime;
		}
		$return_array = PayageApi::Get_payment_buttons($call_array);

		if($return_array[0]['status'] != 0){
			logIt($return_array[0]['error'], "payage_goto", "", "");
			echo $return_array[0]['error'];
			exit;
		}
		
		// loop through buttons to find the type that matches the account for the button clicked by the user.
		foreach($return_array as $index => $return_item){
			if ($index == 0)	// skip the info element
				continue;

			if($return_item['type'] == $payage_account->gateway_shortname){
				break;	
			}
		}
		//print_r($return_item);
		//exit;	

		if($payage_account->gateway_shortname == "SagePay" || $payage_account->gateway_shortname == "Barclaycard" || $payage_account->gateway_shortname == "Skrill"){
			$fields_string = "";
			echo "<html><head></head><body>";
			echo JText::_('RS1_INPUT_SCRN_PLEASE_WAIT');
			echo "<div style=\"visibility:hidden\">";
			echo $return_item['button'];
			echo "</div>";
			echo "<script language='javascript' type='text/javascript'>";
			echo "document.forms[0].submit();";
			echo "</script>";
			echo "</body></html>";
			exit;	
		} elseif($payage_account->gateway_shortname == "PayPlug" || $payage_account->gateway_shortname == "Mollie" ) {
			// pull out the onsubmit url
			$temp = $return_item['button'];			
			$temp = substr($temp, strpos($temp,"window.location=")+17);
			$url = substr($temp, 0, strpos($temp, "'" )); 
			$url = str_replace("&amp;","&",$url);
			//echo $url;
			//exit;			
			$this->setRedirect($url);
		} elseif($payage_account->gateway_shortname == "Paypal") {
			echo "Payage PayPal is not supported, please use ABPro's native PayPal payment processor.";
			exit;
		} else {
			echo "oops, non-supported payment gateway.";
			exit;
		}
	
?>