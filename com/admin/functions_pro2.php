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

//	include( JPATH_SITE."/administrator/components/com_rsappt_pro3/sendmail.php" );




function getCurrentSeatCount($startdate, $starttime, $endtime, $resource, $exclude_request=-1){

	$database = JFactory::getDBO();
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_overlap", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		

	$sql = "SELECT Sum(booked_seats) FROM #__sv_apptpro3_requests ".
		" WHERE ".
		" id_requests != ".$exclude_request." AND ".
		" startdate = '".$startdate."' AND ".
		" starttime = '".$starttime."' AND ".
		" endtime = '".$endtime."' AND ".
		" resource = ".$resource." AND ".
		"(request_status = 'accepted' or request_status = 'pending'".($apptpro_config->block_new=="Yes"?" OR request_status='new'":"").") AND ".
		" booked_seats > 0;";
	try{
		$database->setQuery( $sql );
		$currentcount = $database->loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_func2", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	return $currentcount;
}

function getCurrentTotalSeatCount($startdate, $starttime, $endtime, $resource, $exclude_request=-1){
	// count across ALL resources for this timeslot
	$database = JFactory::getDBO();
	$sql = "SELECT Sum(booked_seats) FROM #__sv_apptpro3_requests ".
		" WHERE ".
		" id_requests != ".$exclude_request." AND ".
		" startdate = '".$startdate."' AND ".
		" starttime = '".$starttime."' AND ".
		" endtime = '".$endtime."' AND ".
//		" resource = ".$resource." AND ".
		"(request_status = 'accepted' or request_status = 'pending') AND ".
		" booked_seats > 0;";
	try{
		$database->setQuery( $sql );
		$currentcount = $database->loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_func2", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
	return $currentcount;
}

function logIt($err, $err_object = "", $err_severity = "", $sql = ""){
	$database = JFactory::getDBO();
	$errsql = "insert into #__sv_apptpro3_errorlog (description, err_object, err_severity, sql_data) ".
		" values('".$database->escape(substr($err,0))."', '".$err_object."', '".$err_severity."', '".$database->escape(substr($sql,0))."')";
	try{
		$database->setQuery($errsql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_func2", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		
}

function tz_offset_to_string($tzoffset){
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
	if(sv_count_($offset_hr_min) > 1){
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
	return $offset_hour.$offset_min;
}	

function translated_status($in){
	switch($in){
		case 'new': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NEW');
			break;
		}
		case 'accepted': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_ACCEPTED');
			break;
		}
		case 'pending': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_PENDING');
			break;
		}
		case 'declined': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_DECLINED');
			break;
		}
		case 'canceled': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_CANCELED');
			break;
		}
		case 'no_show': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NO_SHOW');
			break;
		}
		case 'attended': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_ATTENDED');
			break;
		}
		case 'deleted': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_DELETED');
			break;
		}
		case 'completed': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_COMPLETED');
			break;
		}
		case 'paid': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_PAID');
			break;
		}
		case 'timeout': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_PAYPAL_TIMEOUT');
			break;
		}
		case 'na': {
			return JText::_('RS1_ADMIN_SCRN_REQUEST_STATUS_NA');
			break;
		}
		case 'refunded': {
			return JText::_('RS1_ADMIN_SCRN_PAY_STATUS_REFUNDED');
			break;
		}
		case 'invoiced': {
			return JText::_('RS1_ADMIN_SCRN_PAY_STATUS_INVOICED');
			break;
		}
		case 'to_be_invoiced': {
			return JText::_('RS1_ADMIN_SCRN_PAY_STATUS_TO_BE_INVOICED');
			break;
		}
		case 'DayAndTime':{
			return JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_DAY_TIME');
			break;
		}
		case 'DayOnly':{
			return JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_DAY_ONLY');
			break;
		}
		case 'TimeOnly':{
			return JText::_('RS1_ADMIN_SEAT_ADJUSTMENT_TIME_ONLY');
			break;
		}
		case 'resource':{
			return JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_TYPE_RESOURCE');
			break;
		}
		case 'service':{
			return JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_TYPE_SERVICE');
			break;
		}
		case 'extra':{
			return JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_TYPE_EXTRA');
			break;
		}
		case 'seat':{
			return JText::_('RS1_ADMIN_SCRN_RATE_OVERRIDE_DETAIL_SEAT');
			break;
		}
		
		
	}
}

