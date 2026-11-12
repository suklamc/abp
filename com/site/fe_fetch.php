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


	header('Content-Type: text/html; charset=utf-8'); 
	header("Cache-Control: no-cache, must-revalidate");
	//A date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	
	$jinput = JFactory::getApplication()->input;
	
	// recives the user's selected resource and date
	$id = $jinput->getInt('id');
	$browser = $jinput->getString('browser');
	$fd_gad = $jinput->getString('fd_gad', '0');
	
	$retval = "";
	
	// check caller is logged in 
	$user = JFactory::getUser();
	if($user->guest){
		exit;
	}
	// does the user have elevated priv
//	if($fd_gad == "0"){
//		if($user->usertype != "Author" && $user->usertype != "Editor" && $user->usertype != "Publisher"){ 	
//			exit;
//		}	
//	}
	
	// get user info
	$database = JFactory::getDBO(); 
	$sql = "SELECT * FROM #__users WHERE id='".$id."'";
	try{
		$database->setQuery($sql);
		$row = $database->loadObjectList();
		} catch (RuntimeException $e) {
			logIt($e->getMessage(), "fe_fetch", "", "");
			echo JText::_('RS1_SQL_ERROR');
			return false;
		}		

	if(sv_count_($row)>0){
		$retval = $row[0]->name."|".$row[0]->email;
	} else {
		$retval = "|";
	}


	// check to see if phone is mapped to a cb profile
	// get config info
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_fetch", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		

	// get user credit
	$sql = 'SELECT balance FROM #__sv_apptpro3_user_credit WHERE user_id = '.$id;
	try{
		$database->setQuery($sql);
		$user_credit = NULL;
		$user_credit = $database -> loadResult();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_fetch", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
	if($user_credit != NULL){
		$retval .= "|".$user_credit;
	} else {
		$retval .= "|0";
	}

	if($apptpro_config->phone_cb_mapping != ""){
		$phone = getCBdata($apptpro_config->phone_cb_mapping, $id);
		$retval .= "~phone|".$phone;
	}
	if($apptpro_config->phone_js_mapping != ""){
		$phone = getJSdata($apptpro_config->phone_js_mapping, $id);
		$retval .= "~phone|".$phone;
	}
	if($apptpro_config->phone_profile_mapping != ""){
		$phone = getProfiledata($apptpro_config->phone_profile_mapping, $id);
		$retval .= "~phone|".$phone;
	}



	// get udfs and see if any are mapped to cb profile
	// As the reource may or maynot be selected at this time, only non-resource specific udfs cam be mapped.
	$sql = 'SELECT * FROM #__sv_apptpro3_udfs WHERE published=1 AND scope = "" ORDER BY ordering';
	
	// If you require resource specific UDF mapping on the Staff booking screen, you can try un-commenting the $sql line below 
	// and instructing your staff to select the resource BEFORE they select the user. 
	// This seems to work on some site but not on others(??)
	//$sql = 'SELECT * FROM #__sv_apptpro3_udfs WHERE published=1 ORDER BY ordering';
		
	try{
		$database->setQuery($sql);
		$udf_rows = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "fe_fetch", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		
		

	$k = 0;
	for($i=0; $i < sv_count_($udf_rows ); $i++) {
		$udf_row = $udf_rows[$i];
		// if cb_mapping value specified, fetch the cb data
		if($udf_row->cb_mapping != ""){
			$udf_value = getCBdata($udf_row->cb_mapping, $id);
			$retval .= "~user_field".$i."_value|".$udf_value;
		}
		if($udf_row->js_mapping != ""){
			$udf_value = getJSdata($udf_row->js_mapping, $id);
			$retval .= "~user_field".$i."_value|".$udf_value;
		}
		if($udf_row->profile_mapping != ""){
			$udf_value = getProfiledata($udf_row->profile_mapping, $id);
			$retval .= "~user_field".$i."_value|".$udf_value;
		}
		$k = 1 - $k; 
	} 

	
	echo $retval; 

	exit;	
	

?>