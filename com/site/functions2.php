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

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

function addToCalendar($req_id, $apptpro_config, $preventEcho="No"){

	if($apptpro_config->which_calendar != 'None'){
		$database = JFactory::getDBO();
		// get request info 
		$sql = 'SELECT * FROM #__sv_apptpro3_requests where id_requests = '.$req_id;
		try{
			$database->setQuery($sql);
			$row = NULL;
			$row = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt("addToCalendar-addToCalendar-1,".$e->getMessage(), "", "", "");
			if($preventEcho == "No"){
				echo JText::_('RS1_SQL_ERROR');
			}
			return false;
		}		
		
		// get resource info
		$database = JFactory::getDBO();
		$res_data = NULL;
		$sql = "SELECT * FROM #__sv_apptpro3_resources WHERE id_resources=".$row->resource;
		//echo $sql;
		//exit;
		try{
			$database->setQuery($sql);
			$res_data = $database->loadObject();
		} catch (RuntimeException $e) {
			logIt("addToCalendar-addToCalendar-2,".$e->getMessage(), "", "", "");
			if($preventEcho == "No"){
				echo JText::_('RS1_SQL_ERROR');
			}
			return false;
		}		

		// remove calendar entry
		// First delete calendar record for this request if one exists
		if($apptpro_config->which_calendar == "Google" and $row->google_event_id != ""){
			include_once( JPATH_SITE.DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_rsappt_pro3".DIRECTORY_SEPARATOR."svgcal.php" );
			$gcal = new SVGCal;
			// login
				$result = $gcal->login($res_data);				
			if( $result == "ok"){
				$client = $gcal->getClient();	
				if($client != null){
					if($row->google_calendar_id == ""){
						$gcal->deleteEventById($client, $row->google_event_id);
					} else {
						$result2 = $gcal->deleteEvent($client, $row->google_event_id, $row->google_calendar_id);
						if($result2 != "ok"){
							if($preventEcho == "No"){
								echo $result2;
							}
							local_logIt("addToCalendar-3,".$result2); 
						}
					}
				} else {
					local_logIt("addToCalendar-4,"."$client == null error"); 
				}
			} else {
				if($preventEcho == "No"){
					echo $result;
				}
				local_logIt("addToCalendar-5,".$result); 
			}						
		}				
			
		if ($row->request_status == 'accepted' ){
								
			switch ($apptpro_config->calendar_title) {
			  case 'resource.name': {
				$title_text = JText::_($res_data->name);	
				break;
			  }
			  case 'request.name': {
				$title_text = JText::_($row->name);	
				break;
			  }
 			  default: {
			    // must be a udf, get udf_value
				$sql = "SELECT udf_value FROM #__sv_apptpro3_udfvalues WHERE request_id = ".$req_id." and udf_id=".$apptpro_config->calendar_title;
				$database->setQuery( $sql);
				$title_text = $database->loadResult(); 		
			  }
			}
			if($apptpro_config->calendar_body2 != "") {
				$body_text = buildMessage($req_id, "calendar_body", "No");
			}
			stripslashes($body_text);
			stripslashes($title_text);
			$body_text = str_replace("'", "`", $body_text);
			$title_text = str_replace("'", "`", $title_text);

	
			if($apptpro_config->which_calendar == "Google"){			
				include_once( JPATH_SITE.DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_rsappt_pro3".DIRECTORY_SEPARATOR."svgcal.php" );
				include_once( JPATH_SITE.DIRECTORY_SEPARATOR."configuration.php" );			
				$CONFIG = new JConfig();
				$offset = $CONFIG->offset;
				
				// Joomla team decided to change the time zone offset, which is the number of hours offset from GMT, 
				// from a number to a city name. So now we need to figure out the real offset from the city name.. aarrgggh
				$TimeZonebyCity = new DateTimeZone($CONFIG->offset);
				$localTimebyCity = new DateTime($row->startdate, $TimeZonebyCity);
				$timeOffset = $TimeZonebyCity->getOffset($localTimebyCity);
				$offset = $timeOffset/3600;
						
				$offset = local_tz_offset_to_string($offset);
				$gcal = new SVGCal;
				// login
				$result = $gcal->login($res_data);
				if( $result != "ok"){
					if($preventEcho == "No"){
						echo $result;
					}
					local_logIt("addToCalendar-8,".$result); 
					return false;
				}		
				$gcal->setTZOffset($offset);
				// set calendar
				if($res_data->google_default_calendar_name != ""){
					try{
						$gcal->setCalID($res_data->google_default_calendar_name);
					}catch (Exception $e) { 
						if($preventEcho == "No"){
							echo $e->getMessage();
						}
						local_logIt("addToCalendar-9,".$e->getMessage()); 
						//return false;
					} 				
					//create event
					try{
						$event_id_full = $gcal->createEvent( 
						$title_text,
						$body_text, 
						'',
						trim($row->startdate),
						trim($row->starttime),
						trim($row->enddate),
						trim($row->endtime));
					}catch (Exception $e) { 
						if($preventEcho == "No"){
							echo $e->getMessage();
						}
						local_logIt("addToCalendar-10,".$e->getMessage());
						return false;					
					} 				
					// set event ID back in request
					$database = JFactory::getDBO();
						$sql = "UPDATE #__sv_apptpro3_requests SET google_event_id = '".$event_id_full."', ".
						"google_calendar_id = '".$res_data->google_default_calendar_name."' where id_requests = ".$req_id;
					try{
						$database->setQuery($sql);
						$database->execute();
					} catch (RuntimeException $e) {
						logIt($e->getMessage(), "functions2", "", "");
						echo JText::_('RS1_SQL_ERROR');
						exit;
					}
				}	
			}
		}
	} //end if($apptpro_config->which_calendar != 'None')
	return true;
}

function count_values($value_to_count, $array_to_check){
	// counts $value_to_count in $array_to_check
	$count = 0;
	if(in_array($value_to_count, $array_to_check)){
		foreach ($array_to_check as $value) {
		    if($value == $value_to_count){
				$count ++;
			}	
		}
		unset($value);		
	}
	
	return $count;		
}

function validEmail($email){
   $isValid = true;
	// use php's built-in filter
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
		return false;
	}
//	/**
//	Validate an email address.
//	Provide email address (raw input)
//	Returns true if the email address has the email 
//	address format and the domain exists.
//	*/
//   $atIndex = strrpos($email, "@");
//   if (is_bool($atIndex) && !$atIndex)
//   {
//      $isValid = false;
//   }
//   else
//   {
//      $domain = substr($email, $atIndex+1);
//      $local = substr($email, 0, $atIndex);
//      $localLen = strlen($local);
//      $domainLen = strlen($domain);
//      if ($localLen < 1 || $localLen > 64)
//      {
//         // local part length exceeded
//         $isValid = false;
//      }
//      else if ($domainLen < 1 || $domainLen > 255)
//      {
//         // domain part length exceeded
//         $isValid = false;
//      }
//      else if ($local[0] == '.' || $local[$localLen-1] == '.')
//      {
//         // local part starts or ends with '.'
//         $isValid = false;
//      }
//      else if (preg_match('/\\.\\./', $local))
//      {
//         // local part has two consecutive dots
//         $isValid = false;
//      }
//      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
//      {
//         // character not valid in domain part
//         $isValid = false;
//      }
//      else if (preg_match('/\\.\\./', $domain))
//      {
//         // domain part has two consecutive dots
//         $isValid = false;
//      }
//      else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
//                 str_replace("\\\\","",$local)))
//      {
//         // character not valid in local part unless 
//         // local part is quoted
//         if (!preg_match('/^"(\\\\"|[^"])+"$/',
//             str_replace("\\\\","",$local)))
//         {
//            $isValid = false;
//         }
//      }
//   }
   return $isValid;
}

function getMinute($strTimeval){
	// gets the minute of the day
	$hours = intval(substr($strTimeval,0,2));
	$mins = intval(substr($strTimeval,3,2));
	return ($hours*60)+$mins;
}

function showrow($res_detail, $grid_date, $weekday, $front_desk = ''){
	//return values: past, bookoff, dayoff, disabled, yes
	//if $grid_date < now, return false. This can happen id the user uses the '<<-' button to move te grid into the past


	if(strtotime($grid_date." 23:59") < sv_strtotime_now()){
		return "past";
	}
	// bookoffs
	$database = JFactory::getDBO(); 
	$sql = "SELECT count(*) FROM #__sv_apptpro3_bookoffs WHERE resource_id=".$res_detail->id_resources." AND off_date='".$grid_date."' ".
		" AND full_day='Yes' AND published=1";
	try{
		$database->setQuery( $sql );
		if($database->loadResult()>0){
			return "bookoff";
		}
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "functions2", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}		

	// book_dates
	if($res_detail->date_specific_booking == "Yes"){
		$sql = "SELECT count(*) FROM #__sv_apptpro3_book_dates WHERE resource_id=".$res_detail->id_resources." AND book_date='".$grid_date."' AND published=1";
		try{
			$database->setQuery( $sql );
			if($database->loadResult()==0){
				return "disabled";
			}
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
	}
	
	switch($weekday){
		 case '0': {
			if($res_detail->allowSunday == "No"){ return "dayoff";}
			break;
		  }
		 case '1': {
			if($res_detail->allowMonday == "No"){ return "dayoff";}
			break;
		  }
		 case '2': {
			if($res_detail->allowTuesday == "No"){ return "dayoff";}
			break;
		  }
		 case '3': {
			if($res_detail->allowWednesday == "No"){ return "dayoff";}
			break;
		  }
		 case '4': {
			if($res_detail->allowThursday == "No"){ return "dayoff";}
			break;
		  }
		 case '5': {
			if($res_detail->allowFriday == "No"){ return "dayoff";}
			break;
		  }
		 case '6': {
			if($res_detail->allowSaturday == "No"){ return "dayoff";}
			break;
		  }
	}

	if($res_detail->disable_dates_before != "Today" and	$res_detail->disable_dates_before != "Tomorrow"){
		// resource has specific dates set
		if(strtotime($grid_date." 23:59") < strtotime($res_detail->disable_dates_before)){
			return "disabled";
		}	
	} 
	if($res_detail->disable_dates_before == "Tomorrow"){
		if(strtotime($grid_date) <= sv_strtotime_now()){
			return "disabled";
		}	
	}

	if($res_detail->disable_dates_before == "XDays"){
		if(strtotime($grid_date) < strtotime("+ ".strval($res_detail->disable_dates_before_days)." day")){
			return "disabled";
		}	
	}


	if($res_detail->disable_dates_after != "Not Set" and $res_detail->disable_dates_after != "XDays"){
		// resource has specific dates set
		if(strtotime($grid_date) > strtotime($res_detail->disable_dates_after)){
			return "disabled";
		}	
	}

	if($res_detail->disable_dates_after == "XDays"){		
		if(strtotime($grid_date) >= strtotime("+ ".strval($res_detail->disable_dates_after_days)." day")){
			return "disabled";
		}	
	}

	return "yes";
	
}

function fullyBooked($booking, $res_row, $apptpro_config){
	// max_seats = 0 = no limit
	if($res_row->max_seats == 0){
		return false;
	}	
	
	// max_seats reached?
	$adjusted_max_seats = getSeatAdjustments($booking->startdate, $booking->starttime, $booking->endtime, $booking->resource);
	if($booking->booked_seats >= ($res_row->max_seats + $adjusted_max_seats)){
		return true;
	}	
	// now check to see if there are other bookings and if so how many total seats are booked.
	$currentcount = getCurrentSeatCount($booking->startdate, $booking->starttime, $booking->endtime, $booking->resource);
	if ($currentcount >= ($res_row->max_seats + $adjusted_max_seats)){
		return true;
	}
}

function DateAdd($interval, $number, $date) {

/*
		yyyy year 
		q quarter 
		q quarter 
		m month 
		y day of the year 
		d day 
		w weekday 
		ww week 
		h hour 
		n minute 
		s second 
*/

    $date_time_array = getdate($date);
    $hours = $date_time_array["hours"];
    $minutes = $date_time_array["minutes"];
    $seconds = $date_time_array["seconds"];
    $month = $date_time_array["mon"];
    $day = $date_time_array["mday"];
    $year = $date_time_array["year"];

    switch ($interval) {
    
        case "yyyy":
            $year+=$number;
            break;
        case "q":
            $year+=($number*3);
            break;
        case "m":
            $month+=$number;
            break;
        case "y":
        case "d":
        case "w":
            $day+=$number;
            break;
        case "ww":
            $day+=($number*7);
            break;
        case "h":
            $hours+=$number;
            break;
        case "n":
            $minutes+=$number;
            break;
        case "s":
            $seconds+=$number; 
            break;            
    }
       $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
    return $timestamp;
}

function getBookOffDescription($res_detail, $grid_date){
	$database = JFactory::getDBO(); 
	$sql = "SELECT description FROM #__sv_apptpro3_bookoffs WHERE resource_id=".$res_detail->id_resources." AND off_date='".$grid_date."' AND published=1";
	$database->setQuery( $sql );
	$row = $database -> loadObject();
	//echo $sql;
	//exit;
	return $row;
}


function getDayNamesArray(){
	return array(JText::_('RS1_SUN'),
	JText::_('RS1_MON'),
	JText::_('RS1_TUE'),
	JText::_('RS1_WED'),
	JText::_('RS1_THU'),
	JText::_('RS1_FRI'),
	JText::_('RS1_SAT')
	);
}

function getLongDayNamesArray($starting = 0){
	if($starting == 0){
		return array(JText::_('RS1_SUNDAY'),
				JText::_('RS1_MONDAY'),
				JText::_('RS1_TUESDAY'),
				JText::_('RS1_WEDNESDAY'),
				JText::_('RS1_THURSDAY'),
				JText::_('RS1_FRIDAY'),
				JText::_('RS1_SATURDAY')
				);
	} else {
		return array(JText::_('RS1_MONDAY'),
				JText::_('RS1_TUESDAY'),
				JText::_('RS1_WEDNESDAY'),
				JText::_('RS1_THURSDAY'),
				JText::_('RS1_FRIDAY'),
				JText::_('RS1_SATURDAY'),
				JText::_('RS1_SUNDAY')
				);
	}	

}

function getMonthNamesArray(){
	return array(JText::_('RS1_JANUARY'),
	JText::_('RS1_FEBRUARY'),
	JText::_('RS1_MARCH'),
	JText::_('RS1_APRIL'),
	JText::_('RS1_MAY'),
	JText::_('RS1_JUNE'),
	JText::_('RS1_JULY'),
	JText::_('RS1_AUGUST'),
	JText::_('RS1_SEPTEMBER'),
	JText::_('RS1_OCTOBER'),
	JText::_('RS1_NOVEMBER'),
	JText::_('RS1_DECEMBER')
	);
}


function getCBdata($cb_field_name, $userid){

	if($cb_field_name == ""){
		return;
	}
	
	$database = JFactory::getDBO();	
	$tables = JFactory::getDbo()->getTableList();
	if(in_array($database->replacePrefix('#__comprofiler_fields'), $tables)){
		$database->setQuery("SELECT ".$cb_field_name." FROM #__comprofiler WHERE user_id = ".$userid );
		try{
			$retval = $database->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		
		return $retval;
	}
	return;
}

function getProfiledata($profile_key, $userid){

	if($profile_key == ""){
		return;
	}
	$database = JFactory::getDBO();
//	$tables = JFactory::getDbo()->getTableList();
//	if(in_array($database->replacePrefix('#__user_profiles'), $tables)){
		$database->setQuery("SELECT profile_value FROM #__user_profiles WHERE user_id = ".$userid." AND profile_key='".$profile_key."'" );
		try{
			$retval = $database->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}				
		return json_decode($retval);	
//	}
}


function getJSdata($js_field_name, $userid){
	if($userid == ""){
		return;
	}
	$jspath = JPATH_ROOT .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_community';
	include_once($jspath.DIRECTORY_SEPARATOR. 'libraries' .DIRECTORY_SEPARATOR. 'core.php');

	$JSuser = CFactory::getUser($userid);
	return $JSuser->getInfo($js_field_name);
}


function blocked_by_bookoff($slot, $part_day_bookoffs){
	// Note: $part_day_bookoffs may be multiple ranges for single day
	// If slot starts during bookoff time return true. No test made to determine if slot ends during bookoff 
	// as bookoff range should be made on timeslot boundaries.
	foreach ($part_day_bookoffs as $part_day_bookoff){ 
		//local_logIt("timeslot_starttime:".$slot->timeslot_starttime." - part_day_bookoff->bookoff_starttime:".$part_day_bookoff->bookoff_starttime); 
		if( ( $part_day_bookoff->rolling_bookoff != 'No' 
			&& rb_day($part_day_bookoff->rolling_bookoff, $slot->day_number)) 
			|| ($part_day_bookoff->rolling_bookoff == 'No') ){ 
				// do check if this is a part day book-off, or a rolling book-off and the day matches
				if(strtotime($slot->timeslot_starttime)+1 >= strtotime($part_day_bookoff->bookoff_starttime)
				 && strtotime($slot->timeslot_starttime)+1 <= strtotime($part_day_bookoff->bookoff_endtime)){		
					//local_logIt("block this one: ".$slot->timeslot_starttime); 
					return true;
				}	
			}
	}
	return false;
}



function saveToDB($name,$user_id,$phone,$email,$sms_reminders,$sms_phone,$sms_dial_code,$resource, $category,
		$service_name,$startdate,$starttime,$enddate,$endtime,$request_status,$cancel_code,$grand_total,$amount_due,$booking_deposit,
		$coupon_code,$booked_seats,$applied_credit,$comment, $admin_comment='', $manual_payment_collected='', $gift_cert=''){

		$lang = JFactory::getLanguage();
		$database = JFactory::getDBO();
		
		// ABPro uses a session variable to prevent duplicate booking if the user does a refresh on the confimration screen.
		// IF the user sits on thet screen for 20 minutes (or whatever your session timeout period is) then does a browser refresh
		// the session variable will be gone and ABPro cannot tell that the cached data from the browser is not new data.
		// Fortunately it seems very rare.
		// I have tried all the tricks for telling the browser not to cache the form but (from searching the Internet) there seems to be 
		// no solution that works universally.
		// If you encounter problems with people refreshing after session time out there are a couple of options:
		// 1.) Put a messge on the confimration screen, like you often see on web sites, telling the person to refrain from using the 
		//    back button or refreshing as it may cause a duplicate booking.
		// 2.) Uncomment thet code below which does a chcek to see if the booking is a duplicate. The down side of this is tat some sites
		//    allow duplictaes and this will stop them.
		
		// check for dupe caused by refresh after session timeout
//		$sql = "Select count(*) FROM #__sv_apptpro3_requests WHERE ".
//		"name = '".$database->escape($name)."' ".
//		"AND resource = '".$resource."' ".
//		"AND startdate = '".$startdate."' ".
//		"AND starttime = '".$starttime."' ".
//		"AND enddate = '".$enddate."' ".
//		"AND endtime = '".$endtime."' ".
//		"AND booked_seats = '".$booked_seats."' ".
//		"AND request_status = '".$request_status."' ";
//		try{
//			$database->setQuery($sql);
//			$dupe_check = $database->loadResult(); 
//			$database->execute();
//				} catch (RuntimeException $e) {
//					logIt($e->getMessage(), "functions2", "", "");
//					echo JText::_('RS1_SQL_ERROR');
//					exit;
//				}
//		if($dupe_check > 0){
//			echo "Duplicate booking not saved";
//			exit;
//		}

		if($user_id == null || $user_id == ""){
			$user_id = 0;
		}
		if($amount_due == 0.00){
			$payment_status = "paid";
		} else {
			$payment_status = "pending";
		}

		// check to see if $applied credit has multiple components (user and gift cert)
		$uc_used = 0;
		$gc_used = 0;
		$applied_credit_array = null;
		
		if(strpos($applied_credit, "|") > 0){
			$applied_credit_array = explode("|", $applied_credit);
		} else {	
			if($applied_credit == ""){
				$applied_credit = 0;
			}
		}
		if(sv_count_($applied_credit_array) > 0){
			$applied_credit = $applied_credit_array[0];
			$uc_used = $applied_credit_array[1];
			$gc_used = $applied_credit_array[2];						
		}

		if($manual_payment_collected!=''){
			// total should be payment + amount still due)
			$grand_total = floatval($amount_due) + floatval($manual_payment_collected);
		}

		// Check again for no overlap - this was checked in validation so it will only fail if someone 
		// has booked in the time it took the validation to get back to the client and the form submit itself
		//(a second or two?)
		// get resource info for the selected resource
		$sql = 'SELECT * FROM #__sv_apptpro3_resources where id_resources = '.(int)$resource;
		try{
			$database->setQuery($sql);
			$res_detail = NULL;
			$res_detail = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
			$database->setQuery("UNLOCK TABLES;");
			$database->execute();
			exit;
		}		

		// get config info
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');	
			exit;
		}		

		$adjusted_max_seats = getSeatAdjustments($startdate, $starttime, $endtime, $resource, $res_detail->max_seats);

// lock unlock throws error"This command is not supported in the prepared statement protocol yet "
 
//		$database->setQuery("LOCK TABLES #__sv_apptpro3_requests WRITE");
//		if(!$database->execute()){
//			echo "Unable to LOCK tables";
//			exit;
//		}
		
		$mystartdatetime = "STR_TO_DATE('".$startdate ." ". $starttime ."', '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
		$myenddatetime = "STR_TO_DATE('".$enddate ." ". $endtime ."', '%Y-%m-%d %T')- INTERVAL 1 SECOND";
		$sql = "select count(*) from #__sv_apptpro3_requests "
		." where (resource = '". $resource ."')"
		." and (request_status = 'accepted' or request_status = 'pending'".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"")." )"
		." and ((". $mystartdatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (". $myenddatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime .")"
		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime ."))";
		//echo $sql;
		//exit();
		try{
			$database->setQuery( $sql );
			$overlapcount = $database->loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
//			$database->setQuery("UNLOCK TABLES;");
//			$database->execute();
			exit;
		}		

		if ($overlapcount > ($res_detail->max_seats + $adjusted_max_seats) && $res_detail->max_seats > 0 ){
//			$database->setQuery("UNLOCK TABLES;");
//			$database->execute();
			echo JText::_('RS1_INPUT_SCRN_CONFLICT_ERR');
			// serious problem, bail out
			exit;
		}

		if($res_detail->max_seats > 0 ){
			if($booked_seats > ($res_detail->max_seats + $adjusted_max_seats)){
				echo JText::_('RS1_ADMIN_SCRN_EXCEED_SEATS');
				// serious problem, bail out
//				$database->setQuery("UNLOCK TABLES;");
//				$database->execute();
				logIt(JText::_('RS1_ADMIN_SCRN_EXCEED_SEATS'), "functions2", "", "");
				exit;
			} else {	
				// now check to see if there are other bookings and if so how many total seats are booked.
				$currentcount = getCurrentSeatCount($startdate, $starttime, $endtime, $resource);
				if ($currentcount + $booked_seats > ($res_detail->max_seats + $adjusted_max_seats)){
					echo JText::_('RS1_ADMIN_SCRN_EXCEED_SEATS');
					// serious problem, bail out
//					$database->setQuery("UNLOCK TABLES;");
//					$database->execute();
					logIt(JText::_('RS1_ADMIN_SCRN_EXCEED_SEATS'), "functions2", "", "");
					exit;
				}
				// if you want to check of a max total across ALL resources uncomment the next lines.
				// Replace 123 with the grand total across resources you want to limit to
//					$currentcount = getCurrentTotalSeatCount($startdate, $starttime, $endtime, $resource);
//					if ($currentcount + $booked_seats > 123){
//						$err = $err.JText::_('Exceeded MAX for all resources')."<br>";
//					}
			}
		}
		
		// save to db
		$sSql = "INSERT INTO #__sv_apptpro3_requests(".
		"name, ".
		"user_id, ".
		"phone, ".
		"email, ".
		"sms_reminders, ".
		"sms_phone, ".
		"sms_dial_code, ".
		"resource, ".
		"category, ".
		"service, ".
		"startdate, ".
		"starttime, ".
		"enddate, ".
		"endtime, ".
		"request_status, ".
		"payment_status, ".
		"cancellation_id, ".
		"booking_total, ".
		"booking_due, ".
		"booking_deposit, ".
		"credit_used, ".
		"coupon_code, ".
		"booked_seats, ".
		"admin_comment, ".
		"booking_language, ".
		"manual_payment_collected, ".
		"gift_cert, ".
		"comment ";
		$sSql = $sSql.") VALUES(".
		"'".$database->escape($name)."',".
		"".$user_id.",".
		"'".$database->escape($phone)."',".
		"'".$database->escape($email)."',".
		"'".$sms_reminders."',".
		"'".$database->escape($sms_phone)."',".
		"'".$sms_dial_code."',".
		"'".$resource."',".
		"'".$category."',".
		"'".$database->escape($service_name)."',".
		"'".$startdate."',".
		"'".$starttime."',".
		"'".$enddate."',".
		"'".$endtime."',".
		"'".$request_status."',".
		"'".$payment_status."',".
		"'".$cancel_code."',".
		$grand_total.",".
		$amount_due.",".
		$booking_deposit.",".
		$applied_credit.",".
		"'".$coupon_code."',".
		"'".$booked_seats."',".
		"'".$database->escape($admin_comment)."',".
		"'".$lang->getTag()."',".
		"".$database->escape(($manual_payment_collected==""?0.0:$manual_payment_collected)).",".
		"'".$database->escape($gift_cert)."',".
		"'".$database->escape($comment)."'";
		$sSql = $sSql.")";
//		echo $sSql;
//		exit;
		try{
			$database->setQuery($sSql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
//			$database->setQuery("UNLOCK TABLES;");
//			$database->execute();
			exit;
		}

		// need request id to pass through to PayPal (so PP can pass it back with IPN)
	 	$sSql = "SELECT LAST_INSERT_ID() AS last_id";
		try{
			$database->setQuery($sSql);
			$last_id = NULL;
			$last_id = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
//			$database->setQuery("UNLOCK TABLES;");
//			$database->execute();
			return -1;
		}		

//		$database->setQuery("UNLOCK TABLES;");
//		$database->execute();
		
		// if credit used..
		if(floatval($applied_credit) > 0.00){
			// $applied_credit = total credit applied to this booking
			// $uc_used = user credits consumed by this booking
			// $gc_used = gift certificate credits consumber by this booking		
			if(floatval($uc_used) > 0.00){
				// adjust credit balance 
				$sql = "UPDATE #__sv_apptpro3_user_credit SET balance = balance - ".$uc_used." WHERE user_id = ".$user_id;
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "functions2", "", "");
					echo JText::_('RS1_SQL_ERROR');
					return -1;
				}		
				
				// add credit audit
				$sql = 'INSERT INTO #__sv_apptpro3_user_credit_activity (user_id, request_id, decrease, comment, operator_id, balance) '.
				"VALUES (".$user_id.",".
				$last_id->last_id.",".
				$uc_used.",".
				"'".JText::_('RS1_ADMIN_CREDIT_ACTIVITY_CREDIT_USED')."',".
				$user_id.",".
				"(SELECT balance from #__sv_apptpro3_user_credit WHERE user_id = ".$user_id."))";
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "functions2", "", "");
					echo JText::_('RS1_SQL_ERROR');
					return -1;
				}		
			}

			if(floatval($gc_used) > 0.00){
				// adjust credit balance 
				$sql = "UPDATE #__sv_apptpro3_user_credit SET balance = balance - ".$gc_used." WHERE gift_cert = '".$gift_cert."'";
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "functions2", "", "");
					echo JText::_('RS1_SQL_ERROR');
					return -1;
				}		
				
				// add credit audit
				$sql = 'INSERT INTO #__sv_apptpro3_user_credit_activity (user_id, gift_cert, request_id, decrease, comment, operator_id, balance) '.
				"VALUES (".($user_id==""?-2:$user_id).",".
				"'".$gift_cert."',".
				$last_id->last_id.",".
				$gc_used.",".
				"'".JText::_('RS1_ADMIN_GC_ACTIVITY_CREDIT_USED')."',".
				($user_id==""?-2:$user_id).",".
				"(SELECT balance from #__sv_apptpro3_user_credit WHERE gift_cert = '".$gift_cert."'))";
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "functions2", "", "");
					echo JText::_('RS1_SQL_ERROR');
					return -1;
				}		
			}
			
			
			// if paid in full by credit, set paystatus to paid
			if(floatval($amount_due)==0.00){
				$sql = "UPDATE #__sv_apptpro3_requests SET payment_status = 'paid' WHERE id_requests = ".$last_id->last_id;
				try{
					$database->setQuery($sql);
					$database->execute();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "functions2", "", "");
					echo JText::_('RS1_SQL_ERROR');
					return -1;
				}		
			}
		}
		
		return $last_id;		
}



