<?php
/*
 ****************************************************************
 Copyright (C) 2008-2020 Soft Ventures, Inc. All rights reserved.
 ****************************************************************
 * @package	Appointment Booking Pro - ABPro
 * @copyright	Copyright (C) 2008-2020 Soft Ventures, Inc. All rights reserved.
 * @license	GNU/GPL, see http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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
include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

if(file_exists(JPATH_SITE."/twilio-php-master/src/Twilio/autoload.php")) {
	require_once JPATH_SITE."/twilio-php-master/src/Twilio/autoload.php";
}
	use Twilio\Rest\Client;	


function buildMessage($request_id, $msg_type, $paypal, $cc="", $onscreen="No", $returnArray="No"){

	$messages_to_use = NULL;
	$mail_id = 1; //(global)

	// if cc is passed in, verify it before continuing.
	if($cc != ""){
		if(check_cc($cc, $request_id) == false){
			echo "Bad cc, No Access";
			exit;
		}
	}
	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$res_request_config = NULL;
		$res_request_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	if($request_id != ""){
		// check request language
		$lang = JFactory::getLanguage();
		$langTag =  $lang->getTag();
		if($langTag == ""){
			$langTag = "en_GB";
		}			
		$sql = 'SELECT booking_language FROM #__sv_apptpro3_requests WHERE id_requests = '.$request_id;
		try{
			$database->setQuery($sql);
			$request_lang = NULL;
			$request_lang = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		if($request_lang != "" && $langTag != $request_lang){
			// Change to language used by customer at time of booking
			$langTag = $request_lang;	
			$language = JFactory::getLanguage();
			$language->load('com_rsappt_pro3', JPATH_SITE, $request_lang, true);
		}
		$sql = "SET NAMES 'utf8';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		}		
		$sql = "SET lc_time_names = '".str_replace("-", "_",$langTag)."';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		if($res_request_config->timeFormat == '12'){
			$sql = 'SELECT *, DATE_FORMAT(startdate, "'.php_date_string_to_sql($res_request_config->long_date_format,"MySQL").'") as startdate, '.
			'DATE_FORMAT(starttime, "%l:%i %p") as starttime, '.
			'DATE_FORMAT(enddate, "%W %M %e, %Y") as enddate, '.
			'DATE_FORMAT(endtime, "%l:%i %p") as endtime FROM #__sv_apptpro3_requests WHERE id_requests = '.$request_id;
		} else {
			$sql = 'SELECT *, DATE_FORMAT(startdate, "'.php_date_string_to_sql($res_request_config->long_date_format,"MySQL").'") as startdate, '.
			'DATE_FORMAT(starttime, "%H:%i") as starttime, '.
			'DATE_FORMAT(enddate, "%W %M %e, %Y") as enddate, '.
			'DATE_FORMAT(endtime, "%H:%i") as endtime FROM #__sv_apptpro3_requests WHERE id_requests = '.$request_id;
		}
		try{
			$database->setQuery($sql);
			$request_details = NULL;
			$request_details = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		
		// get resource stuff
		$sql = 'SELECT * FROM #__sv_apptpro3_resources WHERE id_resources = '.$request_details->resource;
		try{
			$database->setQuery($sql);
			$resource_details = NULL;
			$resource_details = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		// get resource service 
		$resource_service = NULL;
		if($request_details->service !=""){
			$sql = 'SELECT * FROM #__sv_apptpro3_services WHERE id_services = '.$request_details->service;
			try{
				$database->setQuery($sql);
				$resource_service = NULL;
				$resource_service = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_sendmail", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
		}
		
		// get request category
		$resource_category = NULL;
		if($request_details->category != "" ){
			$sql = 'SELECT * FROM #__sv_apptpro3_categories WHERE id_categories = '.$request_details->category;
			try{
				$database->setQuery($sql);
				$resource_category = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_sendmail", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
		}

		// message center
		// First determine if category or resource messages are defined.
		// Precedence is resource over category
		// Is there a resource level messge defined
		if($resource_details->mail_id == 1 || $resource_details->mail_id == NULL){
			// no resource level message defined (1 = global)
			if($resource_category != NULL && $resource_category->mail_id > 1){
				// a categoey level message is defined, use that
				$mail_id = $resource_category->mail_id;
			}
		} else {
			// a resource level message has been defined, this take top precenence
			$mail_id = $resource_details->mail_id;
		}
				
	} 

	$sql = 'SELECT * FROM #__sv_apptpro3_mail WHERE id_mail = '.$mail_id;
	try{
		$database->setQuery($sql);
		$messages_to_use = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	//print_r($messages_to_use);			
	$msg_text = "";
	
	if($msg_type == "confirmation" or $msg_type == "calendar_body" ){
		if($paypal == "Yes"){
			$msg_text = JText::_(clean_svkey($messages_to_use->booking_succeeded));		
		} else {
			if($msg_type == "calendar_body"){
				$msg_text = JText::_(clean_svkey($res_request_config->calendar_body2));
			} else {
				$msg_text = JText::_(clean_svkey($messages_to_use->booking_succeeded));
			}
		}

	} else if($msg_type == "confirmation_admin"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_succeeded_admin));

	} else if($msg_type == "cancellation"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_cancel));

	} else if($msg_type == "reminder"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_reminder));
		
	} else if($msg_type == "sms_confirmation"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_succeeded_sms));

	} else if($msg_type == "sms_confirmation_customer"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_succeeded_sms_customer));

	} else if($msg_type == "sms_reminder"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_reminder_sms));

	} else if($msg_type == "sms_cancellation"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_cancel_sms));

	} else if($msg_type == "sms_in_progress"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_in_progress_sms));

	} else if($msg_type == "in_progress_admin"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_in_progress_admin));

	} else if($msg_type == "in_progress"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_in_progress));

	} else if($msg_type == "cart_msg_confirm"){
		$msg_text = JText::_(clean_svkey($res_request_config->cart_msg_confirm));

	} else if($msg_type == "cart_msg_inprogress"){
		$msg_text = JText::_(clean_svkey($res_request_config->cart_msg_inprogress));

	} else if($msg_type == "thankyou"){
		$msg_text = JText::_(clean_svkey($messages_to_use->thank_you_msg));

	} else if($msg_type == "notification"){
		$msg_text = JText::_(clean_svkey($messages_to_use->notification_msg));

	} else if($msg_type == "booking_too_close_to_cancel"){
		$msg_text = JText::_(clean_svkey($messages_to_use->booking_too_close_to_cancel));
	}
	
//  onContentPrepare can cause problems if you are using ABPro cron modules as they are not run in the context of the component	
//	$msg_text = processTokens($request_id, runJoomlaContentPlugins($msg_text));
	$msg_text = processTokens($request_id, $msg_text);

	if($onscreen == "Yes"){
		$msg_text = prep_for_screen($msg_text);
	}

	if($onscreen == "No"){
		$msg_text = prep_for_email($msg_text);
	}
	
	if($returnArray == "Yes"){
		return array($msg_text, $messages_to_use->confirmation_attachment); 
	} else	{
		return($msg_text);		
	}

}

function processTokens($request_id, $msg_text){
	$database = JFactory::getDBO();
	$request_details = NULL;
	$resource_details = NULL;

	$sql = 'SELECT * FROM #__sv_apptpro3_tokens';
	try{
		$database->setQuery($sql);
		$tokens = NULL;
		$tokens = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	
	// get config stuff
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$res_request_config = NULL;
		$res_request_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	
	if($request_id != ""){

		ini_set ( "default_charset", "utf-8" ); 

		// get request details	
		$lang = JFactory::getLanguage();
		$langTag =  $lang->getTag();
		if($langTag == ""){
			$langTag = "en_GB";
		}
		$sql = "SET NAMES 'utf8';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		}		
		$sql = "SET lc_time_names = '".str_replace("-", "_",$langTag)."';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		}		

		if($res_request_config->timeFormat == '12'){
			$sql = 'SELECT *, DATE_FORMAT(startdate, "'.php_date_string_to_sql($res_request_config->long_date_format,"MySQL").'") as startdate, '.
				'DATE_FORMAT(starttime, "%l:%i %p") as starttime, '.
				'DATE_FORMAT(enddate,"'.php_date_string_to_sql($res_request_config->long_date_format,"MySQL").'") as enddate, '.
				'DATE_FORMAT(endtime, "%l:%i %p") as endtime FROM #__sv_apptpro3_requests WHERE id_requests = '.$request_id;
		} else {
			$sql = 'SELECT *, DATE_FORMAT(startdate, "'.php_date_string_to_sql($res_request_config->long_date_format,"MySQL").'") as startdate, '.
				'DATE_FORMAT(starttime, "%H:%i") as starttime, '.
				'DATE_FORMAT(enddate, "'.php_date_string_to_sql($res_request_config->long_date_format,"MySQL").'") as enddate, '.
				'DATE_FORMAT(endtime, "%H:%i") as endtime FROM #__sv_apptpro3_requests WHERE id_requests = '.$request_id;
		}

		try{
			$database->setQuery($sql);
			$request_details = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		// get resource stuff
		$sql = 'SELECT * FROM #__sv_apptpro3_resources WHERE id_resources = '.$request_details->resource;
		try{
			$database->setQuery($sql);
			$resource_details = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		
		// get resource service 
		$resource_service = NULL;
		if($request_details->service !=""){
			$sql = 'SELECT * FROM #__sv_apptpro3_services WHERE id_services = '.$request_details->service;
			try{
				$database->setQuery($sql);
				$resource_service = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_sendmail", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
		} else {
			// no services, remove the resouce_service token...
			$msg_text = str_replace("[resource_service]", "", $msg_text);
		}

		// get resource category
		$resource_category = NULL;
		if($request_details->category != "" ){
			$sql = 'SELECT * FROM #__sv_apptpro3_categories WHERE id_categories = '.$request_details->category;
			try{
				$database->setQuery($sql);
				$resource_category = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "be_sendmail", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}		
		} else {
			// no category, remove the resouce_category token...
			$msg_text = str_replace("[resource_category]", "", $msg_text);
		}	

	} 

		
	$token = "";
	for($i=0; $i < sv_count_($tokens ); $i++) {
		$token = $tokens[$i];
		if($token->db_table == "resources"){	
			$field = $token->db_field;
			$field = "return($"."resource_details->".$field.");";
			//echo $field;
			$msg_text = str_replace($token->token_text, JText::_(eval($field)), $msg_text);
		} else if($token->db_table == "config"){	
			$field = $token->db_field;
			$field = "return($"."res_request_config->".$field.");";
			//echo $field;
			$msg_text = str_replace($token->token_text, JText::_(eval($field)), $msg_text);
		} else if($token->db_table == "services" && $resource_service != null){	
			$field = $token->db_field;
			$field = "return($"."resource_service->".$field.");";
			//echo $field;
			$msg_text = str_replace($token->token_text, JText::_(eval($field)), $msg_text);
		} else if($token->db_table == "categories" && $resource_category != null){	
			$field = $token->db_field;
			$field = "return($"."resource_category->".$field.");";
			//echo $field;
			$msg_text = str_replace($token->token_text, JText::_(eval($field)), $msg_text);
		} else if($token->db_table == "users"){	
			if($request_details->user_id != ""){
				$user = JFactory::getUser($request_details->user_id);
				$field = $token->db_field;			
				$field = "return($"."user->".$field.");";
				$msg_text = str_replace($token->token_text, JText::_(eval($field)), $msg_text);
			}
		} else {
			$field = $token->db_field;
			$field = "return($"."request_details->".$field.");";
			$msg_text = str_replace($token->token_text, JText::_(eval($field)), $msg_text);
		}

	} 
	
	if($request_id != ""){
		
		// do content udfs that are set to not show on screen
		$sql = "SELECT * FROM #__sv_apptpro3_udfs ".
			" WHERE udf_show_on_screen = 'No' AND published=1 ".
			" AND (scope = '' OR scope LIKE '%|".$request_details->resource."|%')".
			" ORDER BY ordering";
	    try{
			$database->setQuery($sql);
			$udfs_hidden = NULL;
			$udfs_hidden = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		if(sv_count_($udfs_hidden)>0){	
			foreach($udfs_hidden as $udf_hidden){
				$msg_text = str_replace("[".$udf_hidden->udf_label."]", JText::_($udf_hidden->udf_content), $msg_text);	
			}	
		}
		
		
		// get udfs and values
		$sql = "SELECT ".
			"#__sv_apptpro3_udfs.udf_label, #__sv_apptpro3_udfvalues.udf_value, ".
			"#__sv_apptpro3_udfvalues.request_id ".
			"FROM ".
			"#__sv_apptpro3_udfs LEFT JOIN ".
			"#__sv_apptpro3_udfvalues ON #__sv_apptpro3_udfvalues.udf_id = ".
			"#__sv_apptpro3_udfs.id_udfs ".
			"AND ".
			"#__sv_apptpro3_udfvalues.request_id = ".$request_id;
	  	try{
			$database->setQuery($sql);
			$udfs = NULL;
			$udfs = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		if(sv_count_($udfs)>0){	
			foreach($udfs as $udf){
				$msg_text = str_replace("[".$udf->udf_label."]", JText::_($udf->udf_value), $msg_text);	
			}	
		}
				
		// get seat_type and values
		$sql = "SELECT ".
			"seat_type_label, id_seat_types ".
			" FROM #__sv_apptpro3_seat_types WHERE published=1";
		try{
			$database->setQuery($sql);
			$seat_types = NULL;
			$seat_types = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		if(sv_count_($seat_types)>0){	
			foreach($seat_types as $seat_type){
			
				$sql = "SELECT seat_type_qty FROM #__sv_apptpro3_seat_counts ".
					" WHERE ".
					"request_id = ".$request_id." AND ".
					"seat_type_id = ".$seat_type->id_seat_types;		
				try{
					$database->setQuery($sql);
					$seats = NULL;
					$seats = $database -> loadObject();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_sendmail", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}		
				if($seats == NULL){
					$seat_count = 0;
				} else {
					$seat_count = $seats->seat_type_qty;
				}
				$msg_text = str_replace("[".$seat_type->seat_type_label."]", $seat_count, $msg_text);	
			}
	
		}

		// get extras and values
		$sql = "SELECT ".
			"extras_label, id_extras ".
			" FROM #__sv_apptpro3_extras WHERE published=1";
		try{
			$database->setQuery($sql);
			$extras = NULL;
			$extras = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		if(sv_count_($extras)>0){	
			$extras_total = 0;			
			foreach($extras as $extra){			
				$sql = "SELECT  #__sv_apptpro3_extras_data.id, #__sv_apptpro3_extras_data.extras_qty,".
					"#__sv_apptpro3_extras.extras_cost, #__sv_apptpro3_extras.cost_unit ".
					" FROM #__sv_apptpro3_extras_data INNER JOIN #__sv_apptpro3_extras ".
					" ON #__sv_apptpro3_extras.id_extras = #__sv_apptpro3_extras_data.extras_id ".
					" WHERE ".
					"request_id = ".$request_id." AND ".
					"extras_id = ".$extra->id_extras;	
				//echo $sql;		
				try{		
					$database->setQuery($sql);
					$extra_item = NULL;
					$extra_item = $database -> loadObject();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "be_sendmail", "", "");
					echo JText::_('RS1_SQL_ERROR').$e->getMessage();
					exit;
				}		
				//print_r($extra_item);
				if($extra_item == NULL){
					$extra_item_qty = 0;
				} else {
					$extra_item_qty = $extra_item->extras_qty;
					// update total
					if($extra_item->cost_unit == "Flat"){
						// flat rate
						$extras_total += (intval($extra_item->extras_qty) * $extra_item->extras_cost);
					} else {
						// per hour
						// need to calculate booing duration
						$firstTime=strtotime($request_details->starttime);
						$lastTime=strtotime($request_details->endtime);
						$duration = ($lastTime-$firstTime)/3600;  // seconds to hours
						$extras_total += (intval($extra_item->extras_qty) * $extra_item->extras_cost) * $duration;
					}
				}
				// If you want checkbox Extras to display text rather than 0/1 uncomment the following lines'		
//				if($extra_item_qty == "0"){
//					$msg_text = str_replace("[".$extra->extras_label."]", "NO", $msg_text);	
//				} else if ($extra_item_qty == "1"){
//					$msg_text = str_replace("[".$extra->extras_label."]", "YES", $msg_text);	
//				}

				$msg_text = str_replace("[".$extra->extras_label."]", $extra_item_qty, $msg_text);	
			}
			$msg_text = str_replace("[extras_total]", round($extras_total,2), $msg_text);
		}	
	}	

	// Token for todays date [today] 
	$lang = JFactory::getLanguage();
	setlocale(LC_TIME, str_replace("-", "_", $lang->getTag())); 
	$display_today = date("l F j, Y");
	$msg_text = str_replace("[today]", $display_today, $msg_text);	

	return $msg_text;
}

function sendMail($to, $subject, $type, $request_id, $cc="", $ics=""){
	
	if($to == ""){
		// no email address
		return true;
	}
	
	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	$mailer = JFactory::getMailer();

	try{
		$mailer->setSender(array($apptpro_config->mailFROM,null));
	}
	catch (Exception $e){
		logIt("Error on setting email FROM address: ".$e->getMessage(), "be_sendmail", "", "");
		return false;		
	}

	if($cc != ""){
		$mailer->addBCC($cc);
	}
	
	if($apptpro_config->html_email == "Yes"){
		$mailer->IsHTML(true);
	}
	$message = "";
	$message_attachment = "";
	switch ($type) {
		case 'confirmation': 
			$temp = buildMessage($request_id, "confirmation", "No", "", "No", "Yes");
			$message .= $temp[0];
			if($temp[1] != ""){
				$message_attachment = JPATH_BASE.$temp[1];
			}				
			//logIt($temp[1], "be_sendmail", "", "");							
		break;
		case 'confirmation_admin': 
			$message .= buildMessage($request_id, "confirmation_admin", "No");			
		break;
		case 'reminder': 
			$message .= buildMessage($request_id, "reminder", "No");			
		break;
		case 'cancellation': 
			$message .= buildMessage($request_id, "cancellation", "No");			
		break;
		case 'thankyou': 
			$message .= buildMessage($request_id, "thankyou", "No");			
		break;
		case 'notification': 
			$message .= buildMessage($request_id, "notification", "No");			
		break;
	}
	
	$message = stripslashes($message);
		
	if($apptpro_config->html_email != "Yes"){
		$message = str_replace("<br>", "\r\n", $message);			
	}

	if($ics == "Yes"){
		$array = array($request_id);
		$ics_file = buildICSfile($array);
		$mailer->AddStringAttachment($ics_file, "appointment_".strval($request_id).".ics");
	}

	// dev only
	ini_set ( "SMTP", "shawmail.cg.shawcable.net" ); 

	if($message_attachment != ""){
		$mailer->addAttachment($message_attachment);
	}
	
	try{
		$mailer->addRecipient(explode(",", $to));		
	}
	catch (Exception $e){
		logIt("Error on setting email TO address: ".$e->getMessage(), "be_sendmail", "", "");
		return false;		
	}
	$mailer->setSubject($subject);		
	$mailer->setBody($message);
	if($mailer->send() != true){
		logIt("Error sending email: ".$mailer->ErrorInfo);
		return false;
	} else {
		return true;
	}

}

function sv_sendSMS($request_id, $type, &$returnCode, $toResource="No"){

	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	if($apptpro_config->enable_clickatell == "No" && $apptpro_config->enable_eztexting == "No" && $apptpro_config->enable_twilio == "No"){
		return false;
	}
	
	// get request deatils, c/w resource sms_phone
	$sql = "SELECT #__sv_apptpro3_requests.*, #__sv_apptpro3_resources.sms_phone as resource_sms_phone ". 
		" FROM #__sv_apptpro3_requests LEFT JOIN #__sv_apptpro3_resources ON ".
		" #__sv_apptpro3_requests.resource =	#__sv_apptpro3_resources.id_resources ".
		" WHERE #__sv_apptpro3_requests.id_requests = ".$request_id;
	try{
		$database->setQuery($sql);
		$request = NULL;
		$request = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	
	$message = "";
	$message_customer = "";
	switch ($type) {
		case 'confirmation': 
			$message .= buildMessage($request_id, "sms_confirmation", "No");			
			$message_customer .= buildMessage($request_id, "sms_confirmation_customer", "No");						
		break;
		case 'reminder': 
			$message .= buildMessage($request_id, "sms_reminder", "No");			
		break;
		case 'cancellation': 
			$message .= buildMessage($request_id, "sms_cancellation", "No");			
		break;
		case 'in_progress': 
			$message .= buildMessage($request_id, "sms_in_progress", "No");			
		break;
	}
	$message = stripslashes($message);
	if($apptpro_config->clickatell_enable_unicode == "No" && $apptpro_config->enable_clickatell == "Yes"){
		$message = str_replace(" ", "+", $message);	
	}
	$message = strip_tags($message);	
	if($message == ""){
		$message = "No message configured";
	}

	if($apptpro_config->enable_clickatell == "Yes"){
		// build string for Clickatell
		if($apptpro_config->clickatell_user == ""){
			$returnCode = "Clickatell login information missing (user)";
			return false;
		}
		if($apptpro_config->clickatell_password == ""){
			$returnCode = "Clickatell login information missing (password)";
			return false;
		}
		if($apptpro_config->clickatell_api_id == ""){
			$returnCode = "Clickatell login information missing (api_id)";
			return false;
		}
	}

	if($apptpro_config->enable_eztexting == "Yes"){
		// build string for EzTexting
		if($apptpro_config->eztexting_user == ""){
			$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_NO_USERNAME');
			return false;
		}
		if($apptpro_config->eztexting_password == ""){
			$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_NO_PASSWORD');
			return false;
		}
	}
	
	if($apptpro_config->enable_twilio == "Yes"){
		// Check for Twilio data setup
		if($apptpro_config->twilio_sid == ""){
			$returnCode = JText::_('RS1_TWILIO_ERR_NO_SID');
			return false;
		}
		if($apptpro_config->twilio_token == ""){
			$returnCode = JText::_('RS1_TWILIO_ERR_NO_TOKEN');
			return false;
		}
		if($apptpro_config->twilio_phone == ""){
			$returnCode = JText::_('RS1_TWILIO_ERR_NO_PHONE');
			return false;
		}
	}

	if($toResource == "No"){
		// going to user
		if($request->sms_reminders == "Yes"){
			// only send if user wants sms
			$sms_phone = $request->sms_phone;
			if($type == 'confirmation'){
				$message = $message_customer;
			}
			if($sms_phone == ""){
				$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_NO_USER_PHONE');
				return false;
			}
		} else {
			$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_USER_NO_SMS');
			return false;
		}
	} else {
		$sms_phone = $request->resource_sms_phone;
		if($sms_phone == ""){
			$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_NO_RES_PHONE');
			return false;
		}
	}

	$sms_phone = str_replace("-", "", $sms_phone);	
	$sms_phone = str_replace("+", "", $sms_phone);	
	$sms_phone = str_replace(" ", "", $sms_phone);	
	// to strip leading 0 uncomment the line below
	//$sms_phone = ltrim($sms_phone, '0');     
	if(strlen($sms_phone)>$apptpro_config->sms_phone_size){
		$sms_phone = substr($sms_phone, strlen($sms_phone)-$apptpro_config->sms_phone_size );
	}
	
	
	//*************** Clickatell.com ******************
	if($apptpro_config->enable_clickatell == "Yes"){
		
		// new Clickatell integration uses curl			
		if($toResource == "No"){
			if($request->sms_dial_code == ""){
				$to=$apptpro_config->clickatell_dialing_code.$sms_phone;
			} else {
				$to=$request->sms_dial_code.$sms_phone;
			}
		} else {
				$to=$apptpro_config->clickatell_dialing_code.$sms_phone;
				// If you have resources with different country codes, uncomment the line below and include the resource's
				// country code in their sms phone setting in the resource setup screen. 
				//$to=$sms_phone;
		}
		$baseurl ="https://platform.clickatell.com/messages/http/";
	
		$url =  $baseurl."send?apiKey=".$apptpro_config->clickatell_api_id;
				
		if($apptpro_config->clickatell_enable_unicode == "No"){
			$url = $url."&to=".$to."&content=".$message;
		} else {
			$url = $url."&to=".$to."&content=".utf16urlencode($message);
		}
		//logIt($url, "be_sendmail", "", "");
		//echo $url;
		//echo "<BR/>";
		// do sendmsg call
		
		$ch = curl_init();	
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		
		$returnCode = curl_exec($ch);
		
		// close cURL resource, and free up system resources
		curl_close($ch);			
		//logIt($returnCode, "be_sendmail", "", "");	

	}
	
	//*************** EzTexting.com ******************
	if($apptpro_config->enable_eztexting == "Yes"){
		$ch=curl_init('https://app.eztexting.com/api/sending');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,"user=".$apptpro_config->eztexting_user.
					"&pass=".trim(encrypt_decrypt('decrypt', $apptpro_config->eztexting_password)).
					"&phonenumber=".$sms_phone.
					"&message=".$message.
					"&express=1");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$data = curl_exec($ch);
		//print($data); /* result of API call*/
		switch ($data) {
		    case 1:
				$returnCode = JText::_('RS1_EZTEXTING_CODE_1');
				break;
			case -1:
				$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_1');
				break;
			case -2:
				$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_2');
				break;
			case -5:
				$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_5');
				break;
			case -7:
				$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_7');
				break;
			case -104:
				$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_104');
				break;
			case -106:
				$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_106');
				break;
			case -10:
				$returnCode = JText::_('RS1_EZTEXTING_CODE_ERR_10');
				break;
		}
		if($data == 1){
			return true;	
		} else {
			return false;	
		}
	}

	//*************** Twilio.com ******************
	if($apptpro_config->enable_twilio == "Yes"){
		// mod to work with new Twilio library courtesy of Timothy Bowman
		if($toResource == "No"){
			if($request->sms_dial_code == ""){
				$to=$apptpro_config->clickatell_dialing_code.$sms_phone;
			} else {
				$to=$request->sms_dial_code.$sms_phone;
			}
		} else {
				$to=$apptpro_config->clickatell_dialing_code.$sms_phone;
				// If you have resources with different country codes, uncomment the line below and include the resource's
				// country code in their sms phone setting in the resource setup screen. 
				//$to=$sms_phone;
		}
		$TwilioVer = 0;
		if (file_exists(JPATH_SITE."/twilio-php/Services/Twilio.php")) {
			require_once JPATH_SITE."/twilio-php/Services/Twilio.php";
			$TwilioVer = 1;
		}
		if (file_exists(JPATH_SITE."/twilio-php-master/src/Twilio/autoload.php")){
			require_once JPATH_SITE."/twilio-php-master/src/Twilio/autoload.php";
			$TwilioVer = 2;
		}
		if (file_exists(JPATH_SITE."/vendor/autoload.php")){
			require_once JPATH_SITE."/vendor/autoload.php";
			$TwilioVer = 3;
		}
		if ($TwilioVer == 1) {
			// Your Account Sid and Auth Token from twilio.com/user/account
			$sid = $apptpro_config->twilio_sid;
			$token = $apptpro_config->twilio_token;
			$client = new Services_Twilio($sid, $token);
			//echo $apptpro_config->twilio_sid."<br/>".$apptpro_config->twilio_token."<br/>".$apptpro_config->twilio_phone."<br/>".$to."<br/>".$message;
			//exit;
			
			// Added + for non-North American numbers
			$sms = $client->account->sms_messages->create($apptpro_config->twilio_phone, "+".$to, $message, array());
			$returnCode = $sms->sid;
		}
		if ($TwilioVer == 2 or $TwilioVer == 3) {
			// Your Account Sid and Auth Token from twilio.com/user/account
			$sid = $apptpro_config->twilio_sid;
			$token = $apptpro_config->twilio_token;
			//$client = new Twilio\Rest\Client($sid, $token);
			$client = new Client($sid, $token);

			//echo $apptpro_config->twilio_sid."<br/>".$apptpro_config->twilio_token."<br/>".$apptpro_config->twilio_phone."<br/>".$to."<br/>".$message;
			//exit;
			
			// Added + for non-North American numbers
			$sms = $client->messages->create("+".$to, array('from' => $apptpro_config->twilio_phone,'body' => $message));

			$returnCode = $sms->sid;
		}
		if ($TwilioVer == 0) {
			$returnCode = "Twilio Library not installed, for more information read the online help document";
		}
		If ($TwilioVer > 3) {
			$returnCode= "Unhandled Error in Twilio Library";
		}
		return true;
	}


