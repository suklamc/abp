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
include_once( JPATH_SITE."/administrator/components/com_rsappt_pro3/functions_pro2.php" );


	header('Content-Type: text/html; charset=utf-8'); 
	header("Cache-Control: no-cache, must-revalidate");
	//A date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	
	$jinput = JFactory::getApplication()->input;

	// recives the user's selected resource and date
	$cancellation_id = $jinput->getString('cancellation_id');
	$browser = $jinput->getString('browser');
	$userDateTime = $jinput->getString('userDateTime');
	$cancel_reason = $jinput->getString('cancel_reason', "");
	$gdpr = $jinput->getString('gdpr', "No");
	
	// is cancellation_id valid
	$database = JFactory::getDBO(); 
	$sql = "SELECT * FROM #__sv_apptpro3_requests WHERE cancellation_id='".$database->escape($cancellation_id)."'";
	try{
		$database->setQuery($sql);
		$rows = $database->loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_cancel", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	if (sv_count_($rows) == 0){
		echo JText::_('RS1_INPUT_SCRN_CANCEL_CODE_INVALID');
		exit;
	}
	if (sv_count_($rows) > 1){
		echo 'Error: More that one booking with that code. Cannot cancel!';
		exit;
	}
	if($rows[0]->request_status == "canceled" && $gdpr != "Yes"){
		echo JText::_('RS1_INPUT_SCRN_ALREADY_CANCELED');
		exit;
	}

	// get config info
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_cancel", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// is it too close to cancel?
	// compare user's time (not server time) to booking time
	// local date/time as yyyy-mm-dd hh:mm:ss
	$sql = "SELECT DATE_SUB(CONCAT(startdate, ' ', starttime), INTERVAL ".$apptpro_config->hours_before_cancel." HOUR) AS cancel_limit FROM #__sv_apptpro3_requests WHERE cancellation_id='".$database->escape($cancellation_id)."'";
	try{
		$database->setQuery($sql);
		$cancel_limit = $database->loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_cancel", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	if(strtotime($cancel_limit) < strtotime($userDateTime) && $gdpr != "Yes"){
		// too late 
		$msg = buildMessage($rows[0]->id_requests, "booking_too_close_to_cancel", "No", "", "Yes");		
		echo $msg;
		exit;
	}

	// First delete calendar record for this request if one exists
	if($apptpro_config->which_calendar == "Google" and $rows[0]->google_event_id != ""){
		include_once( JPATH_SITE."/components/com_rsappt_pro3/svgcal.php" );
		$gcal = new SVGCal;
		// need resource info to get which Google calender login
		$database = JFactory::getDBO();
		$res_data = NULL;
		$sql = "SELECT * FROM #__sv_apptpro3_resources WHERE id_resources=".$rows[0]->resource;
		try{
			$database->setQuery($sql);
			$res_data = $database->loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_cancel", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		// login
		$result = $gcal->login($res_data);
		if( $result == "ok"){
			$client = $gcal->getClient();	
			if($rows[0]->google_calendar_id == ""){
				$gcal->deleteEventById($gcal->getClient(), $rows[0]->google_event_id);
			} else {
				$result = $gcal->deleteEvent($gcal->getClient(), $rows[0]->google_event_id, $rows[0]->google_calendar_id);
				if($result != "ok"){
					echo $result;
					logIt($result); 
				}
			}		
		} else {
			echo $result;
			logIt($result); 
		}										
	}	

	// zap it
	$sql = "UPDATE #__sv_apptpro3_requests SET request_status = 'canceled', ".
	"cancel_reason = '".$cancel_reason."', ".
	"admin_comment = CONCAT( admin_comment, ' *** Canceled by user ***') ". 
	"WHERE cancellation_id='".$database->escape($cancellation_id)."'";
	$database->setQuery($sql);
	if(!$database->execute()){
		logIt($e->getMessage(), "fe_cancel", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}

	$sql = "SELECT #__sv_apptpro3_resources.resource_admins, #__sv_apptpro3_resources.resource_email, #__sv_apptpro3_requests.* ".
		" FROM #__sv_apptpro3_requests INNER JOIN ".
		" #__sv_apptpro3_resources ".
		" ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources ".
		" WHERE #__sv_apptpro3_requests.cancellation_id = '".$database->escape($cancellation_id)."'";
	try{	
		$database->setQuery($sql);
		$req_detail = $database->loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_cancel", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	if($gdpr == "Yes"){
		// this cancel request came from the GDPR screen so we need to remove all personal data
		// First the core booking data
		$filler = JText::_('RS1_GDPR_FILLER');
		$sql = "UPDATE #__sv_apptpro3_requests SET ".
		"name = '".$filler."', ".
		"phone = '".$filler."', ".
		"email = '".$filler."' ".
		"WHERE cancellation_id='".$database->escape($cancellation_id)."'";		
		$database->setQuery($sql);
		if(!$database->execute()){
			logIt($e->getMessage(), "fe_cancel", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
		// Now all UDF data
		$sql = "UPDATE #__sv_apptpro3_udfvalues SET udf_value = '".$filler."' ".
		"WHERE request_id=".$req_detail->id_requests;		
		$database->setQuery($sql);
		if(!$database->execute()){
			logIt($e->getMessage(), "fe_cancel", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		
	}

	// adjust credit
	$user = JFactory::getUser();
	if($apptpro_config->allow_user_credit_refunds == "Yes" && $req_detail->credit_used > 0){
		$refund_amount =$req_detail->credit_used;
		if($req_detail->booking_total > 0 && $req_detail->payment_status == 'paid'){
			// part of booking was paid by paypal, need to add that back to user's credit total
			$refund_amount += $req_detail->booking_total;
		}				
		if($req_detail->gift_cert !=""){
			$sql = "UPDATE #__sv_apptpro3_user_credit SET balance = balance + ".$refund_amount." WHERE gift_cert = ".$req_detail->gift_cert;
		} else {
			$sql = "UPDATE #__sv_apptpro3_user_credit SET balance = balance + ".$refund_amount." WHERE user_id = ".$req_detail->user_id;
		}
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_cancel", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
		
		// set request.credit_used to -1 to indicate refunded and prevent multiple refunds if operator sets to canceled again.
		$sql = "UPDATE #__sv_apptpro3_requests SET credit_used = -1 WHERE id_requests = ".$req_detail->id_requests;
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_cancel", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
		
		// add credit audit
		$sql = 'INSERT INTO #__sv_apptpro3_user_credit_activity (user_id, request_id, gift_cert, increase, comment, operator_id, balance) '.
		"VALUES (".($req_detail->user_id==""?-2:$req_detail->user_id).",".
		$req_detail->id_requests.",".
		"'".$req_detail->gift_cert."',".
		$refund_amount.",".
		"'".JText::_('RS1_ADMIN_CREDIT_ACTIVITY_REFUND_ON_CANCEL')."',".
		$req_detail->user_id.",";
		 // fe-cancel is run by user
		if($req_detail->gift_cert !=""){
			$sql .= "(SELECT balance from #__sv_apptpro3_user_credit WHERE gift_cert = ".$req_detail->gift_cert."))";
		} else {
			$sql .= "(SELECT balance from #__sv_apptpro3_user_credit WHERE user_id = ".$req_detail->user_id."))";
		}
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_cancel", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
	}


	// tell admin
	$language = JFactory::getLanguage();
	$language->load('com_rsappt_pro3', JPATH_SITE, null, true);
	$subject = JText::_('RS1_CANCELLATION_EMAIL_SUBJECT');

	// Notify admin and/or resource
	sendMail($apptpro_config->mailTO, $subject, "cancellation", $req_detail->id_requests);			
	if($req_detail->resource_email != ""){
		sendMail($req_detail->resource_email, $subject, "cancellation", $req_detail->id_requests);	
	}

	// confirmation to customer
	if($req_detail->email != ""){
		sendMail($req_detail->email, $subject, "cancellation", $req_detail->id_requests);			
	}
	
	// SMS to resource
	$config = JFactory::getConfig();
	$tzoffset = $config->get('offset');      
	
	//$offsetdate = JFactory::getDate();
	//$offsetdate->setOffset($tzoffset);
	$tz = new DateTimeZone($tzoffset);
	$offsetdate = new JDate("now", $tz);
	
	$reminder_log_time_format = "Y-m-d H:i:s";
	$returnCode = "";
	sv_sendSMS($req_detail->id_requests, "cancellation", $returnCode, $toResource="Yes");			
	logReminder("User Cancellation of booking: ".$returnCode, $req_detail->id_requests, $user->id, "by user", $offsetdate->format($reminder_log_time_format, true, true));

//	$message = buildMessage($req_detail->id_requests, "cancellation", "No");
	$message = buildMessage($req_detail->id_requests, "cancellation", "No", "", "Yes");
	
	 

	echo strip_tags($message);
	exit;	
	

?>