function getDefaultCalInfo($which_calendar, $res_data, &$cat_id, &$cal_id){
	$database = JFactory::getDBO();

	// get category id from name
	if($which_calendar == "EventList"){
		$sql = "SELECT id as cat_id FROM #__eventlist_categories WHERE catname='".$res_data->default_calendar_category."'";
		$database->setQuery( $sql);
		$cat_id = null;
		$cat_id = $database->loadResult(); 

	} else if($which_calendar == "JEvents"){
		$sql = "SELECT id FROM #__categories WHERE title='".$res_data->default_calendar_category."' ".
		"AND section = 'com_events'";
		$database->setQuery( $sql);
		$cat_id = null;
		$cat_id = $database->loadResult(); 

	} else if($which_calendar == "Thyme"){
		$sql = "SELECT id FROM thyme_calendars WHERE title='".$res_data->default_calendar_category."' ";
		$database->setQuery( $sql);
		$cat_id = null;
		$cat_id = $database->loadResult(); 

	} else if($which_calendar == "JCalPro2"){
		$sql = "SELECT cat_id FROM #__jcalpro2_categories WHERE cat_name='".$res_data->default_calendar_category."'";
		$database->setQuery( $sql);
		$cat_id = null;
		$cat_id = $database->loadResult(); 

		$sql = "SELECT cal_id FROM #__jcalpro2_calendars WHERE cal_name='".$res_data->default_calendar."'";
		$database->setQuery( $sql);
		$cal_id = null;
		$cal_id = $database->loadResult(); 

	} else if($which_calendar == "JCalPro"){
		$sql = "SELECT cat_id FROM #__jcalpro_categories WHERE cat_name='".$res_data->default_calendar_category."'";
		$database->setQuery( $sql);
		$cat_id = null;
		$cat_id = $database->loadResult(); 
	}
	return;
}

