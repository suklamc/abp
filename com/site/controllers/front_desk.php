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

//DEVNOTE: import CONTROLLER object class
jimport( 'joomla.application.component.controller' );


/**
 * rsappt_pro3  Controller
 */
 
class front_deskController extends JControllerForm
{

	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		
		// Register Extra tasks
		$this->registerTask( 'reminders', 'send_reminders' );
		$this->registerTask( 'reminders_sms', 'send_sms_reminders' );
		$this->registerTask( 'thankyou', 'send_thankyou' );
		
		$this->registerTask( 'display_manifest', 'go_display_manifest' );

		$this->registerTask( 'add_booking', 'add_booking' );
		$this->registerTask( 'process_booking_request', 'process_booking_request' );
		$this->registerTask( 'show_confirmation', 'show_confirmation' );
		$this->registerTask( 'show_in_progress', 'show_in_progress' );
		$this->registerTask( 'pp_return', 'pp_return' );


		$this->registerTask( 'do_continue', 'do_continue' );
		$this->registerTask( 'do_book_another', 'do_book_another' );

		$this->registerTask( 'customer_history', 'customer_history' );

		$this->registerTask( 'export_csv', 'export_csv_fe' );
		$this->registerTask( 'printer', 'printer' );

		$user = JFactory::getUser();		
		if($user->guest){
			$this->setRedirect( 'index.php', JText::_('RS1_FRONT_END_ACCESS_ERROR'), 'warning');
		} else{
			$database = JFactory::getDBO();
			// check to see id user is an admin		
			$sql = "SELECT count(*) as count FROM #__sv_apptpro3_resources WHERE ".
				"resource_admins LIKE '%|".$user->id."|%';";
			try{
				$database->setQuery($sql);
				$check = NULL;
				$check = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "controller blocker", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}		
			if($check->count == 0){
				$this->setRedirect( 'index.php', JText::_('RS1_FRONT_END_ACCESS_ERROR'), 'warning');
			}	
		}
		
	}

	function list_bookings()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'front_desk' );
		$jinput->set( 'layout', 'default'  );
		$jinput->set( 'hidemainmenu', 0);


		parent::display();

	}

	function cancel($key=null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');

		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){			
			$this->setRedirect( JRoute::_('index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view='.$frompage.'&Itemid='.$frompage_item );
		}
	}	


	function send_reminders($sms="No"){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$fromtab = $jinput->getString('fromtab');

		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$reminder_log_time_format = "Y-m-d H:i:s";
		$database = JFactory::getDBO();
	
		if (!is_array($cid) || sv_count_($cid) < 1) {
			echo "<script> alert('Select an item for reminder'); window.history.go(-1);</script>\n";
			exit();
		}
	
		// get config info
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		if (sv_count_($cid))
		{
			$ids = implode(',', $cid);
			// get request details
			$sql = "SELECT #__sv_apptpro3_requests.*, DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%W %M %e, %Y') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %l:%i %p') as display_starttime ,".
				"#__sv_apptpro3_resources.name AS resource_name ".
				"FROM (#__sv_apptpro3_requests INNER JOIN #__sv_apptpro3_resources ".
				" ON  #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources )". 
				" WHERE #__sv_apptpro3_requests.id_requests IN (".$database->escape($ids).")";
			try{	
				$database->setQuery($sql);
				$requests = NULL;
				$requests = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "front_desk", "", "");
				echo JText::_('RS1_SQL_ERROR');
				//return false;
			}		
			
			// need current local time based on server time adjusted by Joomla time zone setting
			$config = JFactory::getConfig();
			$tzoffset = $config->get('offset');      
//			if($apptpro_config->daylight_savings_time == "Yes"){
//				$tzoffset = $tzoffset+1;
//			}
//			$offsetdate = JFactory::getDate();
//			$offsetdate->setOffset($tzoffset);
			$tz = new DateTimeZone($tzoffset);
			$offsetdate = new JDate("now", $tz);
			date_default_timezone_set ($tzoffset );
		
			$status = '';
			$language = JFactory::getLanguage();
			$language->load('com_rsappt_pro3', JPATH_SITE, null, true);
			$subject = JText::_('RS1_REMINDER_EMAIL_SUBJECT');
			
			$k = 0;
			for($i=0; $i < sv_count_($requests ); $i++) {
				$request = $requests[$i];
				$err = "";
				if($request->email == "" && $sms=="No"){
					// no email address
					$err .= JText::_('RS1_SMS_MSG_NO_EMAIL');
				} else if($request->request_status != "accepted"){
					// is not 'accepted'?
					$err .= JText::_('RS1_SMS_MSG_NOT_ACCEPTED');
				} else if(strtotime($request->startdate." ".$request->starttime) < strtotime("now")){
					// in the past
					$err .= JText::_('RS1_SMS_MSG_DATE_PASSED');
				}
				if($request->user_id != ""){
					$user = $request->user_id;
				} else {
					$user="-1";
				}
				if($err != ""){
					$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email ." - ". $err.JText::_('RS1_SMS_MSG_NO_REMINDER_SENT');											
					logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
					$status .= $line."<br>";
				} else {
					if($sms=="No"){
						if(sendMail($request->email, $subject, "reminder", $request->id_requests)){
							$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email . ", ".stripslashes($request->name). ", ".stripslashes($request->resource_name).", ".$request->display_starttime. ", ".$request->display_startdate.JText::_('RS1_SMS_MSG_OK');											
							logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
							$status .= $line."<br>";
						} else {
							$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email . ", ".stripslashes($request->name). ", ".stripslashes($request->resource_name).", ".$request->display_starttime. ", ".$request->display_startdate.JText::_('RS1_SMS_MSG_FAILED');											
							logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
							$status .= $line."<br>";
						}	
					} else {
						if($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes"){
							$returnCode = "";
							if(sv_sendSMS($request->id_requests, "reminder", $returnCode )){
								$line = JText::_('RS1_SMS_MSG_TO_RECIP').stripslashes($request->name). JText::_('RS1_SMS_MSG_RET_CODE_OK').$returnCode;											
								logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
								$status .= $line."<br>";
							} else {
								$line = JText::_('RS1_SMS_MSG_TO_RECIP').stripslashes($request->name). JText::_('RS1_SMS_MSG_RET_CODE_FAILED').$returnCode;											
								logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
								$status .= $line."<br>";
							}
						} else {
							logReminder(JText::_('RS1_SMS_MSG_DISABLED'), $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
							$status = JText::_('RS1_SMS_MSG_DISABLED');
						}				
					}
				}
			}
		}
		
		$jinput->set( 'view', 'requests_reminders_fd' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'results', $status);
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'fromtab', $fromtab);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();
		
	}

	function send_sms_reminders(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->send_reminders("Yes");
	}

	function send_thankyou(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');
		$fromtab = $jinput->getString('fromtab');

		$cid	= $jinput->get( 'cid', array(0), 'post', 'array' );
		$reminder_log_time_format = "Y-m-d H:i:s";
		$database = JFactory::getDBO();
	
		if (!is_array($cid) || sv_count_($cid) < 1) {
			echo "<script> alert('Select an item'); window.history.go(-1);</script>\n";
			exit();
		}
	
		// get config info
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
	
		if (sv_count_($cid))
		{
			$ids = implode(',', $cid);
			// get request details
			$sql = "SELECT #__sv_apptpro3_requests.*, DATE_FORMAT(#__sv_apptpro3_requests.startdate, '%W %M %e, %Y') as display_startdate, ".
				"DATE_FORMAT(#__sv_apptpro3_requests.starttime, ' %l:%i %p') as display_starttime ,".
				"#__sv_apptpro3_resources.name AS resource_name ".
				"FROM (#__sv_apptpro3_requests INNER JOIN #__sv_apptpro3_resources ".
				" ON  #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources )". 
				" WHERE #__sv_apptpro3_requests.id_requests IN (".$database->escape($ids).")";
			try{				
				$database->setQuery($sql);
				$requests = NULL;
				$requests = $database -> loadObjectList();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "front_desk", "", "");
				echo JText::_('RS1_SQL_ERROR');
				return false;
			}		
			
			// need current local time based on server time adjusted by Joomla time zone setting
			$config = JFactory::getConfig();
			$tzoffset = $config->get('offset');      
//			if($apptpro_config->daylight_savings_time == "Yes"){
//				$tzoffset = $tzoffset+1;
//			}
//			$offsetdate = JFactory::getDate();
//			$offsetdate->setOffset($tzoffset);
			$tz = new DateTimeZone($tzoffset);
			$offsetdate = new JDate("now", $tz);
		
			$status = '';
			$language = JFactory::getLanguage();
			$language->load('com_rsappt_pro3', JPATH_SITE, null, true);
			$subject = JText::_('RS1_THANKYOU_MSG_SUBJECT');
			
			$k = 0;
			for($i=0; $i < sv_count_($requests ); $i++) {
				$request = $requests[$i];
				$err = "";
				if($request->email == ""){
					// no email address
					$err .= JText::_('RS1_SMS_MSG_NO_EMAIL');
				}
				if($request->user_id != ""){
					$user = $request->user_id;
				} else {
					$user="-1";
				}
				if($err != ""){
					$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email ." - ". $err.JText::_('RS1_SMS_MSG_NO_REMINDER_SENT');											
					logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
					$status .= $line."<br>";
				} else {
					if(sendMail($request->email, $subject, "thankyou", $request->id_requests)){
						$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email . ", ".stripslashes($request->name). ", ".stripslashes($request->resource_name).", ".$request->display_starttime. ", ".$request->display_startdate.JText::_('RS1_SMS_MSG_OK');											
						logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
						$status .= $line."<br>";
					} else {
						$line = JText::_('RS1_SMS_MSG_RECIPIENT'). $request->email . ", ".stripslashes($request->name). ", ".stripslashes($request->resource_name).", ".$request->display_starttime. ", ".$request->display_startdate.JText::_('RS1_SMS_MSG_FAILED');											
						logReminder($line, $request->id_requests, $user, $request->name, $offsetdate->format($reminder_log_time_format, true, true));
						$status .= $line."<br>";
					}	
				}
			}
		}
		
		$jinput->set( 'view', 'requests_thankyou_fd' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'results', $status);
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'fromtab', $fromtab);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();
		
	
	}

	function go_display_manifest()
	{
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'display_manifest' );
		$jinput->set( 'layout', 'default'  );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'listpage', $frompage);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();

	}

	function add_booking()
	{
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'booking_screen_fd' );
		$jinput->set( 'layout', 'default'  );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'listpage', $frompage);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();

	}

	function customer_history()
	{
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'customer_history' );
		$jinput->set( 'layout', 'default'  );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'listpage', $frompage);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));

		parent::display();

	}

	function process_booking_request(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString('frompage');
		$frompage_item = $jinput->getString('frompage_item');

		include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

		$paypal_submit = $jinput->getString('ppsubmit', '0');
	
		$name = $jinput->getString('name');
		$user_id = $jinput->getString('user_id');
		$unit_number = $jinput->getString('unitnumber');
		$phone = $jinput->getString('phone');
		$email = $jinput->getString('email');
		$sms_reminders = $jinput->getString('sms_reminders', "No");
		$sms_phone = $jinput->getString('sms_phone');
		$sms_dial_code = $jinput->getString('sms_dial_code');
		$resource = $jinput->getString('resource');
		$service_name = $jinput->getString('service_name');
		$startdate = $jinput->getString('startdate');
		$starttime = $jinput->getString('starttime');
		$enddate = $jinput->getString('enddate');
		$endtime = $jinput->getString('endtime');
		$comment = $jinput->getString('comment');
		$copyme = $jinput->getString('cbCopyMe');
		$str_udf_count = $jinput->getString('udf_count', "0");
		$str_res_udf_count = $jinput->getString('res_udf_count', "0");
		$int_udf_count = intval($str_udf_count) + intval($str_res_udf_count);

		$applied_credit = $jinput->getString('applied_credit', 0.00);
		$uc_used = $jinput->getString('uc_used', 0.00);
		$gc_used = $jinput->getString('gc_used', 0.00);
		$grand_total = $jinput->getString('grand_total', 0);
		$ammount_due = $grand_total;
		$coupon_code = $jinput->getString('coupon_code','');
		$booked_seats = $jinput->getString('booked_seats', 1);	
		$seat_type_count = $jinput->getString('seat_type_count', -1);
		$extras_count = $jinput->getString('extras_count', -1);
		$admin_comment = $jinput->getString('admin_comment', '');		
		$gift_cert = $jinput->getString('gift_cert', '');		
		$manual_payment_collected = $jinput->getString('manual_payment_collected', '');		
		$category = $jinput->getString('category_id');
		$deposit_amount = $jinput->getString('deposit_amount', 0);
		$err = "";
		
		if($resource == ""){
			$resource = $jinput->getString('selected_resource_id');
		}
		
		if($resource == "" or $resource < 1){
			// should never happen
			die("No Access (2)");
		}

		// get config info
		$database = JFactory::getDBO();
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		$user = JFactory::getUser();
		if($apptpro_config->requireLogin == "Yes" && $user->guest){
			die("No Access (3)");
		}
		
		// get resource info for the selected resource
		$sql = 'SELECT * FROM #__sv_apptpro3_resources where id_resources = '.(int)$resource;
		try{
			$database->setQuery($sql);
			$res_detail = NULL;
			$res_detail = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "front_desk", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

		if ($err == ""){
		
		/* ----------------------------------------------------------------------------------- 
//		/*		Save order to database 
		/* -------------------------------------------------------------------------------------*/

		$request_status = $jinput->getString('book_as_request_status');
		$payment_status = $jinput->getString('book_as_payment_status');
		if($payment_status == "paid"){
			$ammount_due = 0.00;
		}
 
		// a booking can have some user credit and some gift certificate adding up to total applied_credit
		// pass as "applied_credit|uc_used|gc_used"
		$credit_data = $applied_credit."|".$uc_used."|".$gc_used;

  		// save to db
 		$last_id = NULL;
		$cancel_code = md5(uniqid(rand(), true));
		$last_id = saveToDB($name, $user_id ,$phone, $email, $sms_reminders, $sms_phone, $sms_dial_code, $resource, $category,
			$service_name, $startdate, $starttime, $enddate, $endtime, $request_status, $cancel_code, $grand_total,
			$ammount_due, $deposit_amount, $coupon_code, $booked_seats, $credit_data, $comment, $admin_comment, $manual_payment_collected, $gift_cert);		

		if($last_id->last_id == -1){
			exit;
		}		

		// save operator_id to booking record
		$sql = "UPDATE #__sv_apptpro3_requests SET operator_id = ".$user->id." WHERE id_requests = ".$last_id->last_id;
		$database->setQuery($sql);
		$database->execute();
		
		
//		if($apptpro_config->which_calendar != 'None' and $apptpro_config->which_calendar != "Google"){
//			// need to set request to resource's defaults
//			$cat_id = NULL;
//			$cal_id = NULL;
//			getDefaultCalInfo($apptpro_config->which_calendar, $res_detail, $cat_id, $cal_id);
//			if($cat_id != NULL){
//				if($apptpro_config->which_calendar == "JCalPro2"){
//					$sql = "UPDATE #__sv_apptpro3_requests SET calendar_category=".strval($cat_id).", ".
//					"calendar_calendar = ".strval($cal_id)." WHERE id_requests = ".$last_id->last_id;
//				} else {
//					$sql = "UPDATE #__sv_apptpro3_requests SET calendar_category=".strval($cat_id)." WHERE id_requests = ".$last_id->last_id;
//				}								
//				$database->setQuery($sql);
//				$database->execute();
//			}
//		}
		
		// add seat counts to seat_counts table if in use
		if($seat_type_count > 0){
			for($stci=0; $stci<$seat_type_count; $stci++){

				$seat_type_id = $jinput->getString('seat_type_id_'.$stci,"?");
				$seat_type_qty = $jinput->getString('seat_'.$stci, 0);
				if($seat_type_qty > 0){
					$sSql = sprintf("INSERT INTO #__sv_apptpro3_seat_counts (seat_type_id, request_id, seat_type_qty) VALUES(%d, %d, '%s')",
							$seat_type_id,
							$last_id->last_id,
							$database->escape($seat_type_qty));
					try{					
						$database->setQuery($sSql);
						$database->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "ctrl_front_desk", "", "");
						echo JText::_('RS1_SQL_ERROR');
						exit;
					}
				}
			}
		}

		// add extras to extras_data table if in use
		if($extras_count > 0){
			for($ei=0; $ei<$extras_count; $ei++){

				$extras_id = $jinput->getString('extras_id_'.$ei,"?");
				$extras_qty = $jinput->getString('extra_'.$ei, -1);
				if($extras_qty === "on"){$extras_qty = 1;} // extra was displayed as a checkbox
				if($extras_qty > -1){
					$sSql = sprintf("INSERT INTO #__sv_apptpro3_extras_data (extras_id, request_id, extras_qty) VALUES(%d, %d, '%s')",
							$extras_id,
							$last_id->last_id,
							$database->escape($extras_qty));
					try{
						$database->setQuery($sSql);
						$database->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "ctrl_front_desk", "", "");
						echo JText::_('RS1_SQL_ERROR');
						exit;
					}
				}
			}
		}


		// add udf values to udf_values table
		//echo "str_udf_count=".$str_udf_count;
		//echo "int_udf_count=".$int_udf_count;
		if($int_udf_count > 0){
			for($i=0; $i<$int_udf_count; $i++){

				$udf_value = $jinput->getString('user_field'.$i.'_value');
				$sSql = sprintf("INSERT INTO #__sv_apptpro3_udfvalues (udf_id, request_id, udf_value) VALUES(%d, %d, '%s')",
						$jinput->getString('user_field'.$i.'_udf_id'),
						$last_id->last_id,
						$database->escape($udf_value));
				try{
					$database->setQuery($sSql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "ctrl_front_desk", "", "");
					echo JText::_('RS1_SQL_ERROR');
					exit;
				}
			}
		}
	
		
		// if "accepted", add to calendar
		if($request_status == "accepted"){
			addToCalendar($last_id->last_id, $apptpro_config);
			addToEmailMarketing($last_id->last_id, $apptpro_config);
		}	
		
		if($paypal_submit == "1" && floatval($grand_total) > 0){
			/* ----------------------------------------------------------------------------------- 
//			/*		go to PayPal 
			/* -------------------------------------------------------------------------------------*/
	
			if($apptpro_config->enable_paypal == "Yes" || $apptpro_config->enable_paypal == "Opt"){
				GoToPayPal($last_id->last_id, $apptpro_config, $grand_total, "booking_screen_gad", $frompage_item);
			}
			// for paypal, messages are sent from ipn

		} else {
		
			// dev only
			//ini_set ( "SMTP", "shawmail.cg.shawcable.net" ); 

			$message_attachment = "";
			$message = "";
			$message_admin = "";
			// send form		
			$mailer = JFactory::getMailer();
			try{
				$mailer->setSender(array($apptpro_config->mailFROM,null));
			}
			catch (Exception $e){
				logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_front_desk", "", "");
				return false;		
			}

			if($request_status == "accepted"){
				$temp = buildMessage(strval($last_id->last_id), "confirmation", "No", "", "No", "Yes");
				$message .= $temp[0];
				if($temp[1] != ""){
					$message_attachment = JPATH_BASE.$temp[1];
				}				
				$message_admin .= buildMessage(strval($last_id->last_id), "confirmation_admin", "No");
			} else {
				$message .= buildMessage(strval($last_id->last_id), "in_progress", "No");			
				$message_admin .= buildMessage(strval($last_id->last_id), "in_progress", "No");
			}
			
			if($apptpro_config->html_email != "Yes"){
				$message = str_replace("<br>", "\r\n", $message);
			}

			$array = array($last_id->last_id);
			$ics = buildICSfile($array);

			$sql = 'SELECT * FROM #__sv_apptpro3_mail WHERE id_mail = '.($res_detail->mail_id ==1 ||$res_detail->mail_id == null?"1":$res_detail->mail_id);
			try{
				$database->setQuery($sql);
				$messages_to_use = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "booking_screen_gad", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}

			// email to customer
			if($jinput->getString('email') != "" && $jinput->getString('chk_email_confirmation') == "Yes"){
				$to = $jinput->getString('email');

				if($apptpro_config->html_email == "Yes"){
					$mailer->IsHTML(true);
				}

				if($messages_to_use->attach_ics_customer == "Yes"){
					$mailer->AddStringAttachment($ics, "appointment_".strval($last_id->last_id).".ics");
				}

				if($message_attachment != ""){
					$mailer->addAttachment($message_attachment);
				}

				try{
					$mailer->addRecipient($to);
				}
				catch (Exception $e){
					logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_front_desk", "", "");
					return false;		
				}
				$mailer->setSubject(JText::_($apptpro_config->mailSubject));
				$mailer->setBody($message);
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
					logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_front_desk", "", "");
					return false;		
				}

			}
			
			// email to admin
			if($apptpro_config->mailTO != ""){
				$to = $apptpro_config->mailTO;

				if($apptpro_config->html_email == "Yes"){
					$mailer->IsHTML(true);
				}

				if($messages_to_use->attach_ics_admin == "Yes"){
					$mailer->AddStringAttachment($ics, "appointment_".strval($last_id->last_id).".ics");
				}

				try{
					$mailer->addRecipient(explode(",", $to));		
				}
				catch (Exception $e){
					logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_front_desk", "", "");
					return false;		
				}
				$mailer->setSubject(JText::_($apptpro_config->mailSubject));
				$mailer->setBody($message);
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
					logIt("Error on setting email FROM address: ".$e->getMessage(), "ctrl_front_desk", "", "");
					return false;		
				}
			}
			
			// email to resource
			if($res_detail->resource_email != ""){
				$to = $res_detail->resource_email;

				if($apptpro_config->html_email == "Yes"){
					$mailer->IsHTML(true);
				}

				if($messages_to_use->attach_ics_resource == "Yes"){
					$mailer->AddStringAttachment($ics, "appointment_".strval($last_id->last_id).".ics");
				}

				try{
					$mailer->addRecipient(explode(",", $to));		
				}
				catch (Exception $e){
					logIt("Error on setting email TO address: ".$e->getMessage(), "ctrl_front_desk", "", "");
					return false;		
				}
				$mailer->setSubject(JText::_($apptpro_config->mailSubject));
				$mailer->setBody($message_admin);
				if($mailer->send() != true){
					logIt("Error sending email: ".$mailer->ErrorInfo);
				}
			}

			// SMS to resource
			if($apptpro_config->enable_clickatell == "Yes" || $apptpro_config->enable_eztexting == "Yes" || $apptpro_config->enable_twilio == "Yes"){
				$config = JFactory::getConfig();
				$tzoffset = $config->get('offset');      
			$tz = new DateTimeZone($tzoffset);
			$offsetdate = new JDate("now", $tz);
				$reminder_log_time_format = "Y-m-d H:i:s";
				$user = JFactory::getUser();
				if(!$user->guest){
					$bookingUser = $user->id;
				} else {
					$bookingUser = -1;
				}
				$returnCode = "";
	
				if($request_status == "accepted"){
					sv_sendSMS($last_id->last_id, "confirmation", $returnCode, $toResource="Yes");			
					if($apptpro_config->sms_confirmation == "Yes" && $sms_reminders = "Yes"){
						sv_sendSMS($last_id->last_id, "confirmation", $returnCode, $toResource="No");			
					}
				} else {
					sv_sendSMS($last_id->last_id, "in_progress", $returnCode, $toResource="Yes");			
				}
				logReminder("New booking: ".$returnCode, $last_id->last_id, $bookingUser, $name, $offsetdate->format($reminder_log_time_format));
			}
		
			if($request_status == "accepted"){
				// unique $cancel_code required so confimration screen cannot be called directly
				$next_view="show_confirmation&cc=".$cancel_code;
			} else {
				$next_view="show_in_progress&cc=".$cancel_code;
			}
			
			// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
			$config = JFactory::getConfig();
			$seo = $config->get( 'sef' );
			if($frompage_item == ""){
				// from popup caller, no item id
				$frompage_item = 1; // need a place holder for router parsing
			}
			if($seo == "1"){			
				$this->setRedirect( JRoute::_('index.php?option=com_rsappt_pro3&view=front_desk').'&Itemid='.$frompage_item.'&task='.$next_view.'&req_id='.$last_id->last_id );
			} else {
				$this->setRedirect( 'index.php?option=com_rsappt_pro3&view=front_desk&Itemid='.$frompage_item.'&task='.$next_view.'&req_id='.$last_id->last_id );
			}
			}
		}
	
		
	}


	function show_confirmation()
	{
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'fd_confirmation' );
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));
		$jinput->set( 'req_id', $jinput->getString( 'req_id'));
		$cc = $jinput->getString( 'cc', '' );
		$jinput->set( 'cc', $cc);
		if($cc==""){
			echo "No Access (1)";
			exit;
		}

		parent::display();
	}

	function show_in_progress()
	{
		$jinput = JFactory::getApplication()->input;
		$frompage = $jinput->getString( 'frompage', '' );
		$jinput->set( 'view', 'fd_confirmation' );
		$jinput->set( 'frompage', $frompage);
		$jinput->set( 'Itemid', $jinput->getString( 'Itemid'));
		$jinput->set( 'req_id', $jinput->getString( 'req_id'));
		$jinput->set( 'which_message', 'in_progress');
		$cc = $jinput->getString( 'cc', '' );
		$jinput->set( 'cc', $cc);
		if($cc==""){
			echo "No Access (1)";
			exit;
		}

		parent::display();
	}
	

	function pp_return(){
		echo "pp_return";
	}
	
	function do_continue(){
		$jinput = JFactory::getApplication()->input;
		$frompage_item = $jinput->getString('frompage_item');
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){			
			$this->setRedirect( JRoute::_('index.php?option=com_rsappt_pro3&view=front_desk&Itemid='.$frompage_item ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view=front_desk&Itemid='.$frompage_item );
		}
	}

	function do_book_another(){
		$jinput = JFactory::getApplication()->input;
		$frompage_item = $jinput->getString('frompage_item');
		$req_date = $jinput->getString('req_date', "");
		if($req_date != ""){ $req_date = "&mystartdate=".$req_date;}
		 
		// With J1.7, JRoute screws up the url for use with setRedirect, if not using SEO
		$config = JFactory::getConfig();
		$seo = $config->get( 'sef' );
		if($seo == "1"){			
			$this->setRedirect( JRoute::_('index.php?option=com_rsappt_pro3&view=front_desk&task=add_booking&Itemid='.$frompage_item.$req_date ));
		} else {
			$this->setRedirect( 'index.php?option=com_rsappt_pro3&view=front_desk&task=add_booking&Itemid='.$frompage_item.$req_date );
		}
	}

	function export_csv_fe(){
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		include_once(JPATH_SITE.'/components/com_rsappt_pro3/functions2.php');
		do_fe_export();
	}

	function printer(){
		$jinput = JFactory::getApplication()->input;
		$jinput->set( 'view', 'front_desk' );
		$jinput->set( 'hidemainmenu', 1);
		$jinput->set( 'layout', 'default_prt');
		$jinput->set( 'tmpl', 'component');

		parent::display();
	}

}
?>