function setSessionStuff($screen){

	$session =JFactory::getSession();
	$jinput = JFactory::getApplication()->input;

	switch($screen)	{

		case "request":
			$session->set("current_tab", 0);
			$session->set("req_filter_order", $jinput->getString('req_filter_order', 'startdate'));
			$session->set("req_filter_order_Dir", $jinput->getWord('req_filter_order_Dir', 'asc'));
			$session->set("status_filter", $jinput->getString('status_filter', ''));
			$session->set("request_resourceFilter", $jinput->getString('request_resourceFilter', ''));
			$session->set("startdateFilter", $jinput->getString('startdateFilter', ''));
			$session->set("enddateFilter", $jinput->getString('enddateFilter', ''));
			$session->set("categoryFilter", $jinput->getString('categoryFilter', ''));

			$session->set("filter_order", $jinput->getString('filter_order', 'startdate'));
			$session->set("filter_order_Dir", $jinput->getWord('filter_order_Dir', 'asc'));
			break;

		case "resource":
			$session->set("current_tab", $jinput->getString('resources_tab'));
			$session->set("res_filter_order", $jinput->getString('res_filter_order', 'name'));
			$session->set("res_filter_order_Dir", $jinput->getWord('res_filter_order_Dir', 'asc'));
			break;
	
		case "service":
			$session->set("current_tab", $jinput->getString('services_tab'));
			$session->set("srv_filter_order", $jinput->getString('srv_filter_order', 'name'));
			$session->set("srv_filter_order_Dir", $jinput->getWord('srv_filter_order_Dir', 'asc'));
			$session->set("resource_id_Filter", $jinput->getString( 'resource_id_Filter' ));
			break;

		case "timeslot":
			$session->set("current_tab", $jinput->getString('timeslots_tab'));
			$session->set("ts_filter_order", $jinput->getString('ts_filter_order', 'name'));
			$session->set("ts_filter_order_Dir", $jinput->getWord('ts_filter_order_Dir', 'asc'));
			$session->set("resource_id_FilterTS", $jinput->getString( 'resource_id_FilterTS' ));
			$session->set("day_numberFilter", $jinput->getString( 'day_numberFilter' ));
			break;

		case "bookoff":
			$session->set("current_tab", $jinput->getString('bookoffs_tab'));
			$session->set("bo_filter_order", $jinput->getString('bo_filter_order', 'name'));
			$session->set("bo_filter_order_Dir", $jinput->getWord('bo_filter_order_Dir', 'asc'));
			$session->set("resource_id_FilterBO", $jinput->getString( 'resource_id_FilterBO' ));
			break;

		case "paypal":
			$session->set("current_tab", $jinput->getString('paypal_tab'));
			$session->set("pp_filter_order", $jinput->getString('pp_filter_order', 'stamp'));
			$session->set("pp_filter_order_Dir", $jinput->getWord('pp_filter_order_Dir', 'desc'));
			break;

		case "coupons":
			$session->set("current_tab", $jinput->getString('coupons_tab'));
			$session->set("coup_filter_order", $jinput->getString('coup_filter_order', 'stamp'));
			$session->set("coup_filter_order_Dir", $jinput->getWord('coup_filter_order_Dir', 'desc'));
			break;

		case "front_desk":
			$session->set("front_desk_view", $jinput->getString('view'));
			$session->set("front_desk_resource_filter", $jinput->getString('resource_filter'));
			$session->set("front_desk_status_filter", $jinput->getString('status_filter'));
			$session->set("front_desk_cur_month", $jinput->getString('cur_month', ''));
			$session->set("front_desk_cur_year", $jinput->getString('cur_year', ''));
			$session->set("front_desk_cur_week_offset", $jinput->getString('cur_week_offset', ''));
			$session->set("front_desk_cur_day", $jinput->getString('cur_day', ''));
			$session->set("front_desk_user_search", $jinput->getString('user_search', ''));
			break;

	}		
}

function encrypt_decrypt($action, $string) {
	// removed as it was causing the JED checker to fail :-(
   return $string;
}

function recurse_copy($src, $dst) {
	try{
	  $dir = opendir($src);
	  $result = ($dir === false ? false : true);
	
	  if ($result !== false) {
		$result = @mkdir($dst);
	
		if ($result === true) {
		  while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' ) && $result) { 
			  if ( is_dir($src . '/' . $file) ) { 
				$result = recurse_copy($src . '/' . $file,$dst . '/' . $file); 
			  }     else { 
				$result = copy($src . '/' . $file,$dst . '/' . $file); 
			  } 
			} 
		  } 
		  closedir($dir);
		}
	  }
	} catch (Exception $e) {
	    echo 'Caught exception: ',  $e->getMessage(), "\n";
	}		

  return $result;
}
?>