//		if($request->sms_dial_code == ""){
//			$to=$apptpro_config->clickatell_dialing_code.$sms_phone;
//		} else {
//			$to=$request->sms_dial_code.$sms_phone;
//		}
//
//		require_once JPATH_SITE."/twilio-php/Services/Twilio.php"; 
//
//		// Your Account Sid and Auth Token from twilio.com/user/account
//		$sid = $apptpro_config->twilio_sid; 
//		$token = $apptpro_config->twilio_token; 
//		$client = new Services_Twilio($sid, $token);
//		//echo $apptpro_config->twilio_sid."<br/>".$apptpro_config->twilio_token."<br/>".$apptpro_config->twilio_phone."<br/>".$to."<br/>".$message;
//		//exit;
////		$sms = $client->account->sms_messages->create($apptpro_config->twilio_phone, $to, $message, array());
//
//		// Added + for non-North American numbers
//		$sms = $client->account->sms_messages->create($apptpro_config->twilio_phone, "+".$to, $message, array());
//		$returnCode = $sms->sid;
//		
//		return true;
//	}

}

function logReminder($desc, $request_id=-1, $user_id=-1, $name="", $local_time=""){
	$database = JFactory::getDBO();
	$errsql = "insert into #__sv_apptpro3_reminderlog (request_id, user_id, name, description, local_time) ".
		" values(".
		$request_id.",".
		$user_id.",".
		"'".$database->escape($name)."',".
		"'".$database->escape($desc)."',".
		"'".$local_time."')";
	try{
		$database->setQuery($errsql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		return false;
	}		

}

function buildICSfile($cid, $update="No"){
	$retval = "";
	
	// get config stuff
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	// need Joomla TZ for converting to UTC
	require_once( JPATH_CONFIGURATION.DIRECTORY_SEPARATOR.'configuration.php' );
//	$CONFIG = new JConfig();
//	$offset = $CONFIG->offset;
	$timezone = new DateTimeZone(JFactory::getConfig()->get('offset'));
	$offset   = $timezone->getOffset(new DateTime)/3600;	
	
	if($apptpro_config->daylight_savings_time == "Yes"){
		$offset = $offset+1;
	}
	if($offset<0){
		$offset_sign = "+";
	} else {
		$offset_sign = "-";
	}	
	$offset = abs($offset);
	if($offset <10){
		$strOffset = $offset_sign."0".$offset.":00";
	} else {
		$strOffset = $offset_sign.strval($offset).":00";
	}

	$retval = "BEGIN:VCALENDAR\n\n";
	$retval .= "VERSION:2.0\n\n";
	$retval .= "PRODID:-//ABPro/CalendarAppointment\n\n";
	
	foreach ($cid as $one_id) {

		// get request details
		$params = JComponentHelper::getParams('com_languages');
		$sql = "SET lc_time_names = '".str_replace("-", "_", $params->get("site", 'en-GB'))."';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		$sql = 'SELECT *, DATE_FORMAT(startdate, "%Y%m%d") as ics_startdate, '.
		'DATE_FORMAT(starttime, "T%H%i00") as ics_starttime, '.
		'DATE_FORMAT(enddate, "%Y%m%d") as ics_enddate, '.
		'DATE_FORMAT(endtime, "T%H%i00") as ics_endtime, '.
		'DATE_FORMAT(UTC_TIMESTAMP(), "%Y%m%dT%H%i00Z") as ics_newstamp, '.
		'DATE_FORMAT(CONVERT_TZ(created,"'.$strOffset.'","+00:00"),"%Y%m%dT%H%i00Z")as utc_created, '.
		'DATE_FORMAT(endtime, "%H:%i") as endtime FROM #__sv_apptpro3_requests WHERE id_requests = '.$one_id;
		try{
			$database->setQuery($sql);
			$request_details = NULL;
			$request_details = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		// get resource stuff
		$sql = 'SELECT * FROM #__sv_apptpro3_resources WHERE id_resources = '.$request_details->resource;
		try{
			$database->setQuery($sql);
			$resource_details = NULL;
			$resource_details = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "be_sendmail", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		switch ($apptpro_config->calendar_title) {
		  case 'resource.name': {
			$title_text = $resource_details->name;	
			break;
		  }
		  case 'request.name': {
			$title_text = $request_details->name;	
			break;
		  }
		  default: {
			// must be a udf, get udf_value
			$sql = "SELECT udf_value FROM #__sv_apptpro3_udfvalues WHERE request_id = ".$one_id." and udf_id=".$apptpro_config->calendar_title;
			$database->setQuery( $sql);
			$title_text = $database->loadResult(); 		
		  }
		}
		$body_text = "";
		if($apptpro_config->calendar_body2 != "") {
			$body_text = buildMessage($one_id, "calendar_body", "No");
		}
		stripslashes($body_text);
		stripslashes($title_text);
		$body_text = str_replace("'", "`", $body_text);
		$title_text = str_replace("'", "`", $title_text);
		
		
		$retval .= "BEGIN:VEVENT\n\n";
		if($update == "Yes"){
			$retval .= "DTSTAMP:".$request_details->ics_newstamp."\n\n";
		} else {
			$retval .= "DTSTAMP:".$request_details->utc_created."\n\n";
		}
		$retval .= "UID:ABPRO-".$one_id."\n\n";
		$retval .= "DTSTART:".$request_details->ics_startdate.$request_details->ics_starttime."\n\n";
		$retval .= "DTEND:".$request_details->ics_enddate.$request_details->ics_endtime."\n\n";
		$retval .= "SUMMARY:".$title_text."\n\n";
		// ics is not HTML and iCal is real picky so we remove any HTML tags and change \r\n (from the editor) to \\n
		$retval .= "DESCRIPTION:".strip_tags(str_replace("\r\n", "\\n", $body_text))."\n\n";
	
		$retval .= "END:VEVENT\n\n";

	}	
	$retval .= "END:VCALENDAR";

	return $retval;
}

function utf16urlencode($str)
{
    $str = mb_convert_encoding($str, 'UTF-16', 'UTF-8');
    $out ='';
    for ($i = 0; $i < mb_strlen($str, 'UTF-16'); $i++)
    {
        $out .= bin2hex(mb_substr($str, $i, 1, 'UTF-16'));
    }
    return $out;
}

function clean_svkey($strIn){
	// Detect {svkey} and remove everything around the tag contents
	// Required for removing extraneous tags added by text editors from language file keys in messages
	
	$keylength = strlen("{svkey}");
	$startkey = strpos($strIn, "{svkey}");
	if($startkey === false){
		return $strIn;
	} else {
		$endkey = strrpos($strIn, "{svkey}");
		$strOut = substr($strIn, $startkey+$keylength, $endkey-$startkey-$keylength);
		return $strOut;
	}
}

function check_cc($cc, $request_id){
	if($cc == "cart" || $request_id == ""){
		return true;
	}
	$database = JFactory::getDBO();
	$sql = 'SELECT count(*) FROM #__sv_apptpro3_requests WHERE id_requests = '.$database->escape($request_id).' AND cancellation_id ="'.$database->escape($cc).'"';
	try{
		$database->setQuery($sql);
		$check_cc = NULL;
		$check_cc = $database -> loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_sendmail", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	if($check_cc != 1){
		return false;
	} 
	return true;		
}

function prep_for_screen($strIn){
	$strOut = "";
	// remove text inside {hide_on_screen} {/hide_on_screen}
	$strOut = preg_replace("/{hide_on_screen}(.*?){\/hide_on_screen}/s","", $strIn);
	// remove just the tags
	$strOut = str_replace("{hide_in_email}", "", $strOut);
	$strOut = str_replace("{/hide_in_email}", "", $strOut);
	return $strOut;
}

function prep_for_email($strIn){
	$strOut = "";
	// remove text inside {hide_in_email} and [/hide_in_email} if found
	$strOut = preg_replace("/{hide_in_email}(.*?){\/hide_in_email}/s","", $strIn);
	// remove just the tags
	$strOut = str_replace("{hide_on_screen}", "", $strOut);
	$strOut = str_replace("{/hide_on_screen}", "", $strOut);
	return $strOut;
}

/**
 * Runs all Joomla content plugins on a single text string
 */
function runJoomlaContentPlugins( &$text ) {
	if (empty($text)) {
		return $text;
	}
	JPluginHelper::importPlugin('content');

	$content = new stdClass();
	$content->text = &$text;
	$content->params = new JRegistry();

	// Process the content preparation plugins
	if (version_compare(JVERSION, "1.6.0", 'ge'))
	{
		$dispatcher = JEventDispatcher::getInstance();
		$results = $dispatcher->trigger('onContentPrepare', array('com_rsappt_pro3', &$content, &$content->params, 0));
		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_rsappt_pro3', &$content, &$content->params, 0));
	}
	else
	{
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onPrepareContent', array(&$content, &$content->params, 0));
		$results = $dispatcher->trigger('onBeforeDisplayContent', array(&$content, &$content->params, 0));
	}

	$text = &$content->text;

	return $text;
}
	
?>