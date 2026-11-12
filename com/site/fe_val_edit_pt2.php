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

// This is the staff validation to ensure staff do not create booking conflicts when they edit a booking.

defined( '_JEXEC' ) or die( 'Restricted access' );

	include_once( JPATH_SITE."/components/com_rsappt_pro3/functions2.php" );

	function do_staff_edit_validation($request_id,$request_status,$name,$phone,$email,$resource,$startdate,$starttime,
		$enddate,$endtime,$booked_seats,$user_id){	
	
		//$err = 'Validation Failed:<br>';
		$err = JText::_('RS1_INPUT_SCRN_VALIDATION_FAILED');
		
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
	
		if ($resource != "0" AND $resource != "-1" AND $request_status == "accepted"){ // user has seleted a timeslot, validate it
			// get 'current time' as server time adjusted for Joomla time zone
			$CONFIG = new JConfig();
			$offset = $CONFIG->offset;
			$current_datetime = date_create('now', timezone_open($CONFIG->offset));
			$appointment_start_datetime = date_create($startdate." ".$starttime, timezone_open($CONFIG->offset));
	
			// Some documented PHP datetime functions seem to be often missing from php5 
			// convert to 'date' ($current_date_time) rather than 'datetime' ($current_datetime)
			//echo strtotime(date_format($current_datetime, 'Y-m-d H:i:s'))."<br>";
			$current_date_time = strtotime(date_format($current_datetime, 'Y-m-d H:i:s'));
			$appointment_start = strtotime(date_format($appointment_start_datetime, 'Y-m-d H:i:s'));
			
			$first_allowable_start = strtotime("+".strval($res_detail->min_lead_time)." hour", $current_date_time);
	
			// if request_status = 'accepted', check max seats not exceeded
			// first just see if this booking's seats > the resource's
			if($resource != "-1"){
				if($res_detail->max_seats > 1 ){
					$adjusted_max_seats = getSeatAdjustments($startdate, $starttime, $endtime, $resource, $res_detail->max_seats);
					if($booked_seats > ($res_detail->max_seats + $adjusted_max_seats)){
						$err = $err.JText::_('RS1_ADMIN_SCRN_EXCEED_SEATS')."<br>";
					} else {	
						// now check to see if there are other bookings and if so how many total seats are booked.
						$currentcount = getCurrentSeatCount($startdate, $starttime, $endtime, $resource, $request_id);
						if ($currentcount + $booked_seats > ($res_detail->max_seats + $adjusted_max_seats)){
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
			// Check for no overlap
			$mystartdatetime = "STR_TO_DATE('".$startdate ." ". $starttime ."', '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
			$myenddatetime = "STR_TO_DATE('".$enddate ." ". $endtime ."', '%Y-%m-%d %T')- INTERVAL 1 SECOND";
			$sql = "SELECT COUNT(*) from #__sv_apptpro3_requests "
			." WHERE id_requests != ".$request_id. " AND (resource = '". $resource ."')"
			." AND (request_status = 'accepted' or request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"").")"
			." AND ((". $mystartdatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
			." OR (". $myenddatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
			." OR (STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime .")"
			." OR (STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime ."))";
			//	echo $sql; 
			//	exit();
			try{
				$database->setQuery( $sql );
				$overlapcount = $database->loadResult();
			} catch (RuntimeException $e) {
				logIt($e->getMessage(), "fe_val", "", "");
				$err = $err.JText::_('RS1_SQL_ERROR');
			}		
			if ($overlapcount >= $res_detail->max_seats && $res_detail->max_seats > 0 ){
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
	
		}
		
	
		if($err == JText::_('RS1_INPUT_SCRN_VALIDATION_FAILED')){
			$err = JText::_('RS1_INPUT_SCRN_VALIDATION_OK');
		}
	
		return $err;
	}

?>