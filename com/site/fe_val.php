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

	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

	header('Content-Type: text/xml'); 
	header("Cache-Control: no-cache, must-revalidate");
	//A date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	
	//$err = 'Validation Failed:<br>';
	$err = JText::_('RS1_INPUT_SCRN_VALIDATION_FAILED');
	$jinput = JFactory::getApplication()->input;

	
	// recives the user's selected resource and date
	$name = $jinput->getString('name');
	$phone = $jinput->getString('phone', '');
	$use_sms = $jinput->getString('use_sms', '');
	$sms_phone = $jinput->getString('sms_phone', '');
	$email = $jinput->getString('email', "-1");
	$udf_count = $jinput->getInt('udf_count');
	for($i=0; $i<$udf_count; $i++){
		$udf_name = "user_field".$i."_label";		
		$user_field_labels[$i] = $jinput->getString($udf_name);
		$udf_name = "user_field".$i."_value";		
		$user_field_values[$i] = $jinput->getString($udf_name);
		$udf_name = "user_field".$i."_is_required";		
		$user_field_required[$i] = ($jinput->getString($udf_name) == "Yes"? "Yes": "No");
	}
	//$err = $err.print_r($user_field_values);
	//$err = $err.print_r($user_field_required);
	//$err = $err.print_r($user_field_labels);

	$resource = $jinput->getInt('resource');
	$category_id = $jinput->getInt('category_id');
	$startdate = $jinput->getString('startdate');
	$starttime = $jinput->getString('starttime');
	$enddate = $jinput->getString('enddate');
	$endtime = $jinput->getString('endtime');	
	$booked_seats = $jinput->getInt('booked_seats', 1);	
	$user_id = $jinput->getInt('user_id', "");	
	$PayPal_mode = $jinput->getString('PayPal_mode', "");
	$PayPal_due = $jinput->getString('PayPal_due', "");

	$gap = $jinput->getInt('gap', "0");

	// get config info
	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_val", "", "");
		echo JText::_('RS1_SQL_ERROR');	
		exit;
	}		
		
	if ($name == "" ) {
		$err = $err.JText::_('RS1_INPUT_SCRN_NAME_ERR');
	}
	

	if ($apptpro_config->requirePhone == "Yes" && $phone == "" ) {
		$err = $err.JText::_('RS1_INPUT_SCRN_PHONE_ERR');
	}
/*	if(!ereg("^[0-9]{3}-[0-9]{3}-[0-9]{4}$", $phone)) {
		$err = $err.JText::_('RS1_INPUT_SCRN_PHONE_ERR');
	}
*/	

	if($use_sms == "Checked"){
		if($sms_phone == "" ){
			$err = $err.JText::_('RS1_INPUT_SCRN_SMS_PHONE_ERR');
		}
	}
	if ($apptpro_config->requireEmail == "Yes"){
		if( $email == "" ) {
			$err = $err.JText::_('RS1_INPUT_SCRN_EMAIL_ERR');
		} else if(!validEmail($email)){
			$err = $err.JText::_('RS1_INPUT_SCRN_EMAIL_ERR');
		}
	}

