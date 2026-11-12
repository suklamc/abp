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


	header('Content-Type: text/HTML'); 
	header("Cache-Control: no-cache, must-revalidate");
	//A date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	
	$database = JFactory::getDBO();

	$jinput = JFactory::getApplication()->input;	
	$search_string = $jinput->getString("src_for", "-1");	
	$search_string = str_replace("~", "'",$search_string);
	$safe_search_string = '%' . $database->escape( $search_string, true ) . '%' ;
	
	$sql = 'SELECT id,name,username FROM #__users WHERE block = 0 AND (name LIKE '.$database->quote( $safe_search_string, false ).' OR username LIKE '.$database->quote( $safe_search_string, false ).') '.
		' AND id NOT IN (select user_id from #__sv_apptpro3_user_credit where credit_type=\'uc\') order by name';
	try{
		$database->setQuery($sql);
		$result_list = NULL;
		$result_list = $database -> loadObjectList();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "be_ajax_user_search", "", "");
		echo JText::_('RS1_SQL_ERROR').$e->getMessage();
		exit;
	}		

	if(sv_count_($result_list) == 0){
		echo JText::_('RS1_ADMIN_USER_SEARCH_NONE_FOUND').$search_string;
	} else {
		echo "<select id='thename' name='thename' size=".sv_count_($result_list)." onclick='goBack()'>";
		for($i=0; $i < sv_count_($result_list ); $i++) {
			$user_row = $result_list[$i];
			echo "<option value='".$user_row->id."|".$user_row->name."'>".$user_row->name." (".$user_row->username.")</opiton>";			
		}	
		echo "</select>";
		echo "<br/>".JText::_('RS1_ADMIN_USER_SEARCH_PICK_ONE');
	}

	exit;
	

?>