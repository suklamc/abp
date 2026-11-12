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

	// get product details	
	$sql = "SELECT * FROM #__sv_apptpro3_products WHERE id_products = ".$pid;
	try{
		$database->setQuery($sql);
		$product = NULL;
		$product = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "pay_procs_goto", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}

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

			
	$paypal_account = $paypal_settings->paypal_account;
	
	$paypal_url = $paypal_url.'?cmd=_xclick&currency_code='.$paypal_settings->paypal_currency_code.
	"&business=" .$paypal_account;
	$mobile_url = $paypal_url;


	//PayPal will display in the language you have your account set to. 
	//If you want to switch PayPal language in the call you can un-comment the following lines and set the langauge appropriately.
	//The follwon show changing to Japanese
	//$paypal_url .= "&locale.x=ja_JP";
	//$paypal_url .= "&lc=JP";
	$paypal_url .= "&item_name=".JText::_($product->product_name);
	$mobile_url .= "&item_name=".JText::_($product->product_name);
	$paypal_url .= "&item_number=".JText::_($product->id_products);
	$mobile_url .= "&item_number=".JText::_($product->id_products);

	$paypal_url .= "&amount=".$product->product_price;
	$mobile_url .= "&amount=".$product->product_price;
	
	$paypal_url .= "&custom=".$uid;
	$mobile_url .= "&custom=".$uid;

	$paypal_url .= "&return=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&controller=purchase_uc&task=pp_return");
	
	$paypal_url .= "&notify_url=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&controller=purchase_uc&task=pp_ipn"). 
	"&charset=UTF-8";
	
	// mobile does not need 'return' as that comes from the mobile app.
	$mobile_url .= "&notify_url=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&controller=purchase_uc&task=pp_ipn"). 
	"&charset=UTF-8";


	/* The locale of the login or sign-up page, which may have the specific country's language available, depending on localization. 
	If unspecified, PayPal determines the locale by using a cookie in the subscriber's browser. 
	If there is no PayPal cookie, the default locale is US. */
	//$paypal_url .= "&lc=US”
	//$mobile_url .= "&lc=US”
	
	if($paypal_settings->paypal_logo_url != ""){
		$paypal_url .= "&image_url=".$paypal_settings->paypal_logo_url;
	}
	
	//echo $paypal_url;
	//exit;		

	if($mobile_order == "Yes"){
		return $mobile_url;
	} else {
		header("Location: ".$paypal_url);
		exit;	
	}
		
?>