function purgeStalePayPalBookings($minutes_to_stale){
	// If a customer goes to PayPal then bails out without paying PayPal sends no IPN back and so the booking is left 'pending' and locked
	// This function is called when a user opens the bookings screen (if Purge Stale PayPal = Yes in config)
	// This function looks for bookings with status 'pending' and create timestamp + current time > Minutes to Stale in config, if found the booking
	// is set to status 'timeout' to free the timeslot for another user.
	// Note: booking timestamp and this function are both server time so no timezone adjustment is required.
	
	$database = JFactory::getDBO();

// HOW-TO code STARTS here
//		$sql = "SELECT id_requests FROM #__sv_apptpro3_requests ".
//		" WHERE request_status = 'pending' ".
//		" AND DATE_ADD(created, INTERVAL ".$minutes_to_stale." MINUTE) < NOW()";
//	try{
//		$database->setQuery($sql);
//		$purge_ids = $database->loadColumn();
//		} catch (RuntimeException $e) {
//			logIt($e->getMessage(), "functions2", "", "");
//			echo JText::_('RS1_SQL_ERROR');
//			exit;
//		}
//
//	if(sv_count_($purge_ids) > 0 ){	
//		$str_ids = implode(",", $purge_ids);
//		echo $str_ids;
//		$sql = 'SELECT * FROM #__sv_apptpro3_config';
//		try{
//			$database->setQuery($sql);
//			$apptpro_config = NULL;
//			$apptpro_config = $database -> loadObject();
//			} catch (RuntimeException $e) {
//				logIt($e->getMessage(), "purgeStalePayPalBookings", "", "");
//				echo JText::_('RS1_SQL_ERROR');
//				exit;
//			}		
//		$mailer = JFactory::getMailer();		
//		try{
//			$mailer->setSender(array($apptpro_config->mailFROM,null));
//		}
//		catch (Exception $e){
//			logIt("Error on setting email FROM address: ".$e->getMessage(), "purgeStalePayPalBookings", "", "");
//			return false;		
//		}
//		$mailer->addRecipient($apptpro_config->mailTO);
//		$mailer->setSubject('Booking(s) timed out');
//		$mailer->setBody("Booking(s) set to status Timeout: ". $str_ids);
//		$send = $mailer->Send();
//		if ( $send !== true ) {
//		    logIt('Error sending email: '.$e->getMessage(), "purgeStalePayPalBookings", "", ""); 
//		}
//	}
// HOW-TO code ENDS here

		$sql = "UPDATE #__sv_apptpro3_requests SET request_status = 'timeout' ".
		" WHERE request_status = 'pending' ".
		" AND DATE_ADD(created, INTERVAL ".$minutes_to_stale." MINUTE) < NOW()";
	try{
		$database->setQuery($sql);
		$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
	
	$sql = "DELETE FROM #__sv_apptpro3_cart WHERE DATE_ADD(created, INTERVAL ".$minutes_to_stale." MINUTE) < NOW()";
	try{
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "functions2", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}

}

function local_logIt($err){
	$database = JFactory::getDBO();
	$errsql = "insert into #__sv_apptpro3_errorlog (description) ".
		" values('".$database->escape(substr($err,0))."')";
	$database->setQuery($errsql);
	$database->execute();

}

function local_tz_offset_to_string($tzoffset, $no_colon="No"){
	// converts 
	// "0"	-> "+00:00"
	// "2"	-> "+02:00"
	// "2.5"	-> "+02:30"
	// 10	-> "+10:00"
	// 10.5 -> "+10:30"
	// -2	-> "-02:00"
	// -2.5	-> "-02:30"
	// -10	-> "-10:00"
	// -10.5-> "-10:30"
	
	$valOffset = strval($tzoffset);
	if($valOffset == 0){
		return "+00:00";
	}
	$offset_hr_min = explode(".", $tzoffset);
	if(sv_count_($offset_hr_min)>1){
		if($offset_hr_min[1] == "5"){
			$offset_min = ":30";
		}else{
			$offset_min = ":00";
		}
	} else {
		$offset_min = ":00";
	}

	if($valOffset > 0){
		// + offset
		if(strval($offset_hr_min[0]) < 10){
			$offset_hour = "+0".$offset_hr_min[0];
		} else {
			$offset_hour = "+".$offset_hr_min[0];
		}
	}
	if($valOffset < 0){	
		// - offset
		if(abs(strval($offset_hr_min[0])) < 10){
			$offset_hour = substr($offset_hr_min[0],0,1)."0".substr($offset_hr_min[0],1);			
		} else {
			$offset_hour = $offset_hr_min[0];
		}
	}	
	if($no_colon == "Yes"){
		$offset_min = str_replace(":","",$offset_min);
	}
	return $offset_hour.$offset_min;
}	

function display_this_resource($res_detail, $user){
		$display_this_resource = true;
		//if($res_detail->name == JText::_('RS1_GAD_SCRN_RESOURCE_DROPDOWN')){
		if($res_detail->id_resources == 0){
			return true;
		}
		// is this resource restricted to a specific group?
		if($res_detail->access == 'everyone' || stripos($res_detail->access, "|1|") > -1){
			$display_this_resource = true;							
		} else {
			// yes further checking is reqiuired..
			if($res_detail->access == "public_only"){ // for old verison compat
				// do not show if user logged in
				if(!$user->guest){
					$display_this_resource = false;
				}
			} else {
				// access is not everyone and not public_only so we need to see if the user is a member of the group specified
				$groups = str_replace("||", ",", $res_detail->access);
				$groups = str_replace("|", "", $groups);			

				$sql = "SELECT count(*) FROM #__user_usergroup_map WHERE group_id IN (".$groups.") AND user_id = ".$user->id;
				//echo $sql;
				$database = JFactory::getDBO();
				try{
					$database->setQuery($sql);
					$match = null;
					$match = $database->loadResult();
				} catch (RuntimeException $e) {
					logIt($e->getMessage(), "functions2", "", "");
					echo JText::_('RS1_SQL_ERROR');
					exit;
				}		
				if($match == null || $match < 1){
					$display_this_resource = false;
				}
			}
			
		}
		return $display_this_resource;
}

function do_fe_export(){
		//front end export
		$jinput = JFactory::getApplication()->input;
		$filter_order = $jinput->get( 'req_filter_order', 'req_filter_order' );		
		$filter_order_dir = $jinput->get( 'req_filter_order_Dir', 'req_filter_order_Dir' );				

		$frompage = $jinput->getString( 'frompage', '' );
		if($frompage == "front_desk"){
			$uid = $jinput->post->get('cid', array(), 'ARRAY');
		} else {
			$uid = $jinput->post->get('cid_req', array(), 'ARRAY');
		}
		if (!is_array( $uid ) || sv_count_($uid ) < 1 || $uid[0]==0) {
			echo JText::_( 'No item(s) selected for Export' );
			exit;
		}

		$database = JFactory::getDBO();
		// get config info
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try{
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_functions2", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		$lang = JFactory::getLanguage();
		$langTag =  $lang->getTag();
		if($langTag == ""){
			$langTag = "en_GB";
		}
		$sql = "SET lc_time_names = '".str_replace("-", "_",$langTag)."';";
		try{
			$database->setQuery($sql);
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_functions2", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		}		

		// get export settings
		// First core
		$sql = "SELECT * FROM #__sv_apptpro3_export_columns WHERE export_column_type = 'core' ORDER BY export_order";
		try{
			$database->setQuery($sql);
			$export_columns = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_functions2", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		$sql = ' SELECT ';
			foreach($export_columns as $export_column) {	
				if($export_column->export_field == "startdate"){
					$sql .= " DATE_FORMAT(#__".$export_column->export_table.".".$export_column->export_field.", '".php_date_string_to_sql($apptpro_config->long_date_format, "SQL")."')";
					if($export_column->export_header != ""){
						$sql .= " AS '".$export_column->export_header."'";
					}
				} else if($export_column->export_field == "starttime" || $export_column->export_field == "endtime"){
					$sql .= " DATE_FORMAT(#__".$export_column->export_table.".".$export_column->export_field.", '".$export_column->export_format."')";
					if($export_column->export_header != ""){
						$sql .= " AS '".$export_column->export_header."'";
					}					 
				} else {
					$sql .= "#__".$export_column->export_table.".".$export_column->export_field;
					if($export_column->export_header != ""){
						$sql .= " AS '".$export_column->export_header."'";
					}
				}
				$sql .= ",";
			}

		$sql .= '#__sv_apptpro3_requests.id_requests,'. // needed for table JOINS
				" CONCAT(#__sv_apptpro3_requests.startdate, ' ', #__sv_apptpro3_requests.starttime) as startdatetime "; // needed for ordering
		$sql = rtrim($sql, ',');
		$sql .= ' FROM ( #__sv_apptpro3_requests '.
				' LEFT JOIN #__sv_apptpro3_categories ON #__sv_apptpro3_requests.category = #__sv_apptpro3_categories.id_categories '.
				' LEFT JOIN #__sv_apptpro3_resources ON #__sv_apptpro3_requests.resource = #__sv_apptpro3_resources.id_resources  '.
				' LEFT JOIN #__sv_apptpro3_services ON #__sv_apptpro3_requests.service = #__sv_apptpro3_services.id_services )  '.
				" WHERE #__sv_apptpro3_requests.id_requests IN (".implode(",", $uid).")";
		if($filter_order != ""){
			$sql .= " ORDER BY ".$filter_order;
			if($filter_order_dir != ""){
				$sql .= " ".$filter_order_dir;
			}
		}
		//echo $sql;
		//exit;
	
		$database = JFactory::getDBO();
		try{
			$database->setQuery($sql);
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_functions2", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		
		//print_r($rows);
		//exit;		


		// get udfs
		$sql = "SELECT * FROM #__sv_apptpro3_export_columns WHERE export_column_type = 'udf' ORDER BY export_order";
		try{
			$database->setQuery($sql);
			$export_columns = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_functions2", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		$sql2 = " SELECT #__sv_apptpro3_udfs.udf_label, #__sv_apptpro3_export_columns.export_header ".
			" FROM #__sv_apptpro3_udfs ".
			"  INNER JOIN #__sv_apptpro3_export_columns ".
			"    ON (#__sv_apptpro3_export_columns.export_foreign_key = #__sv_apptpro3_udfs.id_udfs ".
			"      AND #__sv_apptpro3_export_columns.export_column_type = 'udf' )".
			"WHERE #__sv_apptpro3_udfs.published = 1";
		$sql2 .= " ORDER BY export_order";
		try{
			$database->setQuery($sql2);
			$udf_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_functions2", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		// get extras
		$sql3 = " SELECT #__sv_apptpro3_extras.*, #__sv_apptpro3_export_columns.export_header ".
			" FROM #__sv_apptpro3_extras ".
			"  INNER JOIN #__sv_apptpro3_export_columns ".
			"    ON (#__sv_apptpro3_export_columns.export_foreign_key = #__sv_apptpro3_extras.id_extras ".
			"      AND #__sv_apptpro3_export_columns.export_column_type = 'extra' )".
			"WHERE #__sv_apptpro3_extras.published = 1";
		$sql3 .= " ORDER BY export_order";
		try{
			$database->setQuery($sql3);
			$extra_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_functions2", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}		

		// get seat types
		$sql3 = " SELECT #__sv_apptpro3_seat_types.*, #__sv_apptpro3_export_columns.export_header ".
			" FROM #__sv_apptpro3_seat_types ".
			"  INNER JOIN #__sv_apptpro3_export_columns ".
			"    ON (#__sv_apptpro3_export_columns.export_foreign_key = #__sv_apptpro3_seat_types.id_seat_types ".
			"      AND #__sv_apptpro3_export_columns.export_column_type = 'seat' )".
			"WHERE #__sv_apptpro3_seat_types.published = 1";
		$sql3 .= " ORDER BY export_order";
		try{
			$database->setQuery($sql3);
			$seat_type_rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}	

		ob_end_clean();
			
		$file_name = 'export_sv_apptpro3_requests.csv';
			
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Accept-Ranges: bytes');
		header('Content-Disposition: attachment; filename='.basename($file_name).';');

		header('Content-Type: text/plain; '.'_ISO');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Pragma: no-cache');

		$csv_save = '';
		if (!empty($rows)) {
				$comma = ',';
				$CR = "\r";
				// Make csv rows for field name
				$i=0;
				$fields = $rows[0];
				$csv_fields = '';
				foreach($fields as $name=>$val) {
						$i++;
						// don't add rows added only for processing
						if($name != "id_requests" && $name != "startdatetime"){
							$csv_fields .= $name.$comma;
						}
				}
				// add columns for udfs
				foreach($udf_rows as $udf_row) {
					if($udf_row->export_header != ""){
						$csv_fields .= $udf_row->export_header.$comma;
					} else {
						$csv_fields .= $udf_row->udf_label.$comma;
					}
				}
				// add columns for extras
				foreach($extra_rows as $extra_row) {
					if($extra_row->export_header != ""){
						//$csv_fields .= $extra_row->export_header." (".$extra_row->extras_cost."/".$extra_row->cost_unit.")".$comma;
						$csv_fields .= $extra_row->export_header.$comma;
					} else {
						//$csv_fields .= $extra_row->extras_label." (".$extra_row->extras_cost."/".$extra_row->cost_unit.")".$comma;
						$csv_fields .= $extra_row->extras_label.$comma;
					}
				}
				// add columns for seat types
				foreach($seat_type_rows as $seat_type_row) {
					if($seat_type_row->export_header != ""){
						$csv_fields .= $seat_type_row->export_header.$comma;
					} else {
						$csv_fields .= $seat_type_row->seat_type_label.$comma;
					}
				}
				
				// Make csv rows for data
				$csv_values = '';
				foreach($rows as $row) {
						$i=0;
						$comma = ',';
						foreach($row as $name=>$val) {
								$i++;
								// don't add rows added only for processing
								if($name != "id_requests" && $name != "startdatetime"){
									$csv_values .= '"'.$val.'"'.$comma;
								}
						}
						// add udf columns data
						// get udf values for this request						
						$sql4IN = "SELECT export_foreign_key FROM #__sv_apptpro3_export_columns".						
								" WHERE export_column_type = 'udf' ORDER BY export_order";		
						try{	
							$database->setQuery($sql4IN);
							$udf_rows_IN = $database -> loadColumn();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "fe_functions2", "", "");
							echo JText::_('RS1_SQL_ERROR');
							return false;
						}		
						$strIN = implode(",", $udf_rows_IN); // IN values as string
						if($strIN != ""){
							$sql2 = "SELECT #__sv_apptpro3_udfvalues.* FROM ".
								" #__sv_apptpro3_udfs LEFT JOIN #__sv_apptpro3_udfvalues ".
								" ON #__sv_apptpro3_udfs.id_udfs = #__sv_apptpro3_udfvalues.udf_id ".
								" AND #__sv_apptpro3_udfvalues.request_id = ".$row->id_requests .
								" WHERE #__sv_apptpro3_udfs.id_udfs IN (".$strIN.") ".
								" AND #__sv_apptpro3_udfs.published=1 ".
								" ORDER BY FIELD(id_udfs, $strIN)";
								
							try{
								$database->setQuery($sql2);
								$udf_value_rows = $database -> loadObjectList();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "fe_functions2", "", "");
								echo JText::_('RS1_SQL_ERROR').$e->getMessage();
								exit;
							}		
							foreach($udf_value_rows as $udf_value_row) {
								$csv_values .= '"'.$udf_value_row->udf_value.'"'.$comma;
							}
						}
						
						// add extras columns data
						// get extras values for this request						
						$sql4IN = "SELECT export_foreign_key FROM #__sv_apptpro3_export_columns".						
								" WHERE export_column_type = 'extra' ORDER BY export_order";		
						try{	
							$database->setQuery($sql4IN);
							$extra_rows_IN = $database -> loadColumn();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "fe_functions2", "", "");
							echo JText::_('RS1_SQL_ERROR');
							return false;
						}		
						$strIN = implode(",", $extra_rows_IN); // IN values as string
						if($strIN != ""){
							$sql3 = "SELECT #__sv_apptpro3_extras_data.*,#__sv_apptpro3_extras.* FROM ".
								" #__sv_apptpro3_extras LEFT JOIN #__sv_apptpro3_extras_data ".
								" ON #__sv_apptpro3_extras.id_extras = #__sv_apptpro3_extras_data.extras_id ".
								" AND #__sv_apptpro3_extras_data.request_id = ".$row->id_requests .
								" WHERE #__sv_apptpro3_extras.id_extras IN (".$strIN.") ".
								" AND #__sv_apptpro3_extras.published=1 ".
								" ORDER BY FIELD(id_extras, $strIN)";
								
							try{
								$database->setQuery($sql3);
								$extras_value_rows = $database -> loadObjectList();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "fe_functions2", "", "");
								echo JText::_('RS1_SQL_ERROR').$e->getMessage();
								exit;
							}		
							foreach($extras_value_rows as $extras_value_row) {
								$csv_values .= '"'.$extras_value_row->extras_qty.'"'.$comma;
							}
						}
						
						// add seat type columns data
						// get seat type values for this request	
						$sql4IN = "SELECT export_foreign_key FROM #__sv_apptpro3_export_columns".						
								" WHERE export_column_type = 'seat' ORDER BY export_order";		
						try{	
							$database->setQuery($sql4IN);
							$seat_counts_rows_IN = $database -> loadColumn();
						} catch (RuntimeException $e) {
							logIt($e->getMessage(), "fe_functions2", "", "");
							echo JText::_('RS1_SQL_ERROR');
							return false;
						}		
						$strIN = implode(",", $seat_counts_rows_IN); // IN values as string
						if($strIN != ""){
							$sql2 = "SELECT #__sv_apptpro3_seat_counts.* FROM ".
								" #__sv_apptpro3_seat_types LEFT JOIN #__sv_apptpro3_seat_counts ".
								" ON #__sv_apptpro3_seat_types.id_seat_types = #__sv_apptpro3_seat_counts.seat_type_id ".
								" AND #__sv_apptpro3_seat_counts.request_id = ".$row->id_requests .
								" WHERE #__sv_apptpro3_seat_types.id_seat_types IN (".$strIN.") ".
								" AND #__sv_apptpro3_seat_types.published=1 ".
								" ORDER BY FIELD(id_seat_types, $strIN)";
						//http://stackoverflow.com/questions/396748/ordering-by-the-order-of-values-in-a-sql-in-clause					
							try{	
								$database->setQuery($sql2);
								$seat_counts_rows = $database -> loadObjectList();
							} catch (RuntimeException $e) {
								logIt($e->getMessage(), "fe_functions2", "", "");
								echo JText::_('RS1_SQL_ERROR');
								return false;
							}		
							foreach($seat_counts_rows as $seat_counts_row) {
								$csv_values .= '"'.$seat_counts_row->seat_type_qty.'"'.$comma;
							}
						}
						$csv_values .= $CR;
				}
				$csv_save = $csv_fields.$CR.$csv_values;
		}


		echo $csv_save;
		die();  // no need to send anything else
}

function getImageSrc($config_image){
	$retval = "";
	$pos = strpos($config_image, DS);
	if ($pos === false) {
		$retval = JURI::base().'components/com_rsappt_pro3/'.$config_image;
	} else {
		$retval = $config_image;
	}
	return $retval;
}

function getOverrideRate($entity_type, $entity_id, $base_rate_or_unit, $userid, $rate_or_unit){
	// Return override rate based on userid and in group and override_rates
	// If user in multiple groups, return lowest rate.
	
	$sql = "SELECT ".
		"Min(#__sv_apptpro3_rate_overrides.rate_override) as rate_override, ".
		"#__sv_apptpro3_rate_overrides.rate_unit_override ".
		"FROM ".
		"#__sv_apptpro3_rate_overrides INNER JOIN ".
		"#__user_usergroup_map ON #__sv_apptpro3_rate_overrides.group_id = ".
		"#__user_usergroup_map.group_id ".
		"WHERE ".
		"#__sv_apptpro3_rate_overrides.published = 1 AND ".
		"#__user_usergroup_map.user_id = ".$userid." AND ".
		"#__sv_apptpro3_rate_overrides.entity_type = '".$entity_type."' AND ".
		"#__sv_apptpro3_rate_overrides.entity_id = ".$entity_id.";";
		
		$database = JFactory::getDBO();
		try{
			$database->setQuery($sql);
			$overrides = null;
			$overrides = $database->loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
		//logIt('$overrides = ('.$entity_type.')'.$overrides->rate_override); 
		
		if($overrides == null || $overrides->rate_override == ""){
			return $base_rate_or_unit;
		} else {
			if($rate_or_unit == "rate"){
				return $overrides->rate_override;
			} else {
				return $overrides->unit_override;
			}
		}

}

function isPayProcEnabled(){
	// Returns tru if any payment processor is enabled
	
	// get list of processors installed		
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_payment_processors WHERE published = 1;';
	try{
		$database->setQuery($sql);
		$pay_procs = NULL;
		$pay_procs = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "functions2", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}		
	
	foreach($pay_procs as $pay_proc){ 
		$sql = "SELECT ".$pay_proc->prefix."_enable FROM #__sv_apptpro3_".$pay_proc->prefix."_settings;";
		try{
			$database->setQuery($sql);
			$temp = NULL;
			$temp = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
		if($temp == "Yes"){
			return true;
		}			
	} 
	return false;
}

function getPayProcinFE($prefix){
	$pay_proc_settings = NULL;
	
	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_'.$prefix.'_settings;';
	try{
		$database->setQuery($sql);
		$pay_proc_settings = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "functions2", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}		
	$fieldname = $prefix."_show_trans_in_fe";
	return $pay_proc_settings->$fieldname;
}

function rb_day($rolling_bookoff, $day){
	$rb_days = explode(",",$rolling_bookoff);
	$int_day = intval($day);
	if($rb_days[$int_day] == "1"){
		return true;
	}
}

function build_mask_filter($day){
	// create rolling_bookoff sql condition as all sible char wildcards except $day
	$retval = "";
	for($i=0; $i<7; $i++){
		if($i == $day){
			$retval .= "1,";
		} else{
			$retval .= "_,";
		}
	}
	$retval = rtrim($retval, ',');
	return $retval;
}

function blockIETooltips($use_jquery_tooltips){
	if($use_jquery_tooltips == "Yes"){
		// JQuery tooltips break IE dropdown lists, so we disable 'title' on <select if running IE
		if((isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))){
			return true;
		}
		return false;		
	}
}

function getResourceEBDiscounts(){
		$database = JFactory::getDBO(); 
		$ret_val = "";

		$sql = "SELECT id_resources,resource_eb_discount,resource_eb_discount_unit,resource_eb_discount_lead ".
		 "FROM #__sv_apptpro3_resources WHERE published = 1";
		try{
			$database->setQuery($sql);
			$resource_rates = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "functions2", "", "");
			return false;
		}		
		$resourceEBDiscountArrayString = "<input type='hidden' id='resource_eb_discount' value='";
		for($i=0; $i<sv_count_($resource_rates); $i++){
			$resourceEBDiscountArrayString = $resourceEBDiscountArrayString.$resource_rates[$i]->id_resources.":".$resource_rates[$i]->resource_eb_discount.":".$resource_rates[$i]->resource_eb_discount_unit.":".$resource_rates[$i]->resource_eb_discount_lead."";
			if($i<sv_count_($resource_rates)-1){
				$resourceEBDiscountArrayString = $resourceEBDiscountArrayString.",";
			}
		}
		$resourceEBDiscountArrayString = $resourceEBDiscountArrayString."'>";
		$ret_val .= $resourceEBDiscountArrayString."\n";
		
		return $ret_val;
}

function getCategoryDurations(){
		$database = JFactory::getDBO(); 
		$ret_val = "";

		$sql = "SELECT id_categories,category_duration,category_duration_unit ".
		 "FROM #__sv_apptpro3_categories WHERE published = 1";
		try{
			$database->setQuery($sql);
			$cat_durations = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "functions2", "", "");
			return false;
		}		
		$categoryDurationsArrayString = "<input type='hidden' id='category_durations' value='";
		for($i=0; $i<sv_count_($cat_durations); $i++){
			$categoryDurationsArrayString = $categoryDurationsArrayString.$cat_durations[$i]->id_categories.":".$cat_durations[$i]->category_duration.":".$cat_durations[$i]->category_duration_unit;
			if($i<sv_count_($cat_durations)-1){
				$categoryDurationsArrayString = $categoryDurationsArrayString.",";
			}

		}
		$categoryDurationsArrayString = $categoryDurationsArrayString."'>";
		$ret_val .= $categoryDurationsArrayString."\n";
		
		return $ret_val;
}

function php_date_string_to_sql($inStr, $to_what){
	// This will make a MySQL DATE_FORAMT string from a php strftime format string or vise versa.
	// http://ca2.php.net/strftime
	//	sql	Description											php	Different
	//	=== ==================================================	=====	=========
	//	%a	Abbreviated weekday name (Sun..Sat)					%a
	//	%b	Abbreviated month name (Jan..Dec)					%b
	//	%c	Month, numeric (0..12)								%m			x
	//	%D	Day of the month with English suffix (0th, 1st, …)	%e			x
	//	%d	Day of the month, numeric (00..31)					%d
	//	%e	Day of the month, numeric (0..31)					%e
	//	%H	Hour (00..23)										%H
	//	%h	Hour (01..12)										%I			x
	//	%I	Hour (01..12)										%I
	//	%i	Minutes, numeric (00..59)							%M			x
	//	%j	Day of year (001..366)								%j
	//	%k	Hour (0..23)										%k
	//	%l	Hour (1..12)										%l (lower-case 'L')
	//	%M	Month name (January..December)						%B			x
	//	%m	Month, numeric (00..12)								%m
	//	%p	AM or PM											%p
	//	%r	Time, 12-hour (hh:mm:ss followed by AM or PM)		%r
	//	%S	Seconds (00..59)									%S
	//	%s	Seconds (00..59)									%S
	//	%T	Time, 24-hour (hh:mm:ss)							%T
	//	%W	Weekday name (Sunday..Saturday)						%A			x
	//	%w	Day of the week (0=Sunday..6=Saturday)				%w
	//	%Y	Year, numeric, four digits							%Y
	//	%y	Year, numeric (two digits)							%y
	
	$retVal = $inStr;
	if($to_what == "PHP"){
		$retVal = str_replace("%c", "%m", $retVal);
		if(WINDOWS){	
			$retVal = str_replace("%e", "%#d", $retVal);
		} else {
			$retVal = str_replace("%D", "%e", $retVal);
		}
		$retVal = str_replace("%h", "%I", $retVal);
		$retVal = str_replace("%M", "%B", $retVal);
		$retVal = str_replace("%i", "%M", $retVal);
		$retVal = str_replace("%W", "%A", $retVal);
	} else {
		//$retVal = str_replace("%m", "%c", $retVal);
		//$retVal = str_replace("%e", "%D", $retVal);
		$retVal = str_replace("%I", "%h", $retVal);
		$retVal = str_replace("%M", "%i", $retVal);
		$retVal = str_replace("%B", "%M", $retVal);
		$retVal = str_replace("%A", "%W", $retVal);
		$retVal = str_replace(",", "", $retVal); // MySQL treats a comma as the end of the format string so remove 
	}
	return $retVal;
}


function addToEmailMarketing($req_id, $apptpro_config){
	$list_id = "";
	$database = JFactory::getDBO(); 

	// get email marketing info
	$email_marketing_info = null;
	$sql = "SELECT * FROM #__sv_apptpro3_email_marketing WHERE id_email_marketing=1;";
	try{
		$database->setQuery($sql);
		$email_marketing_info = $database -> loadObject();
	} catch (RuntimeException $e) {
		echo JText::_('RS1_SQL_ERROR');
		logIt($e->getMessage(), "addToEmailMarketing", "", "");
		return false;
	}	
	
	if($email_marketing_info->mailchimp_enable == "No" && $email_marketing_info->acymailing_enable == "No"){
		return true;
	}
	
	// get request info 
	$req_details = null;
	$sql = "SELECT `#__sv_apptpro3_requests`.`email`, `#__sv_apptpro3_requests`.`name` AS customer_name,".
	" `#__sv_apptpro3_resources`.`mailchimp_list_id`, `#__sv_apptpro3_resources`.`acymailing_list_id` ".
	" FROM `#__sv_apptpro3_requests` INNER JOIN `#__sv_apptpro3_resources` ".
	" ON `#__sv_apptpro3_requests`.`resource` = `#__sv_apptpro3_resources`.`id_resources` ".
	" WHERE `#__sv_apptpro3_requests`.`id_requests` = ".$req_id;	
	//logIt("addToEmailMarketing,".$sql, "", "", "");
	try{
		$database->setQuery($sql);
		$req_details = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt("addToEmailMarketing,".$e->getMessage(), "", "", "");
		if($preventEcho == "No"){
			echo JText::_('RS1_SQL_ERROR');
		}
		return false;
	}		
	if($req_details->email == ""){
		// User did not provide an email, just leave now
		return true;
	}

	if($email_marketing_info->mailchimp_enable == "Yes"){
		include_once( JPATH_SITE.DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_rsappt_pro3".DIRECTORY_SEPARATOR."inc".DIRECTORY_SEPARATOR."MailChimp.php" );
		
		if($req_details->mailchimp_list_id != ""){
			// resource level list setting
			if($req_details->mailchimp_list_id == "-2"){
				// Resource level set to None = do not add to a list
				// We're done here
				return true;
			} else if($req_details->mailchimp_list_id == "-1"){
				// Resource level set to Global
				$list_id = $email_marketing_info->mailchimp_default_list_id;
			} else {	
				// Use specify id set for this resource		
				$list_id = $req_details->mailchimp_list_id;
			}
		}
		
		$fname = "";
		$lname = $req_details->customer_name;
		if($email_marketing_info->mailchimp_split_name == "Yes"){		
			// There is no absolute way to do this as there is an unlimited number of ways a name can be structured. 
			$splitname = doSplitName($lname);
			$fname = $splitname['first'];
			$lname = $splitname['last'];
		}
		if($list_id == ""){ $list_id = $email_marketing_info->mailchimp_default_list_id;}	
		$MailChimp = new \Drewm\MailChimp($email_marketing_info->mailchimp_api_key);
		$ary_params = array(
			'id'                => $list_id,
			'email'             => array('email'=>$req_details->email),
			'merge_vars'        => array('FNAME'=>$fname, 'LNAME'=>$lname),
			'double_optin'      => false,
			'update_existing'   => $email_marketing_info->mailchimp_update_existing,
			'replace_interests' => false,
			'send_welcome'      => $email_marketing_info->mailchimp_send_welcome
		);
		$result = $MailChimp->call('lists/subscribe', $ary_params );
		if($result["error"] != ""){
			logIt("addToEmailMarketing,".$result["error"], "", "", "");
		}
		
	}
	
	if($email_marketing_info->acymailing_enable == "Yes"){
		// based on AcyMailing docs
		// https://www.acyba.com/acymailing/64-acymailing-developer-documentation.html#api_insertuser
	
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
			 logIt('This code can not work without the AcyMailing Component', "functions2", "", "");
			 return false;
		 }
		if($req_details->acymailing_list_id != ""){
			// resource level list setting
			if($req_details->acymailing_list_id == "-2"){
				// Resource level set to None = do not add to a list
				// We're done here
				return true;
			} else if($req_details->acymailing_list_id == "-1"){
				// Resource level set to Global
				$list_id = $email_marketing_info->acymailing_default_list_id;
			} else {	
				// Use specify id set for this resource		
				$list_id = $req_details->acymailing_list_id;
			}
		}
 
 		if($list_id == ""){ $list_id = $email_marketing_info->acymailing_default_list_id;}	

		$myUser = new stdClass();
	 	$myUser->email = $req_details->email;
		$myUser->name = $req_details->customer_name;

		$subscriberClass = acymailing_get('class.subscriber');
	
		//If you require a confirmation but don't want the user to have to confirm his subscription via the API, you can set the confirmed field to 1:
		//$myUser->confirmed = 1;
		// Next line not in docs but was given to an ABPro customer to make it work properly.
		//$subscriberClass->sendConf = false;		 
		
		$new_user_id = $subscriberClass->save($myUser); //this function will return you the ID of the user inserted in the AcyMailing table 	
		
		$subscribe = array($list_id);
		
		$userClass = acymailing_get('class.subscriber');
		
		$newSubscription = array();
		if(!empty($subscribe)){
			foreach($subscribe as $listId){
				$newList = array();
				$newList['status'] = 1;
				$newSubscription[$listId] = $newList;
			}
		}
		
		if(empty($newSubscription)){
			logIt("Specified AcyMailing list found.", "functions2", "", "");		
			return false; //there is nothing to do...
		}
		
		$userClass->saveSubscription($new_user_id,$newSubscription);		
		return true;				
	}
	
	
	return true;	
}

/**
 * splits single name string into salutation, first, last, suffix
 * 
 * @param string $name
 * @return array
 */
function doSplitName($name)
{
    $results = array();

    $r = explode(' ', $name);
    $size = sv_count_($r);

    //check first for period, assume salutation if so
    if (mb_strpos($r[0], '.') === false)
    {
        $results['salutation'] = '';
        $results['first'] = $r[0];
    }
    else
    {
        $results['salutation'] = $r[0];
        $results['first'] = $r[1];
    }

    //check last for period, assume suffix if so
    if (mb_strpos($r[$size - 1], '.') === false)
    {
        $results['suffix'] = '';
    }
    else
    {
        $results['suffix'] = $r[$size - 1];
    }

    //combine remains into last
    $start = ($results['salutation']) ? 2 : 1;
    $end = ($results['suffix']) ? $size - 2 : $size - 1;

    $last = '';
    for ($i = $start; $i <= $end; $i++)
    {
        $last .= ' '.$r[$i];
    }
    $results['last'] = trim($last);

    return $results;
}


//function addToAcyMailing($email_to_add, $name_to_add){
//		// based on AcyMailing docs
//		// https://www.acyba.com/acymailing/64-acymailing-developer-documentation.html#api_insertuser
//		
//		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
//			 logIt('This code can not work without the AcyMailing Component', "functions2", "", "");
//			 return false;
//		 }
//		 
//		$myUser = new stdClass();
//	 	$myUser->email = $email_to_add;	 
//		$myUser->name = $name_to_add; //this information is optional
//		 
//		//You can add as many extra fields as you want if you already created them in AcyMailing
//		//$myUser->country = 'france';
//		//$myUser->phone = '064872754';
//		//...
//		 
//		$subscriberClass = acymailing_get('class.subscriber');
//		 
//		$new_user_id = $subscriberClass->save($myUser); //this function will return you the ID of the user inserted in the AcyMailing table 	
//		
//		$subscribe = array(1); //array(2,4,6); //Id of the lists you want the user to be subscribed to (can be empty)
//		
//		$userClass = acymailing_get('class.subscriber');
//		
//		$newSubscription = array();
//		if(!empty($subscribe)){
//			foreach($subscribe as $listId){
//				$newList = array();
//				$newList['status'] = 1;
//				$newSubscription[$listId] = $newList;
//			}
//		}
//		
//		if(empty($newSubscription)){
//			logIt("Specified AcyMailing list found.", "functions2", "", "");		
//			return false; //there is nothing to do...
//		}
//		
//		$userClass->saveSubscription($new_user_id,$newSubscription);		
//		return true;		
//}

function getIconvCharset(){
	$lang = JFactory::getLanguage();
	$lang_tag = $lang->getTag();
	if(strpos($lang_tag, "-") === true){
		$lang_tag = str_replace("-", "_", $lang_tag);
	}
	if($lang_tag == "ja-JP" || substr($lang_tag, 0, 2) == "zh"){
		return "big5";
	} elseif($lang_tag == "el-GR"){
		return "ISO-8859-7";
	} else {
		return "ISO-8859-2";
	}
}

function getSeatAdjustments($bkg_date, $bkg_start, $bkg_end, $bk_res, $cur_max_seats = -1){
	$database = JFactory::getDBO(); 
	if($cur_max_seats == -1){
		// no max_seats passed in , need to get it now
		$sql = 'SELECT name,max_seats FROM #__sv_apptpro3_resources where id_resources = '.(int)$bk_res;
		try{
			$database->setQuery($sql);
			$cur_max_seats = $database -> loadResult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "functions2", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
	}
	
	$retVal = 0;
	// get seat adjustments
	$seat_adjustments = null;
	$sql = "SELECT * FROM #__sv_apptpro3_seat_adjustments WHERE ".
	" id_resources = ".(int)$bk_res.
	" AND published = 1".
	" AND (start_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$database->escape($bkg_date)."' >= start_publishing ) ".
	" AND (end_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$database->escape($bkg_date)."' <= end_publishing ) ";
	try{
		$database->setQuery($sql);
		$seat_adjustments = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "functions2", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}	

	$day_adjustment = "";
	$time_adjustment = "";

	if(sv_count_($seat_adjustments) == 0){
		$retVal = 0;
	} else {
		foreach($seat_adjustments as $seat_adjustment){
			// are the found adjustments by-day
			if($seat_adjustment->by_day_time == "DayOnly"){
				// Does the booking day match and enabled adjustment day
				$weekday = date( "w", strtotime($bkg_date));
				switch($weekday){
					case 0:
						if($seat_adjustment->adjustSunday == "Yes"){
							$retVal = $seat_adjustment->seat_adjustment;
						}
						break;
					case 1:
						if($seat_adjustment->adjustMonday == "Yes"){
							$retVal = $seat_adjustment->seat_adjustment;
						}
						break;
					case 2:
						if($seat_adjustment->adjustTuesday == "Yes"){
							$retVal = $seat_adjustment->seat_adjustment;
						}
						break;
					case 3:
						if($seat_adjustment->adjustWednesday == "Yes"){
							$retVal = $seat_adjustment->seat_adjustment;
						}
						break;
					case 4:
						if($seat_adjustment->adjustThursday == "Yes"){
							$retVal = $seat_adjustment->seat_adjustment;
						}
						break;
					case 5:
						if($seat_adjustment->adjustFriday == "Yes"){
							$retVal = $seat_adjustment->seat_adjustment;
						}
						break;
					case 6:
						if($seat_adjustment->adjustSaturday == "Yes"){
							$retVal = $seat_adjustment->seat_adjustment;
						}
						break;
														
				}
			}

			if($seat_adjustment->by_day_time == "TimeOnly"){
				// does booking start or end fall in range
				$temp = strtotime($bkg_start)+1;
				if(($temp > strtotime($seat_adjustment->timeRangeStart) && $temp < strtotime($seat_adjustment->timeRangeEnd)) || 
				( $temp > strtotime($seat_adjustment->timeRangeStart) && $temp < strtotime($seat_adjustment->timeRangeEnd))){
					$retVal = $seat_adjustment->seat_adjustment;
				}
				$temp = strtotime($bkg_end)-1;
				if(($temp > strtotime($seat_adjustment->timeRangeStart) && $temp < strtotime($seat_adjustment->timeRangeEnd)) || 
				( $temp > strtotime($seat_adjustment->timeRangeStart) && $temp < strtotime($seat_adjustment->timeRangeEnd))){
					$retVal = $seat_adjustment->seat_adjustment;
				}
			}
			
			if($seat_adjustment->by_day_time == "DayAndTime"){
				// only adjust is both date and time match
				$weekday = date( "w", strtotime($bkg_date));
				$day_match = false;
				$time_match = false;
				$temp_day_adjust = "";
				$temp_time_adjust = "";
				$temp_day_adjust_unit = "";
				$temp_time_adjust_unit = "";
				switch($weekday){
					case 0:
						if($seat_adjustment->adjustSunday == "Yes"){
							$day_match = true;
							$temp_day_adjust = $seat_adjustment->seat_adjustment;
						}
						break;
					case 1:
						if($seat_adjustment->adjustMonday == "Yes"){
							$day_match = true;
							$temp_day_adjust = $seat_adjustment->seat_adjustment;
						}
						break;
					case 2:
						if($seat_adjustment->adjustTuesday == "Yes"){
							$day_match = true;
							$temp_day_adjust = $seat_adjustment->seat_adjustment;
						}
						break;
					case 3:
						if($seat_adjustment->adjustWednesday == "Yes"){
							$day_match = true;
							$temp_day_adjust = $seat_adjustment->seat_adjustment;
						}
						break;
					case 4:
						if($seat_adjustment->adjustThursday == "Yes"){
							$day_match = true;
							$temp_day_adjust = $seat_adjustment->seat_adjustment;
						}
						break;
					case 5:
						if($seat_adjustment->adjustFriday == "Yes"){
							$day_match = true;
							$temp_day_adjust = $seat_adjustment->seat_adjustment;
						}
						break;
					case 6:
						if($seat_adjustment->adjustSaturday == "Yes"){
							$day_match = true;
							$temp_day_adjust = $seat_adjustment->seat_adjustment;
						}
						break;
														
				}
				if($day_match){
					// there is a day_adjustment but we only want to pass back a required adjustment if the time matches also
					$temp = strtotime($bkg_start)+1;
					if(($temp > strtotime($seat_adjustment->timeRangeStart) && $temp < strtotime($seat_adjustment->timeRangeEnd)) || 
					( $temp > strtotime($seat_adjustment->timeRangeStart) && $temp < strtotime($seat_adjustment->timeRangeEnd))){
						$time_match = true;
						$temp_time_adjust = $seat_adjustment->seat_adjustment;
					}
					$temp = strtotime($bkg_end)-1;
					if(($temp > strtotime($seat_adjustment->timeRangeStart) && $temp < strtotime($seat_adjustment->timeRangeEnd)) || 
					( $temp > strtotime($seat_adjustment->timeRangeStart) && $temp < strtotime($seat_adjustment->timeRangeEnd))){
						$time_match = true;
						$temp_time_adjust = $seat_adjustment->seat_adjustment;
					}
				}
				if($day_match && $time_match){
					$day_adjustment = ""; // if date/time adjustment, clear day
					$time_adjustment = $temp_time_adjust;
					$retVal = $temp_time_adjust;
				}
			}
		}
	}
	return $retVal;
}

function getResourceImageURL($ddslick_image_path){
	// Using this as a central point to get image urls so if we deceied to change
	// where we store images, it can be done in a single change here.
	
	// Currently, using the Joomla media manager so..
	return JURI::root( true )."/images/".$ddslick_image_path; 
	
}

function auto_resource($user, $auto_resource_groups, $auto_resource_category ){
	if($user->guest){
		return false;
	}
	// Check that the user is in one of the auto resource groups
	$groups = str_replace("||", ",", $auto_resource_groups);
	$groups = str_replace("|", "", $groups);			

	$sql = "SELECT count(*) FROM #__user_usergroup_map WHERE group_id IN (".$groups.") AND user_id = ".$user->id;
	//echo $sql;
	//exit;
	$database = JFactory::getDBO();
	try{
		$database->setQuery($sql);
		$match = null;
		$match = $database->loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "functions2", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}		
	if($match == null || $match < 1){
		// not in any of the authorized groups, exit
		return false;
	}
	
	$database = JFactory::getDBO(); 
	/*-------------------------------------------------------------
		Resource
	---------------------------------------------------------------*/	
	// create a resource for this user and make them the resource admin
	$sql = "INSERT INTO #__sv_apptpro3_resources (".
		"name,description,resource_admins,category_scope,timeslots,resource_email,ordering)".
		" VALUES(".
		"'".$database->escape($user->name)."','".
		$database->escape($user->name)."','".
		"|".$user->id."|','".
		$auto_resource_category."',".
		"'Specific','".
		$database->escape($user->email)."',1)";
	try{
		$database->setQuery( $sql );
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "auto_resource", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;;
	}
	$sSql = "SELECT LAST_INSERT_ID() AS last_id";
	try{
		$database->setQuery($sSql);
		$last_id = NULL;
		$last_id = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "auto_resource", "", "");
		echo JText::_('RS1_SQL_ERROR');	
		exit;
	}		
	$new_resource_id = $last_id->last_id;

	/*-------------------------------------------------------------
		Resource timeslots
	---------------------------------------------------------------*/	
	// copy of Global timeslots to this resource
	$sql = "SELECT * FROM #__sv_apptpro3_timeslots "
		." WHERE ISNULL(resource_id) OR resource_id = 0";
	try{
		$database->setQuery( $sql );
		$ts_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "auto_resource", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}
	
	//now do inserts
	foreach($ts_rows as $row) {
		$sql = "INSERT INTO #__sv_apptpro3_timeslots (day_number,resource_id,timeslot_starttime,timeslot_endtime,published)".
		" VALUES(".$row->day_number.",".$new_resource_id.",'".$row->timeslot_starttime."','".$row->timeslot_endtime."',".$row->published.")";
		try{
			$database->setQuery( $sql );
			$database->execute();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "auto_resource", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}
	}		
	
	/*-------------------------------------------------------------
		Resource message set
	---------------------------------------------------------------*/	
	// Create a message set for this resource as copy of Global.
	// Check for pre-existing message set for this user.
	$sql = "SELECT * FROM #__sv_apptpro3_mail "
		. " WHERE mail_label ='".$database->escape($user->name)."'";
	try{
		$database->setQuery( $sql );
		$rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "auto_resource", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}

	// if there is a set for this user, set the mail_id 
	if(sv_count_($rows) > 0){
		$new_mail_id = $rows[0]->id_mail;
	} else {
		// no existing message set, create a new one from Global	
		// first get global source rows
		$sql = 'SELECT * FROM #__sv_apptpro3_mail '
			. ' WHERE id_mail = 1 ';
		try{
			$database->setQuery( $sql );
			$rows = $database -> loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "auto_resource", "", "");
			echo JText::_('RS1_SQL_ERROR').$e->getMessage();
			exit;
		}
		foreach($rows as $row) {
			$sql = "INSERT INTO #__sv_apptpro3_mail (".
				"mail_label,booking_succeeded,booking_succeeded_admin,booking_succeeded_sms,booking_in_progress,".
				"booking_in_progress_admin,booking_in_progress_sms,booking_cancel,booking_cancel_sms,booking_too_close_to_cancel,".
				"booking_reminder,booking_reminder_sms,attach_ics_resource,attach_ics_admin,attach_ics_customer,thank_you_msg,".
				"send_on_status,rebook_msg,published)".
			" VALUES(".
				"'".$database->escape($user->name)."','".
				$database->escape($row->booking_succeeded)."','".
				$database->escape($row->booking_succeeded_admin)."','".
				$database->escape($row->booking_succeeded_sms)."','".
				$database->escape($row->booking_in_progress)."','".
				$database->escape($row->booking_in_progress_admin)."','".
				$database->escape($row->booking_in_progress_sms)."','".
				$database->escape($row->booking_cancel)."','".
				$database->escape($row->booking_cancel_sms)."','".
				$database->escape($row->booking_too_close_to_cancel)."','".
				$database->escape($row->booking_reminder)."','".
				$database->escape($row->booking_reminder_sms)."','".
				$database->escape($row->attach_ics_resource)."','".
				$database->escape($row->attach_ics_admin)."','".
				$database->escape($row->attach_ics_customer)."','".
				$database->escape($row->thank_you_msg)."','".
				$database->escape($row->send_on_status)."','".
				$database->escape($row->rebook_msg)."'".
				",1)";
			try{
				$database->setQuery( $sql );
				$database->execute();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "auto_resource", "", "");
				echo JText::_('RS1_SQL_ERROR').$e->getMessage();
				exit;
			}
		}
		// set new message set id back into resource
		$sSql = "SELECT LAST_INSERT_ID() AS last_id";
		try{
			$database->setQuery($sSql);
			$last_id = NULL;
			$last_id = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "auto_resource", "", "");
			echo JText::_('RS1_SQL_ERROR');	
			exit;
		}	
			
		$new_mail_id = $last_id->last_id;
	}
	
	$sql = "UPDATE #__sv_apptpro3_resources ".
		"SET mail_id = ".$new_mail_id." ".
		"WHERE id_resources = ".$new_resource_id;
	try{
		$database->setQuery( $sql );
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "auto_resource", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;;
	}
	
	
	return true;
}

