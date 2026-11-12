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
 *
 * This controller is used for json calls made by mobile web apps.
 *
 */


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


function getSlots($resource, $startdate, $availability_only){

		require_once( JPATH_CONFIGURATION.'/configuration.php' );
		$CONFIG = new JConfig();
		$timezone_identifier = $CONFIG->offset;
		$options = "";	
		$jinput = JFactory::getApplication()->input;
		$browser = $jinput->getString('browser');		
		
		// determine what day the date is
		$day = date("w", strtotime($startdate)); 
	
		$database = JFactory::getDBO(); 
		$sql = 'SELECT * FROM #__sv_apptpro3_config';
		try {
			$database->setQuery($sql);
			$apptpro_config = NULL;
			$apptpro_config = $database -> loadObject();	
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}		

		// get resource info for the selected resource
		$sql = 'SELECT * FROM #__sv_apptpro3_resources where id_resources = '.$resource;
		try{
			$database->setQuery($sql);
			$res_detail = NULL;
			$res_detail = $database -> loadObject();
		} catch (RuntimeException $e) {
			echo JText::_('RS1_SQL_ERROR');
			logIt($e->getMessage(), "getSlots", "", "");
			return false;
		}					
		
		$book_date_ok = true;
		
		// check for date_specific_booking
		if($res_detail->date_specific_booking == "Yes"){
			$sql = "SELECT count(*) FROM #__sv_apptpro3_book_dates WHERE resource_id=".$resource." AND book_date='".$startdate."' ";
			try{
				$database->setQuery( $sql );
				if($database->loadResult()==0){
					$book_date_ok = false;
				}
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "functions2", "", "");
				echo JText::_('RS1_SQL_ERROR');
				exit;
			}		
		}
		
		if($book_date_ok){
			// If Max Seats = 0 we can use th emobile app's logic to fetch slots - this suppors service based duration.
			if($res_detail->max_seats == 1){ 
				// code from mobile app
				$day_off = false;
				$sql = "SELECT id_timeslots as id, timeslot_starttime, timeslot_endtime from  #__sv_apptpro3_timeslots ".
				" WHERE published = 1 ";
				if($res_detail->timeslots == "Global"){
					$sql .= " AND resource_id = 0";
				} else {
					$sql .= " AND resource_id = ".$resource;
				}
				$sql .= " AND day_number = ".$day. 
				" AND (start_publishing is null OR start_publishing = '0000-00-00' OR start_publishing <= '".$startdate."') ".
				" AND (end_publishing is null OR end_publishing = '0000-00-00' OR end_publishing > '".$startdate."')".
				" AND staff_only = 'No' ORDER BY timeslot_starttime";
				try{
					$database->setQuery($sql);
					$timeslot_list = $database->loadObjectList();
				} catch (RuntimeException $e) {
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}		
	
				if(sv_count_($timeslot_list) == 0){
					if($availability_only == 0){
						echo JText::_('RS1_NO_TIMESLOTS');
						jExit();
					} else {
						return 0;
					}
				}
				
				// now get bookings
				$sql = "SELECT *, id_requests as id FROM #__sv_apptpro3_requests WHERE resource = ".$resource." AND (request_status = 'accepted' OR request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").") ".
					" AND startdate = '".$startdate."' ".
					" ORDER BY starttime";
				try
				{
					$database->setQuery($sql);
					$bookings_list = $database->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}		
				//print_r($bookings_list);
				//exit;
					
				// now get book-offs
				$sql = "SELECT id_bookoffs as id, full_day, bookoff_starttime, bookoff_endtime FROM #__sv_apptpro3_bookoffs WHERE off_date = '".$startdate."' AND resource_id = ".$resource." AND published = 1 ".
					" ORDER BY bookoff_starttime";
				try
				{
					$database->setQuery($sql);
					$bookoff_list = $database->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}					
				
				if(sv_count_($bookoff_list) >0){
					foreach($bookoff_list as $bookoff){
						if($bookoff->full_day == "Yes"){
							// thats it we're outa here, its a full day book off
							$day_off = true;
							//echo "Unavailable Day";
							//return;
						}
					}
				}
				if(!$day_off){
					if(sv_count_($bookings_list) == 0 && sv_count_($bookoff_list) == 0){
						// no bookings or book-offs send all timeslots
						$sql = "SELECT id_timeslots as id, timeslot_starttime, timeslot_endtime, timeslot_description, day_number, ";
						if($apptpro_config->timeFormat == '12'){							
							$sql .=	"CONCAT( DATE_FORMAT(timeslot_starttime, '%l:%i%p'), ' - ', DATE_FORMAT(timeslot_endtime, '%l:%i%p') ) as startendtime, ".
							"DATE_FORMAT(timeslot_starttime, '%l:%i%p') as display_starttime, ".
							"DATE_FORMAT(timeslot_endtime, '%l:%i%p') as display_endtime, ";
						} else {
							$sql .=	"CONCAT( DATE_FORMAT(timeslot_starttime, '%H:%i'), ' - ', DATE_FORMAT(timeslot_endtime, '%H:%i') ) as startendtime, ".
							"DATE_FORMAT(timeslot_starttime, '%H:%i') as display_starttime, ".
							"DATE_FORMAT(timeslot_endtime, '%H:%i') as display_endtime, ";
						}					
						$sql .=	"'Available' as booked from #__sv_apptpro3_timeslots ".
						" WHERE published = 1 ".
						" AND day_number = ".$day;
						if($res_detail->timeslots == "Global"){
							$sql .= " AND resource_id = 0";
						} else {
							$sql .= " AND resource_id = ".$resource;
						}					
						$sql .= " AND (start_publishing is null OR start_publishing = '0000-00-00' OR start_publishing <= '".$startdate."') ".
						" AND (end_publishing is null OR end_publishing = '0000-00-00' OR end_publishing > '".$startdate."')".
						"  AND staff_only = 'No' ORDER BY timeslot_starttime";
					} else {				
						// get bookoff blocked ids
						$ts_blocked_ids = "";
						foreach($timeslot_list as $time_slot){
							foreach($bookoff_list as $bookoff){
								if( strtotime($bookoff->bookoff_starttime) == strtotime($time_slot->timeslot_starttime) 
									&& strtotime($bookoff->bookoff_endtime) == strtotime($time_slot->timeslot_endtime)){
										// bkg start & end = ts start & end (bookoff = timeslot)
										$ts_blocked_ids .= $time_slot->id.",";
									} else if( strtotime($bookoff->bookoff_starttime) == strtotime($time_slot->timeslot_starttime)){
										// bkg starts at ts start
										$ts_blocked_ids .= $time_slot->id.",";
									} else if( strtotime($bookoff->bookoff_endtime) == strtotime($time_slot->timeslot_endtime)){
										// bkg end at ts end
										$ts_blocked_ids .= $time_slot->id.",";
									} else if( strtotime($bookoff->bookoff_starttime) > strtotime($time_slot->timeslot_starttime) 
										&& strtotime($bookoff->bookoff_starttime) < strtotime($time_slot->timeslot_endtime)){
										// bkg start > ts start and < ts end (bookoff starts in a timeslot)
										$ts_blocked_ids .= $time_slot->id.",";
									} else if( strtotime($bookoff->bookoff_endtime) > strtotime($time_slot->timeslot_starttime) 
										&& strtotime($bookoff->bookoff_endtime) < strtotime($time_slot->timeslot_endtime)){
										// bkg end > ts start and < ts end (bookoff ends in a timeslot)
										$ts_blocked_ids .= $time_slot->id.",";
									} else if( strtotime($bookoff->bookoff_starttime) < strtotime($time_slot->timeslot_starttime) 
										&& strtotime($bookoff->bookoff_endtime) > strtotime($time_slot->timeslot_endtime)){
										// bkg start < ts start and bkg end > ts end (bookoff covers a timeslot)
										$ts_blocked_ids .= $time_slot->id.",";
									}
								}						
							}
		
						// get booked ids
						if($res_detail->max_seats > 0){ // this version not compatible with max_seats > 1
							$ts_booked_ids = "";
							foreach($timeslot_list as $time_slot){
								foreach($bookings_list as $booking){
									if(fullyBooked($booking, $res_detail, $apptpro_config)){
										if( strtotime($booking->starttime) == strtotime($time_slot->timeslot_starttime) 
											&& strtotime($booking->endtime) == strtotime($time_slot->timeslot_endtime)){
												// bkg start & end = ts start & end (booking = timeslot)
												$ts_booked_ids .= $time_slot->id.",";
											} else if( strtotime($booking->starttime) == strtotime($time_slot->timeslot_starttime)){
												// bkg starts at ts start
												$ts_booked_ids .= $time_slot->id.",";
											} else if( strtotime($booking->endtime) == strtotime($time_slot->timeslot_endtime)){
												// bkg end at ts end
												$ts_booked_ids .= $time_slot->id.",";
											} else if( strtotime($booking->starttime) > strtotime($time_slot->timeslot_starttime) 
												&& strtotime($booking->starttime) < strtotime($time_slot->timeslot_endtime)){
												// bkg start > ts start and < ts end (booking starts in a timeslot)
												$ts_booked_ids .= $time_slot->id.",";
											} else if( strtotime($booking->endtime) > strtotime($time_slot->timeslot_starttime) 
												&& strtotime($booking->endtime) < strtotime($time_slot->timeslot_endtime)){
												// bkg end > ts start and < ts end (booking ends in a timeslot)
												$ts_booked_ids .= $time_slot->id.",";
											} else if( strtotime($booking->starttime) < strtotime($time_slot->timeslot_starttime) 
												&& strtotime($booking->endtime) > strtotime($time_slot->timeslot_endtime)){
												// bkg start < ts start and bkg end > ts end (booking covers a timeslot)
												$ts_booked_ids .= $time_slot->id.",";
											}
										}
									}
								}
						}
		
						if($ts_booked_ids != "" && $ts_blocked_ids != ""){
							// both booked and blocked
							$booked_exp = " IF(id_timeslots IN(".substr($ts_booked_ids,0,strlen($ts_booked_ids)-1)."),'Booked', IF(id_timeslots IN(".substr($ts_blocked_ids,0,strlen($ts_blocked_ids)-1)."),'Unavailable','Available')) as booked ";
						} else if($ts_booked_ids != "" && $ts_blocked_ids == ""){
							// only booked
							$booked_exp = " IF(id_timeslots IN(".substr($ts_booked_ids,0,strlen($ts_booked_ids)-1)."),'Booked', 'Available') as booked ";
						} else if($ts_booked_ids == "" && $ts_blocked_ids != ""){
							// only blocked
							$booked_exp = " IF(id_timeslots IN(".substr($ts_blocked_ids,0,strlen($ts_blocked_ids)-1)."),'Unavailable', 'Available') as booked ";
						} else {
							// neither
							$booked_exp = "'Available' as booked ";
						}
						$sql = "SELECT id_timeslots as id, timeslot_starttime, timeslot_endtime, timeslot_description, day_number, ";
						if($apptpro_config->timeFormat == '12'){							
							$sql .=	"CONCAT( DATE_FORMAT(timeslot_starttime, '%l:%i%p'), ' - ', DATE_FORMAT(timeslot_endtime, '%l:%i%p') ) as startendtime, ".
							"DATE_FORMAT(timeslot_starttime, '%l:%i%p') as display_starttime, timeslot_starttime as timeorder, ".
							"DATE_FORMAT(timeslot_endtime, '%l:%i%p') as display_endtime, ";
						} else {
							$sql .=	"CONCAT( DATE_FORMAT(timeslot_starttime, '%H:%i'), ' - ', DATE_FORMAT(timeslot_endtime, '%H:%i') ) as startendtime, ".
							"DATE_FORMAT(timeslot_starttime, '%H:%i') as display_starttime, timeslot_starttime as timeorder, ".
							"DATE_FORMAT(timeslot_endtime, '%H:%i') as display_endtime, ";
						}					
						$sql .=	$booked_exp."  from  #__sv_apptpro3_timeslots ".
						" WHERE published = 1 ".
						" AND day_number = ".$day;
						if($res_detail->timeslots == "Global"){
							$sql .= " AND resource_id = 0";
						} else {
							$sql .= " AND resource_id = ".$resource;
						}					
						$sql .= " AND (start_publishing is null OR start_publishing = '0000-00-00' OR start_publishing <= '".$startdate."') ".
						" AND (end_publishing is null OR end_publishing = '0000-00-00' OR end_publishing > '".$startdate."')".
						" AND staff_only = 'No' ORDER BY timeslot_starttime";
					}
				}
				try{
					$database->setQuery($sql);
					$slot_rows = NULL;
					$slot_rows = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}		
		
				// get get part day book-offs
				$sql = "SELECT * FROM #__sv_apptpro3_bookoffs ".
					" WHERE ".
					" ( resource_id='".$resource."' ) ".
					" AND ( (off_date = '".$startdate."' AND full_day='No') OR (rolling_bookoff != 'No') )".
					" AND published=1 ORDER BY bookoff_starttime";
				try{
					$database->setQuery($sql);
					$part_day_bookoffs = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}		
		
		//		echo $sql;
		//		print_r($slot_rows);
		//		echo "<br/><br/>";
		//		print_r($part_day_bookoffs);
		//		exit;
				$actual_slots_available = 0;
				
				if(!$day_off){
					foreach($slot_rows as $slot_row){
						
						$ok_to_process_slot = true;
						date_default_timezone_set($timezone_identifier);
						$time_adjusted_for_lead = time() + ($res_detail->min_lead_time * 60 * 60);						
						if(strtotime($startdate." ".$slot_row->timeslot_starttime) < $time_adjusted_for_lead){
							$ok_to_process_slot = false;
						} else {
							$ok_to_process_slot = true;
						}
						
						if(sv_count_($part_day_bookoffs) > 0){
							// need to check each slot to see if blocked by book-off
							if(blocked_by_bookoff($slot_row, $part_day_bookoffs)){
								$ok_to_process_slot = false;
							}						
						}
						if($slot_row->booked == "Available" && $ok_to_process_slot){
//							$options .=  "<option value=\"".$slot_row->timeslot_starttime.",".$slot_row->timeslot_endtime.",".$res_detail->free_booking."\">".$slot_row->startendtime."</option>";
							$options .=  "<option value=\"".$slot_row->timeslot_starttime.",".$slot_row->timeslot_endtime.",".$res_detail->free_booking."\">".$slot_row->startendtime."  ".$slot_row->timeslot_description."</option>";
//							$options .=  "<option value=\"".$slot_row->timeslot_starttime.",".$slot_row->timeslot_endtime.",".$res_detail->free_booking."\">".$slot_row->display_starttime."</option>";
							$actual_slots_available ++;
						}
					}
				}
				
			// end code from mobile app		
			} else {
				// with max seats > 0 the code below shows slots and seats available - NOT compatible with servcie based duration
		
				// select timeslots for that day
				$database = JFactory::getDBO();
				$sql = "SELECT *, timeslot_starttime as non_display_timeslot_starttime, ";
				if($apptpro_config->timeFormat == "12"){
					$sql .= "TIME_FORMAT(timeslot_starttime,'%l:%i %p') as timeslot_starttime, timeslot_starttime as timeorder, ".
					"TIME_FORMAT(timeslot_starttime,'%k:%i') as starttime_24, TIME_FORMAT(timeslot_endtime,'%k:%i') as endtime_24, ".
					"TIME_FORMAT(timeslot_endtime,'%l:%i %p') as timeslot_endtime, TIME_FORMAT(timeslot_starttime,'%H:%i') as db_starttime_24 ";
				} else {
					$sql .= "TIME_FORMAT(timeslot_starttime,'%H:%i') as timeslot_starttime, timeslot_starttime as timeorder,  ".
					"TIME_FORMAT(timeslot_starttime,'%k:%i') as starttime_24, TIME_FORMAT(timeslot_endtime,'%k:%i') as endtime_24, ".
					"TIME_FORMAT(timeslot_endtime,'%H:%i') as timeslot_endtime, TIME_FORMAT(timeslot_starttime,'%H:%i') as db_starttime_24 ";
				}	
				// does the resource use global slots?
				
				if($res_detail->timeslots == "Global"){
					$sql .=	"FROM #__sv_apptpro3_timeslots WHERE published=1 AND (resource_id is null or resource_id = 0) AND day_number = ".$day.
						" AND (start_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$startdate."' >= start_publishing ) ".
						" AND (end_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$startdate."' <= end_publishing ) ".
						" AND staff_only = 'No' ORDER BY timeorder";
				} else {
					$sql .=	"FROM #__sv_apptpro3_timeslots WHERE published=1 AND resource_id = ".$resource." AND day_number = ".$day.
						" AND (start_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$startdate."' >= start_publishing ) ".
						" AND (end_publishing IS NULL OR start_publishing = '0000-00-00' OR '".$startdate."' <= end_publishing ) ".
						" AND staff_only = 'No' ORDER BY timeorder";
				} 
			
				//echo $sql;
				try{
					$database->setQuery($sql);
					$slot_rows = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}		
			
				// select bookings for that date & resource
				$sql = "SELECT starttime FROM #__sv_apptpro3_requests WHERE resource='".$resource."' AND startdate='".$startdate."' AND (request_status='accepted' OR request_status='pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").")";
				//echo $sql;
				try{
					$database->setQuery($sql);
					$booking_rows = $database -> loadColumn ();
				} catch (RuntimeException $e) {
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}		
	
				// get get part day book-offs
				$sql = "SELECT * FROM #__sv_apptpro3_bookoffs ".
					" WHERE ".
					" ( resource_id='".$resource."' ) ".
					" AND ( (off_date = '".$startdate."' AND full_day='No') OR (rolling_bookoff != 'No') )".
					" AND published=1 ORDER BY bookoff_starttime";
				try{
					$database->setQuery($sql);
					$part_day_bookoffs = $database -> loadObjectList();
				} catch (RuntimeException $e) {
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}		
				
				// get resource info for the selected resource
				$sql = 'SELECT * FROM #__sv_apptpro3_resources where id_resources = '.$resource;
				try{
					$database->setQuery($sql);
					$res_detail = NULL;
					$res_detail = $database -> loadObject();
				} catch (RuntimeException $e) {
					echo JText::_('RS1_SQL_ERROR');
					logIt($e->getMessage(), "getSlots", "", "");
					return false;
				}		
				$gotSlots = true;
				if(sv_count_($slot_rows ) == 0) {
					$gotSlots = false;
				}
				
				// The problem now is that we won't know if there are no slots until we walk through the for loop below so we
				// do not know which top row to put in.
				// We will need to build the response as a big string then at the end we can tack the appropriate first option in.
		
				$actual_slots_available = 0;
		
				for($i=0; $i < sv_count_($slot_rows ); $i++) {
										
					$slot_row = $slot_rows[$i];
					$ok_to_process_slot = true;
					$k = 0;
					
					date_default_timezone_set($timezone_identifier);
					$time_adjusted_for_lead = time() + ($res_detail->min_lead_time * 60 * 60);						
					if(strtotime($startdate." ".$slot_row->non_display_timeslot_starttime) < $time_adjusted_for_lead){
						$ok_to_process_slot = false;
					} else {
						$ok_to_process_slot = true;
					}
					
					if(sv_count_($part_day_bookoffs) > 0){
						// need to check each slot to see if blocked by book-off
						if(blocked_by_bookoff($slot_row, $part_day_bookoffs)){
							$ok_to_process_slot = false;
						}
					}
					
					if($ok_to_process_slot){
						$k=0;
						if($res_detail->max_seats != 0){ // a limit has been specified
								$currentcount = getCurrentSeatCount($startdate, $slot_row->db_starttime_24.":00", $slot_row->endtime_24.":00", $res_detail->id_resources);
								if ($currentcount >= $res_detail->max_seats){
								
									// dev only
									//echo "<option value=''>".count_values($slot_row->timeorder, $booking_rows)."</option>";
								
									// IE does not support 'disabled', do not show this slot
									if($browser != "Explorer"){
										//echo "<option value=".$slot_row->starttime_24.",".$slot_row->endtime_24." style='color:cccccc' disabled='disabled'>".$slot_row->timeslot_starttime." - ".$slot_row->timeslot_endtime."</option>";
									}
								} else {
// to show slot end time, uncomment the line below
									$options .= "<option value=\"".$slot_row->starttime_24.",".$slot_row->endtime_24.",".$res_detail->free_booking."\">".$slot_row->timeslot_starttime." - ".$slot_row->timeslot_endtime;

// to NOT show slot end time, uncomment the line below.
//									$options .= "<option value=\"".$slot_row->starttime_24.",".$slot_row->endtime_24.",".$res_detail->free_booking."\">".$slot_row->timeslot_starttime;
									if($apptpro_config->show_available_seats == "Yes"){
										$adjusted_max_seats = getSeatAdjustments($startdate, $slot_row->timeslot_starttime, $slot_row->timeslot_endtime, $res_detail->id_resources, $res_detail->max_seats);								
										$options .= "  (".strval($res_detail->max_seats + $adjusted_max_seats - $currentcount).")";
										//$options .= "  (".strval($res_detail->max_seats - $currentcount).")";
									} 
									$options .="</option>";
									$actual_slots_available ++;
								}
						} else {
							// allow dupes
							$options .=  "<option value=\"".$slot_row->starttime_24.",".$slot_row->endtime_24.",".$res_detail->free_booking."\">".$slot_row->timeslot_starttime." - ".$slot_row->timeslot_endtime."</option>";
							$actual_slots_available ++;
						}
					}
					
					$k = 1 - $k; 
				}
			}
		} else {
			$actual_slots_available = 0;
		}
		
		$options .= "</select>";

		$ret_val = "<select name=\"timeslots\" id=\"timeslots\" style=\"width:auto\" class=\"sv_apptpro_request_dropdown\" onchange=\"set_starttime();selectTimeslotSimple();setDuration();calcTotal();\">";

		if($actual_slots_available == 0){
			$ret_val .= "<option value=\"00:00,00:00\" >".JText::_('RS1_INPUT_SCRN_NO_TIMESLOTS_AVAILABLE')."</option>";	
			
		} else {
			$ret_val .= "<option value=\"00:00,00:00\" >".JText::_('RS1_INPUT_SCRN_TIMESLOT_PROMPT')."</option>";		
		}		
		$ret_val .= $options;
		
		if($availability_only == 1){
				$ret_val = $actual_slots_available;
		}
		
	    return $ret_val;				
	}
?>