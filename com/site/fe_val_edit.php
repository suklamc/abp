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

	include_once( JPATH_SITE."/components/com_rsappt_pro3/fe_val_edit_pt2.php" );
	$jinput = JFactory::getApplication()->input;

	//$err = 'Validation Failed:<br>';
	$err = JText::_('RS1_INPUT_SCRN_VALIDATION_FAILED');

	
	// recives the user's selected resource and date
	$request_id = $jinput->getInt('request', "-1");
	$request_status = $jinput->getWord('request_status', "");
	$name = $jinput->getString('name');
	$phone = $jinput->getString('phone');
	$email = $jinput->getString('email', "-1");

	$resource = $jinput->getInt('resource');
	$startdate = $jinput->getString('startdate');
	$starttime = $jinput->getString('starttime');
	$enddate = $jinput->getString('enddate');
	$endtime = $jinput->getString('endtime');	
	$booked_seats = $jinput->getInt('booked_seats', 1);	
	$user_id = $jinput->getInt('user_id', "");	

	$err = do_staff_edit_validation($request_id,$request_status,$name,$phone,$email,$resource,$startdate,$starttime,
		$enddate,$endtime,$booked_seats,$user_id);

	if($err == JText::_('RS1_INPUT_SCRN_VALIDATION_FAILED')){
		$err = JText::_('RS1_INPUT_SCRN_VALIDATION_OK');
	}

	echo $err;
	exit;	
	

?>