// If you wish to validate service uncomment the code below. 
// For more info see How-to page
// http://appointmentbookingpro.com/how-to/165-force-user-to-choose-a-service.html
//	if($jinput->getString('srv', '') == "-1"){
//		$err = $err."Select a Service<BR/>";		
//	}

	for($i=0; $i<$udf_count; $i++){
		if($user_field_required[$i] == "Yes" && $user_field_values[$i] == ""){
			$err = $err.JText::_($user_field_labels[$i].JText::_('RS1_INPUT_SCRN_UDF_ERR'));
		}
	}

	if($category_id == "0" ) {
		$err = $err.JText::_('RS1_INPUT_SCRN_CATEGORY_ERR');
	} else {
	
		if ($resource == "-1" ) {
			// gad, no timeslot selected
			$err = $err.JText::_('RS1_INPUT_SCRN_TIMESLOT_PROMPT')."<BR>";
		} else {
			if ($resource == "0" ) {
				$err = $err.JText::_('RS1_INPUT_SCRN_RESOURCE_ERR')."<BR>";
			} else {
				if ($startdate == trim(JText::_('RS1_INPUT_SCRN_DATE_PROMPT')) ) {
					$startdate = "0000-00-00";
					$enddate = "0000-00-00";
					$err = $err.JText::_('RS1_INPUT_SCRN_DATE_PROMPT')."<BR>";
				} else {
					if ($starttime == "00" || $starttime == "00:00" || $starttime == "") {
						$err = $err.JText::_('RS1_INPUT_SCRN_TIMESLOT_PROMPT')."<BR>";
					} 		
				}
			}
		}
	}

	if($booked_seats == 0 ){
		$err = $err.JText::_('RS1_INPUT_SCRN_SEATS_ERR');
	}

	if ($resource != "0"){
		// get resource info for the selected resource
		$sql = 'SELECT * FROM #__sv_apptpro3_resources where id_resources = '.$resource;
		try{
			$database->setQuery($sql);
			$res_detail = NULL;
			$res_detail = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_val", "", "");
			echo JText::_('RS1_SQL_ERROR');	
			exit;
		}					
	}

	if ($resource != "0" AND $resource != "-1"){ // user has seleted a timeslot, validate it
		// get 'current time' as server time adjusted for Joomla time zone
		$CONFIG = new JConfig();
		$offset = $CONFIG->offset;
		
		// In Joomla 1.6 time zone offset is not a number buy a city name. 
		// get user's current local time using the Joomla time zone
		$current_datetime = date_create('now', timezone_open($CONFIG->offset));
		//echo date_format($current_datetime, 'Y-m-d H:i:sP') . "<BR/>";

		$appointment_start_datetime = date_create($startdate." ".$starttime, timezone_open($CONFIG->offset));
		//echo date_format($appointment_start_datetime, 'Y-m-d H:i:sP') . "<BR/>";

		// Some documented PHP datetime functions seem to be often missing from php5 
		// convert to 'date' ($current_date_time) rather than 'datetime' ($current_datetime)
		//echo strtotime(date_format($current_datetime, 'Y-m-d H:i:s'))."<br>";
		$current_date_time = strtotime(date_format($current_datetime, 'Y-m-d H:i:s'));
		$appointment_start = strtotime(date_format($appointment_start_datetime, 'Y-m-d H:i:s'));
		
		$first_allowable_start = strtotime("+".strval($res_detail->min_lead_time)." hour", $current_date_time);

		$screen_type = $jinput->getString('scrn');	
		
		// if booking for today, make sure resource allows that
		if($res_detail->min_lead_time == ""){ $res_detail->min_lead_time = 0; }
		//echo $startdate. "<BR/>";
		//echo date_format($current_date_time, "Y-m-d"). "<BR/>";
		if($startdate == strftime("%Y-%m-%d",$current_date_time)){
			// if you want to allow the front desk to book same day but NOT the public, use this line	
			//if($res_detail->disable_dates_before != "Today" && $screen_type != "fd_gad") {
			if($res_detail->disable_dates_before != "Today"){
				$err = $err.JText::_('RS1_INPUT_SCRN_NO_CURRENT_DAY_ERR');
			}
		} 


//		If you stop taking appointments at a certin time the day before, uncomment the folloing lines
//		$tomorrow = new DateTime('tomorrow');
//		if(intval(date("G",$current_date_time)) > 18 && ($startdate == $tomorrow->format('Y-m-d'))){
//			$err .= "Bookings only accepted until 6:00 PM on the day prior to the appointment.";
//        }	

		// check lead time	
		//$err .= "Appointment start: ".strftime("%Y-%m-%d %H:%M", $appointment_start) . "<BR/>";
		//$err .= "First allowable start: ".strftime("%Y-%m-%d %H:%M", $first_allowable_start) . "<BR/>";
		if( $screen_type == "fd_gad"){
			//staff
			if($appointment_start <= $first_allowable_start && $apptpro_config->staff_booking_in_the_past == 0) {
				$err = $err.JText::_('RS1_INPUT_SCRN_TIME_PASSED_ERR');
			}			
		} else {
			//public
			if($appointment_start <= $first_allowable_start) {
				// start time of booking is in the past or not far enough in the future
				$err = $err.JText::_('RS1_INPUT_SCRN_TIME_PASSED_ERR');
			} 
		}
						
		// if request_status = 'accepted', check max seats not exceeded
		// first just see if this booking's seats > the resource's
		$adjusted_max_seats = 0;
		if($resource != "-1"){
			if($res_detail->max_seats > 0 ){
				$adjusted_max_seats = getSeatAdjustments($startdate, $starttime, $endtime, $resource, $res_detail->max_seats);
				if($booked_seats > $res_detail->max_seats + $adjusted_max_seats){
					$err = $err.JText::_('RS1_ADMIN_SCRN_EXCEED_SEATS')."<br>";
				} else {	
					// now check to see if there are other bookings and if so how many total seats are booked.
					$currentcount = getCurrentSeatCount($startdate, $starttime, $endtime, $resource);
					if ($currentcount + $booked_seats > $res_detail->max_seats + $adjusted_max_seats){
					//if ($currentcount + $booked_seats > $res_detail->max_seats){
						$err = $err.JText::_('RS1_ADMIN_SCRN_EXCEED_SEATS')."<br>";
					}
					// if you want to check of a max total across ALL resources uncomment the next lines.
					// Replace 123 with the grand total across resources you want to limit to
//					$currentcount = getCurrentTotalSeatCount($startdate, $starttime, $endtime, $resource);
//					if ($currentcount + $booked_seats > 123){
//						$err = $err.JText::_('RS1_ADMIN_SCRN_EXCEED_SEATS')."<br>";
//					}
				}
			}
		}
		// Still need to check for no overlap
		$mystartdatetime = "STR_TO_DATE('".$startdate ." ". $starttime ."', '%Y-%m-%d %T')+ INTERVAL 1 SECOND";

		if($gap == 0 ){
			$myenddatetime = "STR_TO_DATE('".$enddate ." ". $endtime ."', '%Y-%m-%d %T')- INTERVAL 1 SECOND";
		} else {	
			$temp = ($gap*60)-1;
			$myenddatetime = "STR_TO_DATE('".$enddate ." ". $endtime ."', '%Y-%m-%d %T')+ INTERVAL ".$temp." SECOND";
		}
			
		$sql = "select count(*) from #__sv_apptpro3_requests "
		." where (resource = '". $resource ."')"
		." and (request_status = 'accepted' or request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").")"

