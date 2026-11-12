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


	header('Content-Type: text/xml'); 
	header("Cache-Control: no-cache, must-revalidate");
	//A date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	
	$jinput = JFactory::getApplication()->input;
	
//echo "\"".$jinput->getString('ts')."\"";
//exit;
	$startdate = $jinput->getString('startdate');
	$starttime = $jinput->getString('starttime');
	$enddate = $jinput->getString('enddate');
	$endtime = $jinput->getString('endtime');
	$resource = $jinput->getInt('res_id');
	$retval = "\"".$jinput->getString('ts')."\"|";
	
	// is cancellation_id valid
	$database = JFactory::getDBO(); 
	$sql = "SELECT name, booked_seats FROM #__sv_apptpro3_requests ".
	" WHERE resource = '".$resource."' ".
	" AND request_status = 'accepted' ".
	" AND startdate = '".$startdate."' ".
	" AND enddate = '".$enddate."' ".
	" AND starttime = '".$starttime."' ".
	" AND endtime = '".$endtime."'";
	try{
		$database->setQuery($sql);
		$rows = $database->loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "who_booked", "", "");
		echo JText::_('RS1_SQL_ERROR');
		exit;
	}		
	if (sv_count_($rows) == 0){
		echo "";
		exit;
	}
	
	foreach($rows as $row){
		$retval .= str_replace("'","`",$row->name)."(".$row->booked_seats.") ";
	}
	echo $retval;
	exit;

?>