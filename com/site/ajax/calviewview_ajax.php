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

 
// ajax routine to build a month view
defined( '_JEXEC' ) or die( 'Restricted access' );
	header('Content-Type: text/html; charset=utf-8'); 
	header("Cache-Control: no-cache, must-revalidate");
	//A date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	include( JPATH_SITE."/components/com_rsappt_pro3/svcalendarview.php" );
	$jinput = JFactory::getApplication()->input;

	// get config stuff
	$database = JFactory::getDBO(); 
	$sql = 'SELECT * FROM #__sv_apptpro3_config';
	try{
		$database->setQuery($sql);
		$apptpro_config = NULL;
		$apptpro_config = $database -> loadObject();
	} catch (RuntimeException $e) {
		logIt($e->getMessage(), "calview_ajax", "", "");
		echo JText::_('RS1_SQL_ERROR');
		return false;
	}		


	$cal = new SVCalendarView;
	//echo $cal->getCurrentMonthView();
	$d = getdate(time());
	$view = $jinput->getString('calendarview_view');
	$day = $jinput->getString('day');
	$month = $jinput->getString('month');
	$year = $jinput->getString('year');
	$single_res = $jinput->getInt('single_res');
	$single_cat = $jinput->getInt('single_cat');
	$user = $jinput->getString('user');
	$status = $jinput->getString('status');
	$payment_status = $jinput->getString('payment_status');
	$weekoffset = $jinput->getString('weekoffset');
	$user_search = $jinput->getString('user_search');
	$mobile = false;
	$show_seat_totals = $jinput->getString('showSeatTotals');
	$Itemid = $jinput->getInt('Itemid');
	$Menuid = $jinput->getInt('Menuid');
	
	$printer_view = $jinput->getString('printer', 'No');
	
	$booking_screen = $jinput->getString('booking_screen', 'gad');
	
	$service_resource_ids = $jinput->getString('srv_res_ids', "");
	
	
	if ($month == ""){
		$month = $d["mon"];
	}
	
	if ($year == ""){
		$year = $d["year"];
	}
	
	if ($weekoffset == ""){
		$weekoffset = 0;
	}
	
	global $context;
	$mainframe = JFactory::getApplication();
	//$mainframe->setUserState('front_desk.front_desk_view', $view);
	$mainframe->setUserState('front_desk.front_desk_resource_filter', $single_res);
	$mainframe->setUserState('front_desk.front_desk_category_filter', $single_cat);
	//$mainframe->setUserState('front_desk.day', $day);
	//$mainframe->setUserState('front_desk.month', $month);
	//$mainframe->setUserState('front_desk.year', $year);
	
	$mainframe->setUserState('calendar_view_cur_week_offset', $weekoffset);
	$mainframe->setUserState('calendar_view_cur_day', $day);
	$mainframe->setUserState('calendar_view_cur_month', $month);
	$mainframe->setUserState('calendar_view_cur_year', $year);
	
	$cal->setWeekStartDay(intval($apptpro_config->popup_week_start_day));
	if($mobile){
		$cal->setIsMobile(true);
	}
	
	$cal->setShowSeatTotals($show_seat_totals);
	
	$cal->setItemid($Itemid);
	$cal->setMenuid($Menuid);
	
	$cal->setPrinterView($printer_view);

	if($service_resource_ids != ""){
		$cal->setServiceResourceIds($service_resource_ids);
	}
	switch($booking_screen){
		case "simple":
			$cal->setBookingScreen("booking_screen_simple");
			break;
		case "gad":
			$cal->setBookingScreen("booking_screen_gad");
			break;
		case "wizard":
			$cal->setBookingScreen("bookingscreengadwiz");
			break;
	}
	
	$cal->setResourceFilter($single_res);
	$cal->setCategoryFilter($single_cat);
//			$cal->setResAdmin($user);
	echo $cal->getMonthView($month, $year);
	
	
	exit;
	
	?>