//		." and ((". $mystartdatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
//		." or (". $myenddatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
//		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime .")"
//		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime ."))";
	
		." and ((". $mystartdatetime ." >= CONCAT(startdate, ' ', starttime) and ". $mystartdatetime ." <= CONCAT(enddate, ' ', endtime))"
		." or (". $myenddatetime ." >= CONCAT(startdate, ' ', starttime) and ". $myenddatetime ." <= CONCAT(enddate, ' ', endtime))"
		." or ( CONCAT(startdate, ' ', starttime) >= ". $mystartdatetime ." and CONCAT(startdate, ' ', starttime) <= ". $myenddatetime ." )"
		." or ( CONCAT(enddate, ' ', endtime) >= ". $mystartdatetime ." and CONCAT(enddate, ' ', endtime) <= ". $myenddatetime ."))";
		//print $sql; exit();

		try{
			$database->setQuery( $sql );
			$overlapcount = $database->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_val", "", "");
			$err = $err.JText::_('RS1_SQL_ERROR');
		}		
//		if ($overlapcount > $res_detail->max_dupes){
		if ($overlapcount >= ($res_detail->max_seats + $adjusted_max_seats) && $res_detail->max_seats > 0 ){
			$err = $err.JText::_('RS1_INPUT_SCRN_CONFLICT_ERR');
		}

		// make sure no overlap with book-offs
		$sql = "select count(*) from #__sv_apptpro3_bookoffs "
		." where (resource_id = '". $resource ."')"
		." and published = 1 and full_day = 'No' "
		." and ((". $mystartdatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (". $myenddatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime .")"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(off_date, '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime ."))";
		//print $sql; exit();
		try{
			$database->setQuery( $sql );
			$overlapcount = $database->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_val", "", "");
			$err = $err.JText::_('RS1_SQL_ERROR');
		}		
		if ($overlapcount >0){
			$err = $err.JText::_('RS1_INPUT_SCRN_BO_CONFLICT_ERR');
		}

		// make sure no overlap with rolling book-offs, rolling book-off is time only, ignores date
		// For rolling book-offs we need to see if the weekday matches the rolling_bookoff weekday.
		// Example rolling_bookoff = "0,1,1,1,0,1,0" = rb on mon,tue,wed,fri only
		$weekday = date("w",(strtotime($startdate)));
		$rb_filter = build_mask_filter($weekday);
		$mystartdatetime_rolling = "STR_TO_DATE(CONCAT(CURDATE(), '". $starttime ."'), '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
		$myenddatetime_rolling = "STR_TO_DATE(CONCAT(CURDATE(), '". $endtime ."'), '%Y-%m-%d %T')- INTERVAL 1 SECOND";
		$myenddatetime_rolling = "DATE_ADD( $myenddatetime_rolling, INTERVAL ".$gap." MINUTE)";

		$sql = "select count(*) from #__sv_apptpro3_bookoffs "
		." where (resource_id = '". $resource ."')"
		." and published = 1 and full_day = 'No' "
		." and rolling_bookoff like '".$rb_filter."' "
		." and ((". $mystartdatetime_rolling ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime_rolling ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (". $myenddatetime_rolling ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime_rolling ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime_rolling ." and STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime_rolling .")"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime_rolling ." and STR_TO_DATE(CONCAT(DATE_FORMAT(". $mystartdatetime_rolling .", '%Y-%m-%d') , DATE_FORMAT(bookoff_endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime_rolling ."))";
		//print $sql; exit();
		try{
			$database->setQuery( $sql );
			$overlapcount = $database->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_val", "", "");
			$err = $err.JText::_('RS1_SQL_ERROR');
		}		
		if ($overlapcount >0){
			$err = $err.JText::_('RS1_INPUT_SCRN_BO_CONFLICT_ERR');
		}

		// Check booking does not extend beyond end of last timeslot of the day.
		$weekday = getdate(strtotime($startdate)); 
		$end_of_day = "0000-00-00";
		
		// Get end time for day's last timeslot
		$sql = "SELECT MAX(timeslot_endtime) FROM #__sv_apptpro3_timeslots "
		." WHERE published = 1 ".($screen_type == "fd_gad"?"":" AND staff_only = 'No' ")
		." AND (start_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$startdate."' >= start_publishing) "
		." AND (end_publishing IS NULL OR end_publishing = '0000-00-00' OR '".$enddate."' <= end_publishing) "
		." AND day_number = ".$weekday["wday"]
		." AND resource_id = ";
		if($res_detail->timeslots == "Global" ){
			$sql .= "0";
		} else {
			$sql .= $res_detail->id_resources;
		}
		try{
			$database->setQuery( $sql );
			$end_of_day = $database->loadResult();
			//$err = $err.$end_of_day;
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_val", "", "");
			$err = $err.JText::_('RS1_SQL_ERROR');
		}		
		if( strtotime($endtime) > strtotime($end_of_day) ){
			$err = $err.JText::_('RS1_INPUT_SCRN_BEYOND_EOD');
		}

