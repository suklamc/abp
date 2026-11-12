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


	header('Content-Type: text/xml'); 
	header("Cache-Control: no-cache, must-revalidate");
	//A date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	
	$jinput = JFactory::getApplication()->input;
	
	// recives the user's selected resource and date
	$cancellation_id = $jinput->getString('cancellation_id');
	$browser = $jinput->getString('browser');
	$userDateTime = $jinput->getString('userDateTime');

	// is cancellation_id valid
	$database = JFactory::getDBO(); 
	$sql = "SELECT id_requests FROM #__sv_apptpro3_requests WHERE cancellation_id='".$database->escape($cancellation_id)."'";
	try{
		$database->setQuery($sql);
		$rows = $database->loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_delete", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	if (sv_count_($rows) == 0){
		echo JText::_('RS1_INPUT_SCRN_CANCEL_CODE_INVALID');
		exit;
	}
	if (sv_count_($rows) > 1){
		echo 'Error: More that one booking with that code. Cannot delete!';
		exit;
	}

	// get config info
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_delete", "", "");
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
		logIt($e->getMessage(), "fe_delete", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	if(strtotime($cancel_limit) < strtotime($userDateTime)){
		// too late
		$msg = buildMessage($rows[0]->id_requests, "booking_too_close_to_cancel", "No", "", "Yes");		
		echo $msg;
		exit;
	}

	// First delete calendar record for this request if one exists
	if($apptpro_config->which_calendar == "JEvents"){
		$sql = "DELETE FROM `#__events` WHERE INSTR(extra_info, '[req id:". $rows[0]->id_requests ."]')>0";
	} else if($apptpro_config->which_calendar == "JCalPro"){
		$sql = "DELETE FROM `#__jcalpro_events` WHERE INSTR(description, '[req id:". $rows[0]->id_requests ."]')>0";
	} else if($apptpro_config->which_calendar == "EventList"){
		$sql = "DELETE FROM `#__eventlist_events` WHERE INSTR(datdescription, '[req id:". $rows[0]->id_requests ."]')>0";
	}	
	try{
		$database->setQuery($sql);
		$database->execute();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_delete", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// zap it
	$sql = "UPDATE #__sv_apptpro3_requests SET request_status = 'deleted', ".
	"admin_comment = CONCAT( admin_comment, ' *** Deleted by user ***') ". 
	"WHERE cancellation_id='".$database->escape($cancellation_id)."'";
	$database->setQuery($sql);
	if(!$database->execute()){
		echo $database -> stderr();
		return false;
	}
	
	echo JText::_('RS1_INPUT_SCRN_DELETE_MESSAGE');
	exit;	
	

?>