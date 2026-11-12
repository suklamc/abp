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

	$jinput = JFactory::getApplication()->input;

	// recives the user's selected resource and date
	$startdate = $jinput->getString('startdate');
	$starttime = $jinput->getString('starttime');
	$enddate = $jinput->getString('enddate');
	$endtime = $jinput->getString('endtime');
	$resource = $jinput->getInt('res_id');
	$gap = $jinput->getInt('gap', 0);
	
	$retval = "";
	
	// check for conflict in this timeslot
	$database = JFactory::getDBO(); 
	$sql = 'SELECT max_seats FROM #__sv_apptpro3_resources WHERE id_resources = '.$resource;
	try{
		$database->setQuery($sql);
		$res_max_seats = NULL;
		$res_max_seats = $database -> loadresult();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_overlap", "", "");
			echo JText::_('RS1_SQL_ERROR');
			exit;
		}		
	if($res_max_seats > 1 || $res_max_seats == 0){
		// do not do overlap check for Max Seats > 1
			echo $retval; 
			exit;	
	}
	
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

	$mystartdatetime = "STR_TO_DATE('".$startdate ." ". $starttime ."', '%Y-%m-%d %T')+ INTERVAL 1 SECOND";
	if($gap == 0 ){
		$myenddatetime = "STR_TO_DATE('".$enddate ." ". $endtime ."', '%Y-%m-%d %T')- INTERVAL 1 SECOND";
	} else {	
		$temp = abs(($gap*60)-1);
		$myenddatetime = "STR_TO_DATE('".$enddate ." ". $endtime ."', '%Y-%m-%d %T')+ INTERVAL ".$temp." SECOND";
	}
	$sql = "select *, ";
		if($apptpro_config->timeFormat == '12'){							
			$sql .=" DATE_FORMAT(endtime, '%l:%i %p') as display_endtime, ";
			$sql .=" DATE_FORMAT(DATE_ADD( CONCAT(enddate, ' ', endtime), INTERVAL ".$gap." MINUTE), '%l:%i %p') as display_endtime_with_gap ";
		} else {
			$sql .=" DATE_FORMAT(endtime, '%H:%i') as display_endtime, ";
			$sql .=" DATE_FORMAT(DATE_ADD( CONCAT(enddate, ' ', endtime), INTERVAL ".$gap." MINUTE), '%H:%i') as display_endtime_with_gap ";
		}	
	// add gap to endtime
	$sql .= ", DATE_FORMAT(DATE_ADD( CONCAT(enddate, ' ', endtime), INTERVAL ".$gap." MINUTE), '%H:%i:%s') as endtime_with_gap ";

	$sql .=	" from #__sv_apptpro3_requests "
		." where (resource = '". $resource ."')"
		." and (request_status = 'accepted' or request_status = 'pending' ".($apptpro_config->block_new=="Yes"?"OR request_status='new'":"")." )"
//		." and ((". $mystartdatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $mystartdatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
//		." or (". $myenddatetime ." >= STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') and ". $myenddatetime ." <= STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T'))"
//		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(startdate, '%Y-%m-%d') , DATE_FORMAT(starttime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime .")"
//		." or (STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') >= ". $mystartdatetime ." and STR_TO_DATE(CONCAT(DATE_FORMAT(enddate, '%Y-%m-%d') , DATE_FORMAT(endtime, ' %T')), '%Y-%m-%d %T') <= ". $myenddatetime ."))";

// simpler..
//		." and ((". $mystartdatetime ." >= CONCAT(startdate, ' ', starttime) and ". $mystartdatetime ." <= CONCAT(enddate, ' ', endtime))"
//		." or (". $myenddatetime ." >= CONCAT(startdate, ' ', starttime) and ". $myenddatetime ." <= CONCAT(enddate, ' ', endtime))"
//		." or ( CONCAT(startdate, ' ', starttime) >= ". $mystartdatetime ." and CONCAT(startdate, ' ', starttime) <= ". $myenddatetime ." )"
//		." or ( CONCAT(enddate, ' ', endtime) >= ". $mystartdatetime ." and CONCAT(enddate, ' ', endtime) <= ". $myenddatetime ."))";

		." and ((". $mystartdatetime ." >= CONCAT(startdate, ' ', starttime) and ". $mystartdatetime ." <= DATE_ADD( CONCAT(enddate, ' ', endtime), INTERVAL ".$gap." MINUTE))"
		." or (". $myenddatetime ." >= CONCAT(startdate, ' ', starttime) and ". $myenddatetime ." <= CONCAT(enddate, ' ', endtime))"
		." or ( CONCAT(startdate, ' ', starttime) >= ". $mystartdatetime ." and CONCAT(startdate, ' ', starttime) <= ". $myenddatetime ." )"
//		." or ( DATE_ADD( CONCAT(enddate, ' ', endtime), INTERVAL ".$gap." MINUTE) >= ". $mystartdatetime ." and DATE_ADD( CONCAT(enddate, ' ', endtime), INTERVAL ".$gap." MINUTE) <= ". $myenddatetime ."))";
		." or ( DATE_ADD( STR_TO_DATE(CONCAT(enddate, ' ', endtime), '%Y-%m-%d %T'), INTERVAL ".$gap." MINUTE) >= ". $mystartdatetime ." and DATE_ADD( STR_TO_DATE(CONCAT(enddate, ' ', endtime), '%Y-%m-%d %T'), INTERVAL ".$gap." MINUTE) <= ". $myenddatetime ."))";
	try{
		$database->setQuery($sql);
		$overlaps = $database->loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_overlap", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	if (sv_count_($overlaps) > 0){
		// need to determine best starttime for new booking, either at timeslot start or after end of overlap
		// if an existing booking ends in this timeslot, make that the start time.
		foreach($overlaps as $overlap){
			if($gap == "0"){
				if($overlap->endtime >= $starttime and $overlap->endtime <= $endtime){
					$retval = $overlap->display_endtime."|".$overlap->endtime;
				}
			} else {				
				if($overlap->endtime_with_gap >= $starttime and $overlap->endtime_with_gap <= $endtime){
					$retval = $overlap->display_endtime_with_gap."|".$overlap->endtime_with_gap;
				}
			}	
		}		
	}
	echo $retval; 
	exit;	

?>