//		// Test for other bookings at the same time
//		$sql = "select count(*) from #__sv_apptpro3_requests ".
//			" where user_id = '". $user_id ."' ".
//			" AND startdate = '".$startdate."' ".
//			" AND starttime = '".$starttime."' ".
//			" AND (request_status = 'accepted' or request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").")";
//		try{
//			$database->setQuery( $sql );
//			$concurrent_bookings = $database->loadResult();
//		} catch (RuntimeException $e) {
//			logIt($e->getMessage(), "fe_val", "", "");
//			$err = $err.JText::_('RS1_SQL_ERROR');
//		}		
//		if ($concurrent_bookings > 0){
//			$err = $err."You already have a booking at this time";
//		}

		// Check for limiting
		// Check to see id user is an admin. Staff are not limted as the public are.	
		$safe_search_string = '%|' . $database->escape( $user_id, true ) . '|%' ;	
		$sql = "SELECT count(*) as count FROM #__sv_apptpro3_resources WHERE ".
			"resource_admins LIKE ".$database->quote( $safe_search_string, false ).";";
		$database->setQuery($sql);
		$check = NULL;
		if($user_id != ""){
			try{
				$check = $database -> loadObject();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "fe_val", "", "");
				echo JText::_('RS1_SQL_ERROR');	
				exit;
			}		
		}
		if ($apptpro_config->limit_bookings != "0" && ($check == null || $check->count == 0)){
			if($apptpro_config->limit_bookings_days == "1"){
				$sql = "select count(*) from #__sv_apptpro3_requests ";
				// If user_id is not passed in, as is the case of the mobile app, then use email to check limits.
				// If email is not passed in, it will be "-1" 
				if($user_id != ""){
					$sql .= " where user_id = '". $user_id ."' ";
				} else {
					$sql .= " where email = '". $email ."' ";
				}
				// To make the limit per resource per day uncomment the following line
				//$sql .= " AND resource = '".$resource ."' ";
				$sql .= " AND startdate = '".$startdate."' ".
				" AND (request_status = 'accepted' or request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").")";
				try{
					$database->setQuery( $sql );
					$otherbookingscount = $database->loadResult();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "fe_val", "", "");
					$err = $err.JText::_('RS1_SQL_ERROR');
				}		

				if ($otherbookingscount >= $apptpro_config->limit_bookings){
					$err = $err.JText::_('RS1_INPUT_SCRN_MAX_BOOKINGS_ERR');
				}
			} else {
				// if booking is inside x days window, count others
				if(strtotime($startdate) <= strtotime("+$apptpro_config->limit_bookings_days day")){
					// count bookings between now and $apptpro_config->limit_bookings_days
					$sql = "select count(*) from #__sv_apptpro3_requests ";
					if($user_id != ""){
						$sql .= " where user_id = '". $user_id ."' ";
					} else {
						$sql .= " where email = '". $email ."' ";
					}
					// To make the limit per resource uncomment the following line
					//$sql .= " AND resource = '".$resource ."' ";
					$sql .= " AND startdate >= CURDATE() AND startdate <= DATE_ADD(CURDATE(),INTERVAL $apptpro_config->limit_bookings_days DAY) ".
					" AND (request_status = 'accepted' or request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").")";
					try{
						$database->setQuery( $sql );
						$otherbookingscount = $database->loadResult();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "fe_val", "", "");
						$err = $err.JText::_('RS1_SQL_ERROR');
					}		
					if ($otherbookingscount >= $apptpro_config->limit_bookings){
						$err = $err.JText::_('RS1_INPUT_SCRN_MAX_BOOKINGS_ERR');
					}
				}
			}
		}

		// If PayPal set to Display & Block, fail if amount due > 0
		if($PayPal_mode == "DAB"){
			if($PayPal_due != "0.00"){
				$err = $err.JText::_('RS1_INPUT_SCRN_AMOUNT_DUE_BLOCKING_ERR');
			}
		}		
	}
	

	if($err == JText::_('RS1_INPUT_SCRN_VALIDATION_FAILED')){
//		require_once('recaptchalib.php');
//		$privatekey = "6LewBwsAAAAAAJ8bwQvTJY4FmtgWoWSTzdpGj9wS";
//		$resp = recaptcha_check_answer ($privatekey,
//										$_SERVER["REMOTE_ADDR"],
//										$jinput->getString('recap_chal'),
//										$jinput->getString('recap_resp'));
//		
//		if (!$resp->is_valid) {
//			$err = $err.JText::_('RS1_INPUT_CAPTCHA_ERR');//"The reCAPTCHA wasn't entered correctly. Please re-enter.";
//		} else {
			$err = JText::_('RS1_INPUT_SCRN_VALIDATION_OK');
//		}
	}

	echo $err;
	exit;	
	

?>