function getCBAvatar($user, $format="html"){
	// Not currently used in ABPro
	// Useage: 	echo getCBAvatar(178) 
	// $format: 
	//		html = image
	//		php = Array ( [avatar] => http://localhost/dev30_abp_cb/images/comprofiler/tn178_551d518841a3b.png )
	
	$retval = "";
	if (JComponentHelper::getComponent('com_comprofiler', true)->enabled)
	{
		//Include CB Foundation, code from CB API docs
		global $_CB_framework, $mainframe;		 
		if ( defined( 'JPATH_ADMINISTRATOR' ) ) {
			if ( ! file_exists( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) {
				logIt('CB not installed!', "getCBAvatar", "", "");
		 		return $retval;
			}
			include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
		} else {
			if ( ! file_exists( $mainframe->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/plugin.foundation.php' ) ) {
				logIt('CB not installed!', "getCBAvatar", "", "");
		 		return $retval;
		 	}		 
		 	include_once( $mainframe->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/plugin.foundation.php' );
		}
		Global $_CB_framework;
		$cbUser = CBuser::getInstance($user);
		if ( ! $cbUser ) {
			$cbUser = CBuser::getInstance( null );
		}
		$user = $cbUser->getUserData();
		$avatar = $cbUser->getField( 'avatar', null, $format, 'none', 'list' );
		$retval = $avatar;
	} 
	return $retval;
	
}

function getAccordionTitle($section, $accordion_sections){
	switch ($accordion_sections[$section-1]) {  // -1 because accordion_sections si 0 based array
		case "accord_res":
			return JText::_('RS1_ACCORDION_BLOCK_RES_TITLE');
			break;
		case "accord_basic":
			return JText::_('RS1_ACCORDION_BLOCK_BASIC_TITLE');
			break;
		case "accord_udfs":
			return JText::_('RS1_ACCORDION_BLOCK_UDFS_TITLE');
			break;
		case "accord_extras":
			return JText::_('RS1_ACCORDION_BLOCK_EXTRAS_TITLE');
			break;
		case "accord_seats":
			return JText::_('RS1_ACCORDION_BLOCK_SEATS_TITLE');
			break;
		case "accord_grid":
			return JText::_('RS1_ACCORDION_BLOCK_GRID_TITLE');
			break;
		case "accord_submit":
			return JText::_('RS1_ACCORDION_BLOCK_SUBMIT_TITLE');
			break;
		case "accord_ts_select":
			return JText::_('RS1_ACCORDION_BLOCK_GRID_TITLE');
			break;
		case "accord_submit_simple":
			return JText::_('RS1_ACCORDION_BLOCK_SUBMIT_TITLE');
			break;
	}
}

function getAccordionCodeblock($section, $accordion_sections, $suffix = "", $layout = ""){
	switch ($accordion_sections[$section-1]) {  // -1 because accordion_sections si 0 based array
		case "accord_res":
			return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_resource".($suffix == "mobile"?"_mobile":"").".php";
			break;
		case "accord_basic":
			return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_basic_info".($suffix == "mobile"?"_mobile":"").".php";
			break;
		case "accord_udfs":
			return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_udfs".($suffix == "mobile"?"_mobile":"").".php";
			break;
		case "accord_extras":
			return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_extras".($suffix == "mobile"?"_mobile":"").".php";
			break;
		case "accord_seats":
			return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_seats.php";
			break;
		case "accord_grid":
			if($layout == "mobile_simple"){
				return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_ts_select".($suffix == "mobile"?"_mobile":"").".php";
			} else {
				return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_slots".($suffix == "mobile"?"_mobile":"").".php";
			}
			break;
		case "accord_submit":
			if($layout == "mobile_simple"){
				return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_submit_simple".($suffix == "mobile"?"_mobile":"").".php";
			} else {
				return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_submit".($suffix == "mobile"?"_mobile":"").".php";
			}
			break;
		case "accord_ts_select":
			return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_ts_select".($suffix == "mobile"?"_mobile":"").".php";
			break;
		case "accord_submit_simple":
			return JPATH_COMPONENT.DIRECTORY_SEPARATOR."sv_codeblocks".DIRECTORY_SEPARATOR."sv_codeblock_submit_simple".($suffix == "mobile"?"_mobile":"").".php";
			break;
	}
}

function sv_encode($in){
	// removed base 64 encode as it caused JED checker to fail 
	$in = (str_replace("'", "&#39;", $in));
	return $in;	
}

function sv_count_($in){
	// In php 7.2 they implemented a new error if the arg to count() was not an array
	//
	// ABPro uses count() in about 900 places so rather than making 900 individual code changes to validate the argument,
	// I created this fucntion. This way I can do a global search & replace for count( to sv_count( and chcek for the 
	// array in one place.
	if(is_array($in)){
		return count($in);
	} else {
		return 0;
	}
}

function sv_strtotime_now(){
	// J!4 seems to no mess about with php timezone, they want you to use JDate (I don't)	
	date_default_timezone_set(JFactory::getConfig()->get('offset'));
	return strtotime('now');
}

?>