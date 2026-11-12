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
	
		$sql = 'SELECT * FROM #__sv_apptpro3__2co_settings;';
		try{
			$database->setQuery($sql);
			$_2co_settings = NULL;
			$_2co_settings = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "pay_procs_goto", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}


		$lang = JFactory::getLanguage();
		$demo = "Y";
		if($_2co_settings->_2co_demo == "No"){
			$demo = "N";
		}

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
			$_2co_sid = $_2co_settings->_2co_account;
		} else {
			$_2co_sid = $res_paypal_account;
		}
		
		if($_2co_settings->_2co_item_name == ""){
			$dt = strtotime($startdate." ".$starttime);
			$str_dt = date("D M d Y, g:i", $dt);
			$itemname=JText::_($res_detail->name)." ".$str_dt;
		} else {
			$itemname = processTokens($request_id, JText::_($_2co_settings->_2co_item_name));
		}
		
		$_2co_url = "https://www.2checkout.com/checkout/purchase";		
		$_2co_url .= "?sid=".$_2co_sid.	
		"&mode=2CO".
		"&li_1_type=product".
		"&li_1_product_id=".strval($request_id).
		"&li_1_name=".$itemname.
		"&li_1_quantity=1".
		"&li_1_price=".$payment_required.
		"&li_1_tangible=N".
		"&demo=".$demo.
		"&fixed=Y".
		"&lang=".substr($lang->getTag(),0,2).
		"&merchant_order_id=".strval($request_id).
		"&pay_method=CC".
//		"&currency_vendor=".$_2co_settings->_2co_currency.  // currecny set in your 2CO account
		"&frompage=".$from_screen.
		"&fromitemid=".$frompage_item.
		"&x_receipt_link_url=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&controller=admin&task=ins");
	
		//echo $_2co_url;
		//exit;				
		header("Location: ".$_2co_url);
		exit;	
		
	} else {
		// cannot use resoucre specific account as there could by multiple different resoucres in a cart
		$_2co_sid = $_2co_settings->_2co_account;
				
		// When we return from PayPal the cart will be empty so we need to build the confirmatin message now and store in session
		include_once( JPATH_COMPONENT."/controllers/cart.php" );
		$mycartcontroller = new cartController;
		$session = JFactory::getSession();
		$sid = $session->getId();
		$msg_customer = $mycartcontroller->buildCartMessage($apptpro_config, null, "customer", $sid, "no");
		$session->set('confirmation_message',$msg_customer);
		$msg_cart_in_progress = $mycartcontroller->buildCartMessage($apptpro_config, null, "customer", $sid, "yes");
		$session->set('cart_in_progress_message',$msg_cart_in_progress);

		$_2co_url = "https://www.2checkout.com/checkout/purchase";		
		$_2co_url .= "?sid=".$_2co_sid.	
		"&mode=2CO".
		"&li_1_type=product".
		"&li_1_product_id=".strval($request_id);
		if($paypal_settings->paypal_itemname ==""){
			$_2co_url .= "&li_1_name=ABPro booking(s)";
		} else {
			$_2co_url .= "&li_1_name=".JText::_(trim($apptpro_config->cart_paypal_item));
		}		
		$_2co_url .="&li_1_quantity=1".
		"&li_1_price=".$cart_total.
		"&li_1_tangible=N".
		"&demo=".$demo.
		"&fixed=Y".
		"&lang=".substr($lang->getTag(),0,2).
		"&merchant_order_id=cart|".$cart_row_ids.
		"&pay_method=CC".
		"&currency_vendor=".$_2co_settings->_2co_currency.  // currecny set in your 2CO account
		"&frompage=".$frompage.
		"&fromitemid=".$frompage_item.
		"&x_receipt_link_url=".JURI::base().urlencode("index.php?option=com_rsappt_pro3&controller=admin&task=ins");
	
		//echo $_2co_url;
		//exit;				
		header("Location: ".$_2co_url);
		exit;		
	}

?>